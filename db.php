<?php

class Database {
    private $host = "localhost";
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

    public function getRecentReports($limit = 5) {
        $stmt = $this->pdo->prepare("
            SELECT lr.id, lr.user_id, lr.cat_name, lr.breed, lr.gender, lr.age, lr.color, lr.description, 
                   lr.last_seen_date, lr.last_seen_time, lr.owner_name, lr.phone_number, lr.created_at, 
                   lr.last_seen_location, ri.image_path 
            FROM lost_reports lr
            LEFT JOIN report_images ri ON lr.id = ri.report_id
            ORDER BY lr.created_at DESC 
            LIMIT ?
        ");
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollBack() {
        return $this->pdo->rollBack();
    }
}

$db = new Database();
$pdo = $db->pdo;
?>