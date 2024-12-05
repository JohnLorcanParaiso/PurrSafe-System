<?php
require_once 'db.php';

$recentReports = $db->getRecentReports();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css">
</head>
<body>
    <div class="dropdown">
        <button class="btn btn-outline-secondary position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="images/notifications.png" alt="notifications" style="width: 20px;">
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?php echo count($recentReports); ?>
                <span class="visually-hidden">unread notifications</span>
            </span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown" style="width: 400px; max-height: 500px; overflow-y: auto; overflow-x: hidden;">
            <li><h6 class="dropdown-header" style="font-size: 16px; margin-bottom: 10px;">Notifications</h6></li>
            <li><hr class="dropdown-divider"></li>
            <?php foreach ($recentReports as $report): ?>
                <li>
                    <a class="dropdown-item notification-item" href="viewMore.php?id=<?php echo $report['id']; ?>" style="font-size: 14px; padding: 10px 15px; white-space: normal;">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-bell notification-icon" style="font-size: 50px;"></i>
                            </div>
                            <div class="flex-grow-1 ms-2" style="min-width: 0; max-width: 300px;">
                                <p class="notification-text" style="font-size: 14px; margin: 0; word-break: break-word; white-space: normal; overflow: hidden; text-overflow: ellipsis;">
                                    <?php 
                                    // Query to get found cats
                                    $foundCatsStmt = $pdo->prepare("
                                        SELECT cat_name, location, DATE_FORMAT(created_at, '%d/%m/%Y') as found_date 
                                        FROM found_reports 
                                        WHERE status = 'active'
                                        ORDER BY created_at DESC
                                    ");
                                    $foundCatsStmt->execute();
                                    $foundCat = $foundCatsStmt->fetch();

                                    if ($foundCat) {
                                        echo "Found cat '" . htmlspecialchars($foundCat['cat_name']) . 
                                             "' in " . htmlspecialchars($foundCat['location']) . 
                                             ". Click for details.";
                                    } else {
                                        echo "No found cats reported.";
                                    }
                                    ?>
                                </p>
                                <small class="notification-time text-muted" style="font-size: 12px; line-height: 1.5; display: block;">
                                    Last seen: <?php echo htmlspecialchars($report['last_seen_date'] . ' ' . $report['last_seen_time']); ?>
                                </small>
                            </div>
                        </div>
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html> 