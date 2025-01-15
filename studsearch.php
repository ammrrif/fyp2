<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['UserID'])) {
    header('Location: Login.html');
    exit();
}

// Prevent caching
header("Cache-Control: no-cache, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Database connection (update with your credentials)
$servername = "localhost";
$username = "root"; // your database username
$password = ""; // your database password
$dbname = "fyp"; // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the selected programme from the query parameters
$selectedProgramme = isset($_GET['prog']) ? $_GET['prog'] : '';

// Query to fetch active tutors (i.e., those with sessions in the future) matching the selected programme
$sql = "SELECT tutor_name, subject 
        FROM tutor_sessions 
        WHERE session_date >= CURDATE() AND programme = ?
        GROUP BY tutor_name, subject";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selectedProgramme);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Tutoring System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="studsearch.css">
</head>
<body>
    <!-- Header Section -->
    <header class="header">
        <div class="logo">
            <img src="unitenlogo.png" alt="University Logo">
            <h2 style="margin: 0; color: #1E4DB7;">ONLINE TUTORING SYSTEM</h2>
        </div>
        <nav class="links">
            <a href="mainstud.php">Home</a>
            <a href="tutreg.html">Becoming a Tutor</a>
            <a href="logout.php">Logout</a> <!-- Added logout link -->
        </nav>
    </header>

    <!-- Available Tutors Section -->
    <main class="tutors-section">
        <h1 class="section-title">Available Tutors</h1>
        <div class="tutor-cards">
            <?php
            // Check if there are tutors with active sessions
            if ($result->num_rows > 0) {
                // Loop through and display tutor cards
                while ($row = $result->fetch_assoc()) {
                    // Generate the URL to the tutor details page, passing tutor details as query parameters
                    $tutorUrl = "tutor_details.php?tutor_name=" . urlencode($row['tutor_name']) . "&subject=" . urlencode($row['subject']);
                    
                    echo "<a href='" . $tutorUrl . "' class='tutor-card'>"; // Wrap the card with an anchor tag
                    echo "<p>" . $row['tutor_name'] . "</p>"; // Display tutor name
                    echo "<p>" . $row['subject'] . "</p>"; // Display subject
                    echo "</a>"; // Close the anchor tag
                }
            } else {
                echo "<p>No tutors are currently available for the selected programme.</p>";
            }

            // Close the database connection
            $conn->close();
            ?>
        </div>
    </main>
</body>
</html>
