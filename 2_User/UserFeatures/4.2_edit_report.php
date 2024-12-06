<?php
require_once '../../2_User/UserBackend/userAuth.php';
require_once '../../2_User/UserBackend/db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$login = new Login();
if (!$login->isLoggedIn()) {
    header('Location: ../../2_User/UserBackend/login.php');
    exit();
}

$report_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT r.*, GROUP_CONCAT(ri.image_path) as images 
        FROM lost_reports r 
        LEFT JOIN report_images ri ON r.id = ri.report_id 
        WHERE r.id = ?
        GROUP BY r.id";
$stmt = $pdo->prepare($sql);
$stmt->execute([$report_id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report || $report['user_id'] != $_SESSION['user_id']) {
    header('Location: 3.1_view_reports.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    error_log("POST data: " . print_r($_POST, true));
    error_log("Files data: " . print_r($_FILES, true));
    try {
        error_log("Starting report update process for report_id: " . $report_id);
        
        if (!empty($_POST['phone_number']) && !preg_match("/^[\d\s\-\(\)\+\.]+$/", $_POST['phone_number'])) {
            error_log("Phone number validation failed: " . $_POST['phone_number']);
            throw new Exception("Invalid phone number format.");
        }

        $required_fields = ['cat_name', 'breed', 'color', 'age', 'gender', 'last_seen_date', 
                          'last_seen_location', 'description', 'owner_name', 'phone_number'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                error_log("Missing required field: " . $field);
                throw new Exception("Please fill in all required fields. Missing: " . $field);
            }
        }

        error_log("All required fields validated successfully");

        $pdo->beginTransaction();
        error_log("Transaction started");

        $sql = "UPDATE lost_reports SET 
                cat_name = ?,
                breed = ?,
                color = ?,
                age = ?,
                gender = ?,
                last_seen_date = ?,
                last_seen_time = ?,
                last_seen_location = ?,
                description = ?,
                owner_name = ?,
                phone_number = ?
                WHERE id = ? AND user_id = ?";
                
        $stmt = $pdo->prepare($sql);
        $params = [
            trim($_POST['cat_name']),
            trim($_POST['breed']),
            trim($_POST['color']),
            trim($_POST['age']),
            $_POST['gender'],
            $_POST['last_seen_date'],
            $_POST['last_seen_time'] ?? null,
            trim($_POST['last_seen_location']),
            trim($_POST['description']),
            trim($_POST['owner_name']),
            trim($_POST['phone_number']),
            $report_id,
            $_SESSION['user_id']
        ];
        
        error_log("Executing update with params: " . print_r($params, true));
        $stmt->execute($params);

        // Handle image deletions
        if (!empty($_POST['delete_images'])) {
            $deleteImages = explode(',', $_POST['delete_images']);
            foreach ($deleteImages as $imagePath) {
                // Delete image file from server
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                // Delete image path from database
                $sql = "DELETE FROM report_images WHERE report_id = ? AND image_path = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$report_id, $imagePath]);
                error_log("Deleted image: " . $imagePath);
            }
        }

        // Handle new image uploads
        if (!empty($_FILES['new_images']['name'][0])) {
            $uploadDir = 'uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES['new_images']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['new_images']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileName = uniqid() . '_' . basename($_FILES['new_images']['name'][$key]);
                    $targetFilePath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($tmpName, $targetFilePath)) {
                        // Insert new image path into report_images table
                        $sql = "INSERT INTO report_images (report_id, image_path) VALUES (?, ?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$report_id, $targetFilePath]);
                        error_log("Uploaded new image: " . $targetFilePath);
                    } else {
                        throw new Exception("Failed to upload image: " . $fileName);
                    }
                }
            }
        }

        $pdo->commit();
        error_log("Transaction committed successfully");

        $_SESSION['success'] = true;
        $_SESSION['success_message'] = "Report successfully updated!";
        $_SESSION['report_changes'] = $changes;
        $_SESSION['report_id'] = $report_id;

        // Redirect back to the same page to show the alert
        header('Location: 4.2_edit_report.php?id=' . $report_id . '&success=1');
        exit();

    } catch (Exception $e) {
        error_log("Error in update process: " . $e->getMessage());
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
            error_log("Transaction rolled back");
        }
        
        $_SESSION['error'] = true;
        $_SESSION['error_message'] = $e->getMessage();
        
        header('Location: 4.2_edit_report.php?id=' . $report_id . '&error=1');
        exit();
    }
}
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    $login->logout();
    header('Location: login.php');
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

        case 'settings':
            header("Location: 6_settings.php");
            exit();

        case 'search':
            $search_query = isset($_POST['search']) ? $_POST['search'] : '';
            header("Location: search.php?q=" . urlencode($search_query));
            exit();
    }
}

$username = $_SESSION['username'] ?? 'Guest';
$fullname = $_SESSION['fullname'] ?? 'Guest User';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../../4_Styles/user_style.css">
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
                            <img src="../../3_Images/search.png" alt="search" style="width: 20px;">
                        </button>
                    </div>
                </form>
                <div class="d-flex align-items-center gap-3">
                    <?php include '7_notifications.php'; ?>
                    <form method="POST" class="m-0">
                        <button type="submit" name="action" value="profile" class="btn rounded-circle p-0" style="width: 50px; height: 50px; overflow: hidden; border: none;">
                            <img src="../../3_Images/cat-user.png" alt="user profile" style="width: 100%; height: 100%; object-fit: cover;">
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
                            <h4 class="card-title mb-0">Edit Report</h4>
                        </div>
                        <div class="card-body px-4 py-5">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update">

                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">Cat Information</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Cat Name</label>
                                            <input type="text" class="form-control" name="cat_name" 
                                                   value="<?= htmlspecialchars($report['cat_name']) ?>" 
                                                   placeholder="Enter cat's name" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Breed</label>
                                            <input type="text" class="form-control" name="breed" 
                                                   value="<?= htmlspecialchars($report['breed']) ?>" 
                                                   placeholder="Enter cat's breed" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">Physical Characteristics</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Gender</label>
                                            <select class="form-select" name="gender" required>
                                                <option value="male" <?= strtolower($report['gender']) == 'male' ? 'selected' : '' ?>>Male</option>
                                                <option value="female" <?= strtolower($report['gender']) == 'female' ? 'selected' : '' ?>>Female</option>
                                                <option value="unknown" <?= strtolower($report['gender']) == 'unknown' ? 'selected' : '' ?>>Unknown</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Age (Years)</label>
                                            <input type="number" class="form-control" name="age" min="0" max="30" 
                                                   value="<?= htmlspecialchars($report['age']) ?>" 
                                                   placeholder="Enter age" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Color</label>
                                            <input type="text" class="form-control" name="color" 
                                                   value="<?= htmlspecialchars($report['color']) ?>" 
                                                   placeholder="Enter color(s)" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">Additional Details</h6>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="description" rows="3" 
                                                      placeholder="Description about the cat" required><?= htmlspecialchars($report['description']) ?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Last Seen Date</label>
                                            <input type="date" class="form-control" name="last_seen_date" 
                                                   value="<?= htmlspecialchars($report['last_seen_date']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Last Seen Time (Approximate)</label>
                                            <input type="time" class="form-control" name="last_seen_time" 
                                                   value="<?= htmlspecialchars($report['last_seen_time'] ?? '') ?>">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Last Seen Location</label>
                                            <input type="text" class="form-control" name="last_seen_location" 
                                                   value="<?= htmlspecialchars($report['last_seen_location'] ?? '') ?>" 
                                                   placeholder="Enter the location where the cat was last seen" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">Owner Information</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Owner's Name</label>
                                            <input type="text" class="form-control" name="owner_name" 
                                                   value="<?= htmlspecialchars($report['owner_name'] ?? '') ?>" 
                                                   placeholder="Enter owner's name" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Phone Number</label>
                                            <input type="tel" class="form-control" name="phone_number" 
                                                   value="<?= htmlspecialchars($report['phone_number'] ?? '') ?>" 
                                                   placeholder="Enter phone number" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">Current Images</h6>
                                    <div class="row g-3">
                                        <?php 
                                        $images = explode(',', $report['images']);
                                        foreach ($images as $image): 
                                            if (!empty($image)):
                                        ?>
                                            <div class="col-md-3 mb-2 position-relative">
                                                <img src="<?= htmlspecialchars($image) ?>" class="img-thumbnail" alt="Report Image">
                                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0" 
                                                        onclick="deleteImage('<?= htmlspecialchars($image) ?>')">
                                                    &times;
                                                </button>
                                            </div>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    </div>
                                </div>
                                <input type="hidden" name="delete_images" id="delete_images" value="">

                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">Upload New Images</h6>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <input type="file" class="form-control" name="new_images[]" multiple accept="image/*" max="5">
                                            <div class="form-text">You can upload multiple new images of your cat (Max: 5)</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end mt-4">
                                    <a href="view.php" class="btn btn-secondary me-2">Cancel</a>
                                    <button type="submit" class="btn btn-custom" id="updateButton">Update Report</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php if (isset($_SESSION['error']) && $_SESSION['error']): ?>
        <script>
            Swal.fire({
                title: 'Error!',
                text: '<?= htmlspecialchars($_SESSION['error_message']) ?>',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc3545'
            });
        </script>
        <?php 
        unset($_SESSION['error']);
        unset($_SESSION['error_message']);
        ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success']) && $_SESSION['success']): ?>
        <script>
            Swal.fire({
                title: 'Success!',
                text: '<?= htmlspecialchars($_SESSION['success_message']) ?>',
                icon: 'success',
                confirmButtonText: 'View Reports',
                confirmButtonColor: '#28a745'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'view.php';
                }
            });
        </script>
        <?php 
        unset($_SESSION['success']);
        unset($_SESSION['success_message']);
        ?>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
        function deleteImage(imagePath) {
            // Add confirmation dialog
            if (confirm('Are you sure you want to delete this image?')) {
                // Find and remove the image container from view
                const imageElement = event.target.closest('.col-md-3');
                imageElement.style.opacity = '0.5';  // First fade the image
                setTimeout(() => {
                    imageElement.remove();  // Then remove the element
                }, 300);  // After 300ms transition
                
                // Update hidden input with deleted image path
                const deleteImagesInput = document.getElementById('delete_images');
                let deleteImages = deleteImagesInput.value ? deleteImagesInput.value.split(',') : [];
                
                if (!deleteImages.includes(imagePath)) {
                    deleteImages.push(imagePath);
                }
                
                deleteImagesInput.value = deleteImages.join(',');
            }
        }
    </script>
</body>
</html> 