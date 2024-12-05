<?php
require_once 'userAuth.php';
require_once 'db.php';

$login = new Login();
if (!$login->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

//Logout
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    $login->logout();
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    switch ($action) {
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
            
        case 'search':
            $search_query = isset($_POST['search']) ? $_POST['search'] : '';
            header("Location: search.php?q=" . urlencode($search_query));
            exit();
            
        case 'profile':
            header("Location: profile.php");
            exit();
            
        case 'view_report':
            $report_id = isset($_POST['report_id']) ? $_POST['report_id'] : '';
            header("Location: view_report.php?id=" . urlencode($report_id));
            exit();
            
        case 'view_lost_cat':
            $lost_cat_id = isset($_POST['lost_cat_id']) ? $_POST['lost_cat_id'] : '';
            header("Location: view_lost_cat.php?id=" . urlencode($lost_cat_id));
            exit();
            
        case 'view_lost':
            header("Location: lost_cats.php");
            exit();
    }
}

$username = $_SESSION['username'] ?? 'Guest';
$fullname = $_SESSION['fullname'] ?? 'Guest User';

// Get found reports only for the current user (either as owner or finder)
$sql = "SELECT fr.*, lr.cat_name, lr.breed, u.fullname as founder_name
        FROM found_reports fr
        JOIN lost_reports lr ON fr.report_id = lr.id
        JOIN users u ON fr.user_id = u.id
        WHERE lr.user_id = ? OR fr.user_id = ?  -- Show reports where user is either owner or finder
        ORDER BY fr.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$foundReports = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Found Cat Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles/style.css">
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
                    <button type="submit" name="action" value="myProfile" class="btn btn-link nav-link text-dark">
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
                <h2>Found Cat Reports</h2>
                <div class="d-flex align-items-center gap-3">
                    <?php include 'notifications.php'; ?>
                    <form method="POST" class="m-0">
                        <button type="submit" name="action" value="profile" class="btn rounded-circle p-0" style="width: 50px; height: 50px; overflow: hidden; border: none;">
                            <img src="images/cat-user.png" alt="user profile" style="width: 100%; height: 100%; object-fit: cover;">
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="main-content">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    <?php if (empty($foundReports)): ?>
                        <div class="text-center">
                            <p class="lead">No found cat reports yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($foundReports as $report): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker"></div>
                                    <div class="card shadow-sm mb-4">
                                        <div class="row g-0">
                                            <?php if ($report['image_path']): ?>
                                                <div class="col-md-4">
                                                    <img src="<?php echo htmlspecialchars($report['image_path']); ?>" 
                                                         class="img-fluid rounded-start h-100"
                                                         style="object-fit: cover; cursor: pointer;"
                                                         alt="Found cat image"
                                                         data-bs-toggle="modal"
                                                         data-bs-target="#imageModal<?php echo $report['id']; ?>">
                                                </div>
                                            <?php endif; ?>
                                            <div class="col-md-<?php echo $report['image_path'] ? '8' : '12'; ?>">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h4 class="card-title"><?php echo htmlspecialchars($report['cat_name']); ?></h4>
                                                            <p class="text-muted small">
                                                                <?php 
                                                                    $date = new DateTime($report['created_at']);
                                                                    echo $date->format('M j, Y g:i A');
                                                                ?>
                                                            </p>
                                                        </div>
                                                        <?php 
                                                        if ($report['user_id'] == $_SESSION['user_id']) {
                                                            echo '<span class="badge bg-success">Finder</span>';
                                                        } else {
                                                            echo '<span class="badge bg-primary">Owner</span>';
                                                        }
                                                        ?>
                                                    </div>
                                                    
                                                    <div class="report-details mt-3">
                                                        <div class="row">
                                                            <div class="col-sm-6">
                                                                <p><i class="fas fa-paw me-2"></i><?php echo htmlspecialchars($report['breed']); ?></p>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <p><i class="fas fa-user me-2"></i><?php echo htmlspecialchars($report['founder_name']); ?></p>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <p><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($report['contact_number']); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="message-container mt-3 p-3 bg-light rounded">
                                                        <h6 class="message-header">
                                                            <i class="fas fa-comment-alt me-2"></i>Message:
                                                        </h6>
                                                        <div class="message-content">
                                                            <?php echo nl2br(htmlspecialchars($report['owner_notification'])); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Image Modal -->
                                <?php if ($report['image_path']): ?>
                                    <div class="modal fade" id="imageModal<?php echo $report['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Found Cat Image</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-center">
                                                    <img src="<?php echo htmlspecialchars($report['image_path']); ?>" 
                                                         class="img-fluid" 
                                                         alt="Found cat image">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 