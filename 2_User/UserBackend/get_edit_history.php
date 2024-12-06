<?php
require_once '../../2_User/UserBackend/userAuth.php';
require_once '../../2_User/UserBackend/db.php';

$login = new Login();
if (!$login->isLoggedIn()) {
    http_response_code(401);
    exit('Unauthorized');
}

if (!isset($_GET['report_id'])) {
    http_response_code(400);
    exit('Report ID required');
}

try {
    $sql = "SELECT h.*, u.fullname as editor_name 
            FROM report_edit_history h
            JOIN users u ON h.user_id = u.id
            WHERE h.report_id = ?
            ORDER BY h.edited_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_GET['report_id']]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($history);
} catch (Exception $e) {
    http_response_code(500);
    exit('Error fetching edit history');
} 