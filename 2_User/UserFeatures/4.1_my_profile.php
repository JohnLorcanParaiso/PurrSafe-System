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
        case 'update_profile':
            break;
            
        case 'change_password':
            break;
            
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

        case 'logout':
            $login->logout();
            header("Location: ../../2_User/UserBackend/login.php");
            exit();
        
        case 'delete_report':
            if (isset($_POST['report_id'])) {
                try {
                    $pdo->beginTransaction();
                    
                    $deleteFoundStmt = $pdo->prepare("DELETE FROM found_reports WHERE report_id = ?");
                    $deleteFoundStmt->execute([$_POST['report_id']]);
                    
                    $deleteImagesStmt = $pdo->prepare("DELETE FROM report_images WHERE report_id = ?");
                    $deleteImagesStmt->execute([$_POST['report_id']]);
                    
                    $deleteReportStmt = $pdo->prepare("DELETE FROM lost_reports WHERE id = ? AND user_id = ?");
                    if ($deleteReportStmt->execute([$_POST['report_id'], $_SESSION['user_id']])) {
                        $pdo->commit();
                        $_SESSION['success_message'] = "Report deleted successfully.";
                    } else {
                        throw new Exception("Failed to delete report");
                    }
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $_SESSION['error_message'] = "An error occurred while deleting the report.";
                }
                
                header("Location: 4.1_my_profile.php");
                exit();
            }
            break;
        
        case 'edit':
            if (isset($_POST['report_id'])) {
                header("Location: 4.2_edit_report.php?id=" . (int)$_POST['report_id']);
                exit();
            }
            break;
        
        case 'view_details':
            if (isset($_POST['report_id'])) {
                header("Location: 3.2_view_more.php?id=" . $_POST['report_id']);
                exit();
            }
            break;
    }
}

$username = $_SESSION['username'] ?? 'Guest';
$fullname = $_SESSION['fullname'] ?? 'Guest User';
$email = $_SESSION['email'] ?? '';

$reportsQuery = "SELECT r.*, 
                GROUP_CONCAT(DISTINCT ri.image_path) as images,
                CASE 
                    WHEN r.edited_at IS NOT NULL THEN 1
                    ELSE 0
                END as is_edited
                FROM lost_reports r 
                LEFT JOIN report_images ri ON r.id = ri.report_id 
                WHERE r.user_id = ? 
                GROUP BY r.id 
                ORDER BY r.created_at DESC";
$stmt = $pdo->prepare($reportsQuery);
$stmt->execute([$_SESSION['user_id']]);
$userReports = $stmt->fetchAll(PDO::FETCH_ASSOC);

error_reporting(E_ALL);
ini_set('display_errors', 1);

$stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$_SESSION['profile_image'] = $user['profile_image'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile_picture') {
    $response = ['status' => 'error', 'message' => ''];
    
    try {
        if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Upload error: " . $_FILES['profile_picture']['error']);
        }

        $file = $_FILES['profile_picture'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        error_log("File type: " . $file['type']);
        error_log("File size: " . $file['size']);
        
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception("Invalid file type. Only JPG, PNG, and GIF are allowed. Received: " . $file['type']);
        }

        $uploadDir = __DIR__ . "/../../6_Profile_Pictures/";
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception("Failed to create upload directory");
            }
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newFilename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
        $targetPath = $uploadDir . $newFilename;

        error_log("Target path: " . $targetPath);

        $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $currentUser = $stmt->fetch();

        if ($currentUser && !empty($currentUser['profile_image'])) {
            $oldFile = $uploadDir . $currentUser['profile_image'];
            if (file_exists($oldFile) && $currentUser['profile_image'] !== 'cat-user.png') {
                unlink($oldFile);
            }
        }

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception("Failed to move uploaded file. PHP Error: " . error_get_last()['message']);
        }

        $updateStmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
        if (!$updateStmt->execute([$newFilename, $_SESSION['user_id']])) {
            throw new Exception("Database update failed: " . implode(", ", $updateStmt->errorInfo()));
        }

        $relativePath = "../../6_Profile_Pictures/" . $newFilename;

        $response = [
            'status' => 'success',
            'message' => 'Profile picture updated successfully!',
            'newImage' => $relativePath,
            'debug' => [
                'filename' => $newFilename,
                'fullPath' => $targetPath,
                'userId' => $_SESSION['user_id']
            ]
        ];

    } catch (Exception $e) {
        error_log("Profile picture update error: " . $e->getMessage());
        $response = [
            'status' => 'error',
            'message' => $e->getMessage(),
            'debug' => error_get_last()
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../4_Styles/user_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>User Profile</title>
</head>
<body>
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
        echo $_SESSION['success_message'];
        unset($_SESSION['success_message']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php 
        echo $_SESSION['error_message'];
        unset($_SESSION['error_message']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

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
                <button type="submit" name="action" value="others" class="btn btn-link nav-link text-dark">
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
    <header class="header-container mb-4">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <form id="searchForm" class="d-flex flex-grow-1">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search..">
                    <button type="submit" class="btn btn-outline-secondary">
                        <img src="../../3_Images/search.png" alt="search" style="width: 20px;">
                    </button>
                </div>
            </form>
            <div class="d-flex align-items-center gap-3">
                    <?php include '7_notifications.php'; ?>
                    <form method="POST" class="m-0">
                        <button type="submit" name="action" value="profile" class="btn rounded-circle p-0" style="width: 50px; height: 50px; overflow: hidden; border: none;">
                            <img src="<?= !empty($_SESSION['profile_image']) ? '../../6_Profile_Pictures/' . htmlspecialchars($_SESSION['profile_image']) : '../../3_Images/cat-user.png' ?>" 
                                 alt="user profile" 
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        </button>
                </form>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="position-relative d-inline-block">
                                        <?php
                                        $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
                                        $stmt->execute([$_SESSION['user_id']]);
                                        $user = $stmt->fetch();
                                        $profileImage = !empty($user['profile_image']) ? "../../6_Profile_Pictures/" . $user['profile_image'] : '../../3_Images/cat-user.png';
                                        ?>
                                        <img src="<?= htmlspecialchars($profileImage) ?>" 
                                             alt="Profile Picture" 
                                             id="profileImageDisplay"
                                             class="rounded-circle profile-image" 
                                             style="width: 100px; height: 100px; object-fit: cover;">
                                        
                                        <button type="button" 
                                                class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle"
                                                style="width: 32px; height: 32px; padding: 0;"
                                                onclick="document.getElementById('profilePictureInput').click();">
                                            <i class="fas fa-camera"></i>
                                        </button>
                                    </div>

                                    <form id="profilePictureForm" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="action" value="update_profile_picture">
                                        <input type="file" id="profilePictureInput" name="profile_picture" accept="image/*" style="display: none;">
                                    </form>
                                </div>
                                <div class="col">
                                    <h4 class="mb-1"><?= htmlspecialchars($fullname) ?></h4>
                                    <p class="text-muted mb-0">@<?= htmlspecialchars($username) ?></p>
                                    <p class="text-muted mb-0"><?= htmlspecialchars($email) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                Reports Lost
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($userReports)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-folder-open text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
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
                                                <th>Last Edited</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($userReports as $report): ?>
                                                <tr>
                                                    <td style="width: 100px;">
                                                        <?php 
                                                        $images = array_filter(explode(',', $report['images']));
                                                        if (!empty($images)): 
                                                            $imageCount = count($images);
                                                        ?>
                                                            <div class="position-relative image-preview-container">
                                                                <div class="table-image-slider" id="slider_<?= $report['id'] ?>" data-current="0">
                                                                    <?php foreach ($images as $index => $image): 
                                                                        $imagePath = strpos($image, '../../5_Uploads/') === 0 
                                                                            ? $image 
                                                                            : "../../5_Uploads/" . trim($image);
                                                                    ?>
                                                                        <img src="<?= htmlspecialchars($imagePath) ?>" 
                                                                             alt="Report Image <?= $index + 1 ?>" 
                                                                             class="rounded report-thumbnail"
                                                                             onclick="openImageModal('<?= htmlspecialchars($imagePath) ?>')"
                                                                             style="width: 80px; height: 80px; object-fit: cover;">
                                                                    <?php endforeach; ?>
                                                                </div>
                                                                <?php if ($imageCount > 1): ?>
                                                                    <button class="slider-nav-btn prev" onclick="prevSlide('slider_<?= $report['id'] ?>')">
                                                                        <i class="fas fa-chevron-left"></i>
                                                                    </button>
                                                                    <button class="slider-nav-btn next" onclick="nextSlide('slider_<?= $report['id'] ?>')">
                                                                        <i class="fas fa-chevron-right"></i>
                                                                    </button>
                                                                    <div class="image-count-badge">
                                                                        <small><span class="current-index">1</span>/<?= $imageCount ?></small>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="rounded bg-light d-flex align-items-center justify-content-center" 
                                                                 style="width: 80px; height: 80px;">
                                                                <i class="fas fa-cat text-muted" style="font-size: 2rem;"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <h6 class="mb-1 d-flex align-items-center gap-2">
                                                            <?= htmlspecialchars($report['cat_name']) ?>
                                                            <?php if ($report['is_edited']): ?>
                                                                <span class="badge bg-purple-subtle text-purple" 
                                                                      data-bs-toggle="tooltip" 
                                                                      title="This report has been edited">
                                                                    <i class="fas fa-pen-fancy"></i>
                                                                </span>
                                                            <?php endif; ?>
                                                        </h6>
                                                        <small class="text-muted"><?= htmlspecialchars($report['breed']) ?></small>
                                                    </td>
                                                    <td>
                                                        <?php if (isset($report['status']) && $report['status'] === 'found'): ?>
                                                            <span class="badge bg-success">Found</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning">Lost</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?= date('M j, Y, g:i A', strtotime($report['created_at'])) ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <?php if ($report['edited_at']): ?>
                                                            <small class="text-muted">
                                                                <?= date('M j, Y, g:i A', strtotime($report['edited_at'])) ?>
                                                            </small>
                                                        <?php else: ?>
                                                            <small class="text-muted">-</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="action" value="view_details">
                                                                <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                            </form>
                                                            
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="action" value="edit">
                                                                <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                            </form>
                                                            
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="action" value="delete_report">
                                                                <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                                                                <button type="button" onclick="confirmDelete(this.form)" class="btn btn-sm btn-outline-danger">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </form>
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
        </div>
    </main>
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

<div class="modal fade" id="imageZoomModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <img id="zoomImage" src="" alt="Report Image" class="img-fluid w-100">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="searchModalLabel">Search Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="searchResults" class="row g-4">
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
<script>
    function confirmDelete(form) {
        if (confirm('Are you sure you want to delete this report?')) {
            form.submit();
        }
    }

    let currentImages = [];
    let currentIndex = 0;
    let touchStartX = 0;
    let touchEndX = 0;
    let sliderContainer;
    let isDragging = false;
    let startPos = 0;
    let currentTranslate = 0;
    let prevTranslate = 0;

    function openImageSlider(images, startIndex = 0) {
        currentImages = JSON.parse(images);
        currentIndex = startIndex;
        
        sliderContainer = document.getElementById('sliderContainer');
        sliderContainer.innerHTML = '';
        
        currentImages.forEach((image, index) => {
            const imgDiv = document.createElement('div');
            imgDiv.className = 'slider-image';
            const img = document.createElement('img');
            img.src = image.startsWith('../../5_Uploads/') ? image : '../../5_Uploads/' + image.trim();
            img.alt = 'Cat Image ' + (index + 1);
            imgDiv.appendChild(img);
            sliderContainer.appendChild(imgDiv);
        });

        updateSliderPosition();
        const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
        imageModal.show();
        
        setTimeout(initializeSlider, 300);
    }

    function initializeSlider() {
        sliderContainer = document.getElementById('sliderContainer');
        
        sliderContainer.addEventListener('touchstart', touchStart);
        sliderContainer.addEventListener('touchmove', touchMove);
        sliderContainer.addEventListener('touchend', touchEnd);
        
        sliderContainer.addEventListener('mousedown', touchStart);
        sliderContainer.addEventListener('mousemove', touchMove);
        sliderContainer.addEventListener('mouseup', touchEnd);
        sliderContainer.addEventListener('mouseleave', touchEnd);
    }

    function touchStart(event) {
        isDragging = true;
        startPos = getPositionX(event);
        sliderContainer.style.transition = 'none';
    }

    function touchMove(event) {
        if (!isDragging) return;
        
        const currentPosition = getPositionX(event);
        currentTranslate = prevTranslate + currentPosition - startPos;
        const maxTranslate = 0;
        const minTranslate = -(currentImages.length - 1) * sliderContainer.offsetWidth;
        
        currentTranslate = Math.max(minTranslate, Math.min(currentTranslate, maxTranslate));
        
        sliderContainer.style.transform = `translateX(${currentTranslate}px)`;
    }

    function touchEnd() {
        isDragging = false;
        const movedBy = currentTranslate - prevTranslate;
        if (movedBy < -100 && currentIndex < currentImages.length - 1) {
            currentIndex++;
        }
        if (movedBy > 100 && currentIndex > 0) {
            currentIndex--;
        }
        
        updateSliderPosition();
    }

    function getPositionX(event) {
        return event.type.includes('mouse') ? event.pageX : event.touches[0].clientX;
    }

    function updateSliderPosition() {
        sliderContainer.style.transition = 'transform 0.3s ease-out';
        currentTranslate = -currentIndex * sliderContainer.offsetWidth;
        prevTranslate = currentTranslate;
        sliderContainer.style.transform = `translateX(${currentTranslate}px)`;

        const counter = document.querySelector('.image-counter');
        counter.textContent = `${currentIndex + 1}/${currentImages.length}`;
        
        const prevArrow = document.querySelector('.slider-arrow.prev');
        const nextArrow = document.querySelector('.slider-arrow.next');
        prevArrow.style.display = currentIndex === 0 ? 'none' : 'flex';
        nextArrow.style.display = currentIndex === currentImages.length - 1 ? 'none' : 'flex';
    }

    function prevImage() {
        if (currentIndex > 0) {
            currentIndex--;
            updateSliderPosition();
        }
    }

    function nextImage() {
        if (currentIndex < currentImages.length - 1) {
            currentIndex++;
            updateSliderPosition();
        }
    }

    document.addEventListener('keydown', function(e) {
        if (document.getElementById('imageModal').classList.contains('show')) {
            if (e.key === 'ArrowLeft') prevImage();
            if (e.key === 'ArrowRight') nextImage();
        }
    });

    document.getElementById('profilePictureInput').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (file.size > maxSize) {
                alert('File size must be less than 5MB');
                this.value = '';
                return;
            }

            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!allowedTypes.includes(file.type)) {
                alert('Please upload only JPG, JPEG, or PNG files');
                this.value = '';
                return;
            }
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const sliders = document.querySelectorAll('.table-image-slider');
        
        sliders.forEach(slider => {
            let startX;
            let currentX;
            let isDragging = false;
            let currentIndex = 0;
            const images = slider.querySelectorAll('img');
            const totalImages = images.length;
            
            function updateImagePositions() {
                images.forEach((img, index) => {
                    img.style.transform = `translateX(${(index - currentIndex) * 100}%)`;
                });

                const counter = slider.closest('.image-preview-container').querySelector('.current-index');
                if (counter) {
                    counter.textContent = currentIndex + 1;
                }
            }
            
            function handleTouchStart(e) {
                isDragging = true;
                startX = e.type === 'mousedown' ? e.pageX : e.touches[0].pageX;
                currentX = startX;
                
                slider.style.cursor = 'grabbing';
            }
            
            function handleTouchMove(e) {
                if (!isDragging) return;
                
                e.preventDefault();
                currentX = e.type === 'mousemove' ? e.pageX : e.touches[0].pageX;
                const diff = currentX - startX;
                
                if (Math.abs(diff) > 30) {
                    if (diff > 0 && currentIndex > 0) {
                        currentIndex--;
                        isDragging = false;
                        updateImagePositions();
                    } else if (diff < 0 && currentIndex < totalImages - 1) {
                        currentIndex++;
                        isDragging = false;
                        updateImagePositions();
                    }
                    startX = currentX;
                }
            }
            
            function handleTouchEnd() {
                isDragging = false;
                slider.style.cursor = 'grab';
            }
            
            slider.addEventListener('touchstart', handleTouchStart);
            slider.addEventListener('touchmove', handleTouchMove);
            slider.addEventListener('touchend', handleTouchEnd);
            slider.addEventListener('mousedown', handleTouchStart);
            slider.addEventListener('mousemove', handleTouchMove);
            slider.addEventListener('mouseup', handleTouchEnd);
            slider.addEventListener('mouseleave', handleTouchEnd);
            updateImagePositions();
        });
    });

    function prevSlide(sliderId) {
        const slider = document.getElementById(sliderId);
        const images = slider.querySelectorAll('img');
        const currentIndex = parseInt(slider.dataset.current);
        
        if (currentIndex > 0) {
            slider.dataset.current = currentIndex - 1;
            updateSlider(slider, images, currentIndex - 1);
        }
    }

    function nextSlide(sliderId) {
        const slider = document.getElementById(sliderId);
        const images = slider.querySelectorAll('img');
        const currentIndex = parseInt(slider.dataset.current);
        
        if (currentIndex < images.length - 1) {
            slider.dataset.current = currentIndex + 1;
            updateSlider(slider, images, currentIndex + 1);
        }
    }

    function updateSlider(slider, images, newIndex) {
        images.forEach((img, index) => {
            img.style.transform = `translateX(${(index - newIndex) * 100}%)`;
        });

        const counter = slider.closest('.image-preview-container').querySelector('.current-index');
        if (counter) {
            counter.textContent = newIndex + 1;
        }
    }

    function openImageModal(imagePath) {
        const zoomImage = document.getElementById('zoomImage');
        zoomImage.src = imagePath;
        const modal = new bootstrap.Modal(document.getElementById('imageZoomModal'));
        modal.show();
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.table-image-slider').forEach(slider => {
            const images = slider.querySelectorAll('img');
            updateSlider(slider, images, 0);
        });
    });

    document.getElementById('profilePictureInput').addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const formData = new FormData(document.getElementById('profilePictureForm'));
            const profileImage = document.getElementById('profileImageDisplay');
            profileImage.style.opacity = '0.5';
            console.log('Uploading file:', this.files[0]);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const allProfileImages = document.querySelectorAll('img[src*="6_Profile_Pictures"], img[src*="cat-user.png"]');
                    allProfileImages.forEach(img => {
                        img.src = data.newImage + '?t=' + new Date().getTime();
                    });
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Failed to update profile picture');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to update profile picture'
                });
            })
            .finally(() => {
                profileImage.style.opacity = '1';
            });
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        function updateProfileImages(newImageUrl) {
            const profileImages = document.querySelectorAll('img[src*="6_Profile_Pictures"], img[src*="cat-user.png"]');
            profileImages.forEach(img => {
                img.src = newImageUrl + '?t=' + new Date().getTime();
            });
        }

        const profilePictureInput = document.getElementById('profilePictureInput');
        if (profilePictureInput) {
            profilePictureInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const formData = new FormData();
                    formData.append('profile_picture', this.files[0]);

                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            updateProfileImages(data.newImage);
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            throw new Error(data.message);
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.message
                        });
                    });
                }
            });
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-report');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const form = this.closest('.delete-form');
                const reportId = form.querySelector('input[name="report_id"]').value;
                
                console.log('Deleting report:', reportId);
                
                Swal.fire({
                    title: 'Delete Report?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('report_id', reportId);
                        
                        fetch('delete_report.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            console.log('Response status:', response.status);
                            return response.json();
                        })
                        .then(data => {
                            console.log('Response data:', data);
                            
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'Report has been deleted successfully.',
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                throw new Error(data.message || 'Failed to delete report');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: error.message || 'Failed to delete report'
                            });
                        });
                    }
                });
            });
        });
    });

    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const searchQuery = this.querySelector('input[name="search"]').value;
        if (!searchQuery.trim()) return;

        fetch('search.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'search=' + encodeURIComponent(searchQuery)
        })
        .then(response => response.json())
        .then(data => {
            const resultsContainer = document.getElementById('searchResults');
            resultsContainer.innerHTML = '';

            if (data.results && data.results.length > 0) {
                data.results.forEach(report => {
                    const images = report.images ? report.images.split(',') : [];
                    const displayImage = images[0] ? formatImagePath(images[0]) : '../../3_Images/cat-user.png';
                    
                    const reportCard = `
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100">
                                <img src="${displayImage}" 
                                     class="card-img-top" 
                                     alt="${report.cat_name}"
                                     style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        ${report.cat_name}
                                        ${report.is_edited ? '<span class="badge bg-info">Edited</span>' : ''}
                                    </h5>
                                    <p class="card-text">
                                        <strong>Breed:</strong> ${report.breed}<br>
                                        <strong>Last Seen:</strong> ${report.last_seen_date}
                                    </p>
                                    <a href="3.2_view_more.php?id=${report.id}" 
                                       class="btn btn-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    `;
                    resultsContainer.innerHTML += reportCard;
                });
            } else {
                resultsContainer.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <img src="../../3_Images/no-data.png" alt="No results" 
                             style="width: 120px; opacity: 0.5;">
                        <p class="text-muted mt-3">No results found for "${data.query}"</p>
                    </div>
                `;
            }l
            const searchModal = new bootstrap.Modal(document.getElementById('searchModal'));
            searchModal.show();
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });

    function formatImagePath(image) {
        if (!image) return '../../3_Images/cat-user.png';
        return image.startsWith('../../5_Uploads/') ? image : '../../5_Uploads/' + image.split('/').pop();
    }
</script>
</body>
</html>