<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

// Error reporting for development (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Login - Birzeit Flat Rent";
require_once 'includes/database.inc.php';
require_once 'includes/functions.php';

$error_message = '';
$login_successful = false;

// --- Login Logic ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } else {
        try {
            // Use the existing column name 'password_hash'
            $stmt = $pdo->prepare("SELECT user_id, username, password_hash, user_type FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Store debug info in variables instead of echoing
            $debug_user_id = $user ? $user['user_id'] : 'Not found';
            $debug_password = $user ? $user['password_hash'] : 'Not found';

            // Compare plain-text password directly (insecure)
            if ($user && $password === $user['password_hash']) {
                // Mark login as successful
                $login_successful = true;
                
                // Include header.php which starts the session
                require_once 'includes/header.php';
                
                // Now set session variables after session is started by header.php
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];
                
                // Determine redirect URL
                $redirect_url = $_SESSION['redirect_url'] ?? 'index.php';
                unset($_SESSION['redirect_url']);
                
                // Use JavaScript redirect instead of PHP header redirect
                echo '<script>window.location.href = "' . $redirect_url . '";</script>';
                
                // Clean up and exit
                ob_end_flush();
                exit;
            } else {
                $error_message = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            // Log error instead of echoing
            error_log("Database Error in login.php: " . $e->getMessage());
            $error_message = 'A system error occurred. Please try again later.';
        }
    }
}

// Only include header if login was not successful
if (!$login_successful) {
    require_once 'includes/header.php';
    
    // Now it's safe to output debug info as HTML comments
    if (isset($debug_user_id) && isset($debug_password)) {
        echo "<!-- Debug: User ID: " . htmlspecialchars($debug_user_id) . " -->\n";
        echo "<!-- Debug: Password in DB: " . htmlspecialchars($debug_password) . " -->\n";
    }
?>

<h2>Login</h2>

<?php if ($error_message): ?>
    <div class="error-message"><?php echo esc($error_message); ?></div>
<?php endif; ?>

<form action="login.php" method="POST" class="login-form">
    <fieldset>
        <legend>Enter Your Credentials</legend>
        <div class="form-group">
            <label for="username">Username (Email):</label>
            <input type="email" id="username" name="username" required value="<?php echo esc($_POST['username'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div><br>
        <button type="submit">Login</button>
    </fieldset>
</form>

<p>Don't have an account? <a href="register_customer_step1.php">Register as Customer</a> or <a href="register_owner_step1.php">Register as Owner</a>.</p>

<?php
    }
    require_once 'includes/footer.php';
    
    // End output buffering and send content
    ob_end_flush();
?>