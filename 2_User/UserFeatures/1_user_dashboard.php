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

        case 'settings':
            header("Location: 6_settings.php");
            exit();
            
        case 'search':
            $search_query = isset($_POST['search']) ? $_POST['search'] : '';
            header("Location: search.php?q=" . urlencode($search_query));
            exit();
            
        case 'profile':
            header("Location: 4.1_my_profile.php");
            exit();
            
        default:
            header("Location: 1_user_dashboard.php");
            exit();
    }
}    

$username = $_SESSION['username'] ?? 'Guest';
$fullname = $_SESSION['fullname'] ?? 'Guest User';

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

class DashboardData extends Database {
    public function getRecentReports($limit = 5) {
        try {
            $stmt = $this->conn->prepare("
                SELECT r.* 
                FROM lost_reports r 
                ORDER BY r.created_at DESC 
                LIMIT ?
            ");
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
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM lost_reports");
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
$cat_profile_count = $dashboard->getCatProfileCount();
$report_count = $dashboard->getReportCount();
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
    <link rel="stylesheet" href="../../4_Styles/user_style.css">
</head>
<body>
    <div class="side-menu">
        <div class="text-center">
            <img src="../../3_Images/logo.png" class="logo" style="width: 150px; height: 150px; margin: 20px auto; display: block;">
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
            <!-- Updated welcome message with better styling -->
            <div class="text-center mb-3">
                <h2 class="h3 fw-bold mb-1">Welcome back, <?php echo htmlspecialchars($fullname); ?>! 👋</h2>
                <p class="text-muted small mb-0">Track and manage your cat reports all in one place</p>
                <div class="border-bottom w-25 mx-auto my-2"></div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-body text-center p-3">
                            <i class="fas fa-cat mb-2" style="font-size: 1.8rem; color: #6c757d;"></i>
                            <h1 class="h2 mb-1"><?php echo $cat_profile_count; ?></h1>
                            <h3 class="text-muted h6 mb-0">Found Cat</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-body text-center p-3">
                            <i class="fas fa-search mb-2" style="font-size: 1.8rem; color: #6c757d;"></i>
                            <h1 class="h2 mb-1"><?php echo $report_count; ?></h1>
                            <h3 class="text-muted h6 mb-0">Missing Cat</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 h6">Report Lost and Found Cat</h5>
                                <form method="POST" class="m-0">
                                    <button type="submit" name="action" value="view" class="btn btn-custom btn-sm">View All</button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: calc(100vh - 400px);">
                                <table class="table table-hover table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th class="px-4">Name</th>
                                            <th>Breed</th>
                                            <th>Last Seen</th>
                                            <th>Status</th>
                                            <th class="text-end px-4">Option</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if ($result && $result->num_rows > 0) {
                                            while($row = $result->fetch_assoc()) {
                                                ?>
                                                <tr>
                                                    <td class="px-4"><?php echo htmlspecialchars($row['cat_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['breed']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['last_seen_date']); ?></td>
                                                    <td>
                                                        <?php if ($row['status'] === 'found'): ?>
                                                            <span class="badge bg-success">Found</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning">Missing</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end px-4">
                                                        <a href="3.2_view_more.php?id=<?php echo $row['id']; ?>" 
                                                           class="btn btn-custom btn-sm">View</a>
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
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>