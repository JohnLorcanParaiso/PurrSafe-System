<?php
session_start();
require_once '../../2_User/UserBackend/userAuth.php';
require_once '../../2_User/UserBackend/db.php';

$login = new Login();
if (!$login->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
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
                $_SESSION['report_error'] = $label . " is required.";
                header('Location: 2.1_create_new_report.php');
                exit();
            }
        }

        if (empty($_FILES['cat_images']['name'][0])) {
            $_SESSION['report_error'] = "Please upload at least one image.";
            header('Location: 2.1_create_new_report.php');
            exit();
        }

        if (count(array_filter($_FILES['cat_images']['name'])) > 5) {
            $_SESSION['report_error'] = "Please upload no more than 5 images.";
            header('Location: 2.1_create_new_report.php');
            exit();
        }

        $catName = $_POST['cat_name'];
        $breed = $_POST['breed'];
        $gender = $_POST['gender'];
        $age = $_POST['age'];
        $color = $_POST['color'];
        $description = $_POST['description'];
        $lastSeenDate = $_POST['last_seen_date'];
        $lastSeenTime = $_POST['last_seen_time'] ?? null;
        $lastSeenLocation = $_POST['last_seen_location'] ?? null;
        $ownerName = $_POST['owner_name'];
        $phoneNumber = $_POST['phone_number'];
        $userId = $_SESSION['user_id'];

        $sql = "INSERT INTO lost_reports (
            user_id, cat_name, breed, gender, age, color, 
            description, last_seen_date, last_seen_time, last_seen_location, 
            owner_name, phone_number, created_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, 
            ?, ?, NOW()
        )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $userId, $catName, $breed, $gender, $age, $color,
            $description, $lastSeenDate, $lastSeenTime, $lastSeenLocation,
            $ownerName, $phoneNumber
        ]);
        
        $reportId = $pdo->lastInsertId();

        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadedFiles = 0;
        foreach ($_FILES['cat_images']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['cat_images']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = uniqid() . '_' . $_FILES['cat_images']['name'][$key];
                $uploadFile = $uploadDir . $fileName;

                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($_FILES['cat_images']['type'][$key], $allowedTypes)) {
                    continue;
                }

                if (move_uploaded_file($tmpName, $uploadFile)) {
                    $sql = "INSERT INTO report_images (report_id, image_path) VALUES (?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$reportId, $uploadFile]);
                    $uploadedFiles++;
                }
            }
        }

        if ($uploadedFiles === 0) {
            throw new Exception("Failed to upload any images.");
        }

        $_SESSION['report_success'] = 'Your report has been successfully submitted.';
        header('Location: 2.1_create_new_report.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['report_error'] = "Error: " . $e->getMessage();
        header('Location: 2.1_create_new_report.php');
        exit();
    }
} else {
    header('Location: 2.1_create_new_report.php');
    exit();
} 