<?php
// admin dashboard  - FULL CRUD FOR ADMIN (Vulnerable)
// First Fix - Validation and Sanitization
// Second Fix -  Using Prepared Statements to prevent SQL Injection
// Next Fix - CSRF Protection

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require 'db.php';
require_once 'includes/csrf.php';  // Load CSRF protection

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

//  CSRF Protection: Reject request if token is  invalid 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die('CSRF validation failed.');
    }
}

// HANDLE CRUD ACTIONS (DELETE MOVIE, DELETE BOOKING, ADD MOVIE, EDIT MOVIE) 
// Secured Delete Movie
if (isset($_POST['delete_movie'])) {
    $id = filter_var($_POST['delete_movie'], FILTER_VALIDATE_INT);
    if ($id !== false && $id > 0) {
        $stmt = $pdo->prepare("DELETE FROM movies WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['flash'] = 'Movie deleted!';
        header("Location: admin_dashboard.php");
        exit();
    }
}

// Secured Delete Booking
if (isset($_POST['delete_booking'])) {
    $id = filter_var($_POST['delete_booking'], FILTER_VALIDATE_INT);
    if ($id !== false && $id > 0) {
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['flash'] = 'Bokking deleted!';
        header("Location: admin_dashboard.php");
        exit();
    }
}

// Secured Add Movie
if (isset($_POST['add_movie'])) {
    $title = trim($_POST['title']);
    $time  = $_POST['show_time'];
    $seats = filter_var($_POST['seats'], FILTER_VALIDATE_INT);

    if (empty($title) || $seats === false || $seats <= 0) {
        echo "<script>alert('Invalid movie data.');</script>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO movies (title, show_time, seats_available) VALUES (?, ?, ?)");
        $stmt->execute([$title, $time, $seats]);
        $_SESSION['flash'] = 'Movie Added!';
        header("Location: admin_dashboard.php");
        exit();
    }
}

// Secured Edit Movie
if (isset($_POST['edit_movie'])) {
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    $title = trim($_POST['title']);
    $time = $_POST['show_time'];
    $seats = filter_var($_POST['seats'], FILTER_VALIDATE_INT);

    if ($id && !empty($title) && $seats !== false && $seats >= 0) {
        $stmt = $pdo->prepare("UPDATE movies SET title = ?, show_time = ?, seats_available = ? WHERE id = ?");
        $stmt->execute([$title, $time, $seats, $id]);
        $_SESSION['flash'] = 'Movie updated!';
        header("Location: admin_dashboard.php");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - TFX Cinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="text-white">

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-white">Admin Dashboard</h1>
        <a href="logout.php" class="btn btn-outline-light"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <?php if ($flash): ?>
    <div class="alert alert-info alert-dismissible fade show">
        <?= htmlspecialchars($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- ADD NEW MOVIE -->
    <div class="card mb-4">
        <div class="card-header text-white">
            <h4></i> Add New Movie</h4>
        </div>
        <div class="card-body">
            <!-- Attributes added to the input forms to aid in validation -->
            <form method="POST" class="row g-3">
                <!-- CSRF Token  -->
                <?php csrf_field(); ?>
                <div class="col-md-4">
                    <input type="text" name="title" class="form-control" placeholder="Movie Title" required minlength="1" maxlength="100"> 
                </div>
                <div class="col-md-4">
                    <input type="datetime-local" name="show_time" class="form-control" required>  <!-- Changed to datetime-local -->
                </div>
                <div class="col-md-2">
                    <input type="number" name="seats" class="form-control" placeholder="Seats" required min="1">
                </div>
                <div class="col-md-2">
                    <button name="add_movie" class="btn btn-success w-100"><i class="fas fa-film"></i> Add Movie</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MOVIES LIST WITH EDIT & DELETE -->
    <h3 class="text-white mb-3">Manage Movies</h3>
    <div class="table-responsive mb-5">
        <table class="table table-dark table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Show Time</th>
                    <th>Available Seats</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $movies = $pdo->query("SELECT * FROM movies ORDER BY show_time")->fetchAll();
                foreach ($movies as $m) { ?>
                    <tr>
                        <td><?= $m['id'] ?></td>
                        <td><?= htmlspecialchars($m['title']) ?></td>
                        <td><?= $m['show_time'] ?></td>
                        <td><?= $m['seats_available'] ?></td>
                        <td>
                            <!-- Edit Button (Modal) -->
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#edit<?= $m['id'] ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <!-- Delete Button -->
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this movie?');">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="delete_movie" value="<?= $m['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="edit<?= $m['id'] ?>">
                        <div class="modal-dialog">
                            <form method="POST">
                                <!-- CSRF Token  -->
                                <?php csrf_field(); ?>
                                <div class="modal-content bg-dark text-white">
                                    <div class="modal-header">
                                        <h5>Edit Movie</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                        <div class="mb-3">
                                            <label>Title</label>
                                            <input type="text" name="title" value="<?= $m['title'] ?>" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Show Time</label>
                                            <input type="datetime-local" name="show_time" value="<?= date('Y-m-d\TH:i', strtotime($m['show_time'])) ?>" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Seats</label>
                                            <input type="number" name="seats" value="<?= $m['seats_available'] ?>" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" name="edit_movie" class="btn btn-warning">Save Changes</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- ALL BOOKINGS WITH DELETE -->
    <h3 class="text-white mb-3">All Bookings</h3>
    <div class="table-responsive">
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>User</th>
                    <th>Movie</th>
                    <th>Seats</th>
                    <th>Booked On</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT b.id, u.username, m.title, b.seats_booked, b.booking_time 
                        FROM bookings b 
                        JOIN users u ON b.user_id = u.id 
                        JOIN movies m ON b.movie_id = m.id 
                        ORDER BY b.booking_time DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                foreach ($stmt as $row) { ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= $row['seats_booked'] ?></td>
                        <td><?= $row['booking_time'] ?></td>
                        <td>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Cancel this booking?');">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="delete_booking" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmDelete(type) {
    return confirm(`Are you sure you want to delete this ${type}?`);
}
</script>
</body>
</html>