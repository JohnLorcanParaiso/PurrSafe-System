<?php
require_once 'userAuth.php';

$login = new Login();
if (!$login->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

//Logout
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
            
        case 'search':
            $search_query = isset($_POST['search']) ? $_POST['search'] : '';
            header("Location: search.php?q=" . urlencode($search_query));
            exit();
            
        case 'profile':
            header("Location: profile.php");
            exit();
            
        default:
            header("Location: dashboard.php");
            exit();
    }
}    

$username = $_SESSION['username'] ?? 'Guest';
$fullname = $_SESSION['fullname'] ?? 'Guest User';

class DashboardData extends Database {
    public function getRecentReports($limit = 5) {
        try {
            $stmt = $this->conn->prepare("SELECT name, breed, gender, color FROM reports ORDER BY created_at DESC LIMIT ?");
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            return $stmt->get_result();
        } catch (Exception $e) {
            return false;
        }
    }

    public function getLostCats($limit = 5) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT l.id, l.cat_name, l.cat_image, u.fullname as owner_name 
                 FROM lost_cats l 
                 JOIN users u ON l.user_id = u.id 
                 ORDER BY l.created_at DESC 
                 LIMIT ?"
            );
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            return $stmt->get_result();
        } catch (Exception $e) {
            return false;
        }
    }

    public function getCatProfileCount() {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM cat_profiles");
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getReportCount() {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM reports");
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getProfileCount() {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM users");
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
}

$dashboard = new DashboardData();
$result = $dashboard->getRecentReports();
$lostCats = $dashboard->getLostCats();
$cat_profile_count = $dashboard->getCatProfileCount();
$report_count = $dashboard->getReportCount();
$profile_count = $dashboard->getProfileCount();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
                    <button type="submit" name="action" value="dashboard" class="btn btn-link nav-link text-dark active">
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
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center p-5">
                            <h1 class="display-3 mb-3"><?php echo $cat_profile_count; ?></h1>
                            <h3 class="text-muted h4">Found Cat</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center p-5">
                            <h1 class="display-3 mb-3"><?php echo $report_count; ?></h1>
                            <h3 class="text-muted h4">Missing Cat</h3>
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
                                    <button type="submit" name="action" value="view" class="btn btn-custom">View All</button>
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
                                        <?php 
                                        if ($result && $result->num_rows > 0) {
                                            while($row = $result->fetch_assoc()) {
                                                ?>
                                                <tr>
                                                    <td class="px-4"><?php echo htmlspecialchars($row['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['breed']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['color']); ?></td>
                                                    <td class="text-end px-4">
                                                        <form method="POST" class="m-0 d-inline">
                                                            <input type="hidden" name="report_id" value="<?php echo $row['id']; ?>">
                                                            <button type="submit" name="action" value="view_report" class="btn btn-custom btn-sm">View</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No reports found</td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
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
                                <h5 class="mb-0">Owner's Lost Cat Info</h5>
                                <form method="POST" class="m-0">
                                    <button type="submit" name="action" value="view_lost" class="btn btn-custom">View All</button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="px-4">Cat</th>
                                            <th>Name</th>
                                            <th>Owner</th>
                                            <th class="text-end px-4">Option</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if ($lostCats && $lostCats->num_rows > 0):
                                            while($cat = $lostCats->fetch_assoc()):
                                                $catImage = !empty($cat['cat_image']) ? $cat['cat_image'] : 'images/default-cat.png';
                                        ?>
                                            <tr>
                                                <td class="px-4">
                                                    <img src="<?php echo htmlspecialchars($catImage); ?>" alt="cat" 
                                                         style="width: 30px; height: 30px; border-radius: 50%;">
                                                </td>
                                                <td><?php echo htmlspecialchars($cat['cat_name']); ?></td>
                                                <td><?php echo htmlspecialchars($cat['owner_name']); ?></td>
                                                <td class="text-end px-4">
                                                    <form method="POST" class="m-0">
                                                        <input type="hidden" name="lost_cat_id" value="<?php echo $cat['id']; ?>">
                                                        <button type="submit" name="action" value="view_lost_cat" 
                                                                class="btn btn-custom btn-sm">View</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php 
                                            endwhile;
                                        else:
                                        ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No lost cats reported</td>
                                            </tr>
                                        <?php endif; ?>
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