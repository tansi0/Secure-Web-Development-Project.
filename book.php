<?php
// book.php - Vulnerable: no validation, no seat check
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
require 'db.php';

if ($_POST['seats'] && $_POST['movie_id']) {
    $seats = $_POST['seats'];
    $movie_id = $_POST['movie_id'];
    $user_id = $_SESSION['user_id'];

    // No Check on seat bookings
    $pdo->query("UPDATE movies SET seats_available = seats_available - $seats WHERE id = $movie_id");
    $pdo->query("INSERT INTO bookings (user_id, movie_id, seats_booked) VALUES ($user_id, $movie_id, $seats)");

    echo "<script>alert('Booking successful!'); window.location='user_dashboard.php';</script>";
}
?>