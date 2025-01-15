<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['UserID'])) {
    header('Location: Login.html');
    exit();
}

// Database connection details
$host = 'localhost';
$dbname = 'fyp';
$user = 'root';
$password = '';

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch inserted details from class table
    $stmt = $conn->prepare("SELECT * FROM class WHERE session_id = :session_id");
    $stmt->bindParam(':session_id', $_GET['session_id']);
    $stmt->execute();
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Details</title>
    <meta charset="UTF-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="studclass.css">
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
            <a href="tutreg.php">Becoming a Tutor</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <!-- Main Content Area -->
    <div class="main-content">
        <div class="container">
            <h1>Class Details</h1>
        </div>

        <!-- Container for Uploaded Resources -->
        <div class="resources-container">
            <?php foreach ($resources as $resource): ?>
                <table class="resource-item">
                    <tr>
                        <th>Meeting Link:</th>
                        <td><a href="<?php echo htmlspecialchars($resource['meeting_link']); ?>" target="_blank"><?php echo htmlspecialchars($resource['meeting_link']); ?></a></td>
                    </tr>
                    <tr>
                        <th>Recording Link:</th>
                        <td><a href="<?php echo htmlspecialchars($resource['recording_link']); ?>" target="_blank"><?php echo htmlspecialchars($resource['recording_link']); ?></a></td>
                    </tr>
                    <tr>
                        <th>Resource Path:</th>
                        <td><a href="<?php echo htmlspecialchars($resource['resource_path']); ?>" target="_blank"><?php echo htmlspecialchars($resource['resource_path']); ?></a></td>
                    </tr>
                    <tr>
                        <th>Class Status:</th>
                        <td><?php echo htmlspecialchars($resource['class_status']); ?></td>
                    </tr>
                </table>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>