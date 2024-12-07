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
        
        $stmt = $pdo->prepare("SELECT id FROM feedback WHERE report_id = ?");
        $stmt->execute([$report_id]);
        if ($stmt->fetch()) {
            header("Location: ../UserFeatures/3.3_found_reports.php?error=duplicate");
            exit();
        }

        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO feedback (report_id, rating, feedback_text, feedback_date) 
                              VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
        $stmt->execute([$report_id, $rating, $feedback_text]);
    
        $stmt = $pdo->prepare("INSERT INTO feedbacks (user_id, feedback_text, rating, created_at) 
                              VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
        $stmt->execute([$user_id, $feedback_text, $rating]);
        
        $stmt = $pdo->prepare("UPDATE found_reports SET status = 'returned', 
                              return_confirmed_at = CURRENT_TIMESTAMP 
                              WHERE id = ?");
        $stmt->execute([$report_id]);
        $pdo->commit();
        ?>
        
        <!DOCTYPE html>
        <html>
        <head>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    title: 'Thank You!',
                    text: 'Your feedback has been submitted successfully!',
                    icon: 'success',
                    imageUrl: '../../3_Images/thank_you.gif',
                    imageWidth: 200,
                    imageHeight: 200,
                    confirmButtonColor: '#28a745',
                    allowOutsideClick: false
                }).then((result) => {
                    window.location.href = '../UserFeatures/3.3_found_reports.php';
                });
            </script>
        </body>
        </html>
        <?php
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Feedback submission error: " . $e->getMessage());
        header("Location: ../UserFeatures/3.3_found_reports.php?error=feedback");
        exit();
    }
} 