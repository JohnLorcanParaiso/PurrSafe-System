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
    <title>Manage Users - Admin Dashboard</title>
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
                <li class="active">
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
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">User Management</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="usersTable" class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Sample Data -->
                                            <tr>
                                                <td>1</td>
                                                <td>john_doe</td>
                                                <td>john@example.com</td>
                                                <td>User</td>
                                                <td><span class="badge bg-success">Active</span></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm" onclick="viewUser(1)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" onclick="deleteUser(1)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <!-- Add more sample rows as needed -->
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
            // Initialize DataTable
            $('#usersTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[0, 'asc']]
            });

            // Sidebar toggle
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
            });
        });

        function viewUser(userId) {
            // Sample user data
            const userData = {
                username: 'john_doe',
                email: 'john@example.com',
                role: 'User',
                status: 'Active',
                created_at: '2024-03-20'
            };

            Swal.fire({
                title: 'User Details',
                html: `
                    <div class="text-start">
                        <p><strong>Username:</strong> ${userData.username}</p>
                        <p><strong>Email:</strong> ${userData.email}</p>
                        <p><strong>Role:</strong> ${userData.role}</p>
                        <p><strong>Status:</strong> ${userData.status}</p>
                        <p><strong>Created:</strong> ${userData.created_at}</p>
                    </div>
                `,
                confirmButtonColor: '#1a3c6d'
            });
        }

        function deleteUser(userId) {
            Swal.fire({
                title: 'Delete User',
                text: "Are you sure you want to delete this user?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Deleted!', 'User has been deleted.', 'success')
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