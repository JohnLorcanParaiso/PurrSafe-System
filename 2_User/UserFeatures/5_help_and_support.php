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

        case 'others':
            header("Location: 6_others.php");
            exit();

        case 'search':
            $search_query = isset($_POST['search']) ? $_POST['search'] : '';
            header("Location: search.php?q=" . urlencode($search_query));
            exit();

        case 'profile':
            header("Location: 4.1_my_profile.php");
            exit();
    }
}

$username = $_SESSION['username'] ?? 'Guest';

function isSupportAvailable() {
    date_default_timezone_set('Asia/Singapore');
    $current_time = new DateTime();
    $current_hour = (int)$current_time->format('G');
    $current_minute = (int)$current_time->format('i');
    $current_day = (int)$current_time->format('N');
    
    $is_weekday = ($current_day >= 1 && $current_day <= 5);
    $total_minutes = ($current_hour * 60) + $current_minute;
    
    // Define business hours in minutes (9 AM to 5 PM)
    $start_time = 9 * 60;  // 9 AM in minutes
    $end_time = 17 * 60;   // 5 PM in minutes
    $closing_warning = $end_time - 30; // 30 minutes before closing
    
    if ($is_weekday) {
        if ($total_minutes >= $start_time && $total_minutes < $closing_warning) {
            return 'open';
        } elseif ($total_minutes >= $closing_warning && $total_minutes < $end_time) {
            return 'closing_soon';
        }
    }
    return 'closed';
}

$support_status = isSupportAvailable();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support</title>
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
                    <button type="submit" name="action" value="dashboard" class="btn btn-link nav-link text-dark">
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
                    <button type="submit" name="action" value="help" class="btn btn-link nav-link text-dark active">
                        <i class="fas fa-question-circle me-2"></i> Help and Support
                    </button>
                </form>
            </li>
            <li class="nav-item">
                <form method="POST">
                    <button type="submit" name="action" value="others" class="btn btn-link nav-link text-dark active">
                        <i class="fas fa-ellipsis-h me-2"></i> Others
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
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Help & Support</h2>
            </div>
        </header>

        <main class="main-content">
            <div class="row g-4">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Frequently Asked Questions</h5>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="faqAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                            How do I report a lost cat?
                                        </button>
                                    </h2>
                                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            To report a lost cat, click on "Create New Report" in the side menu. Fill out the form with your cat's details, including photos and last known location. Your report will be visible to other users in the area.
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                            What should I do if I found a cat?
                                        </button>
                                    </h2>
                                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            If you've found a cat, first check if it has any identification. Then create a "Found Cat" report with photos and location details. The system will automatically check for matching lost cat reports in the area.
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                            How do I update my cat's profile?
                                        </button>
                                    </h2>
                                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            Go to "My Profile" and select the cat's profile you want to update. Click the edit button to modify details such as photos, description, or medical information.
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                            How can I improve my cat's search visibility?
                                        </button>
                                    </h2>
                                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            <ul>
                                                <li>Upload clear, high-quality photos from multiple angles</li>
                                                <li>Provide detailed descriptions including distinctive marks</li>
                                                <li>Keep location information accurate and up-to-date</li>
                                                <li>Add tags for specific characteristics</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                            How to delete a report?
                                        </button>
                                    </h2>
                                    <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            To delete a report:
                                            <ol>
                                                <li>Go to "My Profile" in the side menu</li>
                                                <li>Find the report you want to delete</li>
                                                <li>Click on the delete button</li>
                                                <li>Confirm the deletion when prompted</li>
                                            </ol>
                                            <p class="text-muted small">Note: Deleted reports cannot be recovered.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                            How to delete my profile?
                                        </button>
                                    </h2>
                                    <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            To delete your profile:
                                            <ol>
                                                <li>Go to "Others" in the side menu</li>
                                                <li>Click on "Delete Account"</li>
                                                <li>Enter your password to confirm</li>
                                                <li>Select reason for deletion (optional)</li>
                                                <li>Click "Permanently Delete Account"</li>
                                            </ol>
                                            <p class="text-muted small">Warning: This action will permanently delete your account and all associated data.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mt-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Tips & Resources</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="resource-card p-3 border rounded">
                                        <h6><i class="fas fa-paw me-2"></i>Prevention Tips</h6>
                                        <ul class="list-unstyled mb-0">
                                            <li><i class="fas fa-check-circle me-2 text-success"></i>Microchip your cat</li>
                                            <li><i class="fas fa-check-circle me-2 text-success"></i>Use a collar with ID</li>
                                            <li><i class="fas fa-check-circle me-2 text-success"></i>Keep photos updated</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="resource-card p-3 border rounded">
                                        <h6><i class="fas fa-map-marked-alt me-2"></i>Search Strategies</h6>
                                        <ul class="list-unstyled mb-0">
                                            <li><i class="fas fa-check-circle me-2 text-success"></i>Search at dawn/dusk</li>
                                            <li><i class="fas fa-check-circle me-2 text-success"></i>Use familiar sounds</li>
                                            <li><i class="fas fa-check-circle me-2 text-success"></i>Place familiar items outside</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="resource-card p-3 border rounded">
                                        <h6><i class="fas fa-first-aid me-2"></i>Injured Cat Guidelines</h6>
                                        <ul class="list-unstyled mb-0">
                                            <li><i class="fas fa-check-circle me-2 text-success"></i>Take to nearest vet immediately</li>
                                            <li><i class="fas fa-check-circle me-2 text-success"></i>Handle with care and caution</li>
                                            <li><i class="fas fa-check-circle me-2 text-success"></i>Keep cat warm and calm</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="resource-card p-3 border rounded">
                                        <h6><i class="fas fa-camera me-2"></i>Photo Tips</h6>
                                        <ul class="list-unstyled mb-0">
                                            <li><i class="fas fa-check-circle me-2 text-success"></i>Take clear, well-lit photos</li>
                                            <li><i class="fas fa-check-circle me-2 text-success"></i>Include multiple angles</li>
                                            <li><i class="fas fa-check-circle me-2 text-success"></i>Highlight unique markings</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Contact Admin</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h6><i class="fas fa-envelope me-2"></i>Email Address</h6>
                                <p>purrsafecats@gmail.com</p>
                            </div>
                            <div class="mb-4">
                                <h6><i class="fas fa-phone me-2"></i>Phone Support</h6>
                                <p>+63 918 925 8041</p>
                                <div class="d-flex align-items-center">
                                    <small class="text-muted me-2">Available Mon-Fri, 9AM-5PM</small>
                                    <?php
                                    switch($support_status) {
                                        case 'open':
                                            echo '<span class="badge bg-success rounded-pill">Open Now</span>';
                                            break;
                                        case 'closing_soon':
                                            echo '<span class="badge bg-warning rounded-pill">About to Close</span>';
                                            break;
                                        case 'closed':
                                            echo '<span class="badge bg-danger rounded-pill">Closed</span>';
                                            break;
                                    }
                                    ?>
                                </div>
                            </div>
                            <form class="mt-4">
                                <h6><i class="fas fa-ticket-alt me-2"></i>Create Support Ticket</h6>
                                <div class="mb-3">
                                    <select class="form-select">
                                        <option selected>Select Issue Type</option>
                                        <option>Technical Problem</option>
                                        <option>Account Issues</option>
                                        <option>Report Bug</option>
                                        <option>Feature Request</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <textarea class="form-control" rows="3" placeholder="Describe your issue..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-custom w-100">Submit Ticket</button>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Emergency Contacts</h5>
                        </div>
                        <div class="card-body">
                            <div class="emergency-contact mb-3">
                                <h6><i class="fas fa-hospital me-2"></i>Nearest Vet Clinics</h6>
                                <ul class="list-unstyled">
                                    <li>Animal Care Clinic: +63 123 456 7890</li>
                                    <li>Pet Emergency Center: +63 123 456 7891</li>
                                </ul>
                            </div>
                            <div class="emergency-contact">
                                <h6><i class="fas fa-shield-alt me-2"></i>Animal Control</h6>
                                <p class="mb-1">24/7 Hotline: +63 123 456 7892</p>
                                <small class="text-muted">For immediate assistance with stray or injured cats</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
    function updateSupportStatus() {
        const now = new Date();
        const hours = now.getHours();
        const minutes = now.getMinutes();
        const day = now.getDay();
        const totalMinutes = (hours * 60) + minutes;
        
        // Check if it's a weekday (1-5, Monday-Friday)
        const isWeekday = day >= 1 && day <= 5;
        
        // Define business hours in minutes
        const startTime = 9 * 60;  // 9 AM
        const endTime = 17 * 60;   // 5 PM
        const closingWarning = endTime - 30; // 30 minutes before closing
        
        // Update the badge in the Contact Admin card
        const statusBadge = document.querySelector('.contact-admin .badge');
        
        if (isWeekday && totalMinutes >= startTime && totalMinutes < closingWarning) {
            statusBadge.className = 'badge bg-success rounded-pill';
            statusBadge.textContent = 'Open Now';
        } else if (isWeekday && totalMinutes >= closingWarning && totalMinutes < endTime) {
            statusBadge.className = 'badge bg-warning rounded-pill';
            statusBadge.textContent = 'About to Close';
        } else {
            statusBadge.className = 'badge bg-danger rounded-pill';
            statusBadge.textContent = 'Closed';
        }
    }

    // Initial update
    updateSupportStatus();

    // Update status every minute
    setInterval(updateSupportStatus, 60000);
    </script>
</body>
</html>