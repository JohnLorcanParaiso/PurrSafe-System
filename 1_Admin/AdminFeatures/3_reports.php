<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

require_once '../AdminBackend/admin_process.php';

$adminProcess = new AdminProcess();
$reports = $adminProcess->getAllReports();

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

            <!-- Main Content -->
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">All User Reports</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="reportsTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cat Name</th>
                                        <th>Reporter</th>
                                        <th>Breed</th>
                                        <th>Last Seen</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($reports && $reports->num_rows > 0): ?>
                                        <?php while ($report = $reports->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($report['id']); ?></td>
                                                <td><?php echo htmlspecialchars($report['cat_name']); ?></td>
                                                <td><?php echo htmlspecialchars($report['reporter_name']); ?></td>
                                                <td><?php echo htmlspecialchars($report['breed']); ?></td>
                                                <td><?php echo htmlspecialchars($report['last_seen_date']); ?></td>
                                                <td><span class="badge <?php echo $report['status'] === 'found' ? 'bg-success' : 'bg-warning'; ?>"><?php echo ucfirst($report['status']); ?></span></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($report['created_at'])); ?></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm" onclick="viewReport(<?php echo $report['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" onclick="deleteReport(<?php echo $report['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No reports found</td>
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
            $('#reportsTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[6, 'desc']]
            });

            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
            });
        });

        function viewReport(id) {
            fetch(`../AdminBackend/admin_process.php?action=get_report&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    // Format the date
                    const createdAt = new Date(data.created_at).toLocaleString();
                    const lastSeen = new Date(data.last_seen_date).toLocaleDateString();
                    
                    // Create the status badge HTML
                    const statusBadgeClass = data.status === 'found' ? 'bg-success' : 'bg-warning';
                    const statusBadge = `<span class="badge ${statusBadgeClass}">${data.status === 'found' ? 'Found' : 'Missing'}</span>`;

                    Swal.fire({
                        title: 'Report Details',
                        html: `
                            <div class="text-start">
                                <p><strong>Cat Name:</strong> ${data.cat_name}</p>
                                <p><strong>Reporter:</strong> ${data.reporter_name}</p>
                                <p><strong>Breed:</strong> ${data.breed}</p>
                                <p><strong>Gender:</strong> ${data.gender}</p>
                                <p><strong>Color:</strong> ${data.color}</p>
                                <p><strong>Age:</strong> ${data.age}</p>
                                <p><strong>Last Seen Date:</strong> ${lastSeen}</p>
                                <p><strong>Last Seen Location:</strong> ${data.last_seen_location}</p>
                                <p><strong>Description:</strong> ${data.description}</p>
                                <p><strong>Status:</strong> ${statusBadge}</p>
                                <p><strong>Created At:</strong> ${createdAt}</p>
                                ${data.image_path ? `<img src="../../5_Uploads/${data.image_path}" class="img-fluid mt-2" style="max-height: 200px;">` : '<p>No image available</p>'}
                            </div>
                        `,
                        width: '600px',
                        confirmButtonColor: '#1a3c6d',
                        confirmButtonText: 'Close'
                    });
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to load report details'
                    });
                });
        }

        function deleteReport(id) {
            Swal.fire({
                title: 'Delete Report',
                text: "Are you sure you want to delete this report? This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../AdminBackend/admin_process.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=deleteReport&id=${id}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            throw new Error(data.error);
                        }
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'The report has been deleted successfully.',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            // Reload the page to refresh the table
                            location.reload();
                        });
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.message || 'Failed to delete the report'
                        });
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