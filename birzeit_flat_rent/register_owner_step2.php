<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

$page_title = "Owner Registration - Step 2 - Birzeit Flat Rent";
require_once 'includes/database.inc.php'; // Need DB to check email uniqueness
require_once 'includes/functions.php';

// Ensure user has completed step 1
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['registration_data']) || $_SESSION['registration_data']['user_type'] !== 'owner') {
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
        'username' => $_POST['username'] ?? '',
        'password' => $_POST['password'] ?? '',
        'email'    => $_POST['username'] ?? '',
        'password_confirm' => $_POST['password_confirm'] ?? '',
    ];
    
    // Validate Username (must be email)
    if (empty($form_data['username'])) {
        $errors['username'] = 'Username is required';
    } elseif (!filter_var($form_data['username'], FILTER_VALIDATE_EMAIL)) {
        $errors['username'] = 'Username must be a valid email address';
    } else {
        // Check if username already exists in database
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$form_data['username']]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $errors['username'] = 'This email is already registered. Please use a different email or try to log in.';
            }
        } catch (PDOException $e) {
            error_log("Database error checking username: " . $e->getMessage());
            $errors['username'] = 'Error checking username availability. Please try again.';
        }
    }
    
    // Validate Password (6-15 chars, starts with digit, ends with lowercase letter)
    if (empty($form_data['password'])) {
        $errors['password'] = 'Password is required';
    } elseif (!preg_match('/^\d[\w!@#$%^&*()\-+=]{4,13}[a-z]$/', $form_data['password'])) {
        $errors['password'] = 'Password must be 6-15 characters, start with a digit, and end with a lowercase letter';
    }
    
    // Validate Password Confirmation
    if (empty($form_data['password_confirm'])) {
        $errors['password_confirm'] = 'Please confirm your password';
    } elseif ($form_data['password'] !== $form_data['password_confirm']) {
        $errors['password_confirm'] = 'Passwords do not match';
    }
    
    // If no errors, store data in session and redirect to bank info step
    if (empty($errors)) {
        // Update session data with username and password
        $_SESSION['registration_data']['username'] = $form_data['username'];
        $_SESSION['registration_data']['email'] = $form_data['username'];
        $_SESSION['registration_data']['password'] = $form_data['password']; // Will be hashed in step 3
        
        // Redirect to bank info step
        header('Location: register_owner_step2_bank.php');
        exit;
    }
}

require_once 'includes/header.php';

// Pre-fill email from step 1 if available
$email_from_step1 = $_SESSION['registration_data']['email'] ?? '';
?>

<h2>Owner Registration - Step 2: Account Creation</h2>

<?php if (!empty($errors)): ?>
    <section class="error-message">
        <p>Please correct the following errors:</p>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo esc($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<form action="register_owner_step2.php" method="POST" class="registration-form">
    <fieldset>
        <legend>Create Your E-Account</legend>
        <section class="form-group">
            <label for="username">Username (Must be a valid Email):</label>
            <input type="email" id="username" name="username" value="<?php echo esc($form_data['username'] ?? $email_from_step1); ?>" required>
        </section><br>
        <section class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" pattern="\d[\w!@#$%^&*()\-+=]{4,13}[a-z]" title="Password must be 6-15 characters, start with a digit, and end with a lowercase letter." required>
            <small>Must be 6-15 characters, start with a digit, end with a lowercase letter.</small>
        </section><br>
        <section class="form-group">
            <label for="password_confirm">Confirm Password:</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
        </section><br>
        <button type="submit">Proceed to Bank Information</button>
        <button type="button" onclick="window.location.href='register_owner_step1.php'">Back to Step 1</button>
    </fieldset>
</form>

<?php
require_once 'includes/footer.php';
// End output buffering and send content
ob_end_flush();
?>

