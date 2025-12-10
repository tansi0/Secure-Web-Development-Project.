<?php
// book.php - vulnerable
// First Fix - Validation and Sanitization - Added Validaiton and Seat Checks
// Second Fix -  Using Prepared Statements - Prevents SQL Injection
// Next Fix - Race Condition Fix
// Next Vulnerability Fix - Prevent CSRF attacks


session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
require 'db.php';
require_once 'includes/csrf.php';  // Load CSRF protection

//  CSRF Protection: Reject request if token is  invalid 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die('CSRF validation failed.');
    }
}
if (isset($_POST['seats']) && isset($_POST['movie_id'])) {
    $movie_id = filter_var($_POST['movie_id'], FILTER_VALIDATE_INT); // formatted the code better.
    $seats    = filter_var($_POST['seats'], FILTER_VALIDATE_INT); 
    $user_id  = $_SESSION['user_id'];

    // Validate movie_id and Seats: must be positive integer
    if ($movie_id <= 0 || $seats <= 0) {
        echo "<script>alert('Invalid movie or number of seats.'); window.location='user_dashboard.php';</script>";
        exit();
    }

    // Start Transcation – This is fix for race condition 
    $pdo->beginTransaction();

    try {
        // Prevents another user from modifying movie booking section when a previous user is trying to modify and commit changes
        
        // Check available seats
        $stmt = $pdo->prepare("SELECT seats_available FROM movies WHERE id = ? FOR UPDATE");
        $stmt->execute([$movie_id]);
        $movie = $stmt->fetch();

        if (!$movie) {
            throw new Exception("Movie not found.");
        }

        if ($seats > $movie['seats_available']) {
            throw new Exception("Not enough seats available. Only {$movie['seats_available']} left.");
        }

        // Using Prepared Statements to Book Movies
        $stmt = $pdo->prepare("UPDATE movies SET seats_available = seats_available - ? WHERE id = ?");
        $stmt->execute([$seats, $movie_id]);

        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, movie_id, seats_booked) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $movie_id, $seats]);
        
        // Commit the transaction
        $pdo->commit();

        echo "<script>alert('Booking successful!'); window.location='user_dashboard.php';</script>";
    } catch (Exception $e) {
        // If there is a failure → rollback everything
        $pdo->rollBack();
        echo "<script>alert('Booking failed: " . $e->getMessage() . "'); window.location='user_dashboard.php';</script>";
    }
}
?>