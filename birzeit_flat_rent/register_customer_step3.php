<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

$page_title = "Customer Registration - Step 3 - Birzeit Flat Rent";
require_once 'includes/database.inc.php'; // Need DB to save data and generate ID
require_once 'includes/functions.php';

// Ensure user has completed steps 1 and 2
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['registration_data']) || !isset($_SESSION['registration_data']['username']) || !isset($_SESSION['registration_data']['password'])) {
    // Redirect back to step 1 if session data is missing
    header('Location: register_customer_step1.php');
    exit;
}

// --- Confirmation Logic ---
$registration_data = $_SESSION['registration_data'] ?? [];
$customer_id = null;
$confirmation_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['confirm'])) {
    try {
        // Generate unique customer ID (format: C001, C002, etc.)
        $stmt = $pdo->query("SELECT MAX(SUBSTRING(user_id, 2)) AS max_id FROM users WHERE user_type = 'customer'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $next_id = 1;
        if ($result && $result['max_id']) {
            $next_id = intval($result['max_id']) + 1;
        }
        
        $customer_id = 'C' . str_pad($next_id, 3, '0', STR_PAD_LEFT);
        
        // Use the password directly (plain text as per your login implementation)
        $password_hash = $registration_data['password'];
        
        // Simplified SQL query with only essential fields
        $stmt = $pdo->prepare("
            INSERT INTO users (
                user_id, username, password_hash, user_type, national_id, 
                full_name, date_of_birth, email, mobile, status
            ) VALUES (
                ?, ?, ?, 'customer', ?, 
                ?, ?, ?, ?, 'active'
            )
        ");
        
        $stmt->execute([
            $customer_id,
            $registration_data['username'],
            $password_hash,
            $registration_data['national_id'],
            $registration_data['name'],
            $registration_data['dob'],
            $registration_data['email'],
            $registration_data['mobile']
        ]);
        
        // Set confirmation message
        $confirmation_message = "Registration successful! Welcome, " . esc($registration_data['name']) . ". Your unique Customer ID is: " . $customer_id;
        
        // Clear session data
        unset($_SESSION['registration_data']);
        
    } catch (PDOException $e) {
        error_log("Database error in registration: " . $e->getMessage());
        $error_message = "An error occurred during registration. Please try again later.";
    }
} elseif ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['cancel'])) {
    // User cancelled, clear session and redirect
    unset($_SESSION['registration_data']);
    header('Location: index.php');
    exit;
}

require_once 'includes/header.php';
?>

<h2>Customer Registration - Step 3: Confirmation</h2>

<?php if ($error_message): ?>
    <div class="error-message">
        <p><?php echo esc($error_message); ?></p>
        <p><button type="button" onclick="window.location.href='register_customer_step1.php'">Please try again</button></p>
    </div>
<?php elseif ($confirmation_message): ?>
    <div class="confirmation-message success">
        <p><?php echo $confirmation_message; ?></p>
        <p><button type="button" onclick="window.location.href='login.php'">You can now log in</button></p>
    </div>
<?php elseif (!empty($registration_data)): ?>
    <form action="register_customer_step3.php" method="POST" class="confirmation-form">
        <fieldset>
            <legend>Review Your Information</legend>
            <p>Please review the details below. Click 'Confirm' to complete your registration or 'Cancel' to discard.</p>
            
            <div class="review-details">
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
            </div>

            <button type="submit" name="confirm">Confirm Registration</button><br><br>
            <button type="submit" name="cancel" formnovalidate>Cancel</button><br><br>
            <button type="button" onclick="window.location.href='register_customer_step2.php'">Back to Step 2</button>
        </fieldset>
    </form>
<?php else: ?>
    <p>No registration data found. <button type="button" onclick="window.location.href='register_customer_step1.php'">Start the registration process</button></p>
<?php endif; ?>

<?php
require_once 'includes/footer.php';
// End output buffering and send content
ob_end_flush();
?>

