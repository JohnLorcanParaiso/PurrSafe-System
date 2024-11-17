<?php
require_once 'userAuth.php';

// Update the session check
$login = new Login();
if (!$login->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Add logout handling
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    $login->logout();
    header('Location: login.php');
    exit();
}

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    switch ($action) {
        case 'dashboard':
            header("Location: dashboard.php");
            exit();
            
        case 'add_new_cat':
            header("Location: create.php");
            exit();
            
        case 'report_cat':
            header("Location: lfreport.php");
            exit();
            
        case 'view_profile':
            header("Location: viewpf.php");
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
            
        case 'profile':
            header("Location: viewpf.php");
            exit();
            
        default:
            header("Location: dashboard.php");
            exit();
    }
}

$cat_profile_count = 50;  
$report_count = 100;      
$profile_count = 30;      

// Add this near the top after session check
$username = $_SESSION['username'] ?? 'Guest';
$fullname = $_SESSION['fullname'] ?? 'Guest User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <title>User Panel</title>
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
                        Dashboard
                    </button>
                </form>
            </li>
            <li class="nav-item">
                <form method="POST">
                    <button type="submit" name="action" value="add_new_cat" class="btn btn-link nav-link text-dark">
                        Create New Report
                    </button>
                </form>
            </li>
            <li class="nav-item">
                <form method="POST">
                    <button type="submit" name="action" value="report_cat" class="btn btn-link nav-link text-dark">
                        Lost and Found Cat
                    </button>
                </form>
            </li>
            <li class="nav-item">
                <form method="POST">
                    <button type="submit" name="action" value="view_profile" class="btn btn-link nav-link text-dark">
                        My Profile
                    </button>
                </form>
            </li>
            <li class="nav-item">
                <form method="POST">
                    <button type="submit" name="action" value="help" class="btn btn-link nav-link text-dark">
                        Help
                    </button>
                </form>
            </li>
            <li class="nav-item">
                <form method="POST">
                    <button type="submit" name="action" value="settings" class="btn btn-link nav-link text-dark">
                        Settings
                    </button>
                </form>
            </li>
            <li class="nav-item mt-auto">
                <form method="POST">
                    <button type="submit" name="action" value="logout" class="btn btn-link nav-link text-dark">
                        Logout
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
                    <!-- Notification Button -->
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
                    <!-- Profile Button -->
                    <form method="POST" class="m-0">
                        <button type="submit" name="action" value="profile" class="btn btn-outline-secondary rounded-circle p-2">
                            <img src="images/user.png" alt="user profile" style="width: 28px; height: 28px;">
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="main-content">
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center p-4">
                            <h1 class="display-4 mb-3"><?php echo $cat_profile_count; ?></h1>
                            <h3 class="text-muted h5">Add New Cat Profile</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center p-4">
                            <h1 class="display-4 mb-3"><?php echo $report_count; ?></h1>
                            <h3 class="text-muted h5">Report Lost and Found Cat</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center p-4">
                            <h1 class="display-4 mb-3"><?php echo $profile_count; ?></h1>
                            <h3 class="text-muted h5">View Profile</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Report Lost and Found Cat</h5>
                                <form method="POST" class="m-0">
                                    <button type="submit" name="action" value="report_cat" class="btn btn-custom">View All</button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="px-4">Name</th>
                                            <th>Breed</th>
                                            <th>Gender</th>
                                            <th>Color</th>
                                            <th class="text-end px-4">Option</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="px-4">Dexter</td>
                                            <td>Persian</td>
                                            <td>Male</td>
                                            <td>White</td>
                                            <td class="text-end px-4">
                                                <button type="submit" class="btn btn-custom btn-sm">View</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-4">Dexter</td>
                                            <td>Persian</td>
                                            <td>Male</td>
                                            <td>White</td>
                                            <td class="text-end px-4">
                                                <button type="submit" class="btn btn-custom btn-sm">View</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-4">Dexter</td>
                                            <td>Persian</td>
                                            <td>Male</td>
                                            <td>White</td>
                                            <td class="text-end px-4">
                                                <button type="submit" class="btn btn-custom btn-sm">View</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-4">Dexter</td>
                                            <td>Persian</td>
                                            <td>Male</td>
                                            <td>White</td>
                                            <td class="text-end px-4">
                                                <button type="submit" class="btn btn-custom btn-sm">View</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-4">Dexter</td>
                                            <td>Persian</td>
                                            <td>Male</td>
                                            <td>White</td>
                                            <td class="text-end px-4">
                                                <button type="submit" class="btn btn-custom btn-sm">View</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">New Cats</h5>
                                <button type="submit" class="btn btn-custom">View All</button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="px-4">Profile</th>
                                            <th>Name</th>
                                            <th class="text-end px-4">Option</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="px-4">
                                                <img src="images/user.png" alt="user" style="width: 30px; height: 30px; border-radius: 50%;">
                                            </td>
                                            <td>Dexter</td>
                                            <td class="text-end px-4">
                                                <button type="submit" class="btn btn-custom btn-sm">View</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-4">
                                                <img src="images/user.png" alt="user" style="width: 30px; height: 30px; border-radius: 50%;">
                                            </td>
                                            <td>Dexter</td>
                                            <td class="text-end px-4">
                                                <button type="submit" class="btn btn-custom btn-sm">View</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-4">
                                                <img src="images/user.png" alt="user" style="width: 30px; height: 30px; border-radius: 50%;">
                                            </td>
                                            <td>Dexter</td>
                                            <td class="text-end px-4">
                                                <button type="submit" class="btn btn-custom btn-sm">View</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-4">
                                                <img src="images/user.png" alt="user" style="width: 30px; height: 30px; border-radius: 50%;">
                                            </td>
                                            <td>Dexter</td>
                                            <td class="text-end px-4">
                                                <button type="submit" class="btn btn-custom btn-sm">View</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-4">
                                                <img src="images/user.png" alt="user" style="width: 30px; height: 30px; border-radius: 50%;">
                                            </td>
                                            <td>Dexter</td>
                                            <td class="text-end px-4">
                                                <button type="submit" class="btn btn-custom btn-sm">View</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
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