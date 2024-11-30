<?php
require_once 'userAuth.php';
require_once 'db.php';

$login = new Login();
if (!$login->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    switch ($action) {
        case 'update_profile':
            // Handle profile update logic here
            break;
            
        case 'change_password':
            // Handle password change logic here
            break;
            
        // Include the same navigation cases as dashboard
        case 'dashboard':
            header("Location: dashboard.php");
            exit();
        case 'create':
            header("Location: create.php");
            exit();
        case 'view':
            header("Location: view.php");
            exit();
        case 'myProfile':
            header("Location: profile.php");
            exit();
        case 'help':
            header("Location: help.php");
            exit();
        case 'settings':
            header("Location: settings.php");
            exit();
        case 'logout':
            $login->logout();
            header("Location: login.php");
            exit();
        
        // Handle delete report action
        case 'delete_report':
            if (isset($_POST['report_id'])) {
                $deleteStmt = $pdo->prepare("DELETE FROM lost_reports WHERE id = ? AND user_id = ?");
                if ($deleteStmt->execute([$_POST['report_id'], $_SESSION['user_id']])) {
                    $_SESSION['success_message'] = "Report deleted successfully.";
                } else {
                    $_SESSION['error_message'] = "Failed to delete report.";
                }
                header("Location: profile.php");
                exit();
            }
            break;
        
        case 'edit':
            if (isset($_POST['report_id'])) {
                header("Location: edit.php?id=" . $_POST['report_id']);
                exit();
            }
            break;
        
        case 'view_details':
            if (isset($_POST['report_id'])) {
                header("Location: viewMore.php?id=" . $_POST['report_id']);
                exit();
            }
            break;
    }
}

// Get user data from session
$username = $_SESSION['username'] ?? 'Guest';
$fullname = $_SESSION['fullname'] ?? 'Guest User';
$email = $_SESSION['email'] ?? '';

// Fetch user's reports
$reportsQuery = "SELECT r.*, GROUP_CONCAT(ri.image_path) as images 
                 FROM lost_reports r 
                 LEFT JOIN report_images ri ON r.id = ri.report_id 
                 WHERE r.user_id = ? 
                 GROUP BY r.id 
                 ORDER BY r.created_at DESC";
$stmt = $pdo->prepare($reportsQuery);
$stmt->execute([$_SESSION['user_id']]);
$userReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>User Profile</title>
</head>
<body>
<div class="side-menu">
    <div class="text-center">
        <img src="images/logo.png" class="logo" style="width: 150px; height: 150px; margin: 20px auto; display: block;">
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <form method="POST">
                <button type="submit" name="action" value="dashboard" class="btn btn-link nav-link text-dark">
                    <i class="fas fa-home me-2"></i> Dashboard
                </button>
            </form>
        </li>
        <li class="nav-item">
            <form method="POST">
                <button type="submit" name="action" value="create" class="btn btn-link nav-link text-dark">
                    <i class="fas fa-plus-circle me-2"></i> Create New Report
                </button>
            </form>
        </li>
        <li class="nav-item">
            <form method="POST">
                <button type="submit" name="action" value="view" class="btn btn-link nav-link text-dark">
                    <i class="fas fa-eye me-2"></i> View Reports
                </button>
            </form>
        </li>
        <li class="nav-item">
            <form method="POST">
                <button type="submit" name="action" value="myProfile" class="btn btn-link nav-link text-dark active">
                    <i class="fas fa-user me-2"></i> My Profile
                </button>
            </form>
        </li>
        <li class="nav-item">
            <form method="POST">
                <button type="submit" name="action" value="help" class="btn btn-link nav-link text-dark">
                    <i class="fas fa-question-circle me-2"></i> Help and Support
                </button>
            </form>
        </li>
        <li class="nav-item">
            <form method="POST">
                <button type="submit" name="action" value="settings" class="btn btn-link nav-link text-dark">
                    <i class="fas fa-cog me-2"></i> Settings
                </button>
            </form>
        </li>
        <li class="nav-item mt-auto">
            <form method="POST">
                <button type="submit" name="action" value="logout" class="btn btn-link nav-link text-dark">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </button>
            </form>
        </li>
    </ul>
</div>

<div class="container-custom">
    <header class="header-container mb-4">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <form method="POST" class="d-flex flex-grow-1">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search..">
                    <button type="submit" name="action" value="search" class="btn btn-outline-secondary">
                        <img src="images/search.png" alt="search" style="width: 20px;">
                    </button>
                </div>
            </form>
            <div class="d-flex align-items-center gap-3">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="images/notifications.png" alt="notifications" style="width: 20px;">
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            3
                            <span class="visually-hidden">unread notifications</span>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
                        <li><h6 class="dropdown-header">Notifications</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item notification-item" href="#">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <img src="images/notifications.png" alt="notification" class="notification-icon">
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <p class="notification-text">New cat reported missing in your area</p>
                                        <small class="notification-time">3 minutes ago</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item notification-item" href="#">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <img src="images/notifications.png" alt="notification" class="notification-icon">
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <p class="notification-text">Your report has been updated</p>
                                        <small class="notification-time">1 hour ago</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item notification-item" href="#">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <img src="images/notifications.png" alt="notification" class="notification-icon">
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <p class="notification-text">New match found for your lost cat</p>
                                        <small class="notification-time">2 hours ago</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-center view-all" href="#">View All Notifications</a>
                        </li>
                    </ul>
                </div>
                <form method="POST" class="m-0">
                    <button type="submit" name="action" value="profile" class="btn btn-outline-secondary rounded-circle p-2">
                        <img src="images/user.png" alt="user profile" style="width: 28px; height: 28px;">
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="row">
            <div class="col-md-3">
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center">
                        <img src="images/user.png" alt="Profile Picture" class="rounded-circle mb-3" style="width: 80px; height: 80px;">
                        <h5 class="mb-1"><?php echo htmlspecialchars($fullname); ?></h5>
                        <p class="text-muted mb-0">@<?php echo htmlspecialchars($username); ?></p>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0">Profile Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Full Name</label>
                            <p class="mb-2"><?php echo htmlspecialchars($fullname); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Username</label>
                            <p class="mb-2"><?php echo htmlspecialchars($username); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Email</label>
                            <p class="mb-2"><?php echo htmlspecialchars($email); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <!-- Reports Section -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Reports List</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($userReports)): ?>
                            <div class="text-center py-4">
                                <img src="images/no-data.png" alt="No Reports" style="width: 120px; opacity: 0.5;">
                                <p class="text-muted mt-3">You haven't created any reports yet.</p>
                                <form method="POST">
                                    <button type="submit" name="action" value="create" class="btn btn-custom">
                                        Create Your First Report
                                    </button>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Cat</th>
                                            <th>Details</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($userReports as $report): ?>
                                            <tr>
                                                <td style="width: 100px;">
                                                    <?php 
                                                    $images = explode(',', $report['images']);
                                                    if (!empty($images[0])): 
                                                    ?>
                                                        <img src="<?= htmlspecialchars($images[0]) ?>" 
                                                             alt="<?= htmlspecialchars($report['cat_name']) ?>" 
                                                             class="rounded" 
                                                             style="width: 80px; height: 80px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="rounded bg-light d-flex align-items-center justify-content-center" 
                                                             style="width: 80px; height: 80px;">
                                                            <i class="fas fa-cat text-muted" style="font-size: 2rem;"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <h6 class="mb-1"><?= htmlspecialchars($report['cat_name']) ?></h6>
                                                    <small class="text-muted"><?= htmlspecialchars($report['breed']) ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-warning">Active</span>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><?= date('M j, Y', strtotime($report['created_at'])) ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="viewMore.php?id=<?= $report['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="edit.php?id=<?= $report['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $report['id'] ?>)">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
<script>
    function confirmDelete(reportId) {
        if (confirm('Are you sure you want to delete this report?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete_report">
                <input type="hidden" name="report_id" value="${reportId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
</body>
</html>