<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$page_title = "Owner Registration - Final Step - Birzeit Flat Rent";
require_once 'includes/database.inc.php'; // Need DB to save data and generate ID
require_once 'includes/functions.php';

// Ensure user has completed all previous steps
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['registration_data']) || !isset($_SESSION['registration_data']['username']) || 
    !isset($_SESSION['registration_data']['password']) || !isset($_SESSION['registration_data']['bank_name']) ||
    $_SESSION['registration_data']['user_type'] !== 'owner') {
    // Redirect back to step 1 if session data is missing or not for owner
    header('Location: register_owner_step1.php');
    exit;
}

// --- Confirmation Logic ---
$registration_data = $_SESSION['registration_data'] ?? [];
$owner_id = null;
$confirmation_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['confirm'])) {
    try {
        // Generate unique owner ID (format: O001, O002, etc.)
        // Fetch all existing owner IDs
            // Get all existing owner IDs
            $pdo->beginTransaction(); // ✅ START the transaction

            $stmt = $pdo->query("SELECT user_id FROM users WHERE user_id LIKE 'O%'");
            $existing_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Get all existing owner IDs
            // Get all existing owner IDs that start with 'O'
            // Fetch all owner IDs that start with 'O'
            $stmt = $pdo->query("SELECT user_id FROM users WHERE user_id LIKE 'O%'");
            $existing_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Extract numeric parts (e.g., 'O003' => 3)
            $used_numbers = array_map(function($id) {
                return (int)substr($id, 1);
            }, $existing_ids);
            
            // Ensure $used_numbers is an array
            if (!is_array($used_numbers)) {
                $used_numbers = [];
            }
            
            // Find the first unused ID
            $next_id = 1;
            while (in_array($next_id, $used_numbers)) {
                $next_id++;
            }
            
            $owner_id = 'O' . str_pad($next_id, 3, '0', STR_PAD_LEFT);




        // Use the password directly (plain text as per your login implementation)
        $password_hash = $registration_data['password'];
        
        // echo "<pre>";
        // print_r($registration_data);
        // echo "</pre>";

        $stmt = $pdo->prepare("
            INSERT INTO users (
                user_id, username, password_hash, user_type, national_id, 
                full_name, address_flat, address_street, address_city, address_postal,
                date_of_birth, email, mobile, status
            ) VALUES (
                ?, ?, ?, 'owner', ?, 
                ?, ?, ?, ?, ?, 
                ?, ?, ?, 'active'
            )
        ");
        
        $stmt->execute([
            $owner_id,
            $registration_data['username'],
            $password_hash,
            $registration_data['national_id'],
            $registration_data['name'],
            $registration_data['address_flat'] ?? null,
            $registration_data['address_street'] ?? null,
            $registration_data['address_city'] ?? null,
            $registration_data['address_postal'] ?? null,
            $registration_data['dob'],
            $registration_data['email'],
            $registration_data['mobile']
        ]);

        
        // Commit transaction
        $pdo->commit();
        
        // Set confirmation message
        $confirmation_message = "Registration successful! Welcome, " . esc($registration_data['name']) . ". Your unique Owner ID is: " . $owner_id;
        
        // Clear session data
        unset($_SESSION['registration_data']);
        
    } catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $error_message = "Database error: " . $e->getMessage(); // show detailed error
}


} elseif ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['cancel'])) {
    // User cancelled, clear session and redirect
    unset($_SESSION['registration_data']);
    header('Location: index.php');
    exit;
}

require_once 'includes/header.php';
?>

<h2>Owner Registration - Final Step: Confirmation</h2>

<?php if ($error_message): ?>
    <section class="error-message">
        <p><?php echo esc($error_message); ?></p>
        <p><button type="button" onclick="window.location.href='register_owner_step1.php'">Please try again</button></p>
    </section>
<?php elseif ($confirmation_message): ?>
    <section class="confirmation-message success">
        <p><?php echo $confirmation_message; ?></p>
        <p><button type="button" onclick="window.location.href='login.php'">You can now log in</button></p>
    </section>
<?php elseif (!empty($registration_data)): ?>
    <form action="register_owner_step3.php" method="POST" class="confirmation-form">
        <fieldset>
            <legend>Review Your Information</legend>
            <p>Please review the details below. Click 'Confirm' to complete your registration or 'Cancel' to discard.</p>
            
            <section class="review-details">
                <h3>Personal Information</h3>
                <p><strong>National ID:</strong> <?php echo esc($registration_data['national_id'] ?? 'N/A'); ?></p>
                <p><strong>Full Name:</strong> <?php echo esc($registration_data['name'] ?? 'N/A'); ?></p>
                <p><strong>Address:</strong> 
                    <?php echo esc($registration_data['address_flat'] ?? ''); ?> 
                    <?php echo esc($registration_data['address_street'] ?? ''); ?>, 
                    <?php echo esc($registration_data['address_city'] ?? ''); ?> 
                    <?php if (!empty($registration_data['address_postal'])): ?>
                        (<?php echo esc($registration_data['address_postal']); ?>)
                    <?php endif; ?>
                </p>
                <p><strong>Date of Birth:</strong> <?php echo esc($registration_data['dob'] ?? 'N/A'); ?></p>
                <p><strong>Email / Username:</strong> <?php echo esc($registration_data['username'] ?? 'N/A'); ?></p> 
                <p><strong>Mobile:</strong> <?php echo esc($registration_data['mobile'] ?? 'N/A'); ?></p>
                <!-- Password is NOT displayed -->
                
                <h3>Bank Information</h3>
                <p><strong>Bank Name:</strong> <?php echo esc($registration_data['bank_name'] ?? 'N/A'); ?></p>
                <p><strong>Account Number:</strong> <?php echo esc($registration_data['account_number'] ?? 'N/A'); ?></p>
                <p><strong>Branch Name:</strong> <?php echo esc($registration_data['branch_name'] ?? 'N/A'); ?></p>
                <p><strong>SWIFT Code:</strong> <?php echo esc($registration_data['swift_code'] ?? 'N/A'); ?></p>
            </section>

            <button type="submit" name="confirm">Confirm Registration</button><br><br>
            <button type="submit" name="cancel" formnovalidate>Cancel</button><br><br> 
            <button type="button" onclick="window.location.href='register_owner_step2_bank.php'">Back to Bank Information</button>
        </fieldset>
    </form>
<?php else: ?>
    <p>No registration data found. <button type="button" onclick="window.location.href='register_owner_step1.php'">Start the registration process</button></p>
<?php endif; ?>

<?php
require_once 'includes/footer.php';
// End output buffering and send content
ob_end_flush();
?>

