<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_role'] = 'admin';
        
        if (isset($_POST['rememberMe'])) {
            setcookie('admin_login', $username, time() + (86400 * 30), "/");
        }
        
        header("Location: ../../1_Admin/AdminFeatures/1_admin_dashboard.php");
        exit();
    } else {
        // Set error message for wrong credentials
        $_SESSION['error'] = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../4_Styles/admin_style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <title>Admin Login</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <img src="../../3_Images/logo.png" alt="logo" class="img-fluid mb-3" style="max-width: 150px;">
                            <h2 class="dashboard-blue fw-bold">ADMIN LOGIN</h2>
                            <p class="text-muted">Enter your admin credentials</p>
                        </div>

                        <?php if(isset($_SESSION['error'])): ?>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Login Failed',
                                        text: <?php echo json_encode($_SESSION['error']); ?>,
                                        confirmButtonColor: '#3085d6'
                                    });
                                });
                            </script>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label dashboard-blue">Admin Username</label>
                                <input type="text" 
                                       class="form-control custom-input" 
                                       id="username" 
                                       name="username" 
                                       required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label dashboard-blue">Admin Password</label>
                                <input type="password" 
                                       class="form-control custom-input" 
                                       id="password" 
                                       name="password" 
                                       required>
                            </div>
                            <div class="mb-3 form-check d-flex align-items-center">
                                <input type="checkbox" class="form-check-input square-checkbox" id="rememberMe" name="rememberMe">
                                <label class="form-check-label ms-2" for="rememberMe">Remember me</label>
                            </div>
                            <button type="submit" class="btn custom-btn-primary w-100 mb-3">Admin Login</button>
                            <div class="text-center">
                                <p>Back to <a href="../../2_User/UserBackend/login.php" class="dashboard-blue text-decoration-none">User Login</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>