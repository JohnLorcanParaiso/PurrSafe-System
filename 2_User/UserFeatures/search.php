<?php
require_once '../../2_User/UserBackend/userAuth.php';

$login = new Login();
if (!$login->isLoggedIn()) {
    header('Location: ../../2_User/UserBackend/login.php');
    exit();
}

$search_query = isset($_GET['q']) ? trim($_GET['q']) : (isset($_POST['search']) ? trim($_POST['search']) : '');

// Search functionality
if (!empty($search_query)) {
    $sql = "SELECT r.*, u.fullname as reporter_name, GROUP_CONCAT(ri.image_path) as images,
            CASE WHEN r.edited_at IS NOT NULL THEN 1 ELSE 0 END as is_edited
            FROM lost_reports r 
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN report_images ri ON r.id = ri.report_id
            WHERE (r.cat_name LIKE ? OR r.breed LIKE ? OR r.color LIKE ? 
                  OR r.last_seen_location LIKE ? OR r.description LIKE ?)
            GROUP BY r.id
            ORDER BY r.created_at DESC";
    
    $search_term = "%$search_query%";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$search_term, $search_term, $search_term, $search_term, $search_term]);
    $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'results' => $search_results,
        'query' => $search_query
    ]);
    exit();
}

function formatImagePath($image) {
    if (empty($image)) {
        return '../../3_Images/cat-user.png';
    }
    return strpos($image, '../../5_Uploads/') === 0 ? $image : '../../5_Uploads/' . basename($image);
}
?> 