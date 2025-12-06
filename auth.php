<?php
// auth.php -  Vulnerable
session_start();
require 'db.php';

if (isset($_POST['register'])) {
    // No Input Sanitization - xss, SQL Injection possible
    $username = $_POST['username'];
    $password = $_POST['password']; // stored in plaintext!
    $role = $_POST['role']; // can be changed to "admin" from browser!

    // Direct query concatenation → SQL INJECTION
    $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
    
    try {
        $pdo->exec($sql);
        echo "<script>alert('Registration successful!'); window.location='login.html';</script>";
    } catch(Exception $e) {
        echo "<script>alert('Username already exists!');</script>";
    }
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // VULNERABLE TO SQL INJECTION: ' OR '1'='1
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $stmt = $pdo->query($sql);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'admin') {
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