<?php
// Start output buffering
ob_start();

$page_title = "Offer Flat - Step 1: Details - Birzeit Flat Rent";
require_once 'includes/database.inc.php';
require_once 'includes/functions.php';

session_start();

$errors = [];
$form_data = [];

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $form_data = [
        'location' => $_POST['location'] ?? '',
        'address' => $_POST['address'] ?? '',
        'rent_cost' => $_POST['rent_cost'] ?? '',
        'available_from' => $_POST['available_from'] ?? '',
        'available_to' => $_POST['available_to'] ?? '',
        'bedrooms' => $_POST['bedrooms'] ?? '',
        'bathrooms' => $_POST['bathrooms'] ?? '',
        'size_sqm' => $_POST['size_sqm'] ?? '',
        'conditions' => $_POST['conditions'] ?? '',
        'heating' => isset($_POST['heating']) ? 1 : 0,
        'ac' => isset($_POST['ac']) ? 1 : 0,
        'access_control' => isset($_POST['access_control']) ? 1 : 0,
        'features' => $_POST['features'] ?? [],
    ];

    // --- Validation ---
    if (empty($form_data['location'])) {
        $errors['location'] = 'Location is required.';
    }
    if (empty($form_data['address'])) {
        $errors['address'] = 'Address is required.';
    }
    if (empty($form_data['rent_cost']) || !is_numeric($form_data['rent_cost']) || $form_data['rent_cost'] < 0) {
        $errors['rent_cost'] = 'Rent cost must be a positive number.';
    }
    if (empty($form_data['available_from'])) {
        $errors['available_from'] = 'Available from date is required.';
    }
    if (empty($form_data['available_to'])) {
        $errors['available_to'] = 'Available to date is required.';
    } elseif (!empty($form_data['available_from']) && strtotime($form_data['available_to']) < strtotime($form_data['available_from'])) {
        $errors['available_to'] = 'End date must be after start date.';
    }
    if (empty($form_data['bedrooms']) || $form_data['bedrooms'] < 1) {
        $errors['bedrooms'] = 'At least one bedroom is required.';
    }
    if (empty($form_data['bathrooms']) || $form_data['bathrooms'] < 1) {
        $errors['bathrooms'] = 'At least one bathroom is required.';
    }
    if (empty($form_data['size_sqm']) || $form_data['size_sqm'] < 10) {
        $errors['size_sqm'] = 'Size must be at least 10 sqm.';
    }

    // Validate file upload (at least 3 files)
    if (!isset($_FILES['photos']) || count($_FILES['photos']['name']) < 3 || empty($_FILES['photos']['name'][0])) {
        $errors['photos'] = 'Please upload at least 3 photos.';
    }

    // If no errors, store in session and proceed
    if (empty($errors)) {
        $_SESSION['offer_flat_data'] = $form_data;

        // Temporarily store file names only (actual upload logic can be done in step 2)
        $_SESSION['offer_flat_photos'] = $_FILES['photos'];

        header('Location: offer_flat_step2.php');
        exit;
    }
}

require_once 'includes/header.php';
?>

<h2>Offer a Flat for Rent - Step 1: Flat Details</h2>

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

<form action="offer_flat_step1.php" method="POST" enctype="multipart/form-data" class="offer-form">
    <fieldset>
        <legend>Flat Information</legend>
        <section class="form-group">
            <label for="location">Location:</label>
            <input type="text" id="location" name="location" value="<?php echo esc($form_data['location'] ?? ''); ?>" required>
        </section>
        <section class="form-group">
            <label for="address">Full Address:</label>
            <textarea id="address" name="address" required><?php echo esc($form_data['address'] ?? ''); ?></textarea>
        </section>
        <section class="form-group">
            <label for="rent_cost">Rent Cost per Month:</label>
            <input type="number" id="rent_cost" name="rent_cost" value="<?php echo esc($form_data['rent_cost'] ?? ''); ?>" min="0" step="10" required>
        </section>
        <section class="form-group">
            <label for="available_from">Available From:</label>
            <input type="date" id="available_from" name="available_from" value="<?php echo esc($form_data['available_from'] ?? ''); ?>" required>
        </section>
        <section class="form-group">
            <label for="available_to">Available To:</label>
            <input type="date" id="available_to" name="available_to" value="<?php echo esc($form_data['available_to'] ?? ''); ?>" required>
        </section>
        <section class="form-group">
            <label for="bedrooms">Number of Bedrooms:</label>
            <input type="number" id="bedrooms" name="bedrooms" value="<?php echo esc($form_data['bedrooms'] ?? ''); ?>" min="1" required>
        </section>
        <section class="form-group">
            <label for="bathrooms">Number of Bathrooms:</label>
            <input type="number" id="bathrooms" name="bathrooms" value="<?php echo esc($form_data['bathrooms'] ?? ''); ?>" min="1" required>
        </section>
        <section class="form-group">
            <label for="size_sqm">Size (Square Meters):</label>
            <input type="number" id="size_sqm" name="size_sqm" value="<?php echo esc($form_data['size_sqm'] ?? ''); ?>" min="10" required>
        </section>
        <section class="form-group">
            <label for="conditions">Rental Conditions:</label>
            <textarea id="conditions" name="conditions"><?php echo esc($form_data['conditions'] ?? ''); ?></textarea>
        </section>
        <section class="form-group">
            <input type="checkbox" id="heating" name="heating" value="1" <?php if (!empty($form_data['heating'])) echo 'checked'; ?>>
            <label for="heating">Has Heating System</label>
        </section>
        <section class="form-group">
            <input type="checkbox" id="ac" name="ac" value="1" <?php if (!empty($form_data['ac'])) echo 'checked'; ?>>
            <label for="ac">Has Air-Conditioning System</label>
        </section>
        <section class="form-group">
            <input type="checkbox" id="access_control" name="access_control" value="1" <?php if (!empty($form_data['access_control'])) echo 'checked'; ?>>
            <label for="access_control">Has Access Control</label>
        </section>
        <fieldset>
            <legend>Extra Features</legend>
            <?php
            $features = $form_data['features'] ?? [];
            $feature_list = ['parking' => 'Car Parking', 'backyard_insectionidual' => 'Insectionidual Backyard', 'backyard_shared' => 'Shared Backyard', 'playground' => 'Playing Ground', 'storage' => 'Storage'];
            foreach ($feature_list as $value => $label): ?>
                <section class="form-group">
                    <input type="checkbox" id="<?php echo $value; ?>" name="features[]" value="<?php echo $value; ?>" <?php echo in_array($value, $features) ? 'checked' : ''; ?>>
                    <label for="<?php echo $value; ?>"><?php echo $label; ?></label>
                </section>
            <?php endforeach; ?>
        </fieldset>
        <section class="form-group">
            <label for="photos">Photos (Select at least 3):</label>
            <input type="file" id="photos" name="photos[]" multiple accept="image/*" required>
            <?php if (isset($errors['photos'])): ?>
                <span class="error"><?php echo esc($errors['photos']); ?></span>
            <?php endif; ?>
        </section>
        <button type="submit">Proceed to Step 2 (Marketing)</button>
    </fieldset>
</form>

<?php
require_once 'includes/footer.php';
// End output buffering
ob_end_flush();
?>
