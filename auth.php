<?php
// auth.php -  Vulnerable
// First Vulnerability Fix - Input Validation & Sanitization
// Second Vulnerability Fix - Password Hashing 
// Third Vulnerability Fix - Prevent Privilege Escalation. Prevent Role Manipulation by setting user roles to 'user' at backend.
// Fourth Vulnerability Fix - Using Prepared Statements
// Fifth Vulnerability Fix - CSRF Protection

session_start();
require 'db.php';

require_once 'includes/csrf.php';  // Load CSRF protection

//  CSRF Protection: Reject request if token is  invalid 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die('CSRF validation failed');
    }
}

if (isset($_POST['register'])) {
    // Trim and validate inputs - Input validation for Regristration form
    $username = trim($_POST['username']);
    $password = trim($_POST['password']); // vulnerable: stored in plaintext
    
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
    
    // Registered users assigned 'user' role only
    $role = 'user'; // Admin role can only be assigned in DB 

    // Prepared Statement - Safe from SQL Injection
    $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([$username, $hashed_password, $role]);
        echo "<script>alert('Registration successful!'); window.location='login.html';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Username already exists!'); window.location='register.html';</script>";
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

    // Prepraed Statement - Safe Login
    $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
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