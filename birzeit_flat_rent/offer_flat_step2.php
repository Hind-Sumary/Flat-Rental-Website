<?php
// Start session and output buffering
ob_start();
session_start();

$page_title = "Offer Flat - Step 2: Marketing - Birzeit Flat Rent";
require_once 'includes/functions.php';

// --- Check if step 1 is completed ---
if (!isset($_SESSION['offer_flat_data'])) {
    header("Location: offer_flat_step1.php");
    exit;
}

$errors = [];
$places = $_POST['places'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $validated_places = [];

    foreach ($places as $index => $place) {
        $title = trim($place['title'] ?? '');
        $description = trim($place['description'] ?? '');
        $url = trim($place['url'] ?? '');

        if ($title !== '' || $description !== '' || $url !== '') {
            // Basic validation
            if ($title === '') {
                $errors["places_$index"][] = "Place title is required if any information is entered.";
            }

            if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
                $errors["places_$index"][] = "Invalid URL format for place: $title";
            }

            // If no field-specific error, save
            if (!isset($errors["places_$index"])) {
                $validated_places[] = [
                    'title' => $title,
                    'description' => $description,
                    'url' => $url,
                ];
            }
        }
    }

    // If no validation errors, save to session and redirect
    if (empty($errors)) {
        $_SESSION['offer_flat_data']['nearby_places'] = $validated_places;
        header("Location: offer_flat_step3.php");
        exit;
    }
}

require_once 'includes/header.php';
?>

<h2>Offer a Flat for Rent - Step 2: Marketing Information (Optional)</h2>

<?php if (!empty($errors)): ?>
    <section class="error-message">
        <p>Please correct the following errors:</p>
        <ul>
            <?php foreach ($errors as $place_errors): ?>
                <?php foreach ($place_errors as $error): ?>
                    <li><?php echo esc($error); ?></li>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<form action="offer_flat_step2.php" method="POST" class="offer-form">
    <fieldset>
        <legend>Nearby Places</legend>
        <p>Provide info about nearby schools, markets, gardens, etc. (Optional)</p>

        <section id="nearby-places-container">
            <?php
            $old_places = $places ?: [['title' => '', 'description' => '', 'url' => '']];
            foreach ($old_places as $index => $place):
                $title = esc($place['title'] ?? '');
                $desc = esc($place['description'] ?? '');
                $url = esc($place['url'] ?? '');
            ?>
                <section class="nearby-place">
                    <section class="form-group">
                        <label for="place_title_<?php echo $index; ?>">Place Title:</label>
                        <input type="text" id="place_title_<?php echo $index; ?>" name="places[<?php echo $index; ?>][title]" value="<?php echo $title; ?>">
                    </section>
                    <section class="form-group">
                        <label for="place_desc_<?php echo $index; ?>">Short Description:</label>
                        <textarea id="place_desc_<?php echo $index; ?>" name="places[<?php echo $index; ?>][description]"><?php echo $desc; ?></textarea>
                    </section>
                    <section class="form-group">
                        <label for="place_url_<?php echo $index; ?>">URL (if available):</label>
                        <input type="url" id="place_url_<?php echo $index; ?>" name="places[<?php echo $index; ?>][url]" value="<?php echo $url; ?>">
                    </section>
                    <hr>
                </section>
            <?php endforeach; ?>
        </section>

        <!-- Add More Button (can be enhanced with JS later) -->
        <button type="submit">Proceed to Step 3 (Timetable)</button>
        <a href="offer_flat_step1.php">Back to Step 1</a>
    </fieldset>
</form>

<?php
require_once 'includes/footer.php';
ob_end_flush();
?>
