<?php
require_once '../../2_User/UserBackend/userAuth.php';

$login = new Login();
if (!$login->isLoggedIn()) {
    header('Location: ../../2_User/UserBackend/login.php');
    exit();
}

$report_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$report_id) {
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
            header("Location: 3.1_view_reports.php?q=" . urlencode($search_query));
            exit();
        case 'logout':
            $login->logout();
            header('Location: ../UserAuth/login.php');
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
$report = $stmt->fetch();
$stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$_SESSION['profile_image'] = $user['profile_image'] ?? null;

function formatImagePath($image) {
    if (empty($image)) {
        return '../../3_Images/cat-user.png';
    }
    
    if (strpos($image, '../../5_Uploads/') === 0) {
        return $image;
    }
    return '../../5_Uploads/' . basename($image);
}

$images = $report['images'] ? explode(',', $report['images']) : [];
$formatted_images = array_map('formatImagePath', $images);
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
                    <button type="submit" name="action" value="others" class="btn btn-link nav-link text-dark active">
                        <i class="fas fa-ellipsis-h me-2"></i> Others
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
            <a href="4.1_my_profile.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fas fa-arrow-left me-2"></i>Back to My Profile
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
                                    <?php if (!empty($formatted_images)): ?>
                                        <div id="reportImageCarousel" class="carousel slide rounded overflow-hidden" data-bs-ride="carousel">
                                            <div class="carousel-inner">
                                                <?php foreach ($formatted_images as $index => $image): ?>
                                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                                        <img src="<?= htmlspecialchars(formatImagePath($image)) ?>" 
                                                             class="d-block w-100" 
                                                             alt="<?= htmlspecialchars($report['cat_name']) ?>"
                                                             style="height: 300px; object-fit: cover; cursor: pointer;"
                                                             onclick="openImageModal('<?= htmlspecialchars(formatImagePath($image)) ?>')"
                                                             data-index="<?= $index ?>">
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php if (count($formatted_images) > 1): ?>
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

                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Report Timeline</h6>
                            <div class="d-flex flex-column gap-2">
                                <div>
                                    <small class="text-muted">Created:</small>
                                    <br>
                                    <?= date('M j, Y g:i A', strtotime($report['created_at'])) ?>
                                </div>
                                <?php if ($report['edited_at']): ?>
                                <div>
                                    <small class="text-muted">Last Edited:</small>
                                    <br>
                                    <?= date('M j, Y g:i A', strtotime($report['edited_at'])) ?>
                                    <span class="badge bg-info">Edited</span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="image-counter"></span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <div class="modal-image-slider" id="imageSlider">
                        <div class="slider-container" id="sliderContainer">
                        </div>
                        <button class="slider-arrow prev" onclick="prevImage()">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="slider-arrow next" onclick="nextImage()">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
    let currentImageIndex = 0;
    let images = <?= json_encode($formatted_images) ?>;

    function openImageModal(imagePath) {
        currentImageIndex = images.indexOf(imagePath);
        const modal = new bootstrap.Modal(document.getElementById('imageModal'));
        updateModalImage();
        modal.show();
    }

    function prevImage() {
        if (currentImageIndex > 0) {
            currentImageIndex--;
            updateModalImage();
        }
    }

    function nextImage() {
        if (currentImageIndex < images.length - 1) {
            currentImageIndex++;
            updateModalImage();
        }
    }

    function updateModalImage() {
        const container = document.getElementById('sliderContainer');
        container.innerHTML = `<img src="${images[currentImageIndex]}" class="img-fluid" alt="Report Image">`;
        document.querySelector('.image-counter').textContent = `${currentImageIndex + 1}/${images.length}`;
        document.querySelector('.slider-arrow.prev').style.display = currentImageIndex > 0 ? 'block' : 'none';
        document.querySelector('.slider-arrow.next').style.display = currentImageIndex < images.length - 1 ? 'block' : 'none';
    }
    </script>
</body>
</html> 