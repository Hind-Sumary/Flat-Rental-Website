<?php
// This script displays a user card (Owner or Customer)
// It expects user ID and type via GET parameters (e.g., user_card.php?id=O123&type=owner)
require_once 'includes/database.inc.php';
require_once 'includes/functions.php';

$user_id = $_GET['id'] ?? null;
$user_type = $_GET['type'] ?? null; // 'owner' or 'customer'

if (!$user_id || !$user_type || !in_array($user_type, ['owner', 'customer'])) {
    die("Invalid user ID or type specified.");
}

// --- Fetch User Details (Placeholder) ---
// TODO: Fetch user details from the appropriate table (owners or customers) based on $user_id and $user_type
$user_details = null;

if (!$user_details) {
    die("User not found.");
}

// No need for full header/footer if this is meant to be opened in a simple new tab/window
// Or include them if it should look like a standard page
$page_title = "User Card: " . esc($user_details['name']);
// require_once 'includes/header.php'; // Optional: Uncomment if full page layout is desired
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="css/style.css"> 
    <style>
        /* Basic styles for the user card - move to style.css later */
        body { font-family: sans-serif; padding: 20px; background-color: #f9f9f9; }
        .user-card h2 { margin-top: 0; text-align: center; }
        .user-card p { margin: 10px 0; }
        .user-card .city { text-align: center; color: #555; margin-bottom: 15px; }
        .user-card .contact-info span { margin-right: 5px; } /* For icons */
        .user-card a { color: #007bff; text-decoration: none; }
        .user-card a:hover { text-decoration: underline; }
        /* TODO: Add actual phone/email icons using CSS background images or font icons */
        .icon-phone::before { content: '\1F4DE'; margin-right: 5px; } /* Example Unicode phone icon */
        .icon-email::before { content: '\2709'; margin-right: 5px; } /* Example Unicode email icon */
    </style>
</head>
<body>
    <div class="user-card">
        <h2><?php echo esc($user_details['name']); ?></h2>
        <p class="city"><?php echo esc($user_details['city']); ?></p>
        <hr>
        <p class="contact-info">
            <span class="icon-phone"></span><?php echo esc($user_details['telephone']); ?>
        </p>
        <p class="contact-info">
            <span class="icon-email"></span><a href="mailto:<?php echo esc($user_details['email']); ?>"><?php echo esc($user_details['email']); ?></a>
        </p>
    </div>
<?php
// require_once 'includes/footer.php'; // Optional: Uncomment if full page layout is desired
?>
</body>
</html>
