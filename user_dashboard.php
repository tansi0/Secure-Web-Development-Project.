<?php
// User Dashboard 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.html");
    exit();
}
require 'db.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard - TFX Cinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <div>
                <a href="logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>

        <h3 class="text-white">Available Movies</h3>
        <div class="row">
            <?php
            $movies = $pdo->query("SELECT * FROM movies")->fetchAll();
            foreach ($movies as $movie) {
            ?>
                <div class="col-md-4 mb-4">
                    <div class="card text-white">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $movie['title']; ?></h5>
                            <p><strong>Show Time:</strong> <?php echo $movie['show_time']; ?></p>
                            <p><strong>Seats Left:</strong> <?php echo $movie['seats_available']; ?></p>
                            <form action="book.php" method="POST">
                                <input type="hidden" name="movie_id" value="<?php echo $movie['id']; ?>">
                                <div class="mb-3">
                                    <label>Seats</label>
                                    <input type="number" name="seats" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-danger w-100">Book</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</body>
</html>

