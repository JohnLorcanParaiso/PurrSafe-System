<?php
require_once 'userAuth.php';
include 'db.php';

// Start session only if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get the cat's ID from the URL parameter
$cat_id = isset($_GET['id']) ? $_GET['id'] : null;
$cat = null;

if ($cat_id) {
    $query = "SELECT * FROM lost_reports WHERE id = ?";
    $stmt = $db->pdo->prepare($query); // Use PDO for prepared statements
    $stmt->execute([$cat_id]);
    $cat = $stmt->fetch();
}

// Initialize variables for message display
$error_message = "";
$success_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle the action
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

    if (!empty($_POST['owner_notification'])) {
        // Collect form data
        $owner_notification = trim($_POST['owner_notification']);
        $gps_location = isset($_POST['gps_location']) ? trim($_POST['gps_location']) : null;
        $image_path = null;

        // Handle the image upload if provided
        if (!empty($_FILES['image']['name'])) {
            $target_dir = "uploads/";
            $image_path = $target_dir . basename($_FILES['image']['name']);
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                $error_message = "Error uploading image.";
            }
        }

        // Insert data into the `found_reports` table if no errors
        if (empty($error_message)) {
            try {
                $query = "INSERT INTO found_reports (user_id, report_id, owner_notification, image_path, gps_location) 
                          VALUES (?, ?, ?, ?, ?)";
                $stmt = $db->pdo->prepare($query);
                $stmt->execute([$_SESSION['user_id'], $cat_id, $owner_notification, $image_path, $gps_location]);

                // Set success message and redirect to another page
                $success_message = "Thank you for reporting! The owner has been notified.";
                // Redirect to a success page (e.g., view.php or dashboard.php)
                header("Location: view.php");
                exit;
            } catch (Exception $e) {
                $error_message = "An error occurred while submitting the report. Please try again.";
            }
        }
    } else {
        $error_message = "Owner notification is required.";
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
</head>
<body class="bg-light">
    <!-- Side Menu -->
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

    <!-- Main Content Area -->
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
            </div>
        </header>

        <div class="mb-4">
            <a href="view.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fas fa-arrow-left me-2"></i>Back to Reports
            </a>
        </div>

        <!-- Submission Form -->
        <div class="card shadow">
            <div class="card-body p-4">
                <h4 class="card-title mb-4">Submit Found Cat</h4>

                <!-- Display error message if present -->
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
                <?php endif; ?>

                <!-- Display success message if present -->
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
                <?php else: ?>
                    <form method="POST" action="submit_found_cat.php?id=<?= htmlspecialchars($cat['id']) ?>" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="owner_notification" class="form-label">Notify Owner (how the cat will be returned or where to pick up):</label>
                            <textarea id="owner_notification" name="owner_notification" class="custom-input form-control" required></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="image" class="form-label">Upload Image of the Found Cat:</label>
                            <input type="file" id="image" name="image" class="custom-input form-control" accept="image/*" />
                        </div>

                        <div class="mb-4">
                            <label for="gps_location" class="form-label">GPS Location (optional):</label>
                            <input type="text" id="gps_location" name="gps_location" class="custom-input form-control" placeholder="Enter GPS coordinates" />
                        </div>

                        <button type="submit" class="btn-custom btn btn-primary">Submit Report</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>