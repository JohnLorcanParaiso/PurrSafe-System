<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

// Include the database connection
include '../admin_db.php'; // Adjust the path as necessary
$conn = connect(); // Use the connect function from admin_db.php

// Fetch logs from the database
$result = $conn->query("SELECT * FROM logs ORDER BY created_at DESC");
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/style.css">
</head>
<body>
    <div class="wrapper">
        <nav id="sidebar">
            <div class="sidebar-header">
                <img src="../images/logo.png" alt="Logo" class="img-fluid logo">
                <h3>Admin Panel</h3>
            </div>

            <ul class="list-unstyled components">
                <li>
                    <a href="1_dashboard.php">
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
                <li class="active">
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

        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-primary">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="ms-auto d-flex align-items-center">
                        <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</span>
                        <img src="user.png" alt="Admin" class="rounded-circle" width="40">
                    </div>
                </div>
            </nav>

            <div class="container-fluid">
                <h2>System Logs</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Action</th>
                            <th>Admin Username</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['action']; ?></td>
                                <td><?php echo $row['admin_username']; ?></td>
                                <td><?php echo $row['created_at']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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

        $(window).on('load', function() {
            if ($(window).width() <= 768) {
                $('#sidebar').addClass('active');
            }
        });
    </script>
</body>
</html>