<?php
require_once '../../2_User/UserBackend/userAuth.php';
require_once '../../2_User/UserBackend/db.php';

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

        case 'submit_feedback':
            if (isset($_POST['report_id'], $_POST['rating'], $_POST['feedback_text']) && 
                isset($_POST['confirm_return'])) {
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO feedback (report_id, rating, feedback_text, feedback_date) 
                                         VALUES (?, ?, ?, NOW())");
                    $stmt->execute([
                        $_POST['report_id'],
                        $_POST['rating'],
                        $_POST['feedback_text']
                    ]);
                    
                    // Update the found_report status
                    $stmt = $pdo->prepare("UPDATE found_reports SET status = 'completed' WHERE id = ?");
                    $stmt->execute([$_POST['report_id']]);
                    
                    // Redirect to prevent form resubmission
                    header("Location: " . $_SERVER['PHP_SELF'] . "?success=feedback");
                    exit();
                } catch (PDOException $e) {
                    // Log error and show generic message
                    error_log($e->getMessage());
                    header("Location: " . $_SERVER['PHP_SELF'] . "?error=feedback");
                    exit();
                }
            }
            break;
    }
}

// Add success/error messages if they exist
if (isset($_GET['success']) && $_GET['success'] === 'feedback') {
    echo '<div class="alert alert-success">Thank you for your feedback!</div>';
} elseif (isset($_GET['error']) && $_GET['error'] === 'feedback') {
    echo '<div class="alert alert-danger">There was an error submitting your feedback. Please try again.</div>';
}

// Get found reports based on whether an ID was passed
$sql = "SELECT DISTINCT fr.*, lr.cat_name, lr.breed, u.fullname as founder_name,
        f.id as feedback_id, f.rating, f.feedback_text, f.feedback_date 
        FROM found_reports fr
        JOIN lost_reports lr ON fr.report_id = lr.id
        JOIN users u ON fr.user_id = u.id
        LEFT JOIN feedback f ON fr.id = f.report_id
        WHERE (lr.user_id = ? OR fr.user_id = ?)";  // Base conditions

// Add ID filter if coming from notifications
if (isset($_GET['id'])) {
    $sql .= " AND fr.id = ?";
}

$sql .= " GROUP BY fr.id ORDER BY fr.created_at DESC";

$stmt = $pdo->prepare($sql);

// Execute with or without the ID parameter
if (isset($_GET['id'])) {
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_GET['id']]);
} else {
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
}

$found_reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Found Cat Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../../4_Styles/user_style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <button type="submit" name="action" value="help" class="btn btn-link nav-link text-dark">
                        <i class="fas fa-question-circle me-2"></i> Help and Support
                    </button>
                </form>
            </li>
            <li class="nav-item">
                <form method="POST">
                    <button type="submit" name="action" value="others" class="btn btn-link nav-link text-dark">
                        <i class="fas fa-cog me-2"></i> Others
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
        <div class="mb-4">
            <a href="3.1_view_reports.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="fas fa-arrow-left me-2"></i>Back to Reports
            </a>
        </div>

        <div class="row">
            <?php foreach ($found_reports as $report): ?>
                <div class="col-12 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-cat me-2"></i><?= htmlspecialchars($report['cat_name']) ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <?php if ($report['image_path'] && file_exists($report['image_path'])): ?>
                                        <img src="<?= htmlspecialchars($report['image_path']) ?>" 
                                             class="img-fluid rounded mb-3" 
                                             alt="Found cat image"
                                             style="width: 100%; height: 300px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="no-image-container mb-3 rounded bg-light d-flex flex-column align-items-center justify-content-center" 
                                             style="height: 300px; border: 2px dashed #ccc;">
                                            <i class="fas fa-image text-muted mb-2" style="font-size: 48px;"></i>
                                            <p class="text-muted mb-0">No Image Uploaded</p>
                                            <small class="text-muted">The founder didn't provide a photo</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="border-bottom pb-2">Cat Details</h6>
                                    <p><strong>Name:</strong> <?= htmlspecialchars($report['cat_name']) ?></p>
                                    <p><strong>Breed:</strong> <?= htmlspecialchars($report['breed']) ?></p>
                                    <p><strong>Found By:</strong> <?= htmlspecialchars($report['founder_name']) ?></p>
                                    <p><strong>Contact:</strong> <?= htmlspecialchars($report['contact_number']) ?></p>
                                    <p><strong>Message:</strong> <?= nl2br(htmlspecialchars($report['owner_notification'])) ?></p>
                                    <p><strong>Date Found:</strong> <?= date('M j, Y', strtotime($report['created_at'])) ?></p>
                                    <div class="mt-2">
                                        <span class="badge bg-<?= isset($report['feedback_id']) ? 'success' : 'warning' ?>">
                                            <?= isset($report['feedback_id']) ? 'Feedback Submitted' : 'Pending Feedback' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Feedback Section -->
                            <div class="mt-4">
                                <h6 class="border-bottom pb-2">Feedback</h6>
                                <?php if (!isset($report['feedback_id'])): ?>
                                    <?php if ($report['status'] !== 'returned'): ?>
                                        <form id="feedbackForm<?= $report['id'] ?>" class="feedback-form" 
                                              action="../../2_User/UserBackend/submit_feedback.php" method="POST">
                                            <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">How satisfied are you with the return of your cat?</label>
                                                <div class="star-rating">
                                                    <div class="stars">
                                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star star-rating-btn" data-rating="<?= $i ?>" data-report="<?= $report['id'] ?>"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <input type="hidden" name="rating" id="rating<?= $report['id'] ?>" value="" required>
                                                    <small class="rating-text ms-2"></small>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Share your experience</label>
                                                <textarea class="form-control" name="feedback_text" rows="3" 
                                                          placeholder="How was your experience? Was your cat returned safely?" required></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="confirm_return" 
                                                           id="confirmReturn<?= $report['id'] ?>" required>
                                                    <label class="form-check-label" for="confirmReturn<?= $report['id'] ?>">
                                                        I confirm that my cat has been safely returned to me
                                                    </label>
                                                </div>
                                            </div>

                                            <button type="button" class="btn btn-primary submit-feedback-btn" data-report-id="<?= $report['id'] ?>">
                                                <i class="fas fa-paper-plane me-2"></i>Submit Feedback
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>This report has been marked as returned but feedback hasn't been submitted yet.
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="feedback-display">
                                        <div class="stars mb-2">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?= ($i <= $report['rating'] ? 'text-warning' : 'text-muted') ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="feedback-text mb-2"><?= htmlspecialchars($report['feedback_text']) ?></p>
                                        <small class="text-muted">
                                            <i class="fas fa-check-circle text-success me-1"></i>
                                            Cat confirmed returned on <?= date('M j, Y', strtotime($report['feedback_date'])) ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
    document.querySelectorAll('.star-rating').forEach(ratingContainer => {
        const stars = ratingContainer.querySelectorAll('.star-rating-btn');
        const ratingInput = ratingContainer.querySelector('input[name="rating"]');
        const ratingText = ratingContainer.querySelector('.rating-text');
        
        const ratingDescriptions = {
            1: 'Poor',
            2: 'Fair',
            3: 'Good',
            4: 'Very Good',
            5: 'Excellent'
        };

        function updateStars(rating) {
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
            
            if (rating > 0) {
                ratingText.textContent = ratingDescriptions[rating];
            } else {
                ratingText.textContent = '';
            }
        }

        // Handle hover effects
        stars.forEach((star, index) => {
            star.addEventListener('mouseover', () => {
                stars.forEach((s, i) => {
                    if (i <= index) {
                        s.classList.add('hover');
                    } else {
                        s.classList.remove('hover');
                    }
                });
                ratingText.textContent = ratingDescriptions[index + 1];
            });

            star.addEventListener('mouseout', () => {
                stars.forEach(s => s.classList.remove('hover'));
                const currentRating = parseInt(ratingInput.value) || 0;
                if (currentRating > 0) {
                    ratingText.textContent = ratingDescriptions[currentRating];
                } else {
                    ratingText.textContent = '';
                }
            });

            // Handle click
            star.addEventListener('click', () => {
                const rating = index + 1;
                ratingInput.value = rating;
                updateStars(rating);
            });
        });
    });

    document.querySelectorAll('.submit-feedback-btn').forEach(button => {
        button.addEventListener('click', async function() {
            const reportId = this.dataset.reportId;
            const form = document.getElementById(`feedbackForm${reportId}`);
            
            // Validate rating
            const rating = form.querySelector('input[name="rating"]').value;
            if (!rating) {
                Swal.fire({
                    title: 'Missing Rating',
                    text: 'Please select a star rating before submitting',
                    icon: 'warning',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            // Validate feedback text
            const feedbackText = form.querySelector('textarea[name="feedback_text"]').value.trim();
            if (!feedbackText) {
                Swal.fire({
                    title: 'Missing Feedback',
                    text: 'Please provide your feedback before submitting',
                    icon: 'warning',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            // Validate confirmation checkbox
            const confirmReturn = form.querySelector(`#confirmReturn${reportId}`).checked;
            if (!confirmReturn) {
                Swal.fire({
                    title: 'Confirmation Required',
                    text: 'Please confirm that your cat has been safely returned',
                    icon: 'warning',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            // Final confirmation
            const result = await Swal.fire({
                title: 'Submit Feedback?',
                text: 'This action cannot be undone. Are you sure you want to submit your feedback?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Yes, submit!',
                cancelButtonText: 'Cancel'
            });

            if (result.isConfirmed) {
                // Play sound and show gif before submitting
                const sound = document.getElementById('thankYouSound');
                if (sound) {
                    sound.play();
                }
                
                // Show a loading state
                Swal.fire({
                    title: 'Submitting...',
                    text: 'Please wait while we process your feedback',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Submit the form
                form.submit();
            }
        });
    });
    </script>
</body>
</html>