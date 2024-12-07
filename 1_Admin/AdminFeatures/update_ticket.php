<?php
session_start();
require_once '../../1_Admin/AdminBackend/adminAuth.php';
require_once '../../2_User/UserBackend/db.php';

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $ticket_id = filter_var($_POST['ticket_id'], FILTER_VALIDATE_INT);
        $status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);
        $admin_response = filter_var($_POST['admin_response'], FILTER_SANITIZE_STRING);

        if (!$ticket_id || !$status || !$admin_response) {
            throw new Exception('Invalid input data');
        }

        $pdo = connect();
        $stmt = $pdo->prepare("
            UPDATE support_tickets 
            SET status = ?, 
                admin_response = ?,
                admin_id = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        if ($stmt->execute([$status, $admin_response, $_SESSION['admin_id'], $ticket_id])) {
            $_SESSION['success_message'] = 'Ticket updated successfully';
        } else {
            throw new Exception('Failed to update ticket');
        }

        header('Location: receive_ticket.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        header('Location: receive_ticket.php');
        exit();
    }
}

header('Location: receive_ticket.php');
exit(); 