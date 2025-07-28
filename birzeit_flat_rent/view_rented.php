<?php
$page_title = "My Rented Flats - Birzeit Flat Rent";
require_once 'includes/database.inc.php';
require_once 'includes/functions.php';

$page_title = "My Rented Flats - Birzeit Flat Rent";
require_once 'includes/database.inc.php';
require_once 'includes/functions.php';

// --- Authentication Check ---
// if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
//     redirect('login.php');
// }
// $customer_id = $_SESSION['user_id'];

// Get sorting preferences before any output
$sort_column = $_GET['sort'] ?? $_COOKIE['sort_column'] ?? 'default_column_name';
$sort_order = $_GET['order'] ?? $_COOKIE['sort_order'] ?? 'ASC';

// Store preferences in cookies (must be before output)
setcookie('sort_column', $sort_column, time() + (86400 * 30), "/");
setcookie('sort_order', $sort_order, time() + (86400 * 30), "/");

require_once 'includes/header.php';

// Then in your database query, add:
// ORDER BY $sort_column $sort_order

// --- Fetch Rented Flats Logic (Placeholder) ---
// TODO: Fetch flats rented by the current customer ($customer_id) from the database
// TODO: Categorize flats into 'current' and 'past' based on rental dates
// TODO: Implement sorting based on headers and cookies (descending by start date default)

// $rented_flats = []; // Placeholder
// $sort_column = $_COOKIE["rented_sort_column"] ?? 'rental_start_date'; // Default sort
// $sort_order = $_COOKIE["rented_sort_order"] ?? 'DESC'; // Default order


// --- Sorting Icon Helper (Copied from index.php, consider moving to functions.php) ---
function get_sort_icon($column_name, $current_sort_column, $current_sort_order) {
    if ($column_name === $current_sort_column) {
        return $current_sort_order === 'ASC' ? ' &#9650;' : ' &#9660;'; // ▲ or ▼
    }
    return '';
}

?>

<h2>My Rented Flats</h2>

<div class="rented-flats-section">
    <?php if (!empty($rented_flats)): ?>
        <table class="results-table rented-table"> 
            <thead>
                <tr>
                    <th>
                      <a href="?sort=column_name&order=<?php echo ($sort_column == 'column_name' && $sort_order == 'ASC') ? 'DESC' : 'ASC'; ?>">
                        Column Title<?php echo getSortIcon('column_name', $sort_column, $sort_order); ?>
                      </a>
                    </th>
                    <th><a href="#">Flat Reference<?php echo get_sort_icon('flat_ref_no', $sort_column, $sort_order); ?></a></th>
                    <th><a href="#">Monthly Cost<?php echo get_sort_icon('monthly_rental_cost', $sort_column, $sort_order); ?></a></th>
                    <th><a href="#">Start Date<?php echo get_sort_icon('rental_start_date', $sort_column, $sort_order); ?></a></th>
                    <th><a href="#">End Date<?php echo get_sort_icon('rental_end_date', $sort_column, $sort_order); ?></a></th>
                    <th><a href="#">Location<?php echo get_sort_icon('location', $sort_column, $sort_order); ?></a></th>
                    <th><a href="#">Owner<?php echo get_sort_icon('owner_name', $sort_column, $sort_order); ?></a></th>
                    <th>Status</th> 
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rented_flats as $flat): ?>
                    <tr class="status-<?php echo esc($flat['status']); ?>"> 
                        <td>
                            <a href="flat_detail.php?ref=<?php echo urlencode($flat['flat_ref_no']); ?>" target="_blank" class="button-link">
                                <?php echo esc($flat['flat_ref_no']); ?>
                            </a>
                        </td>
                        <td><?php echo esc($flat['monthly_rental_cost']); ?></td>
                        <td><?php echo esc($flat['rental_start_date']); ?></td>
                        <td><?php echo esc($flat['rental_end_date']); ?></td>
                        <td><?php echo esc($flat['location']); ?></td>
                        <td>
                            <a href="user_card.php?id=<?php echo urlencode($flat['owner_id']); ?>&type=owner" target="_blank">
                                <?php echo esc($flat['owner_name']); ?>
                            </a>
                        </td>
                        <td><?php echo esc(ucfirst($flat['status'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You have not rented any flats yet.</p>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
?>
