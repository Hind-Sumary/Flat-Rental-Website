<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

$page_title = "Customer Registration - Step 1 - Birzeit Flat Rent";
require_once 'includes/functions.php';

// Clear any previous session data for registration if starting anew
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Clear session data if user navigates here directly without POST
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    unset($_SESSION['registration_data']); 
}

// --- Form Handling ---
$errors = [];
$form_data = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get form data
    $form_data = [
        'national_id' => $_POST['national_id'] ?? '',
        'name' => $_POST['name'] ?? '',
        'address_flat' => $_POST['address_flat'] ?? '',
        'address_street' => $_POST['address_street'] ?? '',
        'address_city' => $_POST['address_city'] ?? '',
        'address_postal' => $_POST['address_postal'] ?? '',
        'dob' => $_POST['dob'] ?? '',
        'email' => $_POST['email'] ?? '',
        'mobile' => $_POST['mobile'] ?? '',
    ];
    
    // Validate National ID (9 numerical characters)
    if (empty($form_data['national_id'])) {
        $errors['national_id'] = 'National ID is required';
    } elseif (!preg_match('/^\d{9}$/', $form_data['national_id'])) {
        $errors['national_id'] = 'National ID must be exactly 9 digits';
    }
    
    // Validate Name (alphabetic characters only)
    if (empty($form_data['name'])) {
        $errors['name'] = 'Full name is required';
    } elseif (!preg_match('/^[A-Za-z\s]+$/', $form_data['name'])) {
        $errors['name'] = 'Name must contain only letters and spaces';
    }
    
    // Validate Address Flat (1-999)
    if (empty($form_data['address_flat'])) {
        $errors['address_flat'] = 'Flat/House number is required';
    } elseif (!preg_match('/^[1-9]\d{0,2}$/', $form_data['address_flat'])) {
        $errors['address_flat'] = 'Flat/House number must be between 1 and 999';
    }
    
    // Validate Street
    if (empty($form_data['address_street'])) {
        $errors['address_street'] = 'Street name is required';
    }
    
    // Validate City (alphabetic characters only)
    if (empty($form_data['address_city'])) {
        $errors['address_city'] = 'City is required';
    } elseif (!preg_match('/^[A-Za-z\s]+$/', $form_data['address_city'])) {
        $errors['address_city'] = 'City must contain only letters and spaces';
    }
    
    // Validate Postal Code (7 numerical characters)
    if (!empty($form_data['address_postal']) && !preg_match('/^\d{7}$/', $form_data['address_postal'])) {
        $errors['address_postal'] = 'Postal code must be exactly 7 digits';
    }
    
    // Validate Date of Birth (over 21 years old)
    if (empty($form_data['dob'])) {
        $errors['dob'] = 'Date of birth is required';
    } else {
        $dob = new DateTime($form_data['dob']);
        $now = new DateTime();
        $age = $now->diff($dob)->y;
        
        if ($age < 21) {
            $errors['dob'] = 'You must be at least 21 years old to register';
        }
    }
    
    // Validate Email
    if (empty($form_data['email'])) {
        $errors['email'] = 'Email address is required';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    
    // Validate Mobile Number
    if (empty($form_data['mobile'])) {
        $errors['mobile'] = 'Mobile number is required';
    } elseif (!preg_match('/^(05|07)\d{8}$/', $form_data['mobile'])) {
        $errors['mobile'] = 'Please enter a valid mobile number (10 digits starting with 05 or 07)';
    }
    
    // If no errors, store data in session and redirect to step 2
    if (empty($errors)) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['registration_data'] = $form_data;
        
        // Redirect to step 2
        header('Location: register_customer_step2.php');
        exit;
    }
}

require_once 'includes/header.php';
?>

<h2>Customer Registration - Step 1: Personal Details</h2>

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

<form action="register_customer_step1.php" method="POST" class="registration-form">
    <fieldset>
        <legend>Your Information</legend>
        <div class="form-group">
            <label for="national_id">National ID Number (9 digits):</label>
            <input type="text" id="national_id" name="national_id" value="<?php echo esc($form_data['national_id'] ?? ''); ?>" required> 
            <?php if (isset($errors['national_id'])): ?>
                <span class="error"><?php echo esc($errors['national_id']); ?></span>
            <?php endif; ?>
        </div><br>
        <div class="form-group">
            <label for="name">Full Name (Characters only):</label>
            <input type="text" id="name" name="name" value="<?php echo esc($form_data['name'] ?? ''); ?>" pattern="[A-Za-z\s]+" title="Please enter only letters and spaces." required>
            <?php if (isset($errors['name'])): ?>
                <span class="error"><?php echo esc($errors['name']); ?></span>
            <?php endif; ?>
        </div><br>
        <fieldset>
            <legend>Address</legend>
            <div class="form-group">
                <label for="address_flat">Flat/House No (1-999):</label>
                <input type="text" id="address_flat" name="address_flat" value="<?php echo esc($form_data['address_flat'] ?? ''); ?>" required>
                <?php if (isset($errors['address_flat'])): ?>
                    <span class="error"><?php echo esc($errors['address_flat']); ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="address_street">Street Name:</label>
                <input type="text" id="address_street" name="address_street" value="<?php echo esc($form_data['address_street'] ?? ''); ?>" required>
                <?php if (isset($errors['address_street'])): ?>
                    <span class="error"><?php echo esc($errors['address_street']); ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="address_city">City (Characters only):</label>
                <input type="text" id="address_city" name="address_city" value="<?php echo esc($form_data['address_city'] ?? ''); ?>" required>
                <?php if (isset($errors['address_city'])): ?>
                    <span class="error"><?php echo esc($errors['address_city']); ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="address_postal">Postal Code (7 digits):</label>
                <input type="text" id="address_postal" name="address_postal" value="<?php echo esc($form_data['address_postal'] ?? ''); ?>">
                <?php if (isset($errors['address_postal'])): ?>
                    <span class="error"><?php echo esc($errors['address_postal']); ?></span>
                <?php endif; ?>
            </div>
        </fieldset><br>
        <div class="form-group">
            <label for="dob">Date of Birth (Must be 21+ years old):</label>
            <input type="date" id="dob" name="dob" value="<?php echo esc($form_data['dob'] ?? ''); ?>" required>
            <?php if (isset($errors['dob'])): ?>
                <span class="error"><?php echo esc($errors['dob']); ?></span>
            <?php endif; ?>
        </div><br>
        <div class="form-group">
            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" value="<?php echo esc($form_data['email'] ?? ''); ?>" required>
            <?php if (isset($errors['email'])): ?>
                <span class="error"><?php echo esc($errors['email']); ?></span>
            <?php endif; ?>
        </div><br>
        <div class="form-group">
            <label for="mobile">Mobile Number (10 digits starting with 05 or 07):</label>
            <input type="tel" id="mobile" name="mobile" value="<?php echo esc($form_data['mobile'] ?? ''); ?>" required>
            <?php if (isset($errors['mobile'])): ?>
                <span class="error"><?php echo esc($errors['mobile']); ?></span>
            <?php endif; ?>
        </div><br><br>
        <button type="submit">Proceed to Step 2</button>
    </fieldset>
</form>

<?php
require_once 'includes/footer.php';
// End output buffering and send content
ob_end_flush();
?>

