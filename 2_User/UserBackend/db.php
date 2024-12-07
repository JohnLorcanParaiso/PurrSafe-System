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
        try {
            $stmt = $this->pdo->prepare("
                SELECT lr.*, u.id as user_id, u.fullname
                FROM lost_reports lr
                JOIN users u ON lr.user_id = u.id
                WHERE lr.status = 'missing'
                ORDER BY lr.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getRecentMissingReports: " . $e->getMessage());
            return [];
        }
    }

    public function getFoundReportsForUser() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    fr.*,
                    lr.cat_name,
                    fr.created_at as reported_date,
                    u.fullname as founder_name,
                    u.profile_image,
                    fr.image_path,
                    fr.user_id as founder_id
                FROM found_reports fr
                JOIN lost_reports lr ON fr.report_id = lr.id
                JOIN users u ON fr.user_id = u.id
                WHERE lr.user_id = ?
                ORDER BY fr.created_at DESC
            ");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching found reports: " . $e->getMessage());
            return [];
        }
    }

    public function getUnreadMissingReports() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT lr.*, u.id as user_id, u.fullname
                FROM lost_reports lr
                JOIN users u ON lr.user_id = u.id
                JOIN notifications n ON lr.id = n.report_id
                WHERE lr.status = 'missing' 
                AND n.is_read = 0
                ORDER BY lr.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getUnreadMissingReports: " . $e->getMessage());
            return [];
        }
    }

    public function getUnreadFoundReports() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT fr.* 
                FROM found_reports fr
                JOIN notifications n ON fr.id = n.report_id
                WHERE n.user_id = ? AND n.is_read = 0
                AND n.notification_type = 'found'
            ");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching unread found reports: " . $e->getMessage());
            return [];
        }
    }

    public function addNotification($user_id, $title, $message, $report_id, $type) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO notifications (user_id, title, message, report_id, type, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            return $stmt->execute([$user_id, $title, $message, $report_id, $type]);
        } catch (Exception $e) {
            error_log("Error adding notification: " . $e->getMessage());
            return false;
        }
    }

    public function getAllNotifications($user_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT n.*, lr.cat_name, lr.last_seen_date, lr.last_seen_time,
                       fr.created_at as found_date,
                       u.fullname
                FROM notifications n
                LEFT JOIN lost_reports lr ON n.report_id = lr.id
                LEFT JOIN found_reports fr ON n.report_id = fr.id
                LEFT JOIN users u ON lr.user_id = u.id OR fr.user_id = u.id
                WHERE n.user_id = ?
                ORDER BY n.created_at DESC
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting notifications: " . $e->getMessage());
            return [];
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}

$db = new Database();
$pdo = $db->pdo;
?>