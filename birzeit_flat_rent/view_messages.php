<?php
$page_title = "My Messages - Birzeit Flat Rent";
require_once 'includes/database.inc.php';
require_once 'includes/functions.php';

// --- Authentication Check ---
// TODO: Ensure user is logged in (Manager, Owner, or Customer)
// if (!isset($_SESSION['user_id'])) {
//     redirect('login.php');
// }
// $user_id = $_SESSION['user_id'];
// $user_type = $_SESSION['user_type']; // e.g., 'manager', 'owner', 'customer'

// Determine message type based on user role or query param
$message_type = $_GET['type'] ?? $user_type ?? 'general'; // Example logic

require_once 'includes/header.php';

// --- Fetch Messages Logic (Placeholder) ---
// TODO: Fetch messages/notifications relevant to the logged-in user ($user_id, $user_type)
// Examples:
// - Manager: Flats pending approval, new rentals, appointment requests needing action?
// - Owner: Appointment requests, rental confirmations
// - Customer: Appointment confirmations, rental confirmations

$messages = [];

// --- Mock Data (Remove later) ---
if ($message_type === 'manager') {
    $messages = [
        ['id' => 1, 'subject' => 'Flat Pending Approval', 'content' => 'Flat F99887 offered by Owner O123 needs approval.', 'date' => '2025-06-09 10:00:00', 'read' => false, 'related_link' => 'admin_approve_flat.php?ref=F99887'],
        ['id' => 2, 'subject' => 'New Rental', 'content' => 'Flat F67890 rented by Customer C987.', 'date' => '2025-06-08 15:30:00', 'read' => true, 'related_link' => 'flat_detail.php?ref=F67890'],
    ];
} elseif ($message_type === 'owner') {
     $messages = [
        ['id' => 3, 'subject' => 'Appointment Request', 'content' => 'Customer C654 requested viewing for Flat F11223 on 2025-06-11.', 'date' => '2025-06-09 11:00:00', 'read' => false, 'related_link' => 'owner_appointments.php?flat=F11223'],
        ['id' => 4, 'subject' => 'Flat Rented', 'content' => 'Your Flat F67890 has been rented by Fatima Hassan (059XXXXXXX).', 'date' => '2025-06-08 15:35:00', 'read' => false, 'related_link' => 'flat_detail.php?ref=F67890'],
    ];
} // Add customer messages if needed

?>

<h2>My Messages (<?php echo esc(ucfirst($message_type)); ?>)</h2>

<section class="messages-section">
    <?php if (!empty($messages)): ?>
        <ul class="message-list">
            <?php foreach ($messages as $message): ?>
                <li class="message-item <?php echo $message['read'] ? 'read' : 'unread'; ?>">
                    <section class="message-header">
                        <span class="message-subject"><?php echo esc($message['subject']); ?></span>
                        <span class="message-date"><?php echo esc($message['date']); ?></span>
                    </section>
                    <section class="message-content">
                        <p><?php echo esc($message['content']); ?></p>
                        <?php if (!empty($message['related_link'])): ?>
                            <p><a href="<?php echo esc($message['related_link']); ?>">View Details</a></p>
                        <?php endif; ?>
                        <!-- TODO: Add actions like 'Mark as Read', 'Delete' -->
                    </section>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>You have no new messages.</p>
    <?php endif; ?>
</section>

<?php
require_once 'includes/footer.php';
?>
