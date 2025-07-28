<?php
// Start output buffering if not already started
if (!ob_get_level()) {
    ob_start();
}

// Start session for user login, registration, etc.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files like functions if needed early
// require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Birzeit Flat Rent'; ?></title>
    <link rel="stylesheet" href="css/style.css"> 
    <!-- Add any other head elements like favicons or meta tags -->
</head>
<body>
    <header>
        <h1><a href="index.php"><img src="images/logo.png" alt="Birzeit Flat Rent Logo" height="50"> Birzeit Flat Rent</a></h1>
        <nav>
            <ul>
                <li><a href="index.php">Search Flats</a></li>
                <?php if (isset($_SESSION['user_id'])): // Check if user is logged in ?>
                    <?php if ($_SESSION['user_type'] === 'customer'): ?>
                        <li><a href="view_rented.php">My Rented Flats</a></li>
                    <?php elseif ($_SESSION['user_type'] === 'owner'): ?>
                        <li><a href="offer_flat_step1.php">Offer Flat</a></li>
                        <li><a href="view_messages.php?type=owner">Owner Messages</a></li> 
                    <?php elseif ($_SESSION['user_type'] === 'manager'): ?>
                        <li><a href="inquire_flats.php">Inquire Flats</a></li>
                        <li><a href="owner_pending_flats.php">Navigate Flat Requests</a></li>
                        <li><a href="view_messages.php?type=manager">Manager Messages</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                    <li><a href="user_profile.php">Profile</a></li>
                <?php else: // User is not logged in ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register_customer_step1.php">Register as Customer</a></li>
                    <li><a href="register_owner_step1.php">Register as Owner</a></li>
                    <li><a href="aboutUs.php">About Us</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>

