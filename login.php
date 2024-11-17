<?php
require_once 'userAuth.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = new Login();
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Please enter both username and password";
    } else {
        if ($login->loginUser($username, $password)) {
            $_SESSION['logged_in'] = true;
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid username or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <title>Login</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <img src="images/logo.png" alt="logo" class="img-fluid mb-3" style="max-width: 150px;">
                            <h2 class="dashboard-blue fw-bold">USER LOGIN</h2>
                            <p class="text-muted">Enter your credentials to login</p>
                        </div>
                        
                        <?php if(isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php 
                                    echo $_SESSION['success']; 
                                    unset($_SESSION['success']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['error'])): ?>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Login Failed',
                                        text: <?php echo json_encode($_SESSION['error']); ?>,
                                        confirmButtonColor: '#3085d6',
                                        footer: <?php echo json_encode(($_SESSION['error'] == "Account not found. Please register first.") ? 
                                            '<a href="register.php">Click here to register</a>' : ''); ?>
                                    }).then((result) => {
                                        if (result.isConfirmed && <?php echo json_encode($_SESSION['error'] == "Account not found. Please register first."); ?>) {
                                            window.location.href = 'register.php';
                                        }
                                    });
                                });
                            </script>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label dashboard-blue">Username</label>
                                <input type="text" class="form-control custom-input" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label dashboard-blue">Password</label>
                                <input type="password" class="form-control custom-input" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn custom-btn-primary w-100 mb-3">Login</button>
                            <div class="text-center">
                                <p>Don't have an account? <a href="register.php" class="dashboard-blue text-decoration-none">Register here</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
