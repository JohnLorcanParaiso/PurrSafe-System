<?php
session_start();
require_once '../../1_Admin/AdminBackend/adminAuth.php';
require_once '../../2_User/UserBackend/db.php';

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    header('Location: ../../1_Admin/AdminBackend/admin_login.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT st.*, u.fullname, u.email 
    FROM support_tickets st
    JOIN users u ON st.user_id = u.id
    ORDER BY 
        CASE st.status
            WHEN 'pending' THEN 1
            WHEN 'in_progress' THEN 2
            WHEN 'resolved' THEN 3
            WHEN 'closed' THEN 4
        END,
        st.created_at DESC
");
$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Tickets - Admin Dashboard</title>
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
                <li>
                    <a href="3_reports.php">
                        <i class="fas fa-flag"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li class="active">
                    <a href="receive_ticket.php">
                        <i class="fas fa-ticket-alt"></i>
                        <span>Receive Ticket</span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0);" onclick="confirmLogout()">
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
                        <img src="../../3_Images/user.png" alt="Admin" class="rounded-circle" width="40">
                    </div>
                </div>
            </nav>

            <div class="container-fluid">
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Support Tickets</h5>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="filterTickets('all')">All</button>
                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="filterTickets('pending')">Pending</button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="filterTickets('in_progress')">In Progress</button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="filterTickets('resolved')">Resolved</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="ticketsTable" class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>User</th>
                                                <th>Issue Type</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($tickets as $ticket): ?>
                                                <tr class="ticket-row" data-status="<?= htmlspecialchars($ticket['status']) ?>">
                                                    <td>#<?= htmlspecialchars($ticket['id']) ?></td>
                                                    <td>
                                                        <?= htmlspecialchars($ticket['fullname']) ?><br>
                                                        <small class="text-muted"><?= htmlspecialchars($ticket['email']) ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary">
                                                            <?= htmlspecialchars($ticket['issue_type']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= htmlspecialchars($ticket['description']) ?></td>
                                                    <td>
                                                        <?php
                                                        $statusClass = match($ticket['status']) {
                                                            'pending' => 'warning',
                                                            'in_progress' => 'info',
                                                            'resolved' => 'success',
                                                            'closed' => 'secondary',
                                                            default => 'primary'
                                                        };
                                                        ?>
                                                        <span class="badge bg-<?= $statusClass ?>">
                                                            <?= ucfirst(htmlspecialchars($ticket['status'])) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= date('M j, Y g:i A', strtotime($ticket['created_at'])) ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary" 
                                                                onclick="openResponseModal(<?= $ticket['id'] ?>)">
                                                            <i class="fas fa-reply"></i> Respond
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
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

    <div class="modal fade" id="responseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Respond to Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="responseForm" action="update_ticket.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="ticket_id" id="ticketId">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="in_progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Response</label>
                            <textarea class="form-control" name="admin_response" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Send Response</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#ticketsTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[5, 'desc']]
            });

            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
            });
        });

        function filterTickets(status) {
            const rows = document.querySelectorAll('.ticket-row');
            rows.forEach(row => {
                if (status === 'all' || row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function openResponseModal(ticketId) {
            document.getElementById('ticketId').value = ticketId;
            new bootstrap.Modal(document.getElementById('responseModal')).show();
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
                    window.location.href = '../AdminBackend/admin_logout.php?confirm=true';
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