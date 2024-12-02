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
    }
}

$username = $_SESSION['username'] ?? 'Guest';
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
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Help & Support</h2>
                <div class="d-flex align-items-center gap-3">
                    <form method="POST" class="m-0">
                        <button type="submit" name="action" value="profile" class="btn btn-outline-secondary rounded-circle p-2">
                            <img src="images/user.png" alt="user profile" style="width: 28px; height: 28px;">
                        </button>
                    </form>
                </div>
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
                                                <li>Go to "View Reports" in the side menu</li>
                                                <li>Find the report you want to delete</li>
                                                <li>Click on the three dots (⋮) menu</li>
                                                <li>Select "Delete Report"</li>
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
                                                <li>Go to "Settings" in the side menu</li>
                                                <li>Scroll down to "Account Management"</li>
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
                                <small class="text-muted">Available Mon-Fri, 9AM-5PM</small>
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
</body>
</html>