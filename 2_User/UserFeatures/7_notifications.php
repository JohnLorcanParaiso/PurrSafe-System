<?php
require_once '../../2_User/UserBackend/db.php';

$missingReports = $db->getRecentMissingReports();
$foundReports = $db->getFoundReportsForUser();

if (isset($_POST['mark_as_read'])) {
    try {
        $stmt = $pdo->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$_SESSION['user_id']]);
        exit(json_encode(['success' => true]));
    } catch (Exception $e) {
        exit(json_encode(['success' => false]));
    }
}

if (isset($_POST['clear_notifications'])) {
    try {
        $stmt = $pdo->prepare("
            DELETE FROM notifications 
            WHERE user_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        exit(json_encode(['success' => true]));
    } catch (Exception $e) {
        exit(json_encode(['success' => false]));
    }
}

$unreadMissingReports = $db->getUnreadMissingReports();
$unreadFoundReports = $db->getUnreadFoundReports();
$notificationCount = count($unreadMissingReports) + count($unreadFoundReports);
function getAllNotifications($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

$allNotifications = getAllNotifications($pdo, $_SESSION['user_id']);
$recentNotifications = array_slice($allNotifications, 0, 5);
$olderNotifications = array_slice($allNotifications, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" as="style">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../4_Styles/user_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css">
    <style>
        
    </style>
</head>
<body>
    <div class="dropdown">
        <button class="btn btn-outline-secondary position-relative dropdown-toggle" 
                type="button" 
                id="notificationDropdown" 
                data-bs-toggle="dropdown" 
                data-bs-auto-close="true"
                aria-expanded="false">
            <i class="bi bi-bell" style="font-size: 20px;"></i>
            <?php if ($notificationCount > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" id="notificationBadge">
                    <?php echo $notificationCount; ?>
                </span>
            <?php endif; ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
            <li class="d-flex justify-content-between align-items-center px-3 py-2">
                <h6 class="dropdown-header m-0" style="font-size: 16px;">Notifications</h6>
                <?php if ($notificationCount > 0): ?>
                    <button class="btn btn-sm btn-outline-danger clear-notifications" style="font-size: 12px;">
                        Clear All
                    </button>
                <?php endif; ?>
            </li>
            
            <?php if (!empty($foundReports)): ?>
                <li><h6 class="dropdown-header text-success" style="font-size: 14px;">Found Cat Reports</h6></li>
                <?php foreach (array_slice($foundReports, 0, 3) as $report): ?>
                    <li>
                        <a class="dropdown-item notification-item" href="3.3_found_reports.php?id=<?php echo $report['id']; ?>">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-check-circle text-success notification-icon" style="font-size: 50px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <?php
                                    $notificationMessages = [
                                        "🎉 <strong>{$report['founder_name']}</strong> found your fur baby <strong>{$report['cat_name']}</strong>! They're safe and sound.",
                                        "💖 Great news! <strong>{$report['cat_name']}</strong> is safe with <strong>{$report['founder_name']}</strong>.",
                                        "🌟 <strong>{$report['founder_name']}</strong> has found <strong>{$report['cat_name']}</strong>! Your precious one is safe."
                                    ];
                                    
                                    $randomMessage = $notificationMessages[array_rand($notificationMessages)];
                                    ?>
                                    <p class="notification-text mb-2" style="font-size: 15px; line-height: 1.5;">
                                        <?php echo $randomMessage; ?>
                                    </p>
                                    <small class="notification-time d-block mt-1">
                                        Reported: <?php echo date('M j, Y g:i A', strtotime($report['reported_date'])); ?>
                                    </small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($missingReports)): ?>
                <li><h6 class="dropdown-header text-warning" style="font-size: 14px;">Missing Cat Reports</h6></li>
                <?php foreach (array_slice($missingReports, 0, 3) as $report): ?>
                    <li>
                        <a class="dropdown-item notification-item" href="3.2_view_more.php?id=<?php echo $report['id']; ?>" style="font-size: 14px; padding: 10px 15px;">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-exclamation-circle text-warning notification-icon" style="font-size: 50px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <?php
                                    if ($report['user_id'] == $_SESSION['user_id']) {
                                        $selfReportMessages = [
                                            "🔍 You reported your missing fur baby <strong>{$report['cat_name']}</strong>. We'll help spread the word!",
                                            "📢 Your report for <strong>{$report['cat_name']}</strong> has been posted. Stay hopeful!",
                                            "💝 We've shared that <strong>{$report['cat_name']}</strong> is missing. The community will help look!",
                                            "🙏 Your missing report for <strong>{$report['cat_name']}</strong> is active. Don't lose hope!",
                                            "💫 Alert posted for your beloved <strong>{$report['cat_name']}</strong>. Let's find them together!"
                                        ];
                                        $message = $selfReportMessages[array_rand($selfReportMessages)];
                                    } else {
                                        $missingNotificationMessages = [
                                            "💔 <strong>{$report['fullname']}</strong> just lost their fur baby <strong>{$report['cat_name']}</strong>. Let's help them reunite!",
                                            "🔍 Help needed! <strong>{$report['cat_name']}</strong> is missing. Keep an eye out for this precious one.",
                                            "😿 <strong>{$report['fullname']}</strong>'s beloved <strong>{$report['cat_name']}</strong> is missing. Please help them find their way home.",
                                            "🙏 Missing alert! <strong>{$report['cat_name']}</strong> hasn't returned home. Let's help bring them back.",
                                            "⚠️ <strong>{$report['cat_name']}</strong> is lost and their family misses them dearly. Keep your eyes open!"
                                        ];
                                        $message = $missingNotificationMessages[array_rand($missingNotificationMessages)];
                                    }
                                    ?>
                                    <p class="notification-text mb-2" style="font-size: 15px; line-height: 1.5;">
                                        <?php echo $message; ?>
                                    </p>
                                    <small class="notification-time" style="font-size: 12px; line-height: 1.5;">
                                        Last seen: <?php echo date('M j, Y', strtotime($report['last_seen_date'])) . ' ' . 
                                                       date('g:i A', strtotime($report['last_seen_time'])); ?>
                                    </small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php 
            $totalOlderNotifications = count($foundReports) + count($missingReports) - 6;
            if ($totalOlderNotifications > 0): 
            ?>
                <li>
                    <a href="notification_history.php" class="dropdown-item notification-item text-primary">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>View Previous Notifications</span>
                            <span class="badge bg-primary rounded-pill"><?php echo $totalOlderNotifications; ?></span>
                        </div>
                    </a>
                </li>
            <?php endif; ?>
            
            <?php if (empty($missingReports) && empty($foundReports)): ?>
                <li><p class="dropdown-item text-muted">No notifications</p></li>
            <?php endif; ?>
        </ul>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
            const dropdownList = [...dropdownElementList].map(dropdownToggleEl => new bootstrap.Dropdown(dropdownToggleEl));
            const notificationBtn = document.getElementById('notificationDropdown');
            if (notificationBtn) {
                notificationBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const dropdown = bootstrap.Dropdown.getInstance(notificationBtn) || new bootstrap.Dropdown(notificationBtn);
                    dropdown.toggle();
                });

                notificationBtn.setAttribute('data-bs-toggle', 'dropdown');
                notificationBtn.addEventListener('shown.bs.dropdown', function() {
                    fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'mark_as_read=1'
                    });
                });
            }
        });
    </script>
</body>
</html>