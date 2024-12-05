<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

// Include the database connection
include '../../1_Admin/AdminBackend/admin_db.php'; // Adjust the path as necessary
$conn = connect(); // Use the connect function from admin_db.php

// Handle form submission for creating an announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $announcement_title = $_POST['title'];
    $announcement_content = $_POST['content'];

    // Insert announcement into the database
    $stmt = $conn->prepare("INSERT INTO announcements (title, content, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $announcement_title, $announcement_content);
    
    if ($stmt->execute()) {
        // Redirect or show success message
        header('Location: 6_create_announcement.php?success=1');
        exit();
    } else {
        echo "Error: " . $stmt->error; // Display error if insert fails
    }
    $stmt->close();
}

// Fetch previous announcements
$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
if (!$announcements) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Announcement - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../4_Styles/admin_style.css">
    <style>
        .form-control {
            padding: 0.25rem 0.5rem; /* Adjust padding */
            margin-bottom: 0.5rem; /* Adjust margin */
        }
    </style>
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
                </ li>
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
                <li class="active">
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

            <!-- Create Announcement Content -->
            <div class="container-fluid">
                <h2>Create Announcement</h2>
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success" role="alert">
                        Announcement created successfully!
                    </div>
                <?php endif; ?>
                <form method="POST" action="6_create_announcement.php">
                    <div class="mb-1"> 
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-1"> 
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="1" style="resize: none; height: 30px; max-height: 30px;" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Announcement</button>
                </form>

                <h3 class="mt-4">Previous Announcements</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $announcements->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['content']); ?></td>
                                <td><?php echo $row['created_at']; ?></td>
                                <td>
                                    <a href="edit_announcement.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <button class="btn btn-danger btn-sm" onclick="deleteAnnouncement(<?php echo $row['id']; ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        // Sidebar toggle
        $('#sidebarCollapse').on('click', function() {
            $('#sidebar').toggleClass('active');
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
                    window.location.href = '../admin_login.php';
                }
            });
        }

        function deleteAnnouncement(id) {
            Swal.fire({
                title: 'Delete Announcement',
                text: "Are you sure you want to delete this announcement?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Here you would typically make an AJAX call to delete the announcement
                    Swal.fire(
                        'Deleted!',
                        'Announcement has been deleted.',
                        'success'
                    ).then(() => {
                        // Reload the page or update the table
                        location.reload();
                    });
                }
            });
        }
    </script>
</body>
</html>