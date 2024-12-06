<?php
require_once '../../2_User/UserBackend/userAuth.php';
require_once '../../2_User/UserBackend/db.php';

$login = new Login();
if (!$login->isLoggedIn()) {
    header('Location: ../../2_User/UserBackend/login.php');
    exit();
}

//Logout
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    $login->logout();
    header('Location: ../../2_User/UserBackend/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    switch ($action) {
        case 'dashboard':
            header("Location: 1_user_dashboard.php");
            exit();

        case 'create':
            header("Location: 2.1_create_new_report.php");
            exit();

        case 'view':
            header("Location: 3.1_view_reports.php");
            exit();

        case 'myProfile':
            header("Location: 4.1_my_profile.php");
            exit();

        case 'help':
            header("Location: 5_help_and_support.php");
            exit();

        case 'others':
            header("Location: 6_others.php");
            exit();
            
        case 'search':
            $search_query = isset($_POST['search']) ? $_POST['search'] : '';
            header("Location: search.php?q=" . urlencode($search_query));
            exit();
            
        case 'profile':
            header("Location: 4.1_my_profile.php");
            exit();
    }
}

// Get found reports based on whether an ID was passed
$sql = "SELECT fr.*, lr.cat_name, lr.breed, u.fullname as founder_name 
        FROM found_reports fr
        JOIN lost_reports lr ON fr.report_id = lr.id
        JOIN users u ON fr.user_id = u.id
        WHERE (lr.user_id = ? OR fr.user_id = ?)";  // Base conditions

// Add ID filter if coming from notifications
if (isset($_GET['id'])) {
    $sql .= " AND fr.id = ?";
}

$sql .= " ORDER BY fr.created_at DESC";

$stmt = $pdo->prepare($sql);

// Execute with or without the ID parameter
if (isset($_GET['id'])) {
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_GET['id']]);
} else {
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
}

$found_reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" href="../../4_Styles/user_style.css">
</head>
<body>
    <div class="side-menu">
        <div class="text-center">
            <img src="../../3_Images/logo.png" class="logo" style="width: 150px; height: 150px; margin: 20px auto; display: block;">
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
                    <button type="submit" name="action" value="others" class="btn btn-link nav-link text-dark">
                        <i class="fas fa-cog me-2"></i> Others
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
            <a href="3.1_view_reports.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="fas fa-arrow-left me-2"></i>Back to Reports
            </a>
        </div>

        <?php foreach ($found_reports as $report): ?>
        <div class="card shadow border-primary border-2">
            <div class="card-header bg-primary text-white py-3">
                <h4 class="card-title mb-0">
                    <i class="fas fa-search me-2"></i>Found Cat Report
                </h4>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6 order-md-2">
                        <div class="mb-4">
                            <h5 class="text-primary mb-3 border-bottom pb-2">
                                <i class="fas fa-camera me-2"></i>Found Cat Photo
                            </h5>
                            <div class="card border-0">
                                <div class="card-body p-0">
                                    <?php if ($report['image_path']): ?>
                                        <div class="position-relative">
                                            <img src="<?= htmlspecialchars($report['image_path']) ?>" 
                                                 class="img-fluid rounded shadow-sm" 
                                                 alt="Found cat image"
                                                 style="width: 100%; height: 400px; object-fit: cover;">
                                            <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-dark bg-opacity-50 text-white">
                                                <small><i class="far fa-clock me-1"></i>Photo taken: <?= (new DateTime($report['created_at']))->format('M j, Y g:i A') ?></small>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center p-4 border rounded" style="height: 400px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                            <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No photo available</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 order-md-1">
                        <div class="mb-4">
                            <h5 class="text-primary mb-3 border-bottom pb-2">
                                <i class="fas fa-cat me-2"></i>Cat Details
                            </h5>
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <p><strong>Cat Name:</strong><br>
                                            <span class="text-primary"><?= htmlspecialchars($report['cat_name']) ?></span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Breed:</strong><br>
                                            <span class="text-primary"><?= htmlspecialchars($report['breed']) ?></span></p>
                                        </div>
                                        <div class="col-12">
                                            <p><strong>Message to Owner:</strong><br>
                                            <span class="text-muted"><?= nl2br(htmlspecialchars($report['owner_notification'])) ?></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="text-primary mb-3 border-bottom pb-2">
                                <i class="fas fa-user me-2"></i>Finder Information
                            </h5>
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <p class="mb-1"><i class="fas fa-user-circle me-2"></i><strong>Found By:</strong>
                                                <?= htmlspecialchars($report['founder_name']) ?></p>
                                            </div>
                                            <div class="col-12">
                                                <p class="mb-0"><i class="fas fa-phone me-2"></i><strong>Contact:</strong>
                                                <?= htmlspecialchars($report['contact_number']) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light text-muted py-3">
                <i class="fas fa-info-circle me-2"></i>Report created on <?= (new DateTime($report['created_at']))->format('M j, Y g:i A') ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>