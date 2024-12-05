<?php
require_once 'userAuth.php';
require_once 'db.php';

$login = new Login();
if (!$login->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$report_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$report_id) {
    header('Location: view.php');
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
        case 'logout':
            $login->logout();
            header('Location: login.php');
            exit();
    }
}

$sql = "SELECT r.*, GROUP_CONCAT(ri.image_path) as images, 
        u.fullname as reporter_name, u.email as reporter_email
        FROM lost_reports r 
        LEFT JOIN report_images ri ON r.id = ri.report_id 
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.id = ?
        GROUP BY r.id";

$stmt = $pdo->prepare($sql);
$stmt->execute([$report_id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

$images = $report['images'] ? explode(',', $report['images']) : [];


date_default_timezone_set('Asia/Singapore'); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Details - <?= htmlspecialchars($report['cat_name']) ?></title>
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
        <div class="mb-4">
            <a href="view.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fas fa-arrow-left me-2"></i>Back to Reports
            </a>
        </div>

        <div class="card shadow">
            <div class="card-body p-4">
                <h4 class="card-title mb-4">Report Details</h4>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-cat me-2"></i>Cat Information
                            </h5>
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <p><strong>Cat Name:</strong><br>
                                            <?= htmlspecialchars($report['cat_name']) ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Breed:</strong><br>
                                            <?= htmlspecialchars($report['breed']) ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Color:</strong><br>
                                            <?= htmlspecialchars($report['color']) ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Age:</strong><br>
                                            <?= htmlspecialchars($report['age']) ?> years old</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Gender:</strong><br>
                                            <?= htmlspecialchars($report['gender']) ?></p>
                                        </div>
                                        <div class="col-12">
                                            <p><strong>Description:</strong><br>
                                            <?= nl2br(htmlspecialchars($report['description'])) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-clock me-2"></i>Last Seen Date and Time
                            </h5>
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <p><strong>Date:</strong><br>
                                            <?= htmlspecialchars($report['last_seen_date']) ?></p>
                                        </div>
                                        <div class="col-12">
                                            <p><strong>Time:</strong><br>
                                            <?= date('h:i A', strtotime($report['last_seen_time'])) ?></p>
                                        </div>
                                        <div class="col-12">
                                            <p><strong>Location:</strong><br>
                                            <?= htmlspecialchars($report['last_seen_location'] ?? 'Location not specified') ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-4">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-images me-2"></i>Cat Images
                            </h5>
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <?php if (!empty($images)): ?>
                                        <div id="reportImageCarousel" class="carousel slide rounded overflow-hidden" data-bs-ride="carousel">
                                            <div class="carousel-inner">
                                                <?php foreach ($images as $index => $image): ?>
                                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                                        <img src="<?= htmlspecialchars($image) ?>" 
                                                             class="d-block w-100" 
                                                             alt="<?= htmlspecialchars($report['cat_name']) ?>"
                                                             style="height: 300px; object-fit: cover;">
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php if (count($images) > 1): ?>
                                                <button class="carousel-control-prev" type="button" data-bs-target="#reportImageCarousel" data-bs-slide="prev">
                                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Previous</span>
                                                </button>
                                                <button class="carousel-control-next" type="button" data-bs-target="#reportImageCarousel" data-bs-slide="next">
                                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Next</span>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center p-4">
                                            <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No images available</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-user me-2"></i>Contact Information
                            </h5>
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <p><strong>Reporter Name:</strong><br>
                                            <?= htmlspecialchars($report['reporter_name']) ?></p>
                                        </div>
                                        <div class="col-12">
                                            <p class="mb-0"><strong>Email:</strong><br>
                                            <?= htmlspecialchars($report['reporter_email']) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-muted">
                            <small><i class="far fa-clock me-1"></i>Report created on <?= (new DateTime($report['created_at']))->setTimezone(new DateTimeZone('Asia/Singapore'))->format('M j, Y g:i A') ?> (GMT+8)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html> 