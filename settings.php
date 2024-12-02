<?php
require_once 'userAuth.php';

$login = new Login();
if (!$login->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    switch ($action) {
        // Navigation cases
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
            header("Location: profile.php");
            exit();
        case 'help':
            header("Location: help.php");
            exit();
        case 'logout':
            $login->logout();
            header('Location: login.php');
            exit();
    }
}

$username = $_SESSION['username'] ?? 'Guest';
$fullname = $_SESSION['fullname'] ?? 'Guest User';
$email = $_SESSION['email'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles/style.css">
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
                        <i class="fas fa-question-circle me-2"></i> Help and Support
                    </button>
                </form>
            </li>
            <li class="nav-item">
                <form method="POST">
                    <button type="submit" name="action" value="settings" class="btn btn-link nav-link text-dark active">
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
            <h2 class="mb-0">System Settings</h2>
        </header>

        <main class="main-content">
            <div class="row g-4">
                <div class="col-12">
                    <div class="accordion" id="settingsAccordion">
                        <!-- User Information -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#userInfo">
                                    <i class="fas fa-user-edit me-2"></i>User Information
                                </button>
                            </h2>
                            <div id="userInfo" class="accordion-collapse collapse show" data-bs-parent="#settingsAccordion">
                                <div class="accordion-body">
                                    <form method="POST" action="updateSettings.php">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Full Name</label>
                                                <input type="text" class="form-control" name="fullName" value="<?php echo htmlspecialchars($fullname); ?>" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Username</label>
                                                <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Email Address</label>
                                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end mt-3">
                                            <button type="submit" name="action" value="updateUserInfo" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Save Changes
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

        <!-- Update Password -->
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#passwordUpdate">
                    <i class="fas fa-key me-2"></i>Update Password
                </button>
            </h2>
            <div id="passwordUpdate" class="accordion-collapse collapse" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    <form method="POST" action="updateSettings.php">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" name="currentPassword" placeholder="Enter current password" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="newPassword" placeholder="Enter new password" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" name="confirmPassword" placeholder="Confirm new password" required>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" name="action" value="updatePassword" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Account -->
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#deleteAccount">
                    <i class="fas fa-exclamation-triangle me-2"></i>Delete Account
                </button>
            </h2>
            <div id="deleteAccount" class="accordion-collapse collapse" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    <form method="POST" action="updateSettings.php" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <div class="alert alert-danger" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    Warning: This action is permanent and cannot be undone. All your data will be permanently deleted.
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" name="deleteAccountPassword" placeholder="Enter your password" required>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="confirmDelete" id="confirmDelete" required>
                                    <label class="form-check-label" for="confirmDelete">
                                        I understand this is permanent
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" name="action" value="deleteAccount" class="btn btn-danger">
                                <i class="fas fa-trash-alt me-2"></i>Delete Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- About -->
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#about">
                    <i class="fas fa-info-circle me-2"></i>About
                </button>
            </h2>
            <div id="about" class="accordion-collapse collapse" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    <!-- Website Information -->
                    <div class="mb-4">
                        <h6 class="mb-3">About PurrSafe</h6>
                        <p class="text-muted small">
                        This project is a comprehensive platform designed to simplify the process of reuniting lost cats with their owners. 
                        It provides users with essential tools to report missing cats, browse through listings of found cats, and manage profiles for the users and their cats.
                        </p>
                    </div>

                    <!-- Developers Section -->
                    <h6 class="mb-3">Development Team - GROUP 3</h6>
                    <div class="row g-3">
                        <div class="col-md-4 text-center">
                            <div class="mb-2">
                                <img src="images/developers/john.jpg" class="rounded-circle" alt="Lorcan" 
                                     style="width: 80px; height: 80px; object-fit: cover;">
                            </div>
                            <h6 class="mb-1">John Lorcan Paraiso</h6>
                            <small class="text-muted d-block">Leader</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="mb-2">
                                <img src="images/developers/jane.jpg" class="rounded-circle" alt="Justin" 
                                     style="width: 80px; height: 80px; object-fit: cover;">
                            </div>
                            <h6 class="mb-1">Justin Katigbak</h6>
                            <small class="text-muted d-block">Member</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="mb-2">
                                <img src="images/developers/mike.jpg" class="rounded-circle" alt="Jaika" 
                                     style="width: 80px; height: 80px; object-fit: cover;">
                            </div>
                            <h6 class="mb-1">Jaika Remina Madrid</h6>
                            <small class="text-muted d-block">Member</small>
                        </div>
                    </div>

                    <!-- Version Information -->
                    <div class="mt-4 text-center">
                        <small class="text-muted">
                            Version 1.0.0 | December 2024
                            <br>
                            © PurrSafe Lost and Found Cat System. All rights reserved.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html> 