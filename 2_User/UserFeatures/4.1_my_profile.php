<?php
require_once '../../2_User/UserBackend/userAuth.php';
require_once '../../2_User/UserBackend/db.php';

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
        
        // Handle delete report action
        case 'delete_report':
            if (isset($_POST['report_id'])) {
                try {
                    $pdo->beginTransaction();
                    
                    // Delete in correct order based on foreign key constraints
                    
                    // 1. First delete from found_reports
                    $deleteFoundStmt = $pdo->prepare("DELETE FROM found_reports WHERE report_id = ?");
                    $deleteFoundStmt->execute([$_POST['report_id']]);
                    
                    // 2. Delete from report_images
                    $deleteImagesStmt = $pdo->prepare("DELETE FROM report_images WHERE report_id = ?");
                    $deleteImagesStmt->execute([$_POST['report_id']]);
                    
                    // 3. Delete from notifications
                    $deleteNotifStmt = $pdo->prepare("DELETE FROM notifications WHERE report_id = ?");
                    $deleteNotifStmt->execute([$_POST['report_id']]);
                    
                    // 4. Finally delete from lost_reports
                    $deleteReportStmt = $pdo->prepare("DELETE FROM lost_reports WHERE id = ? AND user_id = ?");
                    if ($deleteReportStmt->execute([$_POST['report_id'], $_SESSION['user_id']])) {
                        $pdo->commit();
                        $_SESSION['swal_success'] = "Report deleted successfully!";
                    } else {
                        throw new Exception("Failed to delete report");
                    }
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    error_log("Delete error: " . $e->getMessage());
                    $_SESSION['swal_error'] = "An error occurred while deleting the report. Please try again.";
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

// Get user data from session
$username = $_SESSION['username'] ?? 'Guest';
$fullname = $_SESSION['fullname'] ?? 'Guest User';
$email = $_SESSION['email'] ?? '';

// Fetch user's reports
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

// At the beginning of the file, add error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch user's profile image
$stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$_SESSION['profile_image'] = $user['profile_image'] ?? null;

// Update the profile picture handling code
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile_picture') {
    $response = ['status' => 'error', 'message' => ''];
    
    try {
        if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Upload error: " . $_FILES['profile_picture']['error']);
        }

        $file = $_FILES['profile_picture'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        // Debug information
        error_log("File type: " . $file['type']);
        error_log("File size: " . $file['size']);
        
        // Verify file type
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception("Invalid file type. Only JPG, PNG, and GIF are allowed. Received: " . $file['type']);
        }

        // Create absolute path for upload directory
        $uploadDir = __DIR__ . "/../../6_Profile_Pictures/";
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception("Failed to create upload directory");
            }
        }

        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newFilename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
        $targetPath = $uploadDir . $newFilename;

        error_log("Target path: " . $targetPath);

        // Delete old profile picture
        $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $currentUser = $stmt->fetch();

        if ($currentUser && !empty($currentUser['profile_image'])) {
            $oldFile = $uploadDir . $currentUser['profile_image'];
            if (file_exists($oldFile) && $currentUser['profile_image'] !== 'cat-user.png') {
                unlink($oldFile);
            }
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception("Failed to move uploaded file. PHP Error: " . error_get_last()['message']);
        }

        // Update database
        $updateStmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
        if (!$updateStmt->execute([$newFilename, $_SESSION['user_id']])) {
            throw new Exception("Database update failed: " . implode(", ", $updateStmt->errorInfo()));
        }

        // Set the relative path for the response
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
            <form method="POST" class="d-flex flex-grow-1">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search..">
                    <button type="submit" name="action" value="search" class="btn btn-outline-secondary">
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
                    <!-- Add this new card for profile picture and user info -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="position-relative d-inline-block">
                                        <?php
                                        // Fetch profile image from database
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

                                    <!-- Hidden form for profile picture upload -->
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
<<<<<<< HEAD
                        </div>
                    </div>

                    <!-- Your existing reports card continues here -->
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
=======
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
                                                    $images = explode(',', $report['images']);
                                                    if (!empty($images[0])): 
                                                    ?>
                                                        <div class="report-images">
                                                            <?php foreach ($images as $index => $image): 
                                                                if ($index < 3): // Show up to 3 images ?>
                                                                    <img src="<?= htmlspecialchars($image) ?>" 
                                                                         alt="<?= htmlspecialchars($report['cat_name']) ?>" 
                                                                         class="rounded report-thumbnail"
                                                                         onclick="openImageModal('<?= htmlspecialchars($image) ?>')"
                                                                         style="width: 80px; height: 80px; object-fit: cover; cursor: pointer; margin: 2px;">
                                                                <?php endif; 
                                                                if ($index === 3): ?>
                                                                    <div class="more-images">+<?= count($images) - 3 ?></div>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </div>
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
                                                            <span class="badge text-white" style="background-color: #6f42c1;">Edited</span>
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
                                                            <input type="hidden" name="report_id" value="<?= (int)$report['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                        </form>
                                                        
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $report['id'] ?>)">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                </td>
>>>>>>> 256adb81c82fa779fef14d64a3bd7b54f2a4acb0
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
                                                                <input type="hidden" name="report_id" value="<?= (int)$report['id'] ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                            </form>
                                                            
                                                            <a href="delete_report.php?id=<?= $report['id'] ?>" 
                                                               class="btn btn-sm btn-outline-danger"
                                                               onclick="return confirm('Are you sure you want to delete this report?');">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
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
                        <!-- Images will be dynamically inserted here -->
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
        
        // Clear and populate the slider container
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
        
        // Initialize touch events after modal is shown
        setTimeout(initializeSlider, 300);
    }

    function initializeSlider() {
        sliderContainer = document.getElementById('sliderContainer');
        
        // Touch events
        sliderContainer.addEventListener('touchstart', touchStart);
        sliderContainer.addEventListener('touchmove', touchMove);
        sliderContainer.addEventListener('touchend', touchEnd);
        
        // Mouse events
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
        
        // Add boundaries
        const maxTranslate = 0;
        const minTranslate = -(currentImages.length - 1) * sliderContainer.offsetWidth;
        
        currentTranslate = Math.max(minTranslate, Math.min(currentTranslate, maxTranslate));
        
        sliderContainer.style.transform = `translateX(${currentTranslate}px)`;
    }

    function touchEnd() {
        isDragging = false;
        const movedBy = currentTranslate - prevTranslate;
        
        // If moved enough negative, next slide
        if (movedBy < -100 && currentIndex < currentImages.length - 1) {
            currentIndex++;
        }
        // If moved enough positive, previous slide
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
        
        // Update counter and arrows
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

    // Add keyboard navigation
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
                
                // Update counter
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
                
                if (Math.abs(diff) > 30) { // Threshold for swipe
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
            
            // Touch events
            slider.addEventListener('touchstart', handleTouchStart);
            slider.addEventListener('touchmove', handleTouchMove);
            slider.addEventListener('touchend', handleTouchEnd);
            
            // Mouse events
            slider.addEventListener('mousedown', handleTouchStart);
            slider.addEventListener('mousemove', handleTouchMove);
            slider.addEventListener('mouseup', handleTouchEnd);
            slider.addEventListener('mouseleave', handleTouchEnd);
            
            // Initialize positions
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
        
        // Update counter
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

    // Initialize all sliders
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
            
            // Show loading state
            profileImage.style.opacity = '0.5';
            
            // Debug: Log the file being uploaded
            console.log('Uploading file:', this.files[0]);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update all profile images on the page
                    const allProfileImages = document.querySelectorAll('img[src*="6_Profile_Pictures"], img[src*="cat-user.png"]');
                    allProfileImages.forEach(img => {
                        img.src = data.newImage + '?t=' + new Date().getTime();
                    });
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Reload the page to update all instances
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
        // Update all profile images when a new one is uploaded
        function updateProfileImages(newImageUrl) {
            const profileImages = document.querySelectorAll('img[src*="6_Profile_Pictures"], img[src*="cat-user.png"]');
            profileImages.forEach(img => {
                img.src = newImageUrl + '?t=' + new Date().getTime();
            });
        }

        // Listen for profile picture changes
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
                
                console.log('Deleting report:', reportId); // Debug log
                
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
                            console.log('Response status:', response.status); // Debug log
                            return response.json();
                        })
                        .then(data => {
                            console.log('Response data:', data); // Debug log
                            
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'Report has been deleted successfully.',
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    window.location.reload(); // Reload the page
                                });
                            } else {
                                throw new Error(data.message || 'Failed to delete report');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error); // Debug log
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
</script>

<style>
.delete-report {
    cursor: pointer;
    transition: all 0.2s ease;
}

.delete-report:hover {
    transform: scale(1.1);
    color: #dc3545 !important;
}

.delete-report i {
    font-size: 1.1rem;
}

.delete-form {
    margin: 0;
    padding: 0;
}
</style>
</body>
</html>