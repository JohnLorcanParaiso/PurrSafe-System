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

require_once '../../2_User/UserBackend/db.php';

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
            
        case 'found':
            $report_id = isset($_POST['report_id']) ? $_POST['report_id'] : '';
            if ($report_id) {
                try {
                    // Redirect to submit_found_cat form first
                    header("Location: submit_found_cat.php?report_id=" . urlencode($report_id));
                    exit();
                } catch (PDOException $e) {
                    $_SESSION['error_message'] = "Database error occurred.";
                    header("Location: 3.1_view_reports.php");
                }
            }
            exit();
            
        case 'submit_found':
            $report_id = isset($_POST['report_id']) ? $_POST['report_id'] : '';
            if ($report_id && !empty($_POST['owner_notification']) && !empty($_POST['contact_number'])) {
                try {
                    $db->pdo->beginTransaction();

                    // Handle file upload
                    $image_path = null;
                    if (!empty($_FILES['image']['name'])) {
                        $target_dir = "../../5_Uploads/";
                        if (!file_exists($target_dir)) {
                            mkdir($target_dir, 0777, true);
                        }
                        
                        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
                        $image_path = $target_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                            // File uploaded successfully
                            $image_path = basename($image_path); // Store only filename in database
                        } else {
                            throw new Exception("Failed to upload image");
                        }
                    }

                    // Get the lost report details
                    $ownerQuery = "SELECT user_id, cat_name FROM lost_reports WHERE id = ?";
                    $ownerStmt = $pdo->prepare($ownerQuery);
                    $ownerStmt->execute([$report_id]);
                    $reportDetails = $ownerStmt->fetch();

                    if (!$reportDetails) {
                        throw new Exception("Lost report not found");
                    }

                    // Insert found report
                    $query = "INSERT INTO found_reports (user_id, report_id, owner_notification, founder_name, contact_number, image_path) 
                              VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($query);
                    $success = $stmt->execute([
                        $_SESSION['user_id'],
                        $report_id,
                        $_POST['owner_notification'],
                        $_SESSION['fullname'],
                        $_POST['contact_number'],
                        $image_path
                    ]);

                    if (!$success) {
                        throw new Exception("Failed to insert found report");
                    }

                    // Update the lost_reports status
                    $updateQuery = "UPDATE lost_reports SET status = 'found' WHERE id = ?";
                    $updateStmt = $pdo->prepare($updateQuery);
                    if (!$updateStmt->execute([$report_id])) {
                        throw new Exception("Failed to update lost report status");
                    }

                    // Create notification for the owner
                    $ownerNotification = "Good news! Your cat '" . htmlspecialchars($reportDetails['cat_name']) . "' has been found! Check your found reports section for contact details of the person who found your cat.";
                    $notificationQuery = "INSERT INTO notifications (user_id, message, is_read, created_at) 
                                        VALUES (?, ?, 0, NOW())";
                    $notificationStmt = $pdo->prepare($notificationQuery);
                    if (!$notificationStmt->execute([
                        $reportDetails['user_id'],
                        $ownerNotification
                    ])) {
                        throw new Exception("Failed to create owner notification");
                    }

                    // Create notification for the finder
                    $finderNotification = "Thank you for submitting a found report for the cat '" . htmlspecialchars($reportDetails['cat_name']) . "'! We have notified the owner, and they will be able to see your contact information. They will contact you soon to arrange the reunion.";
                    if (!$notificationStmt->execute([
                        $_SESSION['user_id'],
                        $finderNotification
                    ])) {
                        throw new Exception("Failed to create finder notification");
                    }

                    $db->pdo->commit();
                    exit(json_encode(['status' => 'success']));
                    
                } catch (Exception $e) {
                    $db->pdo->rollBack();
                    error_log("Found report submission error: " . $e->getMessage());
                    http_response_code(500);
                    exit(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
                }
            }
            http_response_code(400);
            exit(json_encode(['status' => 'error', 'message' => 'Invalid input']));
    }
}

$sql = "SELECT r.*, u.fullname as reporter_name, u.email as reporter_email, u.profile_pic as profile_picture, 
        GROUP_CONCAT(ri.image_path) as images, r.edited_at 
        FROM lost_reports r 
        LEFT JOIN report_images ri ON r.id = ri.report_id 
        LEFT JOIN users u ON r.user_id = u.id 
        GROUP BY r.id 
        ORDER BY r.created_at DESC";
$stmt = $pdo->query($sql);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add this function to format image paths
function formatImagePath($image) {
    if (empty($image)) {
        return '../../3_Images/cat-user.png'; // Default image
    }
    
    // If it's a full path, return as is
    if (strpos($image, '../../5_Uploads/') === 0) {
        return $image;
    }
    
    // Otherwise, prepend the uploads directory path
    return '../../5_Uploads/' . basename($image);
}

// Add this function at the top of your file with other functions
function getRandomMessage($isFound = false) {
    $foundMessages = [
        "Happily Found! Coming Home! 🏠",
        "Don't Worry, I'm Safe Now! 💕",
        "Found My Way Back Home! 🐱",
        "Reunited With My Hooman! 💝",
        "Happy Ending Achieved! ✨",
        "Back In My Hooman's Arms! 🤗",
        "Mission Accomplished: Home Bound! 🌟",
        "Found & Loved Again! 💫",
        "Purring With Joy: I'm Found! 😺",
        "Home Sweet Home At Last! 🏡",
        "Finally Back Where I Belong! 💖",
        "Cuddles With My Family Again! 🤗",
        "Safe & Sound With My Hooman! 🏡",
        "No More Adventures, I'm Home! 🐱",
        "Found My Forever Home Again! 💕"
    ];
    
    $searchingMessages = [
        "I Want To Go Home... 💔",
        "Missing My Hooman... 😿",
        "Please Help Me Get Home 🙏",
        "Looking For My Family 🔍",
        "Can't Wait To Be Home 🏠",
        "Missing My Warm Bed... 🛏️",
        "Hoping To See You Soon... ❤️",
        "Where Are You, Hooman? 🐱",
        "Need Cuddles From My Family 🤗",
        "Searching For My Way Back 🌟",
        "Lost & Looking For Home 🏠",
        "Someone Help Me Find My Family 💕",
        "Missing My Food Bowl... 🍽️",
        "My Hooman Must Be Worried 💔",
        "Just Want My Warm Blanket 🛏️"
    ];
    
    $messages = $isFound ? $foundMessages : $searchingMessages;
    return $messages[array_rand($messages)];
}

$username = $_SESSION['username'] ?? 'Guest';
$fullname = $_SESSION['fullname'] ?? 'Guest User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../../4_Styles/user_style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .modal-dialog-wide {
            max-width: 800px;
        }
        
        .found-marker {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 8px;
            color: white;
            text-align: center;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 -2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            z-index: 1;
            font-size: 0.9rem;
            background: linear-gradient(to top, rgba(0,0,0,0.7), rgba(0,0,0,0));
            padding-top: 20px;
        }

        .found-marker i {
            font-size: 1.1em;
            animation: bounce 1s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }

        .image-container {
            position: relative;
            overflow: hidden;
        }

        .found-image {
            transition: filter 0.3s ease;
            filter: brightness(0.9);
        }

        .loading-text {
            transition: opacity 0.2s ease-in-out;
        }
    </style>
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
                    <button type="submit" name="action" value="view" class="btn btn-link nav-link text-dark active">
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
        <?php if (isset($_GET['success']) && $_GET['success'] === 'found'): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                Thank you for reporting! The cat has been marked as found and the owner has been notified.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <header class="header-container mb-4">
            <div class="d-flex justify-content-between align-items-center gap-3">
                <form method="POST" class="d-flex flex-grow-1">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search..">
                        <button type="submit" name="action" value="search" class="btn btn-outline-secondary">
                            <img src="../../3_Images/search.png" alt="search" style="width: 20px;">
                        </button>
                    </div>
                </form>
                <div class="d-flex align-items-center gap-3">
                    <?php include '7_notifications.php'; ?>
                    <form method="POST" class="m-0">
                        <button type="submit" name="action" value="profile" class="btn rounded-circle p-0" style="width: 50px; height: 50px; overflow: hidden; border: none;">
                            <img src="../../3_Images/cat-user.png" alt="user profile" style="width: 100%; height: 100%; object-fit: cover;">
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="main-content">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-4">
                            <h4 class="card-title mb-0">Missing Cat Reports</h4>
                        </div>
                        <div class="card-body">
                            <?php if (empty($reports)): ?>
                                <p class="text-center">No reports found.</p>
                            <?php else: ?>
                                <div class="row g-4">
                                    <?php foreach ($reports as $report): ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card h-100">
                                                <?php 
                                                $images = explode(',', $report['images']);
                                                if (!empty($images[0])): 
                                                    $displayImage = formatImagePath($images[0]);
                                                ?>
                                                    <div class="image-container">
                                                        <img src="<?= htmlspecialchars($displayImage) ?>" 
                                                             class="card-img-top <?= $report['status'] === 'found' ? 'found-image' : '' ?>" 
                                                             alt="<?= htmlspecialchars($report['cat_name']) ?>"
                                                             style="height: 200px; object-fit: cover;">
                                                        <?php if ($report['status'] === 'found'): ?>
                                                            <div class="found-marker" style="background: linear-gradient(to top, #28a745 60%, rgba(40, 167, 69, 0));">
                                                                <i class="fas fa-heart"></i>
                                                                <?= getRandomMessage(true) ?>
                                                                <i class="fas fa-heart"></i>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="found-marker" style="background: linear-gradient(to top, #007bff 60%, rgba(0, 123, 255, 0));">
                                                                <i class="fas fa-search"></i>
                                                                <?= getRandomMessage(false) ?>
                                                                <i class="fas fa-paw"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="card-body">
    <h5 class="card-title d-flex justify-content-between align-items-center">
        <?= htmlspecialchars($report['cat_name']) ?>
        <div>
        <?php if ($report['user_id'] == $_SESSION['user_id']): ?>
    <span class="badge bg-info d-flex align-items-center gap-2" style="font-size: 0.8rem; padding: 8px 12px;">
        Owner: <?= htmlspecialchars($_SESSION['fullname']) ?>
        <div style="margin-right: -20px; margin-top: -20px; margin-bottom: -20px;">
            <img src="<?= !empty($report['profile_picture']) ? '../../6_Profile_Pictures/' . htmlspecialchars($report['profile_picture']) : '../../3_Images/cat-user.png' ?>" 
                 alt="Profile" 
                 class="rounded-circle" 
                 style="width: 70px; height: 70px; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        </div>
    </span>
<?php else: ?>
    <span class="badge bg-warning text-dark d-flex align-items-center gap-2" style="font-size: 0.8rem; padding: 8px 12px;">
        Owner: <?= htmlspecialchars($report['reporter_name']) ?>
        <div style="margin-right: -20px; margin-top: -20px; margin-bottom: -20px;">
            <img src="<?= !empty($report['profile_picture']) ? '../../6_Profile_Pictures/' . htmlspecialchars($report['profile_picture']) : '../../3_Images/cat-user.png' ?>" 
                 alt="Profile" 
                 class="rounded-circle" 
                 style="width: 70px; height: 70px; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        </div>
    </span>
<?php endif; ?>
        </div>
    </h5>
    <p class="card-text">
        <strong>Breed:</strong> <?= htmlspecialchars($report['breed']) ?><br>
        <strong>Last Seen:</strong> <?= htmlspecialchars($report['last_seen_date']) ?>
    </p>
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex gap-1">
            <a href="3.2_view_more.php?id=<?php echo $report['id']; ?>" 
               class="btn btn-outline-primary btn-sm rounded-pill px-2 py-1" style="font-size: 0.9rem;">
                <i class="fas fa-arrow-right"></i> View More
            </a>
            <?php if ($report['status'] === 'found'): ?>
                <a href="#" 
                   class="btn btn-outline-secondary btn-sm rounded-pill px-2 py-1" 
                   style="font-size: 0.9rem;"
                   onclick="showFoundPopup('<?= htmlspecialchars($report['cat_name']) ?>')">
                    <i class="fas fa-check-circle"></i> Found
                </a>
            <?php elseif ($report['user_id'] != $_SESSION['user_id']): ?>
                <a href="#" 
                   class="btn btn-outline-primary btn-sm rounded-pill px-2 py-1" 
                   style="font-size: 0.9rem;"
                   onclick="event.preventDefault(); showFoundForm('<?= $report['id'] ?>');">
                    <i class="fas fa-exclamation-circle"></i> Found
                </a>
            <?php endif; ?>
        </div>
        <div class="text-end">
            <small class="text-muted d-block">
                Created: <?= (new DateTime($report['created_at']))->format('M j, Y g:i A') ?>
            </small>
            <?php if (!empty($report['edited_at'])): ?>
                <small class="text-muted d-block">
                    Last Edited: <?= (new DateTime($report['edited_at']))->format('M j, Y g:i A') ?>
                </small>
            <?php endif; ?>
        </div>
    </div>
</div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        </div>

    <div class="modal fade" id="foundCatModal" tabindex="-1" aria-labelledby="foundCatModalLabel" aria-hidden="false">
        <div class="modal-dialog modal-dialog-wide">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="foundCatModalLabel">Submit Found Cat Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="foundCatForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="submit_found">
                        <input type="hidden" name="report_id" id="report_id">

                        <div class="mb-4">
                            <label for="owner_notification" class="form-label">Notify Owner (please provide details about the cat's condition and how to contact you):</label>
                            <textarea id="owner_notification" name="owner_notification" class="custom-input form-control" required placeholder="Describe the condition of the cat, how the owner can contact you, and any other relevant information."></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Your Name:</label>
                            <input type="text" class="custom-input form-control" value="<?= htmlspecialchars($_SESSION['fullname']) ?>" disabled>
                            <input type="hidden" name="founder_name" value="<?= htmlspecialchars($_SESSION['fullname']) ?>">
                        </div>

                        <div class="mb-4">
                            <label for="contact_number" class="form-label">Contact Number:</label>
                            <input type="text" id="contact_number" name="contact_number" class="custom-input form-control" required placeholder="Enter your contact number">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">How would you like to proceed?</label>
                            <div class="border p-3 rounded d-flex">
                                <div class="me-2">
                                    <input type="radio" id="return_cat" name="proceed_option" value="return_cat" class="btn-check" autocomplete="off" required>
                                    <label for="return_cat" class="btn btn-outline-warning">Return the Cat to Owner</label>
                                </div>
                                <div>
                                    <input type="radio" id="owner_claim" name="proceed_option" value="owner_claim" class="btn-check" autocomplete="off">
                                    <label for="owner_claim" class="btn btn-outline-success">Owner to Claim the Cat</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="image" class="form-label">Upload Image of the Found Cat (optional):</label>
                            <input type="file" id="image" name="image" class="custom-input form-control" accept="image/*" />
                        </div>

                        <button type="submit" class="btn btn-primary">Submit Report</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
    function showFoundForm(reportId) {
        document.getElementById('report_id').value = reportId;
        var modal = new bootstrap.Modal(document.getElementById('foundCatModal'));
        modal.show();
    }

    document.getElementById('foundCatForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        fetch('3.1_view_reports.php', {  // Update the URL to the current file
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                var modal = bootstrap.Modal.getInstance(document.getElementById('foundCatModal'));
                modal.hide();
                
                // Play the meow sound
                const meowSound = document.getElementById('meowSound');
                if (meowSound) {
                    meowSound.play();
                }
                
                Swal.fire({
                    title: 'Success!',
                    text: 'Thank you for reporting! The owner has been notified.',
                    imageUrl: '../../3_Images/praying-cat.gif',
                    imageWidth: 200,
                    imageHeight: 200,
                    imageAlt: 'Thank you cat',
                    showConfirmButton: true
                }).then((result) => {
                    window.location.href = '3.1_view_reports.php?success=found';
                });
            } else {
                throw new Error(data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'An error occurred while submitting the report.'
            });
        });
    });

    function showFoundPopup(catName) {
        Swal.fire({
            title: 'Cat Already Found',
            text: `${catName} has already been found and is no longer missing.`,
            icon: 'info',
            confirmButtonText: 'OK',
            confirmButtonColor: '#2196F3',
            allowOutsideClick: false,
            customClass: {
                popup: 'animated fadeInDown'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Optionally redirect or perform any action when OK is clicked
                window.location.reload();
            }
        });
    }
    </script>
    <img src="../../3_Images/praying-cat.gif" class="flying-cat" id="flyingCat" alt="Thank you!">
    <audio id="meowSound" src="../../7_Sounds/cute ringtone   2.mp3" preload="auto"></audio>
</body>
</html>