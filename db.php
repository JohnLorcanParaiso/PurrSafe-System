<?php

// Prevent class redeclaration if the class already exists
if (!class_exists('Database')) {
    class Database {
        private $host = "localhost:3307";
        private $username = "root";
        private $password = "";
        private $database = "purrsafe_db";
        protected $conn;
        public $pdo;

        public function __construct() {
            try {
                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
                $this->conn = new mysqli(
                    $this->host,
                    $this->username,
                    $this->password,
                    $this->database
                );
                $this->conn->set_charset('utf8mb4');

                $this->pdo = new PDO(
                    "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4",
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (mysqli_sql_exception $e) {
                throw new Exception("Database connection failed: " . $e->getMessage());
            } catch (PDOException $e) {
                throw new Exception("PDO connection failed: " . $e->getMessage());
            }
        }

        protected function sanitize($input) {
            return htmlspecialchars(strip_tags(trim($input)));
        }

        protected function getUserData($userId) {
            $stmt = $this->conn->prepare("SELECT id, username, email, fullname FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }
    }
}

// Create the Database instance if it's not already created
if (!isset($db)) {
    $db = new Database();
    $pdo = $db->pdo;
}
?>