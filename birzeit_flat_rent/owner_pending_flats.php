<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

$page_title = "Pending Flat Approvals - Birzeit Flat Rent";
require_once 'includes/database.inc.php';
require_once 'includes/functions.php';

// Ensure only logged-in managers can access this page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'manager') {
    redirect('login.php'); // Redirect to login if not a manager
}

$flats = [];
$error_message = '';
$success_message = '';

// Handle Accept/Reject actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $flat_ref_no = $_POST['flat_ref_no'] ?? '';
    $action = $_POST['action'] ?? '';

    if (!empty($flat_ref_no) && !empty($action)) {
        try {
            $pdo->beginTransaction();
            if ($action === 'accept') {
                $stmt = $pdo->prepare("UPDATE flats SET status = 'available' WHERE flat_ref_no = ? AND status = 'pending_approval'");
                $stmt->execute([$flat_ref_no]);
                $success_message = "Flat " . esc($flat_ref_no) . " has been approved and is now available.";
            } elseif ($action === 'reject') {
                $stmt = $pdo->prepare("UPDATE flats SET status = 'unavailable' WHERE flat_ref_no = ? AND status = 'pending_approval'");
                $stmt->execute([$flat_ref_no]);
                $success_message = "Flat " . esc($flat_ref_no) . " has been rejected and marked as unavailable.";
            }
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_message = "Database error: Unable to update flat status.";
            error_log("Database error in owner_pending_flats.php: " . $e->getMessage());
        }
    }
}

// Fetch pending approval flats
try {
    $stmt = $pdo->prepare("SELECT * FROM flats WHERE status = 'pending_approval'");
    $stmt->execute();
    $flats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Database error: Unable to fetch pending flats.";
    error_log("Database error in owner_pending_flats.php: " . $e->getMessage());
}

require_once 'includes/header.php';
?>

<h2>Pending Flat Approvals</h2>

<?php if (!empty($success_message)): ?>
    <div class="success-message">
        <?php echo esc($success_message); ?>
    </div>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <div class="error-message">
        <?php echo esc($error_message); ?>
    </div>
<?php endif; ?>

<?php if (empty($flats)): ?>
    <p>No flats currently pending approval.</p>
<?php else: ?>
    <table class="results-table">
        <thead>
            <tr>
                <th>Ref No.</th>
                <th>Owner ID</th>
                <th>Location</th>
                <th>Address</th>
                <th>Rent Cost</th>
                <th>Bedrooms</th>
                <th>Bathrooms</th>
                <th>Size (sqm)</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($flats as $flat): ?>
                <tr>
                    <td><?php echo esc($flat['flat_ref_no']); ?></td>
                    <td><?php echo esc($flat['owner_id']); ?></td>
                    <td><?php echo esc($flat['location']); ?></td>
                    <td><?php echo esc($flat['address']); ?></td>
                    <td>$<?php echo esc(number_format($flat['rent_cost'], 2)); ?></td>
                    <td><?php echo esc($flat['bedrooms']); ?></td>
                    <td><?php echo esc($flat['bathrooms']); ?></td>
                    <td><?php echo esc($flat['size_sqm']); ?></td>
                    <td><?php echo esc(ucfirst(str_replace('_', ' ', $flat['status']))); ?></td>
                    <td>
                        <form method="POST" action="owner_pending_flats.php" style="display:inline-block;">
                            <input type="hidden" name="flat_ref_no" value="<?php echo esc($flat['flat_ref_no']); ?>">
                            <button type="submit" name="action" value="accept" class="btn btn-success">Accept</button>
                        </form>
                        <form method="POST" action="owner_pending_flats.php" style="display:inline-block;">
                            <input type="hidden" name="flat_ref_no" value="<?php echo esc($flat['flat_ref_no']); ?>">
                            <button type="submit" name="action" value="reject" class="btn btn-danger">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
require_once 'includes/footer.php';
ob_end_flush();
?>

