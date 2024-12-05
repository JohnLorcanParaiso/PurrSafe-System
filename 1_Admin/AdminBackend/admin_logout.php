<?php
session_start();

// Check if the logout confirmation is set
if (isset($_GET['confirm']) && $_GET['confirm'] === 'true') {
    // Clear the session
    $_SESSION = [];

    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }

    // Clear remember me cookie if it exists
    if (isset($_COOKIE['admin_login'])) {
        setcookie('admin_login', '', time() - 3600, '/'); // Clear the remember me cookie
    }

    // Destroy the session
    session_destroy();

    // Redirect to admin login
    header('Location: /Admin/admin_login.php'); // Redirect to the correct login page
    exit();
} else {
    // If not confirmed, redirect back to the dashboard or show a confirmation message
    header('Location: /Admin/1_dashboard.php'); // Redirect to the dashboard
    exit();
}
?>