<?php
require_once 'userAuth.php';
require_once 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$login = new Login();
if (!$login->isLoggedIn()) {
    header('Location: login.php');
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
    header('Location: view.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    try {
        $required_fields = ['cat_name', 'breed', 'color', 'age', 'gender', 'last_seen_date', 
                          'description', 'owner_name', 'phone_number'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields.");
            }
        }

        $pdo->beginTransaction();

        $original_report = [
            'cat_name' => $report['cat_name'],
            'breed' => $report['breed'],
            'color' => $report['color'],
            'age' => $report['age'],
            'gender' => $report['gender'],
            'last_seen_date' => $report['last_seen_date'],
            'last_seen_time' => $report['last_seen_time'],
            'description' => $report['description'],
            'owner_name' => $report['owner_name'],
            'phone_number' => $report['phone_number']
        ];

        $sql = "UPDATE lost_reports SET 
                cat_name = ?,
                breed = ?,
                color = ?,
                age = ?,
                gender = ?,
                last_seen_date = ?,
                last_seen_time = ?,
                description = ?,
                owner_name = ?,
                phone_number = ?,
                updated_at = NOW()
                WHERE id = ?";
                
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([
            trim($_POST['cat_name']),
            trim($_POST['breed']),
            trim($_POST['color']),
            trim($_POST['age']),
            $_POST['gender'],
            $_POST['last_seen_date'],
            $_POST['last_seen_time'] ?? null,
            trim($_POST['description']),
            trim($_POST['owner_name']),
            trim($_POST['phone_number']),
            $report_id
        ]);

        if (!$success) {
            throw new Exception("Failed to update report.");
        }

        $new_report = [
            'cat_name' => trim($_POST['cat_name']),
            'breed' => trim($_POST['breed']),
            'color' => trim($_POST['color']),
            'age' => trim($_POST['age']),
            'gender' => $_POST['gender'],
            'last_seen_date' => $_POST['last_seen_date'],
            'last_seen_time' => $_POST['last_seen_time'] ?? null,
            'description' => trim($_POST['description']),
            'owner_name' => trim($_POST['owner_name']),
            'phone_number' => trim($_POST['phone_number'])
        ];

        $changes = [];
        foreach ($new_report as $field => $new_value) {
            if ($original_report[$field] != $new_value) {
                $sql = "INSERT INTO report_updates 
                        (report_id, field_name, old_value, new_value, updated_by, updated_at) 
                        VALUES (?, ?, ?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $report_id,
                    $field,
                    $original_report[$field],
                    $new_value,
                    $_SESSION['user_id']
                ]);

                $changes[$field] = [
                    'old' => $original_report[$field],
                    'new' => $new_value
                ];
            }
        }

        if (!empty($_FILES['new_images']['name'][0])) {
            $uploadDir = 'uploads/';
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; 

            foreach ($_FILES['new_images']['tmp_name'] as $key => $tmp_name) {
 
                if (!in_array($_FILES['new_images']['type'][$key], $allowed_types)) {
                    throw new Exception("Invalid file type. Only JPG, PNG, and GIF files are allowed.");
                }
                if ($_FILES['new_images']['size'][$key] > $max_size) {
                    throw new Exception("File size too large. Maximum size is 5MB.");
                }

                $fileName = uniqid() . '_' . $_FILES['new_images']['name'][$key];
                if (move_uploaded_file($tmp_name, $uploadDir . $fileName)) {
                    $sql = "INSERT INTO report_images (report_id, image_path) VALUES (?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$report_id, $uploadDir . $fileName]);
                } else {
                    throw new Exception("Error uploading file.");
                }
            }
        }

        $pdo->commit();

        $_SESSION['success'] = true;
        $_SESSION['success_message'] = "Report successfully updated!";
        $_SESSION['report_changes'] = $changes;
        $_SESSION['report_id'] = $report_id;

        header('Location: view.php');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        
        $_SESSION['error_message'] = $e->getMessage();
        
        header('Location: edit.php?id=' . $report_id);
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
            header("Location: dashboard.php");
            exit();
        case 'create':
            header("Location: create.php");
            exit();
        case 'view':
            header("Location: view.php");
            exit();
        case 'myProfile':
        case 'profile':
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
                                            <div class="col-md-3 mb-2">
                                                <img src="<?= htmlspecialchars($image) ?>" class="img-thumbnail" alt="Report Image">
                                            </div>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">Upload New Images</h6>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <input type="file" class="form-control" name="new_images[]" multiple accept="image/*">
                                            <div class="form-text">You can upload multiple new images of your cat</div>
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

    <?php if (isset($error)): ?>
    <script>
        Swal.fire({
            title: 'Error!',
            text: '<?= htmlspecialchars($error) ?>',
            icon: 'error',
            confirmButtonColor: '#3085d6'
        });
    </script>
    <?php endif; ?>

    <?php if (isset($_GET['success']) && $_SESSION['report_status'] === 'success'): ?>
    <script>
        Swal.fire({
            title: 'Success!',
            html: `
                <p><?= $_SESSION['report_message'] ?></p>
                <div class="mt-3">
                    <p><strong>Cat Name:</strong> <?= htmlspecialchars($_SESSION['report_details']['cat_name']) ?></p>
                    <p><strong>Breed:</strong> <?= htmlspecialchars($_SESSION['report_details']['breed']) ?></p>
                    <p><strong>Last Seen Date:</strong> <?= htmlspecialchars($_SESSION['report_details']['last_seen_date']) ?></p>
                </div>
            `,
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
        unset($_SESSION['report_status']);
        unset($_SESSION['report_message']);
        unset($_SESSION['report_details']);
    endif; 
    ?>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html> 