<!-- Login to Access User/Admin Interface -->
<!-- First Fix - Validation and Sanitization -->
<!-- Next Fix - CSRF Protection -->

<?php
session_start();  
require_once 'includes/csrf.php';  
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - TFX Cinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card p-4">
                    <h2 class="text-center mb-4">Welcome Back</h2>
                    <form action="auth.php" method="POST">
                        <!-- CSRF Token  -->
                        <?php csrf_field(); ?>  
                        <div class="mb-3">
                            <label>Username</label>
                            <!-- Added attributes to aid user input validaiton -->
                            <input type="text" name="username" class="form-control" required minlength="3" maxlength="20" pattern="[a-zA-Z0-9]+" title="Username must be 3-20 alphanumeric characters">
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <!-- Added attributes to aid user input validaiton -->
                            <input type="password" name="password" class="form-control" required minlength="6" title="Password must be at least 6 characters">
                        </div>
                        <button type="submit" name="login" class="btn btn-danger w-100">Login</button>
                    </form>
                    <!-- Updated Registration link -->
                    <p class="text-center mt-3">New User? <a href="register.php">Register</a></p>
                    <a href="index.html" class="btn btn-secondary w-100 mt-2">Back</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>