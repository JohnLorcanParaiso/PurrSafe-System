<?php
require_once '../../2_User/UserBackend/userAuth.php';

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

require_once '../../2_User/UserBackend/db.php';

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
            
        case 'found':
            $report_id = isset($_POST['report_id']) ? $_POST['report_id'] : '';
            if ($report_id) {
                try {
                    // Redirect to submit_found_cat form first
                    header("Location: submit_found_cat.php?report_id=" . urlencode($report_id));
                    exit();
                } catch (PDOException $e) {
                    $_SESSION['error_message'] = "Database error occurred.";
                    header("Location: 3.1_view_reports.php");
                }
            }
            exit();
            
        case 'undo_found':
            $report_id = isset($_POST['report_id']) ? $_POST['report_id'] : '';
            if ($report_id) {
                try {
                    $stmt = $pdo->prepare("UPDATE lost_reports SET status = 'lost' WHERE id = ?");
                    $success = $stmt->execute([$report_id]);
                    
                    if ($success) {
                        $_SESSION['success_message'] = "Report status reverted to lost.";
                    } else {
                        $_SESSION['error_message'] = "Failed to update report status.";
                    }
                    header("Location: 3.1_view_reports.php");
                } catch (PDOException $e) {
                    $_SESSION['error_message'] = "Database error occurred.";
                    header("Location: 3.1_view_reports.php");
                }
            }
            exit();
            
        case 'submit_found':
            $report_id = isset($_POST['report_id']) ? $_POST['report_id'] : '';
            if ($report_id && !empty($_POST['owner_notification']) && !empty($_POST['contact_number'])) {
                try {
                    $db->pdo->beginTransaction();

                    // Handle file upload
                    $image_path = null;
                    if (!empty($_FILES['image']['name'])) {
                        $target_dir = "uploads/";
                        $image_path = $target_dir . basename($_FILES['image']['name']);
                        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
                    }

                    // Insert found report
                    $query = "INSERT INTO found_reports (user_id, report_id, owner_notification, founder_name, contact_number, image_path) 
                              VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([
                        $_SESSION['user_id'],
                        $report_id,
                        $_POST['owner_notification'],
                        $_SESSION['fullname'],
                        $_POST['contact_number'],
                        $image_path
                    ]);

                    // Update the lost_reports status
                    $updateQuery = "UPDATE lost_reports SET status = 'found' WHERE id = ?";
                    $updateStmt = $pdo->prepare($updateQuery);
                    $updateStmt->execute([$report_id]);

                    // Get the owner's user_id
                    $ownerQuery = "SELECT user_id FROM lost_reports WHERE id = ?";
                    $ownerStmt = $pdo->prepare($ownerQuery);
                    $ownerStmt->execute([$report_id]);
                    $owner = $ownerStmt->fetch();

                    // Create notification
                    $notificationQuery = "INSERT INTO notifications (recipient_id, sender_id, message, created_at, is_read) 
                                        VALUES (?, ?, ?, NOW(), 0)";
                    $notificationStmt = $pdo->prepare($notificationQuery);
                    $notificationStmt->execute([
                        $owner['user_id'],
                        $_SESSION['user_id'],
                        "Someone has found your cat! Check the found reports for details."
                    ]);

                    $db->pdo->commit();
                    exit('success');
                } catch (Exception $e) {
                    $db->pdo->rollBack();
                    http_response_code(500);
                    exit('error');
                }
            }
            http_response_code(400);
            exit('invalid input');
    }
}

$sql = "SELECT r.*, GROUP_CONCAT(ri.image_path) as images 
        FROM lost_reports r 
        LEFT JOIN report_images ri ON r.id = ri.report_id 
        GROUP BY r.id 
        ORDER BY r.created_at DESC";
$stmt = $pdo->query($sql);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add this function to format image paths
function formatImagePath($image) {
    if (empty($image)) {
        return '../../3_Images/cat-user.png'; // Default image
    }
    
    // If it's a full path, return as is
    if (strpos($image, '../../5_Uploads/') === 0) {
        return $image;
    }
    
    // Otherwise, prepend the uploads directory path
    return '../../5_Uploads/' . basename($image);
}

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
    <link rel="stylesheet" href="../../4_Styles/user_style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .modal-dialog-wide {
            max-width: 800px;
        }
    </style>
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
        <?php if (isset($_GET['success']) && $_GET['success'] === 'found'): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                Thank you for reporting! The cat has been marked as found and the owner has been notified.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

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
                                                    $displayImage = formatImagePath($images[0]);
                                                ?>
                                                    <div class="image-container position-relative">
                                                        <?php if ($report['status'] === 'found'): ?>
                                                            <a href="#" onclick="showFoundPopup(); return false;">
                                                        <?php else: ?>
                                                            <a href="3.2_view_more.php?id=<?php echo $report['id']; ?>">
                                                        <?php endif; ?>
                                                            <img src="<?= htmlspecialchars($displayImage) ?>" 
                                                                 class="card-img-top" 
                                                                 alt="<?= htmlspecialchars($report['cat_name']) ?>"
                                                                 style="height: 200px; object-fit: cover; cursor: pointer;">
                                                            <?php if ($report['status'] === 'found'): ?>
                                                                <div class="found-marker">FOUND</div>
                                                            <?php endif; ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="card-body">
                                                    <h5 
                                                        class="card-title"><?= htmlspecialchars($report['cat_name']) ?></h5>
                                                    <p class="card-text">
                                                        <strong>Breed:</strong> <?= htmlspecialchars($report['breed']) ?><br>
                                                        <strong>Last Seen:</strong> <?= htmlspecialchars($report['last_seen_date']) ?>
                                                    </p>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div class="d-flex gap-1">
                                                            <a href="3.2_view_more.php?id=<?php echo $report['id']; ?>" 
                                                               class="btn btn-outline-primary btn-sm rounded-pill px-2 py-1" style="font-size: 0.9rem;">
                                                                <i class="fas fa-arrow-right"></i> View More
                                                            </a>
                                                            <?php if ($report['status'] === 'found'): ?>
                                                                <a href="#" 
                                                                   class="btn btn-outline-danger btn-sm rounded-pill px-2 py-1" 
                                                                   style="font-size: 0.9rem;"
                                                                   onclick="event.preventDefault(); document.getElementById('undo-found-form-<?= $report['id'] ?>').submit();">
                                                                    <i class="fas fa-undo"></i> Undo Found
                                                                </a>
                                                                <form id="undo-found-form-<?= $report['id'] ?>" method="POST" style="display: none;">
                                                                    <input type="hidden" name="action" value="undo_found">
                                                                    <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                                                                </form>
                                                            <?php else: ?>
                                                                <a href="#" 
                                                                   class="btn btn-outline-primary btn-sm rounded-pill px-2 py-1" 
                                                                   style="font-size: 0.9rem;"
                                                                   onclick="event.preventDefault(); showFoundForm('<?= $report['id'] ?>');">
                                                                    <i class="fas fa-exclamation-circle"></i> Found
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
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

    <div class="modal fade" id="foundCatModal" tabindex="-1" aria-labelledby="foundCatModalLabel" aria-hidden="false">
        <div class="modal-dialog modal-dialog-wide">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="foundCatModalLabel">Submit Found Cat Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="foundCatForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="submit_found">
                        <input type="hidden" name="report_id" id="report_id">

                        <div class="mb-4">
                            <label for="owner_notification" class="form-label">Notify Owner (please provide details about the cat's condition and how to contact you):</label>
                            <textarea id="owner_notification" name="owner_notification" class="custom-input form-control" required placeholder="Describe the condition of the cat, how the owner can contact you, and any other relevant information."></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Your Name:</label>
                            <input type="text" class="custom-input form-control" value="<?= htmlspecialchars($_SESSION['fullname']) ?>" disabled>
                            <input type="hidden" name="founder_name" value="<?= htmlspecialchars($_SESSION['fullname']) ?>">
                        </div>

                        <div class="mb-4">
                            <label for="contact_number" class="form-label">Contact Number:</label>
                            <input type="text" id="contact_number" name="contact_number" class="custom-input form-control" required placeholder="Enter your contact number">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">How would you like to proceed?</label>
                            <div class="border p-3 rounded d-flex">
                                <div class="me-2">
                                    <input type="checkbox" id="return_cat" name="return_cat" value="1" class="btn-check" autocomplete="off">
                                    <label for="return_cat" class="btn btn-outline-warning">Return the Cat to Owner</label>
                                </div>
                                <div>
                                    <input type="checkbox" id="owner_claim" name="owner_claim" value="1" class="btn-check" autocomplete="off">
                                    <label for="owner_claim" class="btn btn-outline-success">Owner to Claim the Cat</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="image" class="form-label">Upload Image of the Found Cat:</label>
                            <input type="file" id="image" name="image" class="custom-input form-control" accept="image/*" />
                        </div>

                        <button type="submit" class="btn btn-primary">Submit Report</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
    function showFoundForm(reportId) {
        document.getElementById('report_id').value = reportId;
        var modal = new bootstrap.Modal(document.getElementById('foundCatModal'));
        modal.show();
    }

    document.getElementById('foundCatForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        fetch('view.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            var modal = bootstrap.Modal.getInstance(document.getElementById('foundCatModal'));
            modal.hide();
            
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Thank you for reporting! The owner has been notified.',
                showConfirmButton: true
            }).then((result) => {
                window.location.href = 'view.php?success=found';
            });
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while submitting the report.'
            });
        });
    });

    function showFoundPopup() {
        Swal.fire({
            title: 'Cat Already Found',
            text: 'This cat has already been found and is no longer missing.',
            icon: 'info',
            confirmButtonText: 'OK',
            confirmButtonColor: '#2196F3'
        });
    }
    </script>
</body>
</html> 