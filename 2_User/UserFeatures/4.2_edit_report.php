<?php
require_once '../../2_User/UserBackend/userAuth.php';
require_once '../../2_User/UserBackend/db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$login = new Login();
if (!$login->isLoggedIn()) {
    header('Location: ../../2_User/UserBackend/login.php');
    exit();
}

// Get report ID from URL
$report_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch the report details with images
try {
    $sql = "SELECT r.*, GROUP_CONCAT(ri.image_path) as images 
            FROM lost_reports r 
            LEFT JOIN report_images ri ON r.id = ri.report_id 
            WHERE r.id = ? AND r.user_id = ?
            GROUP BY r.id";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$report_id, $_SESSION['user_id']]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        $_SESSION['error'] = "Report not found or unauthorized access.";
        header('Location: 4.1_my_profile.php');
        exit();
    }

    // Convert images string to array
    $report['image_array'] = $report['images'] ? explode(',', $report['images']) : [];

} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching report: " . $e->getMessage();
    header('Location: 4.1_my_profile.php');
    exit();
}

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'update':
            try {
                // Start transaction
                $pdo->beginTransaction();
        
                // Update main report information
                // In the update case, modify the UPDATE query:
                $sql = "UPDATE lost_reports SET 
                        cat_name = ?, breed = ?, color = ?, age = ?, gender = ?,
                        last_seen_date = ?, last_seen_time = ?, last_seen_location = ?,
                        description = ?, owner_name = ?, phone_number = ?, edited_at = CURRENT_TIMESTAMP
                        WHERE id = ? AND user_id = ?";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                        trim($_POST['cat_name']),
                        trim($_POST['breed']),
                        trim($_POST['color']),
                        (int)$_POST['age'],
                        $_POST['gender'],
                        $_POST['last_seen_date'],
                        $_POST['last_seen_time'] ?: null,
                        trim($_POST['last_seen_location']),
                        trim($_POST['description']),
                        trim($_POST['owner_name']),
                        trim($_POST['phone_number']),
                        $report_id,
                        $_SESSION['user_id']
                ]);
        
                // Handle image deletions
                if (!empty($_POST['delete_images'])) {
                    foreach ($_POST['delete_images'] as $image) {
                        if (file_exists($image)) {
                            unlink($image);
                        }
                        $sql = "DELETE FROM report_images WHERE report_id = ? AND image_path = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$report_id, $image]);
                    }
                }
        
                // Handle new image uploads
                if (!empty($_FILES['new_images']['name'][0])) {
                    $uploadDir = 'uploads/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
        
                    foreach ($_FILES['new_images']['tmp_name'] as $key => $tmp_name) {
                        if ($_FILES['new_images']['error'][$key] === UPLOAD_ERR_OK) {
                            $fileName = uniqid() . '_' . basename($_FILES['new_images']['name'][$key]);
                            $targetPath = $uploadDir . $fileName;
                            
                            if (move_uploaded_file($tmp_name, $targetPath)) {
                                $sql = "INSERT INTO report_images (report_id, image_path) VALUES (?, ?)";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$report_id, $targetPath]);
                            }
                        }
                    }
                }
        
                $pdo->commit();
                $_SESSION['success'] = "Report updated successfully!";
                header("Location: 4.1_my_profile.php");
                exit();
        
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['error'] = "Error updating report: " . $e->getMessage();
                header("Location: 4.2_edit_report.php?id=" . $report_id);
                exit();
            }
            break;

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

        case 'logout':
            $login->logout();
            header('Location: ../../2_User/UserBackend/login.php');
            exit();
    }
}

$username = $_SESSION['username'] ?? 'Guest';
$fullname = $_SESSION['fullname'] ?? 'Guest User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../../4_Styles/user_style.css">
</head>
<body>
    <!-- Side Menu -->
    <div class="side-menu">
        <!-- ... copy the side menu from 4.1_my_profile.php ... -->
    </div>

    <div class="container-custom">
        <!-- Header -->
        <header class="header-container mb-4">
            <!-- ... copy the header from 4.1_my_profile.php ... -->
        </header>

        <main class="main-content">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h4 class="card-title mb-0">Edit Report</h4>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update">
                        
                        <!-- Cat Information -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Cat Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Cat Name</label>
                                    <input type="text" class="form-control" name="cat_name" 
                                           value="<?= htmlspecialchars($report['cat_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Breed</label>
                                    <select class="form-select" id="breedSelect" name="breed" required>
                                        <option value="" disabled>Search or select breed...</option>
                                        <optgroup label="Common/Mixed Breeds">
                                            <option value="Domestic Short Hair (Mixed)" <?= $report['breed'] == 'Domestic Short Hair (Mixed)' ? 'selected' : '' ?>>Domestic Short Hair (Mixed)</option>
                                            <option value="Domestic Medium Hair (Mixed)" <?= $report['breed'] == 'Domestic Medium Hair (Mixed)' ? 'selected' : '' ?>>Domestic Medium Hair (Mixed)</option>
                                            <option value="Domestic Long Hair (Mixed)" <?= $report['breed'] == 'Domestic Long Hair (Mixed)' ? 'selected' : '' ?>>Domestic Long Hair (Mixed)</option>
                                            <option value="Mixed Breed" <?= $report['breed'] == 'Mixed Breed' ? 'selected' : '' ?>>Mixed Breed</option>
                                            <option value="Unknown" <?= $report['breed'] == 'Unknown' ? 'selected' : '' ?>>Unknown</option>
                                        </optgroup>
                                        <optgroup label="Pure Breeds">
                                            <option value="Abyssinian" <?= $report['breed'] == 'Abyssinian' ? 'selected' : '' ?>>Abyssinian</option>
                                            <option value="American Bobtail" <?= $report['breed'] == 'American Bobtail' ? 'selected' : '' ?>>American Bobtail</option>
                                            <option value="American Curl" <?= $report['breed'] == 'American Curl' ? 'selected' : '' ?>>American Curl</option>
                                            <option value="American Shorthair" <?= $report['breed'] == 'American Shorthair' ? 'selected' : '' ?>>American Shorthair</option>
                                            <option value="American Wirehair" <?= $report['breed'] == 'American Wirehair' ? 'selected' : '' ?>>American Wirehair</option>
                                            <option value="Balinese" <?= $report['breed'] == 'Balinese' ? 'selected' : '' ?>>Balinese</option>
                                            <option value="Bengal" <?= $report['breed'] == 'Bengal' ? 'selected' : '' ?>>Bengal</option>
                                            <option value="Birman" <?= $report['breed'] == 'Birman' ? 'selected' : '' ?>>Birman</option>
                                            <option value="Bombay" <?= $report['breed'] == 'Bombay' ? 'selected' : '' ?>>Bombay</option>
                                            <option value="British Shorthair" <?= $report['breed'] == 'British Shorthair' ? 'selected' : '' ?>>British Shorthair</option>
                                            <option value="Burmese" <?= $report['breed'] == 'Burmese' ? 'selected' : '' ?>>Burmese</option>
                                            <option value="Chartreux" <?= $report['breed'] == 'Chartreux' ? 'selected' : '' ?>>Chartreux</option>
                                            <option value="Cornish Rex" <?= $report['breed'] == 'Cornish Rex' ? 'selected' : '' ?>>Cornish Rex</option>
                                            <option value="Devon Rex" <?= $report['breed'] == 'Devon Rex' ? 'selected' : '' ?>>Devon Rex</option>
                                            <option value="Egyptian Mau" <?= $report['breed'] == 'Egyptian Mau' ? 'selected' : '' ?>>Egyptian Mau</option>
                                            <option value="Exotic Shorthair" <?= $report['breed'] == 'Exotic Shorthair' ? 'selected' : '' ?>>Exotic Shorthair</option>
                                            <option value="Himalayan" <?= $report['breed'] == 'Himalayan' ? 'selected' : '' ?>>Himalayan</option>
                                            <option value="Maine Coon" <?= $report['breed'] == 'Maine Coon' ? 'selected' : '' ?>>Maine Coon</option>
                                            <option value="Manx" <?= $report['breed'] == 'Manx' ? 'selected' : '' ?>>Manx</option>
                                            <option value="Norwegian Forest Cat" <?= $report['breed'] == 'Norwegian Forest Cat' ? 'selected' : '' ?>>Norwegian Forest Cat</option>
                                            <option value="Persian" <?= $report['breed'] == 'Persian' ? 'selected' : '' ?>>Persian</option>
                                            <option value="Ragdoll" <?= $report['breed'] == 'Ragdoll' ? 'selected' : '' ?>>Ragdoll</option>
                                            <option value="Russian Blue" <?= $report['breed'] == 'Russian Blue' ? 'selected' : '' ?>>Russian Blue</option>
                                            <option value="Siamese" <?= $report['breed'] == 'Siamese' ? 'selected' : '' ?>>Siamese</option>
                                            <option value="Siberian" <?= $report['breed'] == 'Siberian' ? 'selected' : '' ?>>Siberian</option>
                                            <option value="Sphynx" <?= $report['breed'] == 'Sphynx' ? 'selected' : '' ?>>Sphynx</option>
                                        </optgroup>
                                        <optgroup label="Other">
                                            <option value="Other" <?= $report['breed'] == 'Other' ? 'selected' : '' ?>>Other (Please specify in description)</option>
                                            <option value="Not Sure" <?= $report['breed'] == 'Not Sure' ? 'selected' : '' ?>>Not Sure</option>
                                        </optgroup>
                                    </select>
                                    <div class="form-text">Start typing to search for breeds</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Physical Characteristics</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Color</label>
                                    <input type="text" class="form-control" name="color" 
                                           value="<?= htmlspecialchars($report['color']) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Age (Years)</label>
                                    <input type="number" class="form-control" name="age" 
                                           value="<?= htmlspecialchars($report['age']) ?>"
                                           min="0" max="30" step="1" 
                                           style="width: 80px;"
                                           oninput="this.value = Math.round(this.value);" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" name="gender" required>
                                        <option value="male" <?= strtolower($report['gender']) == 'male' ? 'selected' : '' ?>>Male</option>
                                        <option value="female" <?= strtolower($report['gender']) == 'female' ? 'selected' : '' ?>>Female</option>
                                        <option value="unknown" <?= strtolower($report['gender']) == 'unknown' ? 'selected' : '' ?>>Unknown</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Last Seen Information -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Last Seen Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Last Seen Date</label>
                                    <input type="date" class="form-control" name="last_seen_date" 
                                           value="<?= htmlspecialchars($report['last_seen_date']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Seen Time (Optional)</label>
                                    <input type="time" class="form-control" name="last_seen_time" 
                                           value="<?= htmlspecialchars($report['last_seen_time'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Last Seen Location</label>
                                    <input type="text" class="form-control" name="last_seen_location" 
                                           value="<?= htmlspecialchars($report['last_seen_location']) ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Additional Details</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="3" 
                                              required><?= htmlspecialchars($report['description']) ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Contact Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Owner Name</label>
                                    <input type="text" class="form-control" name="owner_name" 
                                           value="<?= htmlspecialchars($report['owner_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" name="phone_number" 
                                           value="<?= htmlspecialchars($report['phone_number']) ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- Current Images -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Current Images</h6>
                            <div class="row g-3">
                                <?php if (!empty($report['image_array'])): ?>
                                    <?php foreach ($report['image_array'] as $image): ?>
                                        <div class="col-auto">
                                            <div class="position-relative">
                                                <img src="<?= htmlspecialchars($image) ?>" 
                                                     class="rounded" 
                                                     style="width: 100px; height: 100px; object-fit: cover;">
                                                <div class="form-check position-absolute top-0 end-0 m-1">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="delete_images[]" 
                                                           value="<?= htmlspecialchars($image) ?>">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <p class="text-muted">No images uploaded</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($report['image_array'])): ?>
                                <small class="text-muted">Check the boxes to delete images</small>
                            <?php endif; ?>
                        </div>

                        <!-- Upload New Images -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Upload New Images</h6>
                            <input type="file" class="form-control" name="new_images[]" 
                                   accept="image/*" multiple>
                            <small class="text-muted">You can select multiple images</small>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="4.1_my_profile.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
    // Prevent decimal input for age
    document.querySelector('input[name="age"]').addEventListener('keypress', function(evt) {
        if (evt.key === '.' || evt.key === ',') {
            evt.preventDefault();
        }
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const phoneNumber = document.querySelector('input[name="phone_number"]').value;
        if (!phoneNumber.match(/^[\d\s\-\(\)\+\.]+$/)) {
            e.preventDefault();
            alert('Please enter a valid phone number');
            return;
        }
    });
    </script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    $(document).ready(function() {
        $('#breedSelect').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Search or select breed...',
            allowClear: true,
            selectionCssClass: 'select2--large',
            dropdownCssClass: 'select2--large',
        });

        // Custom styling to match your theme
        const style = document.createElement('style');
        style.textContent = `
            .select2-container--bootstrap-5 .select2-selection {
                border: 1px solid #dee2e6;
                padding: 0.375rem 0.75rem;
                height: calc(3.5rem + 2px);
                line-height: 1.5;
                font-size: 1rem;
            }
            .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
                padding: 0.375rem 0;
                color: #212529;
            }
            .select2-container--bootstrap-5 .select2-dropdown {
                border-color: #dee2e6;
                border-radius: 0.375rem;
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            }
            .select2-container--bootstrap-5 .select2-results__option--highlighted[aria-selected] {
                background-color: #ff6b6b;
                color: white;
            }
            .select2-container--bootstrap-5 .select2-results__option[aria-selected=true] {
                background-color: #ff6b6b;
                color: white;
            }
            .select2-search__field {
                padding: 0.5rem !important;
            }
            .select2-container--bootstrap-5 .select2-selection--single {
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right 0.75rem center;
                background-size: 16px 12px;
            }
            .select2-container--bootstrap-5 .select2-selection--single .select2-selection__clear {
                right: 2rem;
            }
        `;
        document.head.appendChild(style);
    });
    </script>
</body>
</html>