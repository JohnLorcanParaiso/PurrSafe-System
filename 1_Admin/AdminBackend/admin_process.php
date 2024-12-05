<?php
session_start();

$ADMIN_USERNAME = "admin";
$ADMIN_PASSWORD = "admin123";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Please enter both username and password";
        header('Location: admin_login.php');
        exit();
    }
    
    // Check against static credentials
    if ($username === $ADMIN_USERNAME && $password === $ADMIN_PASSWORD) {
        // Set admin session
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_role'] = 'admin';
        
        // Handle remember me
        if (isset($_POST['rememberMe'])) {
            setcookie('admin_login', $username, time() + (86400 * 30), "/"); // 30 days
        }
        
        $_SESSION['success'] = "Welcome back, Administrator!";
        header('Location: admin_features/1_dashboard.php');
        exit();
    } else {
        $_SESSION['error'] = "Invalid admin credentials";
        header('Location: admin_login.php');
        exit();
    }
} else {
    header('Location: admin_login.php');
    exit();
}
?>