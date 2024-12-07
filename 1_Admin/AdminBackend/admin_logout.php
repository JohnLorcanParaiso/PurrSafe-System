<?php
session_start();

if (isset($_GET['confirm']) && $_GET['confirm'] === 'true') {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }

    header('Location: ../../1_Admin/AdminBackend/admin_login.php');
    exit();
} else {
    header('Location: ../AdminFeatures/1_admin_dashboard.php');
    exit();
}
?>