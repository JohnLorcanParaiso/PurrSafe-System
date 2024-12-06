<?php
require_once '../../2_User/UserBackend/userAuth.php';
require_once '../../2_User/UserBackend/db.php';

$login = new Login();
if (!$login->isLoggedIn()) {
    header('Location: ../../2_User/UserBackend/login.php');
    exit();
}

$db = new Database();
$allNotifications = $db->getAllNotifications($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../4_Styles/user_style.css">
    <style>
        .notification-item {
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
        }
        .notification-item:hover {
            background-color: #f8f9fa;
            border-left-color: #0d6efd;
        }
        .notification-item.missing {
            border-left-color: #ffc107;
        }
        .notification-item.found {
            border-left-color: #198754;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center bg-white">
                <h4 class="mb-0">Notification History</h4>
                <a href="1_user_dashboard.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($allNotifications)): ?>
                    <div class="list-group">
                        <?php foreach ($allNotifications as $notification): ?>
                            <a href="<?php 
                                echo $notification['type'] === 'missing' 
                                    ? '3.2_view_more.php?id=' . $notification['report_id']
                                    : '3.3_found_reports.php?id=' . $notification['report_id']; 
                                ?>" 
                               class="list-group-item list-group-item-action notification-item <?php echo $notification['type']; ?>">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <h6 class="mb-1">
                                        <?php if ($notification['type'] === 'missing'): ?>
                                            <i class="bi bi-exclamation-circle text-warning me-2"></i>
                                        <?php else: ?>
                                            <i class="bi bi-check-circle text-success me-2"></i>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($notification['title']); ?>
                                    </h6>
                                    <small class="text-muted">
                                        <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?>
                                    </small>
                                </div>
                                <p class="mb-1"><?php echo $notification['message']; ?></p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted">No notification history found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 