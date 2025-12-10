<?php
// CSRF Protection Helper
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generates a unique token once per session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); 
}

// Output hidden CSRF token field in forms
function csrf_field() {
    echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

// Verify CSRF token 
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>