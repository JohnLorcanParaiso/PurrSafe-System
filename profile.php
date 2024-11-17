<?php
require_once 'userAuth.php';
require_once 'db.php';

$login = new Login();
if (!$login->isLoggedIn()) {
    header('Location: login.php');
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
        case 'settings':
            header("Location: settings.php");
            exit();
        case 'logout':
            $login->logout();
            header("Location: login.php");
            exit();
    }
}

// Get user data from session
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>User Profile</title>
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
                <button type="submit" name="action" value="myProfile" class="btn btn-link nav-link text-dark active">
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
                                <span class="visually-hidden">unread notifications</span>
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
                            <li><h6 class="dropdown-header">Notifications</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item notification-item" href="#">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <img src="images/notifications.png" alt="notification" class="notification-icon">
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <p class="notification-text">New cat reported missing in your area</p>
                                            <small class="notification-time">3 minutes ago</small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item notification-item" href="#">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <img src="images/notifications.png" alt="notification" class="notification-icon">
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <p class="notification-text">Your report has been updated</p>
                                            <small class="notification-time">1 hour ago</small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item notification-item" href="#">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <img src="images/notifications.png" alt="notification" class="notification-icon">
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <p class="notification-text">New match found for your lost cat</p>
                                            <small class="notification-time">2 hours ago</small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-center view-all" href="#">View All Notifications</a>
                            </li>
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
                <div class="col-md-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body text-center">
                            <img src="images/user.png" alt="Profile Picture" class="rounded-circle mb-3" style="width: 150px; height: 150px;">
                            <h4><?php echo htmlspecialchars($fullname); ?></h4>
                            <p class="text-muted">@<?php echo htmlspecialchars($username); ?></p>
                            <button class="btn btn-custom" onclick="document.getElementById('profilePicInput').click()">
                                Change Profile Picture
                            </button>
                            <input type="file" id="profilePicInput" hidden accept="image/*">
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Account Statistics</h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Reports Created</span>
                                <span class="badge bg-primary rounded-pill">12</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Cats Found</span>
                                <span class="badge bg-success rounded-pill">5</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Active Reports</span>
                                <span class="badge bg-warning rounded-pill">3</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Profile Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_profile">
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($username); ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($email); ?>">
                                </div>
                                <button type="submit" class="btn btn-custom">Update Profile</button>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="change_password">
                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" class="form-control" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="new_password" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" name="confirm_password" required>
                                </div>
                                <button type="submit" class="btn btn-custom">Change Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('profilePicInput').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
            }
        });
    </script>
</body>
</html>