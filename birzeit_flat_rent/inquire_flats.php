<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

$page_title = "Inquire Flats - Birzeit Flat Rent";
require_once 'includes/database.inc.php'; // Include PDO connection
require_once 'includes/functions.php';   // Include helper functions

// Set cookies BEFORE including header.php
$sort_column = $_GET['sort'] ?? $_COOKIE['sort_column'] ?? 'rent_cost';
$sort_order = $_GET['order'] ?? $_COOKIE['sort_order'] ?? 'ASC';

// Store preferences in cookies
setcookie('sort_column', $sort_column, time() + (86400 * 30), "/");
setcookie('sort_order', $sort_order, time() + (86400 * 30), "/");

// Include header
require_once 'includes/header.php';

// Initialize search parameters
$location = $_GET['location'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$bedrooms = $_GET['bedrooms'] ?? '';
$bathrooms = $_GET['bathrooms'] ?? '';
$furnished = $_GET['furnished'] ?? '';

$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$exact_date = $_GET['exact_date'] ?? '';
$owner_id = $_GET['owner_id'] ?? '';
$customer_id = $_GET['customer_id'] ?? '';

// Initialize search results
$search_results = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET)) {
    try {
        $sql = "SELECT f.flat_ref_no, f.location, f.address, f.rent_cost, 
                       f.available_from, f.bedrooms, f.bathrooms, f.size_sqm, 
                       fp.photo_path as photo_url
                FROM flats f
                LEFT JOIN flat_photos fp ON f.flat_ref_no = fp.flat_ref_no AND fp.is_primary = TRUE
                WHERE f.status = 'available'";
        
        $params = [];

        if (!empty($location)) {
            $sql .= " AND f.location LIKE ?";
            $params[] = '%' . $location . '%';
        }

        if (!empty($min_price)) {
            $sql .= " AND f.rent_cost >= ?";
            $params[] = $min_price;
        }

        if (!empty($max_price)) {
            $sql .= " AND f.rent_cost <= ?";
            $params[] = $max_price;
        }

        if (!empty($bedrooms)) {
            $sql .= " AND f.bedrooms >= ?";
            $params[] = $bedrooms;
        }

        if (!empty($bathrooms)) {
            $sql .= " AND f.bathrooms >= ?";
            $params[] = $bathrooms;
        }

        if ($furnished !== '') {
            $sql .= " AND f.furnished = ?";
            $params[] = $furnished;
        }

        if (!empty($from_date)) {
            $sql .= " AND f.available_from >= ?";
            $params[] = $from_date;
        }

        if (!empty($to_date)) {
            $sql .= " AND f.available_from <= ?";
            $params[] = $to_date;
        }

        if (!empty($exact_date)) {
            $sql .= " AND f.available_from = ?";
            $params[] = $exact_date;
        }

        if (!empty($owner_id)) {
            $sql .= " AND f.owner_id = ?";
            $params[] = $owner_id;
        }

        if (!empty($customer_id)) {
            $sql .= " AND EXISTS (
                        SELECT 1 FROM rented_flats r 
                        WHERE r.flat_ref_no = f.flat_ref_no 
                          AND r.customer_id = ?
                     )";
            $params[] = $customer_id;
        }

        $valid_columns = ['flat_ref_no', 'rent_cost', 'available_from', 'location', 'bedrooms'];
        $valid_orders = ['ASC', 'DESC'];

        if (!in_array($sort_column, $valid_columns)) $sort_column = 'rent_cost';
        if (!in_array($sort_order, $valid_orders)) $sort_order = 'ASC';

        $sql .= " ORDER BY f.$sort_column $sort_order";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Database error in search: " . $e->getMessage());
    }
} else {
    try {
        $stmt = $pdo->query("SELECT f.flat_ref_no, f.location, f.address, f.rent_cost, 
                            f.available_from, f.bedrooms, f.bathrooms, f.size_sqm, 
                            fp.photo_path as photo_url
                            FROM flats f
                            LEFT JOIN flat_photos fp ON f.flat_ref_no = fp.flat_ref_no AND fp.is_primary = TRUE
                            WHERE f.status = 'available'
                            ORDER BY f.rent_cost ASC
                            LIMIT 10");
        $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error loading default flats: " . $e->getMessage());
    }
}

function get_sort_icon($column_name, $current_sort_column, $current_sort_order) {
    if ($column_name === $current_sort_column) {
        return $current_sort_order === 'ASC' ? ' &#9650;' : ' &#9660;';
    }
    return '';
}
?>

<section class="search-section">
    <h2>Search Available Flats</h2>
    <form action="index.php" method="GET" class="search-form">

        <!-- Existing filters -->
        <section class="form-group">
            <label for="location">Location:</label>
            <input type="text" id="location" name="location" value="<?php echo esc($location); ?>">
        </section><br>
        <section class="form-group">
            <label for="min_price">Min Price:</label>
            <input type="number" id="min_price" name="min_price" step="50" value="<?php echo esc($min_price); ?>">
        </section><br>
        <section class="form-group">
            <label for="max_price">Max Price:</label>
            <input type="number" id="max_price" name="max_price" step="50" value="<?php echo esc($max_price); ?>">
        </section><br>
        <section class="form-group">
            <label for="bedrooms">Bedrooms:</label>
            <input type="number" id="bedrooms" name="bedrooms" min="1" value="<?php echo esc($bedrooms); ?>">
        </section><br>
        <section class="form-group">
            <label for="bathrooms">Bathrooms:</label>
            <input type="number" id="bathrooms" name="bathrooms" min="1" value="<?php echo esc($bathrooms); ?>">
        </section><br>
        <section class="form-group">
            <label for="furnished">Furnished:</label>
            <select id="furnished" name="furnished">
                <option value="">Any</option>
                <option value="1" <?php echo $furnished === '1' ? 'selected' : ''; ?>>Yes</option>
                <option value="0" <?php echo $furnished === '0' ? 'selected' : ''; ?>>No</option>
            </select>
        </section><br>

        <!-- New manager filters -->
        <section class="form-group">
            <label for="from_date">Available From:</label>
            <input type="date" id="from_date" name="from_date" value="<?php echo esc($from_date); ?>">
        </section><br>
        <section class="form-group">
            <label for="to_date">Available To:</label>
            <input type="date" id="to_date" name="to_date" value="<?php echo esc($to_date); ?>">
        </section><br>
        <section class="form-group">
            <label for="exact_date">Available On (Exact Date):</label>
            <input type="date" id="exact_date" name="exact_date" value="<?php echo esc($exact_date); ?>">
        </section><br>
        <section class="form-group">
            <label for="owner_id">Owner ID:</label>
            <input type="text" id="owner_id" name="owner_id" value="<?php echo esc($owner_id); ?>">
        </section><br>
        <section class="form-group">
            <label for="customer_id">Customer ID (Rented By):</label>
            <input type="text" id="customer_id" name="customer_id" value="<?php echo esc($customer_id); ?>">
        </section><br>

        <button type="submit">Search</button>
    </form>

    <section class="results-section">
        <h3>Search Results</h3>
        <?php if (!empty($search_results)): ?>
            <table class="results-table">
                <thead>
                    <tr>
                        <th><a href="?sort=flat_ref_no&order=<?php echo ($sort_column == 'flat_ref_no' && $sort_order == 'ASC') ? 'DESC' : 'ASC'; ?>">Flat Ref<?php echo get_sort_icon('flat_ref_no', $sort_column, $sort_order); ?></a></th>
                        <th><a href="?sort=rent_cost&order=<?php echo ($sort_column == 'rent_cost' && $sort_order == 'ASC') ? 'DESC' : 'ASC'; ?>">Rent<?php echo get_sort_icon('rent_cost', $sort_column, $sort_order); ?></a></th>
                        <th><a href="?sort=available_from&order=<?php echo ($sort_column == 'available_from' && $sort_order == 'ASC') ? 'DESC' : 'ASC'; ?>">Available<?php echo get_sort_icon('available_from', $sort_column, $sort_order); ?></a></th>
                        <th><a href="?sort=location&order=<?php echo ($sort_column == 'location' && $sort_order == 'ASC') ? 'DESC' : 'ASC'; ?>">Location<?php echo get_sort_icon('location', $sort_column, $sort_order); ?></a></th>
                        <th><a href="?sort=bedrooms&order=<?php echo ($sort_column == 'bedrooms' && $sort_order == 'ASC') ? 'DESC' : 'ASC'; ?>">Bedrooms<?php echo get_sort_icon('bedrooms', $sort_column, $sort_order); ?></a></th>
                        <th>Photo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($search_results as $flat): ?>
                        <tr>
                            <td><?php echo esc($flat['flat_ref_no']); ?></td>
                            <td><?php echo esc($flat['rent_cost']); ?></td>
                            <td><?php echo esc($flat['available_from']); ?></td>
                            <td><?php echo esc($flat['location']); ?></td>
                            <td><?php echo esc($flat['bedrooms']); ?></td>
                            <td>
                                <?php
                                $photo_path = 'images/' . esc($flat['flat_ref_no']) . '/photo1.jpg';
                                ?>
                                <img src="<?php echo $photo_path; ?>" alt="Flat photo" height="50"
                                     onerror="this.onerror=null; this.src='images/flat_placeholder.jpg';">
                                <a href="flat_detail.php?ref=<?php echo urlencode($flat['flat_ref_no']); ?>" target="_blank">
                                    <img src="images/icon.png" alt="Details" height="20" style="margin-left: 6px;">
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No flats found matching your criteria.</p>
        <?php endif; ?>
    </section>
</section>

<?php
require_once 'includes/footer.php';
ob_end_flush();
?>
