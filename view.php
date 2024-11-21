<?php
require_once 'userAuth.php';

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

require_once 'db.php';

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
    }
}

$sql = "SELECT r.*, GROUP_CONCAT(ri.image_path) as images 
        FROM lost_reports r 
        LEFT JOIN report_images ri ON r.id = ri.report_id 
        GROUP BY r.id 
        ORDER BY r.created_at DESC";
$stmt = $pdo->query($sql);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

$username = $_SESSION['username'] ?? 'Guest';
$fullname = $_SESSION['fullname'] ?? 'Guest User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <button type="submit" name="action" value="view" class="btn btn-link nav-link text-dark active">
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
                        <i class="fas fa-question-circle me-2"></i> Help
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
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
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
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-4">
                            <h4 class="card-title mb-0">Missing Cat Reports</h4>
                        </div>
                        <div class="card-body">
                            <?php if (empty($reports)): ?>
                                <p class="text-center">No reports found.</p>
                            <?php else: ?>
                                <div class="row g-4">
                                    <?php foreach ($reports as $report): ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card h-100">
                                                <?php 
                                                $images = explode(',', $report['images']);
                                                if (!empty($images[0])): 
                                                ?>
                                                    <a href="viewMore.php?id=<?php echo $report['id']; ?>">
                                                        <img src="<?= htmlspecialchars($images[0]) ?>" 
                                                             class="card-img-top" 
                                                             alt="<?= htmlspecialchars($report['cat_name']) ?>"
                                                             style="height: 200px; object-fit: cover; cursor: pointer;">
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <div class="card-body">
                                                    <h5 
                                                        class="card-title"><?= htmlspecialchars($report['cat_name']) ?></h5>
                                                    <p class="card-text">
                                                        <strong>Breed:</strong> <?= htmlspecialchars($report['breed']) ?><br>
                                                        <strong>Last Seen:</strong> <?= htmlspecialchars($report['last_seen_date']) ?>
                                                    </p>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <a href="viewMore.php?id=<?php echo $report['id']; ?>" 
                                                           class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                                            <i class="fas fa-arrow-right me-1"></i> View Details
                                                        </a>
                                                        <a href="submit_found_cat.php?id=<?php echo $report['id']; ?>" 
                                                            class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                                            <i class="fas fa-arrow-right me-1"></i> Submit Found
                                                        </a>
                                                        <small class="text-muted">
                                                            <?php
                                                            $created = new DateTime($report['created_at']);
                                                            echo $created->format('M j, Y g:i A');
                                                            ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
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
</body>
</html> 