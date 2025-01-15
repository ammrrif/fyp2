<?php
session_start();
$tutor_id = isset($_GET['tutor_id']) ? $_GET['tutor_id'] : 0;

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch tutor name for display
$tutor_sql = "SELECT Name FROM tutor WHERE id = ?";
$tutor_stmt = $conn->prepare($tutor_sql);
$tutor_stmt->bind_param("i", $tutor_id);
$tutor_stmt->execute();
$tutor_result = $tutor_stmt->get_result();
$tutor = $tutor_result->fetch_assoc();
$tutor_name = $tutor ? $tutor['Name'] : 'N/A';

// Fetch reviews
$reviews_sql = "SELECT review, rating FROM reviews WHERE tutor_id = ?";
$reviews_stmt = $conn->prepare($reviews_sql);
$reviews_stmt->bind_param("i", $tutor_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ratings - Online Tutoring System</title>
    <link rel="stylesheet" href="tutordetails.css">
</head>
<body>
    <header class="header">
        <div class="logo">
            <img src="unitenlogo.png" alt="University Logo">
            <h2 style="margin: 0; color: #1E4DB7;">ONLINE TUTORING SYSTEM</h2>
        </div>
        <nav class="links">
            <a href="main.html">Home</a>
            <a href="tutreg.html">Becoming A Tutor</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <section class="ratings">
            <h1 class="ratings-title">Reviews and Ratings for <?php echo htmlspecialchars($tutor_name); ?></h1>
            <table>
                <thead>
                    <tr>
                        <th>Review</th>
                        <th>Rating</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reviews_result->num_rows > 0): ?>
                        <?php while ($row = $reviews_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['review']); ?></td>
                                <td><?php echo htmlspecialchars($row['rating']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2">No reviews found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>

    <?php $conn->close(); ?>
</body>
</html>