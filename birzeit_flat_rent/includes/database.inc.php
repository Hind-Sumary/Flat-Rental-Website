<?php
// database.inc.php
// Database configuration and connection details.

// Define database connection constants
define('DB_HOST', 'localhost:3306');
define('DB_NAME', 'web1213151_birzeit_flat_rent');
define('DB_USER', 'web1213151_dbuser'); 
define('DB_PASS', 'Hans2512$');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully";
} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage());
}
?>