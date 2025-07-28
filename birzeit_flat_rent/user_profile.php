<?php
// user_profile.php
session_start();

$page_title = "User Profile - Birzeit Flat Rent";
require_once 'includes/header.php';
require_once 'includes/database.inc.php';
?>

<link rel="stylesheet" href="css/style.css">

<section class="container" style="padding: 20px; max-width: 800px; margin: auto;">
    <h2>User Profile</h2>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <p>You are not logged in. <a href="login.php">Login here</a>.</p>
    <?php else: ?>
        <?php
            $user_id = $_SESSION['user_id'];
            $photo_path = "uploads/profile_photos/$user_id.jpg";

            // If file doesn't exist, use default avatar
            if (!file_exists($photo_path)) {
                $photo_path = "images/avatar.jpg";
            }
        ?>

        <section class="profile-details" style="text-align: center;">
            <img src="<?php echo htmlspecialchars($photo_path); ?>" alt="Profile Photo"
                 style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%; margin-bottom: 15px; border: 2px solid #ccc;">
            
            <p><strong>User ID:</strong> <?php echo htmlspecialchars($_SESSION['user_id']); ?></p>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            <p><strong>User Type:</strong> <?php echo htmlspecialchars(ucfirst($_SESSION['user_type'])); ?></p>

            <?php if ($_SESSION['user_type'] === 'customer'): ?>
                <p><a href="view_rented.php">View Rented Flats</a></p>
            <?php elseif ($_SESSION['user_type'] === 'owner'): ?>
                <p><a href="offer_flat_step1.php">Offer a New Flat</a></p>
                <p><a href="view_messages.php?type=owner">View Owner Messages</a></p>
            <?php elseif ($_SESSION['user_type'] === 'manager'): ?>
                <p><a href="inquire_flats.php">Inquire Flats</a></p>
                <p><a href="view_messages.php?type=manager">View Manager Messages</a></p>
            <?php endif; ?>

            <p><a href="logout.php">Logout</a></p>
        </section>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
