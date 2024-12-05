<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';

class Register extends Database {

    public function registerUser($fullname, $username, $email, $password) {
        try {
            // Sanitize inputs
            $username = $this->sanitize($username);
            $email = $this->sanitize($email);
            $fullname = $this->sanitize($fullname);
            
            // Check if username or email already exists
            $checkStmt = $this->conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $checkStmt->bind_param("ss", $username, $email);
            $checkStmt->execute();
            
            if ($checkStmt->get_result()->num_rows > 0) {
                throw new Exception("Username or email already exists");
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $this->conn->prepare("INSERT INTO users (fullname, username, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $fullname, $username, $email, $hashedPassword);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }
}

class Login extends Database {

    public function loginUser($username, $password) {
        try {
            $username = $this->sanitize($username);
            
            $stmt = $this->conn->prepare("SELECT id, username, email, fullname, password FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['fullname'] = $user['fullname'];
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function logout() {
        session_unset();
        session_destroy();
        return true;
    }

    public function getUserProfile($userId = null) {
        if ($userId === null) {
            $userId = $_SESSION['user_id'] ?? 0;
        }
        return $this->getUserData($userId);
    }

    public function updateProfile($userId, $data) {
        try {
            $fullname = $this->sanitize($data['fullname']);
            $email = $this->sanitize($data['email']);
            
            $stmt = $this->conn->prepare("UPDATE users SET fullname = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $fullname, $email, $userId);
            
            if ($stmt->execute()) {
                $_SESSION['fullname'] = $fullname;
                $_SESSION['email'] = $email;
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            return false;
        }
    }

    public function verifyPassword($password) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        $stmt = $this->pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return true;
        }
        return false;
    }

    public function deleteAccount($userId) {
        $db = new Database();
        
        try {
            $db->beginTransaction();
            
            // Delete reports first
            $stmt = $db->pdo->prepare("DELETE FROM reports WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // Then delete the user
            $stmt = $db->pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            
            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            return false;
        }
    }
}
?> 