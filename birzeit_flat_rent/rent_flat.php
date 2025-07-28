<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

// Start session at the very beginning before any checks
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = "Rent Flat - Birzeit Flat Rent";
require_once 'includes/database.inc.php';
require_once 'includes/functions.php';

// Get flat reference number from query string
$flat_ref_no = $_GET['ref'] ?? null;

if (!$flat_ref_no) {
    redirect('index.php'); // Redirect if no flat reference
}

// Include header before authentication check
require_once 'includes/header.php';

// --- Authentication Check ---
$is_customer = isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'customer';
$customer_id = $is_customer ? $_SESSION['user_id'] : null;
$auth_warning = '';

if (!$is_customer) {
    $auth_warning = "You must be logged in as a customer to rent a flat. Please <a href='login.php'>login</a> or <a href='register_customer_step1.php'>register</a> first.";
}

// --- Fetch Flat & Owner Details ---
$flat_details = null;
$owner_details = null;
$error_db = '';

try {
    $stmt = $pdo->prepare("
    SELECT f.*, u.user_id as owner_id, 
           u.full_name as owner_name,
           CONCAT_WS(', ', u.address_flat, u.address_street, u.address_city, u.address_postal) as owner_address,
           u.mobile as owner_mobile, 
           u.email as owner_email
    FROM flats f
    JOIN users u ON f.owner_id = u.user_id
    WHERE f.flat_ref_no = ?
    ");


    $stmt->execute([$flat_ref_no]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        $error_db = "Flat not found or is no longer available.";
    } else {
        $flat_details = [
            'flat_ref_no' => $result['flat_ref_no'],
            'location' => $result['location'],
            'address' => $result['address'],
            'bedrooms' => $result['bedrooms'],
            'bathrooms' => $result['bathrooms'],
            'size_sqm' => $result['size_sqm'],
            'rental_cost' => $result['rent_cost'], // ✅ Corrected column
            'status' => $result['status']
        ];

        $owner_details = [
            'id' => $result['owner_id'],
            'name' => $result['owner_name'],
            'address' => $result['owner_address'],
            'mobile' => $result['owner_mobile'],
            'email' => $result['owner_email']
        ];

        if ($flat_details['status'] !== 'available') {
            $error_db = "This flat is not currently available for rent.";
        }
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage()); // TEMPORARY — reveals exact issue
}

// Fetch customer details if logged in
$customer_details = null;
if ($is_customer && !$error_db) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$customer_id]);
        $customer_details = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer_details) {
            $error_db = "Customer information not found.";
        }
    } catch (PDOException $e) {
        $error_db = "Database error: Unable to fetch customer details.";
        error_log("Database error in rent_flat.php: " . $e->getMessage());
    }
}

// --- Form Handling ---
$confirmation_details = null;
$payment_required = false;
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && $is_customer && !$error_db) {
    if (isset($_POST['submit_period'])) {
        $rental_start_date = $_POST['rental_start_date'] ?? '';
        $rental_end_date = $_POST['rental_end_date'] ?? '';

        $start_timestamp = strtotime($rental_start_date);
        $end_timestamp = strtotime($rental_end_date);
        $current_timestamp = strtotime(date('Y-m-d'));

        if ($start_timestamp < $current_timestamp) {
            $error_message = "Error: Start date cannot be in the past.";
        } elseif ($end_timestamp <= $start_timestamp) {
            $error_message = "Error: End date must be after start date.";
        } else {
            try {
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as count FROM rentals 
                    WHERE flat_ref_no = ? 
                    AND status = 'active'
                    AND (
                        (? BETWEEN rental_start_date AND rental_end_date) OR
                        (? BETWEEN rental_start_date AND rental_end_date) OR
                        (rental_start_date BETWEEN ? AND ?) OR
                        (rental_end_date BETWEEN ? AND ?)
                    )
                ");
                $stmt->execute([
                    $flat_ref_no, 
                    $rental_start_date, $rental_end_date,
                    $rental_start_date, $rental_end_date,
                    $rental_start_date, $rental_end_date
                ]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result['count'] > 0) {
                    $error_message = "Error: This flat is already rented for part or all of the selected period.";
                } else {
                    $payment_required = true;
                    $days = round(($end_timestamp - $start_timestamp) / (60 * 60 * 24));
                    $daily_rate = $flat_details['rental_cost'];
                    $total_rent = $days * $daily_rate;

                    $confirmation_details = [
                        'rental_start_date' => $rental_start_date,
                        'rental_end_date' => $rental_end_date,
                        'total_rent' => $total_rent,
                        'rental_days' => $days,
                        'customer_name' => $customer_details['full_name'],
                        'customer_id' => $customer_details['user_id']
                    ];
                }
            } catch (PDOException $e) {
                $error_message = "Database error: Unable to check flat availability.";
                error_log("Database error in rent_flat.php: " . $e->getMessage());
            }
        }

    } elseif (isset($_POST['confirm_rent'])) {
        $rental_start_date = $_POST['rental_start_date'] ?? '';
        $rental_end_date = $_POST['rental_end_date'] ?? '';
        $total_rent = $_POST['total_rent'] ?? 0;
        $cc_number = $_POST['cc_number'] ?? '';
        $cc_expiry = $_POST['cc_expiry'] ?? '';
        $cc_name = $_POST['cc_name'] ?? '';

        if (strlen($cc_number) !== 9 || !is_numeric($cc_number)) {
            $error_message = "Error: Credit card number must be exactly 9 digits.";
        } elseif (!preg_match('/(0[1-9]|1[0-2])\/([0-9]{2})/', $cc_expiry)) {
            $error_message = "Error: Credit card expiry must be in MM/YY format.";
        } elseif (empty($cc_name)) {
            $error_message = "Error: Name on card is required.";
        } else {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("
                    INSERT INTO rentals (flat_ref_no, customer_id, rental_start_date, rental_end_date, 
                                         total_rent, payment_date, payment_method, status)
                    VALUES (?, ?, ?, ?, ?, CURRENT_DATE(), 'credit_card', 'active')
                ");
                $stmt->execute([
                    $flat_ref_no,
                    $customer_id,
                    $rental_start_date,
                    $rental_end_date,
                    $total_rent
                ]);


                $stmt = $pdo->prepare("UPDATE flats SET status = 'rented' WHERE flat_ref_no = ?");
                $stmt->execute([$flat_ref_no]);

                $message = "Your flat {$flat_ref_no} has been rented by {$customer_details['full_name']} from {$rental_start_date} to {$rental_end_date}.";
                $stmt = $pdo->prepare("
                    INSERT INTO messages (recipient_id, sender_id, subject, content, related_link, is_read, date_sent)
                    VALUES (?, ?, ?, ?, ?, 0, CURRENT_TIMESTAMP)
                ");
                $stmt->execute([
                    $owner_details['id'],
                    $customer_id,
                    "Flat Rented: {$flat_ref_no}",
                    $message,
                    "view_messages.php?type=owner"
                ]);

                $message = "You have successfully rented flat {$flat_ref_no} from {$rental_start_date} to {$rental_end_date}. Total cost: $" . number_format($total_rent, 2) . ".";
                $stmt->execute([
                    $customer_id,
                    $owner_details['id'],
                    "Rental Confirmation: {$flat_ref_no}",
                    $message,
                    "view_rented.php"
                ]);

                $pdo->commit();

                echo "<div class='confirmation-message success'>
                    Flat successfully rented! You can collect the key from the owner: " . 
                    esc($owner_details['name']) . " (" . esc($owner_details['mobile']) . ").<br><br>
                    A confirmation message has been sent to your inbox.
                </div>";

                require_once 'includes/footer.php';
                ob_end_flush();
                exit;
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error_message = "Database error: Unable to complete rental transaction.";
                die("<strong>Database error:</strong> " . $e->getMessage());
                error_log("Database error in rent_flat.php: " . $e->getMessage());
            }
        }
    }
}
?>

<h2>Rent Flat: <?php echo $flat_details ? esc($flat_details['flat_ref_no']) : 'Not Found'; ?></h2>

<?php if (!empty($error_db)): ?>
    <div class="error-message">
        <?php echo esc($error_db); ?>
        <p><a href="index.php" class="btn btn-secondary">Return to Search</a></p>
    </div>
<?php elseif (!empty($auth_warning)): ?>
    <div class="warning-message">
        <?php echo $auth_warning; ?>
    </div>
    
    <!-- Show flat details in read-only mode for non-logged in users -->
    <div class="flat-preview">
        <h3>Flat Details (Preview)</h3>
        <p><strong>Reference Number:</strong> <?php echo esc($flat_details['flat_ref_no']); ?></p>
        <p><strong>Location:</strong> <?php echo esc($flat_details['location']); ?></p>
        <p><strong>Address:</strong> <?php echo esc($flat_details['address']); ?></p>
        <p><strong>Size:</strong> <?php echo esc($flat_details['size_sqm']); ?> sqm</p>
        <p><strong>Bedrooms:</strong> <?php echo esc($flat_details['bedrooms']); ?></p>
        <p><strong>Bathrooms:</strong> <?php echo esc($flat_details['bathrooms']); ?></p>
        <p><strong>Daily Rental Cost:</strong> $<?php echo esc($flat_details['rental_cost']); ?></p>
        
        <div class="login-prompt">
            <p>Please <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-primary">Login</a> or 
            <a href="register_customer_step1.php" class="btn btn-secondary">Register as Customer</a> to rent this flat.</p>
        </div>
    </div>
<?php else: ?>

    <?php if (!empty($error_message)): ?>
        <div class="error-message">
            <?php echo esc($error_message); ?>
        </div>
    <?php endif; ?>

    <?php if ($is_customer && $payment_required && $confirmation_details): ?>
        <!-- Step 2: Confirmation and Payment Form -->
        <form action="rent_flat.php?ref=<?php echo urlencode($flat_ref_no); ?>" method="POST" class="renting-form">
            <fieldset>
                <legend>Step 2: Confirm Details & Payment</legend>
                <h4>Review Rental Summary</h4>
                <p><strong>Flat Reference:</strong> <?php echo esc($flat_details['flat_ref_no']); ?></p>
                <p><strong>Location:</strong> <?php echo esc($flat_details['location']); ?></p>
                <p><strong>Address:</strong> <?php echo esc($flat_details['address']); ?></p>
                <p><strong>Size:</strong> <?php echo esc($flat_details['size_sqm']); ?> sqm</p>
                <p><strong>Bedrooms:</strong> <?php echo esc($flat_details['bedrooms']); ?></p>
                <p><strong>Bathrooms:</strong> <?php echo esc($flat_details['bathrooms']); ?></p>
                <p><strong>Owner:</strong> <?php echo esc($owner_details['name']); ?> (ID: <?php echo esc($owner_details['id']); ?>)</p>
                <p><strong>Renter:</strong> <?php echo esc($confirmation_details['customer_name']); ?> (ID: <?php echo esc($confirmation_details['customer_id']); ?>)</p>
                <p><strong>Rental Start Date:</strong> <?php echo esc($confirmation_details['rental_start_date']); ?></p>
                <p><strong>Rental End Date:</strong> <?php echo esc($confirmation_details['rental_end_date']); ?></p>
                <p><strong>Rental Duration:</strong> <?php echo esc($confirmation_details['rental_days']); ?> days</p>
                <p><strong>Daily Rate:</strong> $<?php echo esc($flat_details['rental_cost']); ?></p>
                <p><strong>Total Rent:</strong> $<?php echo esc($confirmation_details['total_rent']); ?></p>
                
                <h4>Enter Payment Details</h4>
                <div class="form-group">
                    <label for="cc_number">Credit Card Number (9 digits):</label>
                    <input type="text" id="cc_number" name="cc_number" pattern="\d{9}" title="Enter exactly 9 digits." required>
                </div>
                <div class="form-group">
                    <label for="cc_expiry">Expiry Date (MM/YY):</label>
                    <input type="text" id="cc_expiry" name="cc_expiry" placeholder="MM/YY" pattern="(0[1-9]|1[0-2])\/([0-9]{2})" title="Enter MM/YY format." required>
                </div>
                <div class="form-group">
                    <label for="cc_name">Name on Card:</label>
                    <input type="text" id="cc_name" name="cc_name" required>
                </div>

                <!-- Hidden fields to pass period details -->
                <input type="hidden" name="rental_start_date" value="<?php echo esc($confirmation_details['rental_start_date']); ?>">
                <input type="hidden" name="rental_end_date" value="<?php echo esc($confirmation_details['rental_end_date']); ?>">
                <input type="hidden" name="total_rent" value="<?php echo esc($confirmation_details['total_rent']); ?>">

                <div class="form-actions">
                    <button type="submit" name="confirm_rent" class="btn btn-primary">Confirm Rent</button>
                    <button type="button" onclick="window.location.href='rent_flat.php?ref=<?php echo urlencode($flat_ref_no); ?>'" class="btn btn-secondary">Back to Dates</button>
                </div>
            </fieldset>
        </form>

    <?php elseif ($is_customer): ?>
        <!-- Step 1: Enter Rental Period Form -->
        <form action="rent_flat.php?ref=<?php echo urlencode($flat_ref_no); ?>" method="POST" class="renting-form">
            <fieldset>
                <legend>Step 1: Select Rental Period</legend>
                <h4>Flat Details</h4>
                <p><strong>Reference Number:</strong> <?php echo esc($flat_details['flat_ref_no']); ?></p>
                <p><strong>Location:</strong> <?php echo esc($flat_details['location']); ?></p>
                <p><strong>Address:</strong> <?php echo esc($flat_details['address']); ?></p>
                <p><strong>Size:</strong> <?php echo esc($flat_details['size_sqm']); ?> sqm</p>
                <p><strong>Bedrooms:</strong> <?php echo esc($flat_details['bedrooms']); ?></p>
                <p><strong>Bathrooms:</strong> <?php echo esc($flat_details['bathrooms']); ?></p>
                <p><strong>Daily Rental Cost:</strong> $<?php echo esc($flat_details['rental_cost']); ?></p>
                <p><strong>Owner:</strong> <?php echo esc($owner_details['name']); ?> (ID: <?php echo esc($owner_details['id']); ?>)</p>
                
                <h4>Enter Desired Rental Period</h4>
                <div class="form-group">
                    <label for="rental_start_date">Rental Start Date:</label>
                    <input type="date" id="rental_start_date" name="rental_start_date" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="rental_end_date">Rental End Date:</label>
                    <input type="date" id="rental_end_date" name="rental_end_date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                </div>
                <div class="form-actions">
                    <button type="submit" name="submit_period" class="btn btn-primary">Calculate Rent & Proceed to Payment</button>
                    <button type="button" onclick="window.location.href='index.php'" class="btn btn-secondary">Cancel</button>
                </div>
            </fieldset>
        </form>
        
        <script>
        // Client-side validation to ensure end date is after start date
        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('rental_start_date');
            const endDateInput = document.getElementById('rental_end_date');
            
            startDateInput.addEventListener('change', function() {
                // Set minimum end date to be at least one day after start date
                const startDate = new Date(this.value);
                startDate.setDate(startDate.getDate() + 1);
                
                const year = startDate.getFullYear();
                const month = String(startDate.getMonth() + 1).padStart(2, '0');
                const day = String(startDate.getDate()).padStart(2, '0');
                
                endDateInput.min = `${year}-${month}-${day}`;
                
                // If current end date is before new minimum, reset it
                if (endDateInput.value && new Date(endDateInput.value) <= new Date(this.value)) {
                    endDateInput.value = `${year}-${month}-${day}`;
                }
            });
        });
        </script>
    <?php endif; ?>
<?php endif; ?>

<?php
require_once 'includes/footer.php';
// End output buffering and send content
ob_end_flush();
?>
