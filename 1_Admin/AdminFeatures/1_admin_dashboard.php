<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

// Add this near the top of the file, after session_start()
require_once '../../2_User/UserBackend/db.php';

abstract class Dashboard extends Database {
    abstract public function getTotalUsers();
    abstract public function getTotalReports();
    abstract public function getActiveUsers();
    
    // Common method that can be used by all dashboard types
    protected function getCount($query) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc()['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    // Common method for fetching reports
    protected function fetchReports($query, $limit) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            return $stmt->get_result();
        } catch (Exception $e) {
            return false;
        }
    }
}

class AdminDashboard extends Dashboard {
    public function getTotalUsers() {
        return $this->getCount("SELECT COUNT(*) as count FROM users");
    }

    public function getTotalReports() {
        return $this->getCount("SELECT COUNT(*) as count FROM lost_reports");
    }

    public function getActiveUsers() {
        return $this->getCount("
            SELECT COUNT(DISTINCT user_id) as count 
            FROM lost_reports 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
    }

    public function getRecentReports($limit = 10) {
        $query = "
            SELECT lr.*, u.username as reporter_name,
                   ri.image_path,
                   CASE 
                       WHEN fr.id IS NOT NULL THEN 'found'
                       ELSE 'missing'
                   END as status
            FROM lost_reports lr
            LEFT JOIN users u ON lr.user_id = u.id
            LEFT JOIN report_images ri ON lr.id = ri.report_id
            LEFT JOIN found_reports fr ON lr.id = fr.report_id
            GROUP BY lr.id
            ORDER BY lr.created_at DESC
            LIMIT ?
        ";
        return $this->fetchReports($query, $limit);
    }
}

// Initialize the dashboard
$dashboard = new AdminDashboard();
$totalUsers = $dashboard->getTotalUsers();
$totalReports = $dashboard->getTotalReports();
$activeUsers = $dashboard->getActiveUsers();
$recentReports = $dashboard->getRecentReports();

// Update the statistics cards section with real data
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../../4_Styles/admin_style.css">
</head>
<body>
    <div class="wrapper">
        <nav id="sidebar">
            <div class="sidebar-header">
                <img src="../../3_Images/logo.png" alt="Logo" class="img-fluid logo">
                <h3>Admin Panel</h3>
            </div>

            <ul class="list-unstyled components">
                <li class="active">
                    <a href="1_admin_dashboard.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="2_manage_users.php">
                        <i class="fas fa-users"></i>
                        <span>Manage Users</span>
                    </a>
                </li>
                <li>
                    <a href="3_reports.php">
                        <i class="fas fa-flag"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li>
                    <a href="4_feedbacks.php">
                        <i class="fas fa-comments"></i>
                        <span>Feedbacks</span>
                    </a>
                </li>
                <li>
                    <a href="5_create_announcement.php">
                        <i class="fas fa-bullhorn"></i>
                        <span>Create Announcement</span>
                    </a>
                </li>
                <li>
                    <a href="6_settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li>
                    <a href="#" onclick="confirmLogout()">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-primary">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="ms-auto d-flex align-items-center">
                        <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</span>
                        <img src="../../3_Images/user.png" alt="Admin" class="rounded-circle" width="40">
                    </div>
                </div>
            </nav>

            <!-- Dashboard Content -->
            <div class="container-fluid">
                <!-- Statistics Cards -->
                <div class="row mt-4">
                    <div class="col-md-4 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2">Total Users</h6>
                                        <h2 class="card-title mb-0"><?php echo $totalUsers; ?></h2>
                                    </div>
                                    <div class="icon-circle bg-primary">
                                        <i class="fas fa-users text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2">Total Reports</h6>
                                        <h2 class="card-title mb-0"><?php echo $totalReports; ?></h2>
                                    </div>
                                    <div class="icon-circle bg-warning">
                                        <i class="fas fa-flag text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2">Active Users</h6>
                                        <h2 class="card-title mb-0"><?php echo $activeUsers; ?></h2>
                                    </div>
                                    <div class="icon-circle bg-success">
                                        <i class="fas fa-user-check text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reports Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Reports</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="reportsTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Name</th>
                                        <th>Breed</th>
                                        <th>Gender</th>
                                        <th>Color</th>
                                        <th>Date of Lost</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recentReports && $recentReports->num_rows > 0): ?>
                                        <?php while ($report = $recentReports->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($report['id']); ?></td>
                                                <td><?php echo htmlspecialchars($report['cat_name']); ?></td>
                                                <td><?php echo htmlspecialchars($report['breed']); ?></td>
                                                <td><?php echo htmlspecialchars($report['gender']); ?></td>
                                                <td><?php echo htmlspecialchars($report['color']); ?></td>
                                                <td><?php echo date('F j, Y', strtotime($report['last_seen_date'])); ?></td>
                                                <td>
                                                    <button class="btn btn-action btn-view" onclick="viewReport(<?php echo $report['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-action btn-delete" onclick="deleteReport(<?php echo $report['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No reports found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#reportsTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[4, 'desc']]
            });

            // Sidebar toggle
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
            });

            // Responsive sidebar
            $(window).resize(function() {
                if ($(window).width() <= 768) {
                    $('#sidebar').addClass('active');
                }
            });

            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
        });

        // Logout confirmation
        function confirmLogout() {
            Swal.fire({
                title: 'Logout Confirmation',
                text: "Are you sure you want to logout?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, logout!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to admin_logout.php with confirmation
                    window.location.href = '../admin_login.php';
                }
            });
        }

        // View report details
        function viewReport(reportId) {
            // Fetch report details via AJAX
            fetch(`../AdminBackend/admin_process.php?action=get_report&id=${reportId}`)
                .then(response => response.json())
                .then(report => {
                    if (report.error) {
                        throw new Error(report.error);
                    }
                    Swal.fire({
                        title: 'Report Details',
                        html: `
                            <div class="text-start">
                                <p><strong>Cat Name:</strong> ${report.cat_name}</p>
                                <p><strong>Reporter:</strong> ${report.reporter_name}</p>
                                <p><strong>Breed:</strong> ${report.breed}</p>
                                <p><strong>Description:</strong> ${report.description}</p>
                                <p><strong>Last Seen:</strong> ${report.last_seen_date}</p>
                                <p><strong>Status:</strong> ${report.status}</p>
                            </div>
                        `,
                        confirmButtonColor: '#1a3c6d'
                    });
                })
                .catch(error => {
                    Swal.fire('Error', error.message || 'Failed to load report details', 'error');
                });
        }

        // Delete report
        function deleteReport(reportId) {
            Swal.fire({
                title: 'Delete Report',
                text: "Are you sure you want to delete this report?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Here you would typically make an AJAX call to delete the report
                    Swal.fire(
                        'Deleted!',
                        'Report has been deleted.',
                        'success'
                    ).then(() => {
                        // Reload the page or update the table
                        location.reload();
                    });
                }
            });
        }

        // Handle responsive sidebar on page load
        $(window).on('load', function() {
            if ($(window).width() <= 768) {
                $('#sidebar').addClass('active');
            }
        });
    </script>
</body>
</html>