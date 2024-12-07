<?php
require_once '../../2_User/UserBackend/userAuth.php';

$login = new Login();
if (!$login->isLoggedIn()) {
    header('Location: ../../2_User/UserBackend/login.php');
    exit();
}

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

$sql = "SELECT fr.*, lr.cat_name, lr.breed, u.fullname as founder_name
        FROM found_reports fr
        JOIN lost_reports lr ON fr.report_id = lr.id
        JOIN users u ON fr.user_id = u.id
        WHERE (lr.user_id = ? OR fr.user_id = ?)";

if (isset($_GET['id'])) {
    $sql .= " AND fr.id = ?";
}

$sql .= " GROUP BY fr.id ORDER BY fr.created_at DESC";

$stmt = $pdo->prepare($sql);

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        <div class="row">
            <?php foreach ($found_reports as $report): ?>
                <div class="col-12 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-cat me-2"></i><?= htmlspecialchars($report['cat_name']) ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <?php if (!empty($report['image_path'])): ?>
                                        <img src="../../<?= htmlspecialchars($report['image_path']) ?>" 
                                             class="img-fluid rounded mb-3" 
                                             alt="Found cat image"
                                             style="width: 100%; height: 300px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="no-image-container mb-3 rounded bg-light d-flex flex-column align-items-center justify-content-center" 
                                             style="height: 300px; border: 2px dashed #ccc;">
                                            <i class="fas fa-image text-muted mb-2" style="font-size: 48px;"></i>
                                            <p class="text-muted mb-0">No Image Uploaded</p>
                                            <small class="text-muted">The founder didn't provide a photo</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="border-bottom pb-2">Cat Details</h6>
                                    <p><strong>Name:</strong> <?= htmlspecialchars($report['cat_name']) ?></p>
                                    <p><strong>Breed:</strong> <?= htmlspecialchars($report['breed']) ?></p>
                                    <p><strong>Found By:</strong> <?= htmlspecialchars($report['founder_name']) ?></p>
                                    <p><strong>Contact:</strong> <?= htmlspecialchars($report['contact_number']) ?></p>
                                    <p><strong>Message:</strong> <?= nl2br(htmlspecialchars($report['owner_notification'])) ?></p>
                                    <p><strong>Date Found:</strong> <?= date('M j, Y', strtotime($report['created_at'])) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>