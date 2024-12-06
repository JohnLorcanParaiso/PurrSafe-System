<?php
require_once '../../2_User/UserBackend/userAuth.php';
require_once '../../2_User/UserBackend/db.php';

$login = new Login();
if (!$login->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $report_id = $_POST['report_id'];
        $rating = $_POST['rating'];
        $feedback_text = $_POST['feedback_text'];
        $user_id = $_SESSION['user_id'];
        
        // Begin transaction
        $pdo->beginTransaction();
        
        // Insert feedback
        $stmt = $pdo->prepare("INSERT INTO feedbacks (user_id, report_id, rating, feedback_text) 
                              VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $report_id, $rating, $feedback_text]);
        
        // Update report status
        $stmt = $pdo->prepare("UPDATE found_reports SET status = 'returned', 
                              return_confirmed_at = CURRENT_TIMESTAMP 
                              WHERE id = ?");
        $stmt->execute([$report_id]);
        
        $pdo->commit();
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} 