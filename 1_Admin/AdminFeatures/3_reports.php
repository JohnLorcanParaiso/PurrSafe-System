<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Dashboard</title>
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
                <li>
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
                <li class="active">
                    <a href="3_reports.php">
                        <i class="fas fa-flag"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li>
                    <a href="4_system_logs.php">
                        <i class="fas fa-file-alt"></i>
                        <span>System Logs</span>
                    </a>
                </li>
                <li>
                    <a href="5_feedbacks.php">
                        <i class="fas fa-comments"></i>
                        <span>Feedbacks</span>
                    </a>
                </li>
                <li>
                    <a href="6_create_announcement.php">
                        <i class="fas fa-bullhorn"></i>
                        <span>Create Announcement</span>
                    </a>
                </li>
                <li>
                    <a href="7_settings.php">
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

            <!-- Main Content -->
            <div class="container-fluid">
                <!-- Report Type Tabs -->
                <ul class="nav nav-tabs mb-4" id="reportTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="lost-tab" data-bs-toggle="tab" data-bs-target="#lost" type="button">
                            Lost Cats Reports
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="found-tab" data-bs-toggle="tab" data-bs-target="#found" type="button">
                            Found Cats Reports
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="reportsContent">
                    <!-- Lost Cats Tab -->
                    <div class="tab-pane fade show active" id="lost">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Lost Cats Reports</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="lostCatsTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Cat Name</th>
                                                <th>Reporter</th>
                                                <th>Location</th>
                                                <th>Date Lost</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Sample Data -->
                                            <tr>
                                                <td>1</td>
                                                <td>Whiskers</td>
                                                <td>John Doe</td>
                                                <td>Main Street</td>
                                                <td>2024-03-20</td>
                                                <td><span class="badge bg-warning">Lost</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm" onclick="viewReport(1, 'lost')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-success btn-sm" onclick="updateStatus(1, 'lost')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" onclick="deleteReport(1, 'lost')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Found Cats Tab -->
                    <div class="tab-pane fade" id="found">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Found Cats Reports</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="foundCatsTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Description</th>
                                                <th>Finder</th>
                                                <th>Location</th>
                                                <th>Date Found</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Sample Data -->
                                            <tr>
                                                <td>1</td>
                                                <td>Orange Tabby</td>
                                                <td>Jane Smith</td>
                                                <td>Park Avenue</td>
                                                <td>2024-03-21</td>
                                                <td><span class="badge bg-info">Found</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm" onclick="viewReport(1, 'found')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-success btn-sm" onclick="updateStatus(1, 'found')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" onclick="deleteReport(1, 'found')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
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
            // Initialize DataTables
            $('#lostCatsTable, #foundCatsTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[0, 'desc']]
            });

            // Sidebar toggle
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
            });
        });

        function viewReport(id, type) {
            // Sample report data
            const reportData = {
                lost: {
                    1: {
                        catName: 'Whiskers',
                        reporter: 'John Doe',
                        contact: '123-456-7890',
                        location: 'Main Street',
                        dateLost: '2024-03-20',
                        description: 'Gray tabby with white paws',
                        status: 'Lost'
                    }
                },
                found: {
                    1: {
                        description: 'Orange Tabby',
                        finder: 'Jane Smith',
                        contact: '098-765-4321',
                        location: 'Park Avenue',
                        dateFound: '2024-03-21',
                        status: 'Found'
                    }
                }
            };

            const report = reportData[type][id];
            const title = type === 'lost' ? 'Lost Cat Report Details' : 'Found Cat Report Details';
            const content = type === 'lost' ? `
                <div class="text-start">
                    <p><strong>Cat Name:</strong> ${report.catName}</p>
                    <p><strong>Reporter:</strong> ${report.reporter}</p>
                    <p><strong>Contact:</strong> ${report.contact}</p>
                    <p><strong>Location:</strong> ${report.location}</p>
                    <p><strong>Date Lost:</strong> ${report.dateLost}</p>
                    <p><strong>Description:</strong> ${report.description}</p>
                    <p><strong>Status:</strong> ${report.status}</p>
                </div>
            ` : `
                <div class="text-start">
                    <p><strong>Description:</strong> ${report.description}</p>
                    <p><strong>Finder:</strong> ${report.finder}</p>
                    <p><strong>Contact:</strong> ${report.contact}</p>
                    <p><strong>Location:</strong> ${report.location}</p>
                    <p><strong>Date Found:</strong> ${report.dateFound}</p>
                    <p><strong>Status:</strong> ${report.status}</p>
                </div>
            `;

            Swal.fire({
                title: title,
                html: content,
                confirmButtonColor: '#1a3c6d'
            });
        }

        function updateStatus(id, type) {
            Swal.fire({
                title: 'Update Status',
                text: "Mark this report as resolved?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, resolve it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Updated!', 'Report status has been updated.', 'success')
                    .then(() => {
                        location.reload();
                    });
                }
            });
        }

        function deleteReport(id, type) {
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
                    Swal.fire('Deleted!', 'Report has been deleted.', 'success')
                    .then(() => {
                        location.reload();
                    });
                }
            });
        }

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
                    window.location.href = '../admin_login.php';
                }
            });
        }
    </script>
</body>
</html>