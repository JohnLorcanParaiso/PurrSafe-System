<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class AdminLogin {
    private $conn;

    public function __construct() {
        require_once '../../2_User/UserBackend/db.php';
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function isLoggedIn() {
        return isset($_SESSION['admin_id']) && $_SESSION['admin_role'] === 'admin';
    }

    public function login($username, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_role'] = 'admin';
                return true;
            }
        }
        return false;
    }

    public function logout() {
        session_unset();
        session_destroy();
        return true;
    }
} 