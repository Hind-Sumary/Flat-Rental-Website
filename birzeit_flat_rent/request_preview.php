<?php
// Start output buffering to prevent headers already sent errors
ob_start();

$page_title = "Request Flat Preview - Birzeit Flat Rent";
require_once 'includes/database.inc.php';
require_once 'includes/functions.php';

// Get flat reference number from query string
$flat_ref_no = $_GET['ref'] ?? null;

if (!$flat_ref_no) {
    redirect('index.php');
    exit;
}

// --- Authentication Check (Optional - Guests might be able to view slots) ---
// TODO: Decide if login is required to request. If so, check session.
// $customer_id = $_SESSION['user_id'] ?? null;

// --- Fetch Available Time Slots (Placeholder) ---
$sql = "SELECT * FROM appointments 
        WHERE flat_ref_no = ? 
          AND status = 'available'
          AND appointment_date >= CURDATE()
        ORDER BY appointment_date, time_from";

$stmt = $pdo->prepare($sql);
$stmt->execute([$flat_ref_no]);
$available_slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Form Handling (Placeholder) ---
$request_message = '';
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['book_slot_id'])) {
    // TODO: Check if user is logged in (if required)
    // TODO: Get customer ID
    $slot_id = $_POST['book_slot_id'];
    // TODO: Verify the selected slot ID is valid and still available
    // TODO: Mark the slot as taken in the database (or create an appointment record linking user, flat, slot)
    // TODO: Send notification to the owner (via view_messages logic)
    // TODO: Set confirmation message

    // --- Mock Success Logic ---
    $request_message = "Appointment requested successfully! The owner has been notified and will confirm.";
    // Refresh slots to show updated status (or hide form)
    // For mock, just show message
    $available_slots = []; // Clear slots after booking for simplicity here
}

// Include header AFTER all processing is done
require_once 'includes/header.php';
?>

<h2>Request Flat Preview Appointment for: <?php echo esc($flat_ref_no); ?></h2>

<?php if ($request_message): ?>
    <section class="confirmation-message success">
        <p><?php echo $request_message; ?></p>
        <p><a href="flat_detail.php?ref=<?php echo urlencode($flat_ref_no); ?>" class="btn btn-secondary">Back to Flat Details</a></p>
    </section>
<?php elseif (!empty($available_slots)): ?>
    <p>Select an available time slot below to request a viewing appointment.</p>
    <form action="request_preview.php?ref=<?php echo urlencode($flat_ref_no); ?>" method="POST">
        <table class="results-table appointment-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time Slot</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($available_slots as $slot): ?>
                    <tr class="slot-<?php echo esc($slot['status']); ?>">
                        <td><?php echo esc($slot['appointment_date']); ?></td>
                        <td><?php echo esc($slot['time_from']) . ' - ' . esc($slot['time_to']); ?></td>
                        <td>
                            <?php if ($slot['status'] !== 'available'): ?>
                                <button type="button" disabled>Booked</button>
                            <?php else: ?>
                                <button type="submit" name="book_slot_id" value="<?php echo esc($slot['appointment_id']); ?>">Book</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>
<?php else: ?>
    <p>No available time slots for previewing this flat at the moment. Please check back later or contact the agency.</p>
<?php endif; ?>

<p><a href="flat_detail.php?ref=<?php echo urlencode($flat_ref_no); ?>" class="btn btn-secondary">Back to Flat Details</a></p>

<?php
require_once 'includes/footer.php';
// End output buffering and flush content
ob_end_flush();
?>

