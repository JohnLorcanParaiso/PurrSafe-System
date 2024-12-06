<?php
session_start();
require_once '../../2_User/UserBackend/userAuth.php';
require_once '../../2_User/UserBackend/db.php';

$login = new Login();
if (!$login->isLoggedIn()) {
    header('Location: ../../2_User/UserBackend/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate required fields
        $required_fields = [
            'cat_name' => 'Cat Name',
            'breed' => 'Breed',
            'gender' => 'Gender',
            'age' => 'Age',
            'color' => 'Color',
            'description' => 'Description',
            'last_seen_date' => 'Last Seen Date',
            'owner_name' => "Owner's Name",
            'phone_number' => 'Phone Number'
        ];

        foreach ($required_fields as $field => $label) {
            if (empty($_POST[$field])) {
                throw new Exception($label . " is required.");
            }
        }

        // Validate image upload
        if (empty($_FILES['cat_images']['name'][0])) {
            throw new Exception("Please upload at least one image.");
        }

        if (count(array_filter($_FILES['cat_images']['name'])) > 5) {
            throw new Exception("Please upload no more than 5 images.");
        }

        // Begin database transaction
        $pdo->beginTransaction();

        // Insert report data
        $sql = "INSERT INTO lost_reports (
            user_id, cat_name, breed, gender, age, color, 
            description, last_seen_date, last_seen_time, last_seen_location, 
            owner_name, phone_number, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_SESSION['user_id'],
            trim($_POST['cat_name']),
            trim($_POST['breed']),
            $_POST['gender'],
            trim($_POST['age']),
            trim($_POST['color']),
            trim($_POST['description']),
            $_POST['last_seen_date'],
            $_POST['last_seen_time'] ?? null,
            trim($_POST['last_seen_location'] ?? ''),
            trim($_POST['owner_name']),
            trim($_POST['phone_number'])
        ]);
        
        $reportId = $pdo->lastInsertId();

        // Handle image uploads
        $uploadDir = '../../5_Uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $uploadedFiles = 0;

        foreach ($_FILES['cat_images']['tmp_name'] as $key => $tmpName) {
            // Validate file type
            if (!in_array($_FILES['cat_images']['type'][$key], $allowedTypes)) {
                continue;
            }

            if ($_FILES['cat_images']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = uniqid() . '_' . basename($_FILES['cat_images']['name'][$key]);
                $uploadFile = $uploadDir . $fileName;

                if (move_uploaded_file($tmpName, $uploadFile)) {
                    // Insert image record
                    $sql = "INSERT INTO report_images (report_id, image_path) VALUES (?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$reportId, $uploadFile]);
                    $uploadedFiles++;
                }
            }
        }

        if ($uploadedFiles === 0) {
            throw new Exception("Failed to upload any images. Please try again.");
        }

        // Commit transaction
        $pdo->commit();

        $_SESSION['report_success'] = "Report has been successfully created!";
        header("Location: 2.1_create_new_report.php");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        $_SESSION['report_error'] = "Error creating report. Please try again.";
        header("Location: 2.1_create_new_report.php");
        exit();
    }
} else {
    header('Location: 2.1_create_new_report.php');
    exit();
} 