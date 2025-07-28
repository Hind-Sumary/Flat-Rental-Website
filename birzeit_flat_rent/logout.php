<?php
session_start(); // Access the existing session.
require_once 'includes/functions.php';

// Unset all session variables.
$_SESSION = [];

// This will destroy the session!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), 
              '', 
              time() - 42000,
              $params["path"], 
              $params["domain"],
              $params["secure"], 
              $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();
//cookie_distroy();

// Redirect to the homepage or login page after logout.
redirect('index.php');
?>