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
                        <i class="fas fa-question-circle me-2"></i> Help
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
                <!-- Display Settings -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-desktop me-2"></i>Display Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="updateSettings.php">
                                <div class="mb-3">
                                    <label class="form-label">Theme Mode</label>
                                    <select class="form-select" name="themeMode">
                                        <option value="light">Light</option>
                                        <option value="dark">Dark</option>
                                        <option value="auto">Auto (System)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Accent Color</label>
                                    <select class="form-select" name="accentColor">
                                        <option value="blue">Blue</option>
                                        <option value="green">Green</option>
                                        <option value="purple">Purple</option>
                                        <option value="orange">Orange</option>
                                        <option value="red">Red</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Font Family</label>
                                    <select class="form-select" name="fontFamily">
                                        <option value="poppins">Poppins</option>
                                        <option value="roboto">Roboto</option>
                                        <option value="opensans">Open Sans</option>
                                        <option value="lato">Lato</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Font Size</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="range" class="form-range" min="12" max="20" step="1" name="fontSize" id="fontSizeRange">
                                        <span id="fontSizeValue">16px</span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Animation Speed</label>
                                    <select class="form-select" name="animationSpeed">
                                        <option value="normal">Normal</option>
                                        <option value="fast">Fast</option>
                                        <option value="slow">Slow</option>
                                        <option value="off">Off</option>
                                    </select>
                                </div>
                                <button type="submit" name="action" value="updateDisplay" class="btn btn-primary">Save Display Settings</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- User Information -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>User Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="updateSettings.php">
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="fullName" value="<?php echo htmlspecialchars($fullname); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" class="form-control" name="currentPassword" placeholder="Enter current password" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="newPassword" placeholder="Leave blank to keep current password">
                                </div>
                                <button type="submit" name="action" value="updateUserInfo" class="btn btn-primary">Save User Information</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Update Password -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-key me-2"></i>Update Password</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="updateSettings.php">
                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" class="form-control" name="currentPassword" placeholder="Enter current password" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="newPassword" placeholder="Enter new password" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" name="confirmPassword" placeholder="Confirm new password" required>
                                </div>
                                <button type="submit" name="action" value="updatePassword" class="btn btn-primary">Update Password</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Delete Account -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Delete Account</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="updateSettings.php" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                                <div class="alert alert-primary" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    Warning: This action is permanent and cannot be undone. All your data will be permanently deleted.
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" class="form-control" name="deleteAccountPassword" placeholder="Enter your password to confirm" required>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="confirmDelete" id="confirmDelete" required>
                                        <label class="form-check-label" for="confirmDelete">
                                            I understand that this action is permanent and cannot be undone
                                        </label>
                                    </div>
                                </div>
                                <button type="submit" name="action" value="deleteAccount" class="btn btn-primary">
                                    <i class="fas fa-trash-alt me-2"></i>Delete Account
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html> 