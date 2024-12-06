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

// Get report ID from URL
$report_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch the report details with images
try {
    $sql = "SELECT r.*, GROUP_CONCAT(ri.image_path) as images 
            FROM lost_reports r 
            LEFT JOIN report_images ri ON r.id = ri.report_id 
            WHERE r.id = ? AND r.user_id = ?
            GROUP BY r.id";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$report_id, $_SESSION['user_id']]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        $_SESSION['error'] = "Report not found or unauthorized access.";
        header('Location: 4.1_my_profile.php');
        exit();
    }

    // Convert images string to array
    $report['image_array'] = $report['images'] ? explode(',', $report['images']) : [];

} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching report: " . $e->getMessage();
    header('Location: 4.1_my_profile.php');
    exit();
}

// Fetch user's profile image
$stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$_SESSION['profile_image'] = $user['profile_image'] ?? null;

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'update':
            try {
                // Start transaction
                $pdo->beginTransaction();
        
                // Update main report information
                // In the update case, modify the UPDATE query:
                $sql = "UPDATE lost_reports SET 
                        cat_name = ?, breed = ?, color = ?, age = ?, gender = ?,
                        last_seen_date = ?, last_seen_time = ?, last_seen_location = ?,
                        description = ?, owner_name = ?, phone_number = ?, edited_at = CURRENT_TIMESTAMP
                        WHERE id = ? AND user_id = ?";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                        trim($_POST['cat_name']),
                        trim($_POST['breed']),
                        trim($_POST['color']),
                        (int)$_POST['age'],
                        $_POST['gender'],
                        $_POST['last_seen_date'],
                        $_POST['last_seen_time'] ?: null,
                        trim($_POST['last_seen_location']),
                        trim($_POST['description']),
                        trim($_POST['owner_name']),
                        trim($_POST['phone_number']),
                        $report_id,
                        $_SESSION['user_id']
                ]);
        
                // Handle image deletions
                if (!empty($_POST['delete_images'])) {
                    foreach ($_POST['delete_images'] as $image) {
                        if (file_exists($image)) {
                            unlink($image);
                        }
                        $sql = "DELETE FROM report_images WHERE report_id = ? AND image_path = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$report_id, $image]);
                    }
                }
        
                // Handle new image uploads
                if (!empty($_FILES['new_images']['name'][0])) {
                    $uploadDir = 'uploads/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
        
                    foreach ($_FILES['new_images']['tmp_name'] as $key => $tmp_name) {
                        if ($_FILES['new_images']['error'][$key] === UPLOAD_ERR_OK) {
                            $fileName = uniqid() . '_' . basename($_FILES['new_images']['name'][$key]);
                            $targetPath = $uploadDir . $fileName;
                            
                            if (move_uploaded_file($tmp_name, $targetPath)) {
                                $sql = "INSERT INTO report_images (report_id, image_path) VALUES (?, ?)";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$report_id, $targetPath]);
                            }
                        }
                    }
                }
        
                $pdo->commit();
                $_SESSION['success'] = "Report updated successfully!";
                header("Location: 4.1_my_profile.php");
                exit();
        
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['error'] = "Error updating report: " . $e->getMessage();
                header("Location: 4.2_edit_report.php?id=" . $report_id);
                exit();
            }
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
            header('Location: ../../2_User/UserBackend/login.php');
            exit();
    }
}

$username = $_SESSION['username'] ?? 'Guest';
$fullname = $_SESSION['fullname'] ?? 'Guest User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../../4_Styles/user_style.css">
</head>
<body>
    <!-- Side Menu -->
    <div class="side-menu">
        <!-- ... copy the side menu from 4.1_my_profile.php ... -->
    </div>

    <div class="container-custom">
        <!-- Header -->
        <header class="header-container mb-4">
            <!-- ... copy the header from 4.1_my_profile.php ... -->
        </header>

        <main class="main-content">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h4 class="card-title mb-0">Edit Report</h4>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update">
                        
                        <!-- Cat Information -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Cat Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Cat Name</label>
                                    <input type="text" class="form-control" name="cat_name" 
                                           value="<?= htmlspecialchars($report['cat_name']) ?>" required>
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
                                    <label class="form-label">Color</label>
                                    <input type="text" class="form-control" name="color" 
                                           value="<?= htmlspecialchars($report['color']) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Age (Years)</label>
                                    <input type="number" class="form-control" name="age" 
                                           value="<?= htmlspecialchars($report['age']) ?>"
                                           min="0" max="30" step="1" 
                                           style="width: 80px;"
                                           oninput="this.value = Math.round(this.value);" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" name="gender" required>
                                        <option value="male" <?= strtolower($report['gender']) == 'male' ? 'selected' : '' ?>>Male</option>
                                        <option value="female" <?= strtolower($report['gender']) == 'female' ? 'selected' : '' ?>>Female</option>
                                        <option value="unknown" <?= strtolower($report['gender']) == 'unknown' ? 'selected' : '' ?>>Unknown</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Last Seen Information -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Last Seen Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Last Seen Date</label>
                                    <input type="date" class="form-control" name="last_seen_date" 
                                           value="<?= htmlspecialchars($report['last_seen_date']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Seen Time (Optional)</label>
                                    <input type="time" class="form-control" name="last_seen_time" 
                                           value="<?= htmlspecialchars($report['last_seen_time'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Last Seen Location</label>
                                    <input type="text" class="form-control" name="last_seen_location" 
                                           value="<?= htmlspecialchars($report['last_seen_location']) ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Additional Details</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="3" 
                                              required><?= htmlspecialchars($report['description']) ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Contact Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Owner Name</label>
                                    <input type="text" class="form-control" name="owner_name" 
                                           value="<?= htmlspecialchars($report['owner_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" name="phone_number" 
                                           value="<?= htmlspecialchars($report['phone_number']) ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- Current Images -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Current Images</h6>
                            <div class="row g-3">
                                <?php if (!empty($report['image_array'])): ?>
                                    <?php foreach ($report['image_array'] as $image): ?>
                                        <div class="col-auto">
                                            <div class="position-relative">
                                                <img src="<?= htmlspecialchars($image) ?>" 
                                                     class="rounded" 
                                                     style="width: 100px; height: 100px; object-fit: cover;">
                                                <div class="form-check position-absolute top-0 end-0 m-1">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="delete_images[]" 
                                                           value="<?= htmlspecialchars($image) ?>">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <p class="text-muted">No images uploaded</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($report['image_array'])): ?>
                                <small class="text-muted">Check the boxes to delete images</small>
                            <?php endif; ?>
                        </div>

                        <!-- Upload New Images -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Upload New Images</h6>
                            <input type="file" class="form-control" name="new_images[]" 
                                   accept="image/*" multiple>
                            <small class="text-muted">You can select multiple images</small>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="4.1_my_profile.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
    // Prevent decimal input for age
    document.querySelector('input[name="age"]').addEventListener('keypress', function(evt) {
        if (evt.key === '.' || evt.key === ',') {
            evt.preventDefault();
        }
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const phoneNumber = document.querySelector('input[name="phone_number"]').value;
        if (!phoneNumber.match(/^[\d\s\-\(\)\+\.]+$/)) {
            e.preventDefault();
            alert('Please enter a valid phone number');
            return;
        }
    });
    </script>
</body>
</html>