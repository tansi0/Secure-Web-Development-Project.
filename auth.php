<?php
// auth.php -  Vulnerable
// First Vulnerability Fix - Input Validation & Sanitization
// Second Vulnerability Fix - Password Hashing 

session_start();
require 'db.php';

if (isset($_POST['register'])) {
    // Trim and validate inputs - Input validation for Regristration form
    $username = trim($_POST['username']);
    $password = trim($_POST['password']); // vulnerable: stored in plaintext
    $role = $_POST['role']; // vulnerable: can be changed to "admin" from browser

    // Server Validation for username input
    if (!preg_match('/^[a-zA-Z0-9]{3,20}$/', $username)) {
        echo "<script>alert('Username most be 3-20 alphanumeric characters.'); window.location='register.html';</script>";
        exit();
    }

    // Server side Validation for password input
    if (strlen($password) < 6) {
        echo "<script>alert('Password must be at least 6 characters.'); window.location='register.html';</script>";
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Direct query concatenation → SQL INJECTION
    $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', '$role')"; //Use the hashed password in the query
    
    try {
        $pdo->exec($sql);
        echo "<script>alert('Registration successful!'); window.location='login.html';</script>";
    } catch(Exception $e) {
        echo "<script>alert('Username already exists!');</script>";
    }
}

if (isset($_POST['login'])) {
    // Trim and validate inputs - Input validation for Login form
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Server Validation for username input
    if (!preg_match('/^[a-zA-Z0-9]{3,20}$/', $username)) {
        echo "<script>alert('Invalid username: Must be 3-20 alphanumeric characters.'); window.location='login.html';</script>";
        exit();
    }

    // Server side Validation for password input
    if (strlen($password) < 6) {
        echo "<script>alert('Invalid password: Must be at least 6 characters.'); window.location='login.html';</script>";
        exit();
    }

    // VULNERABLE TO SQL INJECTION: ' OR '1'='1
    $sql = "SELECT * FROM users WHERE username = '$username' LIMIT 1"; // Fetch user by username
    $stmt = $pdo->query($sql);
    $user = $stmt->fetch();

    // Check if user exists & password is correct
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: user_dashboard.php");
        }
        exit();
    } else {
        echo "<script>alert('Invalid credentials!'); window.location='login.html';</script>";
    }
}
?>