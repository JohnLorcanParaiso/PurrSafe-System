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

    public function getRecentMissingReports() {
        $query = "SELECT lr.id, lr.cat_name, lr.last_seen_date, lr.last_seen_time 
                  FROM lost_reports lr
                  ORDER BY lr.created_at DESC
                  LIMIT 10";
                  
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (mysqli_sql_exception $e) {
            error_log("Error getting recent missing reports: " . $e->getMessage());
            return [];
        }
    }

    public function getFoundReportsForUser() {
        $userId = $_SESSION['user_id'];
        
        $query = "SELECT 
                    fr.id,
                    'found_match' as type,
                    lr.cat_name,
                    fr.created_at as reported_date
                  FROM found_reports fr
                  JOIN lost_reports lr ON fr.report_id = lr.id
                  WHERE 
                    (lr.user_id = ?) /* Someone found your lost cat */
                    OR 
                    (fr.user_id = ?) /* You found someone's cat */
                  ORDER BY fr.created_at DESC
                  LIMIT 10";
                  
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $userId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (mysqli_sql_exception $e) {
            error_log("Error getting found reports: " . $e->getMessage());
            return [];
        }
    }
}

$db = new Database();
$pdo = $db->pdo;
?>