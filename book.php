<?php
// book.php - vulnerable
// First Fix - Validation and Sanitization - Added Validaiton and Seat Checks
// Second Fix -  Using Prepared Statements - Prevents SQL Injection

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
require 'db.php';

if (isset($_POST['seats']) && isset($_POST['movie_id'])) {
    // Validate movie_id: must be positive integer
    $movie_id = filter_var($_POST['movie_id'], FILTER_VALIDATE_INT);
    if ($movie_id === false || $movie_id <= 0) {
        echo "<script>alert('Invalid movie selection.'); window.location='user_dashboard.php';</script>";
        exit();
    }

    // Validate seats: positive integer
    $seats = filter_var($_POST['seats'], FILTER_VALIDATE_INT);
    if ($seats === false || $seats <= 0) {
        echo "<script>alert('Invalid number of seats: Must be a positive number.'); window.location='user_dashboard.php';</script>";
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // Check available seats
    $stmt = $pdo->prepare("SELECT seats_available FROM movies WHERE id = ?");
    $stmt->execute([$movie_id]);
    $movie = $stmt->fetch();
    if (!$movie || $seats > $movie['seats_available']) {
        echo "<script>alert('Not enough seats available or invalid movie.'); window.location='user_dashboard.php';</script>";
        exit();
    }

    // Using Prepared Statements
    $stmt = $pdo->prepare("UPDATE movies SET seats_available = seats_available - ? WHERE id = ?");
    $stmt->execute([$seats, $movie_id]);

    $stmt = $pdo->prepare("INSERT INTO bookings (user_id, movie_id, seats_booked) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $movie_id, $seats]);
    
    echo "<script>alert('Booking successful!'); window.location='user_dashboard.php';</script>";
}
?>