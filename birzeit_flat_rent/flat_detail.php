<?php
// Start output buffering to prevent headers already sent errors
ob_start();

require_once 'includes/database.inc.php';
require_once 'includes/functions.php';

// Get flat reference number from query string
$flat_ref_no = $_GET['ref'] ?? $_GET['flat_ref_no'] ?? null;

if (!$flat_ref_no) {
    // Redirect if no reference number is provided
    redirect('index.php'); 
    exit;
}

// Initialize variables
$flat_details = null;
$flat_photos = [];
$flat_features = [];
$marketing_info = [];

try {
    // Fetch flat details from the database
    $stmt = $pdo->prepare("SELECT * FROM flats WHERE flat_ref_no = ? AND status = 'available'");
    $stmt->execute([$flat_ref_no]);
    $flat_details = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$flat_details) {
        // Handle case where flat is not found
        $page_title = "Flat Not Found - Birzeit Flat Rent";
        require_once 'includes/header.php';
        echo "<section class='error-message'>";
        echo "<h2>Flat Not Found</h2>";
        echo "<p>The flat you're looking for is not available or doesn't exist.</p>";
        echo "<p><a href='index.php'>← Back to Search</a></p>";
        echo "</section>";
        require_once 'includes/footer.php';
        exit;
    }
    
    // Get photos using YOUR file structure: images/F001/F001_1.jpg
    $available_photos = [];
    for ($i = 1; $i <= 10; $i++) {
        $photo_path = "images/{$flat_ref_no}/{$flat_ref_no}_$i.jpg";
        if (file_exists($photo_path)) {
            $available_photos[] = [
                'path' => $photo_path,
                'number' => $i,
                'is_primary' => ($i === 1),
                'description' => "Photo $i of flat {$flat_ref_no}",
                'filename' => "{$flat_ref_no}_$i.jpg"
            ];
        }
    }
    
    // Try to fetch flat photos from database as backup
    $stmt = $pdo->prepare("SELECT * FROM flat_photos WHERE flat_ref_no = ? ORDER BY is_primary DESC, photo_id ASC");
    $stmt->execute([$flat_ref_no]);
    $db_photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Use file system photos if available, otherwise use database photos
    $flat_photos = !empty($available_photos) ? $available_photos : $db_photos;
    
    // Fetch flat features
    $stmt = $pdo->prepare("SELECT * FROM flat_features WHERE flat_ref_no = ?");
    $stmt->execute([$flat_ref_no]);
    $flat_features_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process features into a simple array
    $flat_features = [];
    foreach ($flat_features_raw as $feature) {
        if (!empty($feature['feature_name'])) {
            $flat_features[] = $feature['feature_name'];
        }
    }
    
    // Fetch marketing information
    $stmt = $pdo->prepare("SELECT * FROM flat_marketing WHERE flat_ref_no = ?");
    $stmt->execute([$flat_ref_no]);
    $marketing_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process marketing info with proper null checking
    $marketing_info = [
        'landmarks' => [],
        'important_places' => []
    ];
    
    foreach ($marketing_raw as $marketing) {
        // Use safe_get to avoid undefined key errors
        $info_type = safe_get($marketing, 'info_type', '');
        $info_name = safe_get($marketing, 'info_name', '');
        $info_url = safe_get($marketing, 'info_url', '#');
        
        if (!empty($info_name)) {
            if ($info_type === 'landmark') {
                $marketing_info['landmarks'][] = [
                    'name' => $info_name,
                    'url' => $info_url
                ];
            } elseif ($info_type === 'important_place') {
                $marketing_info['important_places'][] = [
                    'name' => $info_name,
                    'url' => $info_url
                ];
            }
        }
    }
    
    // Add some default marketing info if none exists
    if (empty($marketing_info['landmarks']) && empty($marketing_info['important_places'])) {
        $marketing_info = [
            'landmarks' => [
                ['name' => 'Birzeit University', 'url' => '#'],
                ['name' => 'Local Market', 'url' => '#']
            ],
            'important_places' => [
                ['name' => 'Nearest School', 'url' => '#'],
                ['name' => 'Supermarket', 'url' => '#']
            ]
        ];
    }
    
} catch (PDOException $e) {
    // Handle database errors
    error_log("Database error in flat_detail.php: " . $e->getMessage());
    $page_title = "Error - Birzeit Flat Rent";
    require_once 'includes/header.php';
    echo "<section class='error-message'>";
    echo "<h2>Database Error</h2>";
    echo "<p>Sorry, there was an error retrieving the flat details. Please try again later.</p>";
    echo "<p><a href='index.php'>← Back to Search</a></p>";
    echo "</section>";
    require_once 'includes/footer.php';
    exit;
}

// Set page title with safe escaping
$page_title = "Flat Details: " . esc(safe_get($flat_details, 'flat_ref_no', 'Unknown')) . " - Birzeit Flat Rent";
require_once 'includes/header.php'; // Include the header
?>

<section class="flat-detail-container">
    <aside class="marketing-info">
        <h4>Nearby Information</h4>
        <?php if (!empty($marketing_info['landmarks'])): ?>
            <h5>Landmarks</h5>
            <ul>
                <?php foreach ($marketing_info['landmarks'] as $landmark): ?>
                    <li><a href="<?php echo esc(safe_get($landmark, 'url', '#')); ?>" target="_blank"><?php echo esc(safe_get($landmark, 'name', 'Unknown')); ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if (!empty($marketing_info['important_places'])): ?>
            <h5>Important Places</h5>
            <ul>
                <?php foreach ($marketing_info['important_places'] as $place): ?>
                    <li><a href="<?php echo esc(safe_get($place, 'url', '#')); ?>" target="_blank"><?php echo esc(safe_get($place, 'name', 'Unknown')); ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </aside>

    <section class="flatcard"> <!-- Flex container -->
        <section class="flat-photos">
            <h4>Photos</h4>
            <?php if (!empty($flat_photos)): ?>
                <section class="photo-gallery">
                    <?php foreach ($flat_photos as $photo): ?>
                        <?php 
                        // Handle both file system photos and database photos
                        $photo_path = safe_get($photo, 'path', safe_get($photo, 'photo_path', ''));
                        $is_primary = safe_get($photo, 'is_primary', false) || safe_get($photo, 'number', 0) === 1;
                        $description = safe_get($photo, 'description', safe_get($photo, 'photo_description', ''));
                        ?>
                        
                        <?php if (!empty($photo_path)): ?>
                            <section class="photo-container">
                                <img src="<?php echo esc($photo_path); ?>" 
                                     alt="<?php echo esc($description ?: 'Photo of flat ' . safe_get($flat_details, 'flat_ref_no', '')); ?>" 
                                     class="flat-photo">
                                
                                <?php if ($is_primary): ?>
                                    <span class="primary-photo-label">Main Photo</span>
                                <?php endif; ?>
                                
                                <?php if (!empty($description)): ?>
                                    <p class="photo-description"><?php echo esc($description); ?></p>
                                <?php endif; ?>
                                
                                <!-- Show filename for debugging -->
                                <small class="photo-info">
                                    File: <?php echo esc(basename($photo_path)); ?>
                                    <?php if (isset($photo['number'])): ?>
                                        | Photo #<?php echo esc($photo['number']); ?>
                                    <?php endif; ?>
                                </small>
                            </section>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </section>
                
                <section class="photo-summary">
                    <p><strong>Total Photos:</strong> <?php echo count($flat_photos); ?></p>
                    <p><strong>Photo Directory:</strong> images/<?php echo esc(safe_get($flat_details, 'flat_ref_no', '')); ?>/</p>
                </section>
                
            <?php else: ?>
                <section class="no-photos">
                    <p>No photos found for this flat.</p>
                    
                    <!-- Debug information -->
                    <section class="debug-info">
                        <h5>Debug Information:</h5>
                        <p><strong>Looking for photos in:</strong> images/<?php echo esc(safe_get($flat_details, 'flat_ref_no', '')); ?>/</p>
                        <p><strong>Expected format:</strong> <?php echo esc(safe_get($flat_details, 'flat_ref_no', '')); ?>_1.jpg, <?php echo esc(safe_get($flat_details, 'flat_ref_no', '')); ?>_2.jpg, etc.</p>
                        
                        <?php
                        // Check what files actually exist
                        $flat_ref = safe_get($flat_details, 'flat_ref_no', '');
                        $photo_dir = "images/$flat_ref/";
                        ?>
                        
                        <p><strong>Directory exists:</strong> <?php echo is_dir($photo_dir) ? 'Yes' : 'No'; ?></p>
                        
                        <?php if (is_dir($photo_dir)): ?>
                            <p><strong>Files in directory:</strong></p>
                            <ul>
                                <?php
                                $files = scandir($photo_dir);
                                foreach ($files as $file) {
                                    if ($file !== '.' && $file !== '..') {
                                        echo '<li>' . esc($file) . '</li>';
                                    }
                                }
                                ?>
                            </ul>
                        <?php endif; ?>
                    </section>
                </section>
            <?php endif; ?>
        </section>

        <section class="flat-description">
            <h3>Flat Details: <?php echo esc(safe_get($flat_details, 'flat_ref_no', 'Unknown')); ?></h3>
            <p><strong>Location:</strong> <?php echo esc(safe_get($flat_details, 'location', 'Not specified')); ?></p>
            <p><strong>Address:</strong> <?php echo esc(safe_get($flat_details, 'address', 'Not specified')); ?></p>
            <p><strong>Rent Cost:</strong> $<?php echo number_format((float)safe_get($flat_details, 'rent_cost', 0), 2); ?> / month</p>
            <?php if (!empty(safe_get($flat_details, 'rental_conditions', ''))): ?>
                <p><strong>Rental Conditions:</strong> <?php echo esc(safe_get($flat_details, 'rental_conditions', '')); ?></p>
            <?php endif; ?>
            <p><strong>Bedrooms:</strong> <?php echo esc(safe_get($flat_details, 'bedrooms', 'Not specified')); ?></p>
            <p><strong>Bathrooms:</strong> <?php echo esc(safe_get($flat_details, 'bathrooms', 'Not specified')); ?></p>
            <p><strong>Size:</strong> <?php echo esc(safe_get($flat_details, 'size_sqm', 'Not specified')); ?> sqm</p>
            <p><strong>Heating:</strong> <?php echo safe_get($flat_details, 'heating', 0) ? 'Yes' : 'No'; ?></p>
            <p><strong>Air Conditioning:</strong> <?php echo safe_get($flat_details, 'ac', 0) ? 'Yes' : 'No'; ?></p>
            <?php if (!empty(safe_get($flat_details, 'access_control', ''))): ?>
                <p><strong>Access Control:</strong> <?php echo esc(safe_get($flat_details, 'access_control', '')); ?></p>
            <?php endif; ?>
            
            <!-- Availability Information -->
            <section class="availability-info">
                <h4>Availability</h4>
                <p><strong>Available From:</strong> <?php echo esc(safe_get($flat_details, 'available_from', 'Not specified')); ?></p>
                <p><strong>Available To:</strong> <?php echo esc(safe_get($flat_details, 'available_to', 'Not specified')); ?></p>
                <p><strong>Status:</strong> <span class="status-<?php echo esc(safe_get($flat_details, 'status', 'unknown')); ?>"><?php echo ucfirst(esc(safe_get($flat_details, 'status', 'Unknown'))); ?></span></p>
            </section>
            
            <!-- Additional Features -->
            <?php if (!empty($flat_features)): ?>
                <section class="additional-features">
                    <h4>Additional Features</h4>
                    <ul>
                        <?php foreach ($flat_features as $feature): ?>
                            <li><?php echo esc($feature); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>
            
            <!-- Owner Information (if available) -->
            <?php if (!empty(safe_get($flat_details, 'owner_id', ''))): ?>
                <?php
                try {
                    $stmt = $pdo->prepare("SELECT first_name, last_name, email, mobile FROM users WHERE user_id = ? AND user_type = 'owner'");
                    $stmt->execute([safe_get($flat_details, 'owner_id', '')]);
                    $owner = $stmt->fetch(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    $owner = null;
                }
                ?>
                <?php if ($owner): ?>
                    <section class="owner-info">
                        <h4>Owner Contact</h4>
                        <p><strong>Name:</strong> <?php echo esc(safe_get($owner, 'first_name', '') . ' ' . safe_get($owner, 'last_name', '')); ?></p>
                        <p><strong>Email:</strong> <a href="mailto:<?php echo esc(safe_get($owner, 'email', '')); ?>"><?php echo esc(safe_get($owner, 'email', '')); ?></a></p>
                        <?php if (!empty(safe_get($owner, 'mobile', ''))): ?>
                            <p><strong>Mobile:</strong> <a href="tel:<?php echo esc(safe_get($owner, 'mobile', '')); ?>"><?php echo esc(safe_get($owner, 'mobile', '')); ?></a></p>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </section>

    <nav class="side-navigation">
        <h4>Actions</h4>
        <ul>
            <li><a href="request_preview.php?ref=<?php echo urlencode(safe_get($flat_details, 'flat_ref_no', '')); ?>" class="btn btn-primary">Request Flat Viewing Appointment</a></li>
            <li><a href="rent_flat.php?ref=<?php echo urlencode(safe_get($flat_details, 'flat_ref_no', '')); ?>" class="btn btn-success">Rent this Flat</a></li>
            <li><a href="index.php" class="btn btn-secondary">← Back to Search</a></li>
        </ul>
    </nav>
</section>

<?php
require_once 'includes/footer.php'; // Include the footer
ob_end_flush(); // End output buffering
?>

