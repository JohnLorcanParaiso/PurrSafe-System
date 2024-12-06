<?php
session_start();
require_once '../../2_User/UserBackend/db.php';

// Check if ID is provided in URL
if (isset($_GET['id'])) {
    $db = new Database();
    $pdo = $db->pdo;
    
    try {
        $report_id = $_GET['id'];
        $user_id = $_SESSION['user_id'];

        // First verify if the report exists and belongs to the user
        $checkStmt = $pdo->prepare("SELECT id FROM lost_reports WHERE id = ? AND user_id = ?");
        $checkStmt->execute([$report_id, $user_id]);
        
        if (!$checkStmt->fetch()) {
            $_SESSION['error'] = "Report not found or you don't have permission to delete it.";
            header("Location: 4.1_my_profile.php");
            exit();
        }

        // Start transaction
        $pdo->beginTransaction();

        // Delete in correct order due to foreign key constraints
        
        // 1. Delete from found_reports
        $stmt = $pdo->prepare("DELETE FROM found_reports WHERE report_id = ?");
        $stmt->execute([$report_id]);

        // 2. Delete from report_images
        $stmt = $pdo->prepare("DELETE FROM report_images WHERE report_id = ?");
        $stmt->execute([$report_id]);

        // 3. Delete from notifications
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE report_id = ?");
        $stmt->execute([$report_id]);

        // 4. Finally delete from lost_reports
        $stmt = $pdo->prepare("DELETE FROM lost_reports WHERE id = ? AND user_id = ?");
        $stmt->execute([$report_id, $user_id]);

        // Commit transaction
        $pdo->commit();

        $_SESSION['success'] = "Report deleted successfully!";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Failed to delete report: " . $e->getMessage();
    }
    
    header("Location: 4.1_my_profile.php");
    exit();
}

// If no ID provided
$_SESSION['error'] = "No report specified for deletion.";
header("Location: 4.1_my_profile.php");
exit();
?> 