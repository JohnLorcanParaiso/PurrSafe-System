<?php
session_start();

$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Clear remember me cookie if it exists
if (isset($_COOKIE['admin_login'])) {
    setcookie('admin_login', '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to admin login
header('Location: ../admin.php');
exit();
?>