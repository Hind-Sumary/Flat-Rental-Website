<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

$page_title = "Owner Registration - Bank Information - Birzeit Flat Rent";
require_once 'includes/database.inc.php'; // May need DB for validation
require_once 'includes/functions.php';

// Ensure user has completed step 1 and 2
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['registration_data']) || !isset($_SESSION['registration_data']['username']) || 
    !isset($_SESSION['registration_data']['password']) || $_SESSION['registration_data']['user_type'] !== 'owner') {
    // Redirect back to step 1 if session data is missing or not for owner
    header('Location: register_owner_step1.php');
    exit;
}

// --- Form Handling ---
$errors = [];
$form_data = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get form data
    $form_data = [
        'bank_name' => $_POST['bank_name'] ?? '',
        'account_number' => $_POST['account_number'] ?? '',
        'branch_name' => $_POST['branch_name'] ?? '',
        'swift_code' => $_POST['swift_code'] ?? '',
    ];
    
    // Validate Bank Name
    if (empty($form_data['bank_name'])) {
        $errors['bank_name'] = 'Bank name is required';
    } elseif (!preg_match('/^[A-Za-z\s]+$/', $form_data['bank_name'])) {
        $errors['bank_name'] = 'Bank name must contain only letters and spaces';
    }
    
    // Validate Account Number
    if (empty($form_data['account_number'])) {
        $errors['account_number'] = 'Account number is required';
    } elseif (!preg_match('/^\d{10,20}$/', $form_data['account_number'])) {
        $errors['account_number'] = 'Account number must be 10-20 digits';
    }
    
    // Validate Branch Name
    if (empty($form_data['branch_name'])) {
        $errors['branch_name'] = 'Branch name is required';
    }
    
    // Validate SWIFT Code
    if (empty($form_data['swift_code'])) {
        $errors['swift_code'] = 'SWIFT code is required';
    } elseif (!preg_match('/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/', $form_data['swift_code'])) {
        $errors['swift_code'] = 'Please enter a valid SWIFT code (8 or 11 characters)';
    }
    
    // If no errors, store data in session and redirect to step 3
    if (empty($errors)) {
        // Update session data with bank information
        $_SESSION['registration_data']['bank_name'] = $form_data['bank_name'];
        $_SESSION['registration_data']['account_number'] = $form_data['account_number'];
        $_SESSION['registration_data']['branch_name'] = $form_data['branch_name'];
        $_SESSION['registration_data']['swift_code'] = $form_data['swift_code'];
        
        // Redirect to step 3
        header('Location: register_owner_step3.php');
        exit;
    }
}

require_once 'includes/header.php';
?>

<h2>Owner Registration - Bank Information</h2>

<?php if (!empty($errors)): ?>
    <div class="error-message">
        <p>Please correct the following errors:</p>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo esc($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="register_owner_step2_bank.php" method="POST" class="registration-form">
    <fieldset>
        <legend>Bank Account Details</legend>
        <p>Please provide your bank account information for rental payments.</p>
        
        <div class="form-group">
            <label for="bank_name">Bank Name:</label>
            <input type="text" id="bank_name" name="bank_name" value="<?php echo esc($form_data['bank_name'] ?? ''); ?>" required>
        </div><br>
        
        <div class="form-group">
            <label for="account_number">Account Number:</label>
            <input type="text" id="account_number" name="account_number" value="<?php echo esc($form_data['account_number'] ?? ''); ?>" required>
            <small>10-20 digits, no spaces or special characters</small>
        </div><br>
        
        <div class="form-group">
            <label for="branch_name">Branch Name:</label>
            <input type="text" id="branch_name" name="branch_name" value="<?php echo esc($form_data['branch_name'] ?? ''); ?>" required>
        </div><br>
        
        <div class="form-group">
            <label for="swift_code">SWIFT Code:</label>
            <input type="text" id="swift_code" name="swift_code" value="<?php echo esc($form_data['swift_code'] ?? ''); ?>" required>
            <small>8 or 11 characters (e.g., BARCGB22XXX)</small>
        </div><br>
        
        <button type="submit">Proceed to Final Step</button>
        <button type="button" onclick="window.location.href='register_owner_step2.php'">Back to Account Creation</button>
    </fieldset>
</form>

<?php
require_once 'includes/footer.php';
// End output buffering and send content
ob_end_flush();
?>

