<?php
require_once 'userAuth.php';
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$cat_id = isset($_GET['id']) ? $_GET['id'] : null;
$cat = null;

if ($cat_id) {
    $query = "SELECT * FROM lost_reports WHERE id = ?";
    $stmt = $db->pdo->prepare($query);
    $stmt->execute([$cat_id]);
    $cat = $stmt->fetch();
}

$error_message = "";
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log(print_r($_POST, true));

    // Handle the navigation actions first
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'dashboard':
                header("Location: dashboard.php");
                exit;

            case 'create':
                header("Location: create.php");
                exit;

            case 'view':
                header("Location: view.php");
                exit;

            case 'myProfile':
                header("Location: profile.php");
                exit;

            case 'help':
                header("Location: help.php");
                exit;

            case 'settings':
                header("Location: settings.php");
                exit;

            case 'logout':
                header("Location: logout.php");
                exit;
        }
    }

    // Check if all required fields are filled
    if (!empty($_POST['owner_notification']) && !empty($_POST['contact_number'])) {
        // Collect form data
        $owner_notification = trim($_POST['owner_notification']);
        $founder_name = $_SESSION['fullname'];
        $contact_number = trim($_POST['contact_number']);
        $image_path = null;

        // Handle file upload
        if (!empty($_FILES['image']['name'])) {
            $target_dir = "uploads/";
            $image_path = $target_dir . basename($_FILES['image']['name']);
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                $error_message = "Error uploading image.";
            }
        }

        // If no errors, insert into database
        if (empty($error_message)) {
            try {
                // Begin transaction
                $db->pdo->beginTransaction();

                // Insert found report
                $query = "INSERT INTO found_reports (user_id, report_id, owner_notification, founder_name, contact_number, image_path) 
                          VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $db->pdo->prepare($query);
                $stmt->execute([$_SESSION['user_id'], $cat_id, $owner_notification, $founder_name, $contact_number, $image_path]);

                // Update the lost_reports status to 'found'
                $updateQuery = "UPDATE lost_reports SET status = 'found' WHERE id = ?";
                $updateStmt = $db->pdo->prepare($updateQuery);
                $updateStmt->execute([$cat_id]);

                // Get the owner's user_id from lost_reports
                $ownerQuery = "SELECT user_id FROM lost_reports WHERE id = ?";
                $ownerStmt = $db->pdo->prepare($ownerQuery);
                $ownerStmt->execute([$cat_id]);
                $owner = $ownerStmt->fetch();

                // Create notification for the cat owner
                $notificationQuery = "INSERT INTO notifications (recipient_id, sender_id, message, created_at, is_read) 
                                    VALUES (?, ?, ?, NOW(), 0)";
                $notificationStmt = $db->pdo->prepare($notificationQuery);
                $notificationStmt->execute([
                    $owner['user_id'],
                    $_SESSION['user_id'],
                    "Someone has found your cat! Check the found reports for details."
                ]);

                $db->pdo->commit();
                ?>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Thank you for reporting! The owner has been notified.',
                        showConfirmButton: true
                    }).then((result) => {
                        window.location.href = 'view.php?success=found';
                    });
                </script>
                <?php
                exit;
            } catch (Exception $e) {
                $db->pdo->rollBack();
                $error_message = "An error occurred while submitting the report. Please try again.";
            }
        }
    } else {
        $error_message = "Owner notification and contact number are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Found Cat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
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
                <h4 class="card-title mb-4">Submit Found Cat</h4>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
                <?php else: ?>
                    <form method="POST" action="submit_found_cat.php?id=<?= htmlspecialchars($cat['id']) ?>" enctype="multipart/form-data">
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
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>