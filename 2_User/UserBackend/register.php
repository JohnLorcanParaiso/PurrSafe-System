<?php
require_once 'userAuth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $register = new Register();
    
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($fullname) || empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters long";
    } else {
        if ($register->registerUser($fullname, $username, $email, $password)) {
            $_SESSION['success'] = "Registration successful! Please login.";
            header("Location: login.php");
            exit();
        } else {
            $_SESSION['error'] = "Registration failed. Username or email may already exist.";
            header("Location: register.php");
            exit();
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
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../4_Styles/user_style.css">
    <title>Register</title>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm" style="aspect-ratio: 1/1.2;">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <img src="../../3_Images/logo.png" alt="logo" class="img-fluid mb-3" style="max-width: 150px;">
                            <h2 class="dashboard-blue">Create Account</h2>
                            <p class="text-muted">Fill in your details to register</p>
                        </div>

                        <?php if(isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php 
                                    echo $_SESSION['error']; 
                                    unset($_SESSION['error']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php 
                                    echo $_SESSION['success']; 
                                    unset($_SESSION['success']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="register.php" method="POST">
                            <div class="mb-3">
                                <label for="fullname" class="form-label dashboard-blue">Full Name</label>
                                <input type="text" class="form-control custom-input" id="fullname" name="fullname" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label dashboard-blue">Username</label>
                                <input type="text" class="form-control custom-input" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label dashboard-blue">Email</label>
                                <input type="email" class="form-control custom-input" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label dashboard-blue">Password</label>
                                <input type="password" class="form-control custom-input" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label dashboard-blue">Confirm Password</label>
                                <input type="password" class="form-control custom-input" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn custom-btn-primary w-100 mb-3">Register</button>
                            <div class="text-center">
                                <p>Already have an account? <a href="login.php" class="dashboard-blue text-decoration-none">Login here</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php if(isset($_SESSION['success'])): ?>
    <script>
        Swal.fire({
            title: "Registration Successful!",
            text: "<?php echo htmlspecialchars($_SESSION['success']); ?>",
            icon: "success",
            confirmButtonColor: '#2196F3',
            allowOutsideClick: false, 
            allowEscapeKey: false      
        }).then((result) => {
            window.location.href = 'login.php';  
        });
    </script>
    <?php 
        unset($_SESSION['success']);
    endif; 
    ?>
</body>
</html> 