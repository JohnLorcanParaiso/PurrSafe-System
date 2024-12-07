<?php
session_start();
require_once '../../2_User/UserBackend/userAuth.php';
require_once '../../2_User/UserBackend/db.php';

$login = new Login();
if (!$login->isLoggedIn()) {
    header('Location: ../../2_User/UserBackend/login.php');
    exit();
}

$showAlert = false;
$alertType = '';
$alertMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user_id = filter_var($_POST['user_id']);
        $issue_type = filter_var($_POST['issue_type']);
        $description = filter_var($_POST['description']);
        
        if (!$user_id || !$issue_type || !$description) {
            $showAlert = true;
            $alertType = 'error';
            $alertMessage = 'Please fill in all required fields.';
        } else {
            // Insert the ticket
            $stmt = $pdo->prepare("
                INSERT INTO support_tickets (user_id, issue_type, description, status) 
                VALUES (?, ?, ?, 'pending')
            ");
            
            if ($stmt->execute([$user_id, $issue_type, $description])) {
                $showAlert = true;
                $alertType = 'success';
                $alertMessage = 'Your support ticket has been submitted successfully!';
            } else {
                throw new Exception('Failed to insert ticket');
            }
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        $showAlert = true;
        $alertType = 'error';
        $alertMessage = 'An error occurred while submitting your ticket. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Ticket</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        <?php if ($showAlert): ?>
            Swal.fire({
                title: '<?php echo $alertType === 'success' ? 'Success!' : 'Error!'; ?>',
                text: '<?php echo $alertMessage; ?>',
                icon: '<?php echo $alertType; ?>',
                confirmButtonColor: '<?php echo $alertType === 'success' ? '#3085d6' : '#d33'; ?>'
            }).then((result) => {
                window.location.href = '../../2_User/UserFeatures/5_help_and_support.php';
            });
        <?php else: ?>
            window.location.href = '../../2_User/UserFeatures/5_help_and_support.php';
        <?php endif; ?>
    </script>
</body>
</html> 