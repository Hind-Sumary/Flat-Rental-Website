<?php
// Start session and output buffering
ob_start();
session_start();

require_once 'includes/database.inc.php';
require_once 'includes/functions.php';

$page_title = "Offer Flat - Step 3: Timetable & Submit - Birzeit Flat Rent";

// Check if previous steps are complete
if (!isset($_SESSION['offer_flat_data'])) {
    header("Location: offer_flat_step1.php");
    exit;
}

$form_data = $_SESSION['offer_flat_data'];
$photos = $_SESSION['offer_flat_photos'] ?? [];
$appointments = $_POST['appointments'] ?? [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate appointments
    foreach ($appointments as $i => $slot) {
        if (empty($slot['date']) || empty($slot['time_from']) || empty($slot['time_to'])) {
            $errors['appointments'][$i] = "All appointment fields are required.";
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Generate flat_ref_no: F001, F002, ...
            $stmt = $pdo->query("SELECT COUNT(*) FROM flats");
            $count = $stmt->fetchColumn();
            $flat_ref_no = "F00" . ($count + 1);

            // Insert flat
            $stmt = $pdo->prepare("
                INSERT INTO flats (
                    flat_ref_no, owner_id, location, address, rent_cost,
                    available_from, available_to, bedrooms, bathrooms, size_sqm,
                    rental_conditions, heating, ac, access_control, status
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending_approval'
                )
            ");
            $stmt->execute([
                $flat_ref_no,
                $_SESSION['owner_id'] ?? 'O001', // replace with real session owner ID
                $form_data['location'],
                $form_data['address'],
                $form_data['rent_cost'],
                $form_data['available_from'],
                $form_data['available_to'],
                $form_data['bedrooms'],
                $form_data['bathrooms'],
                $form_data['size_sqm'],
                $form_data['conditions'],
                $form_data['heating'],
                $form_data['ac'],
                $form_data['access_control'] ? 'Yes' : 'No',
            ]);

            // Insert appointments
            $stmt = $pdo->prepare("
                INSERT INTO appointments (flat_ref_no, appointment_date, time_from, time_to)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($appointments as $slot) {
                $stmt->execute([
                    $flat_ref_no,
                    $slot['date'],
                    $slot['time_from'],
                    $slot['time_to']
                ]);
            }

            // Insert message to manager
            $stmt = $pdo->prepare("
                INSERT INTO messages (recipient_id, sender_id, subject, content, is_read)
                VALUES (?, ?, ?, ?, 0)
            ");
            $stmt->execute([
                'M001', // manager ID
                $_SESSION['owner_id'] ?? 'O001',
                'New Flat Submission',
                "Owner {$_SESSION['owner_id']} submitted a new flat with reference $flat_ref_no."
            ]);

            $pdo->commit();

            // Clear session
            unset($_SESSION['offer_flat_data'], $_SESSION['offer_flat_photos']);

            header("Location: confirmation.php?flat_ref_no=" . urlencode($flat_ref_no));
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors['db'] = "Database error: " . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<h2>Offer a Flat for Rent - Step 3: Preview Appointment Times</h2>

<?php if (!empty($errors)): ?>
    <section class="error-message">
        <p>Please correct the following errors:</p>
        <ul>
            <?php foreach ($errors as $group): ?>
                <?php
                if (is_array($group)) {
                    foreach ($group as $e) echo "<li>" . esc($e) . "</li>";
                } else {
                    echo "<li>" . esc($group) . "</li>";
                }
                ?>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<form action="offer_flat_step3.php" method="POST" class="offer-form">
    <fieldset>
        <legend>Available Appointment Slots</legend>
        <p>Provide times when you are available to show the flat to interested customers.</p>
        <section id="appointments-container">
            <?php
            $old_appointments = $appointments ?: [['date' => '', 'time_from' => '', 'time_to' => '']];
            foreach ($old_appointments as $i => $slot):
            ?>
                <section class="form-group">
                    <label>Date:</label>
                    <input type="date" name="appointments[<?= $i ?>][date]" value="<?= esc($slot['date'] ?? '') ?>" required>
                    <label>From:</label>
                    <input type="time" name="appointments[<?= $i ?>][time_from]" value="<?= esc($slot['time_from'] ?? '') ?>" required>
                    <label>To:</label>
                    <input type="time" name="appointments[<?= $i ?>][time_to]" value="<?= esc($slot['time_to'] ?? '') ?>" required>
                </section>
            <?php endforeach; ?>
        </section>
        <button type="submit">Submit Flat for Approval</button>
        <a href="offer_flat_step2.php">Back to Step 2</a>
    </fieldset>
</form>

<?php
require_once 'includes/footer.php';
ob_end_flush();
?>
