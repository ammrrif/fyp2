<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['UserID'])) {
    header('Location: Login.html');
    exit();
}

// Get tutor name from session or database
$tutorName = isset($_SESSION['TutorName']) ? $_SESSION['TutorName'] : 'Tutor';

// If the tutor's name is not in the session, fetch it from the database
if (!isset($_SESSION['TutorName'])) {
    // Database connection details
    $host = 'localhost';
    $dbname = 'fyp';
    $user = 'root';
    $password = '';

    try {
        // Connect to the database
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch the tutor's name
        $tutor_id = $_SESSION['UserID'];
        $stmt = $conn->prepare("SELECT Name FROM tutor WHERE ID = :tutor_id");
        $stmt->bindParam(':tutor_id', $tutor_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $tutorName = $result['Name'];
            $_SESSION['TutorName'] = $tutorName; // Store the tutor's name in the session
        }
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Prevent caching
header("Cache-Control: no-cache, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Database connection details
$host = 'localhost';
$dbname = 'fyp';
$user = 'root';
$password = '';

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch upcoming tutoring sessions for the logged-in tutor
    $tutor_id = $_SESSION['UserID'];
    $stmt = $conn->prepare("SELECT s.name as student_name, ts.subject, ts.session_date, ts.session_time, ts.session_id
                            FROM bookings b 
                            JOIN tutor_sessions ts ON b.session_id = ts.session_id 
                            JOIN student s ON b.student_id = s.id 
                            LEFT JOIN class c ON ts.session_id = c.session_id
                            WHERE b.tutor_id = :tutor_id AND ts.session_date >= CURDATE() AND (c.class_status IS NULL OR c.class_status != 'completed') AND b.status = 'approved'
                            ORDER BY ts.session_date, ts.session_time");
    $stmt->bindParam(':tutor_id', $tutor_id);
    $stmt->execute();
    $upcoming_sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutor Main Page</title>
    <meta charset="UTF-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="tutmain.css">
</head>
<body>
    <!-- Header Section -->
    <header class="header">
        <div class="logo">
            <img src="unitenlogo.png" alt="University Logo">
            <h2 style="margin: 0; color: #1E4DB7;">ONLINE TUTORING SYSTEM</h2>
        </div>
        <nav class="links">
            <a href="tutmain.php">Home</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <!-- Main Content with Sidebar -->
    <div class="container">
        <!-- Left Sidebar -->
        <aside class="sidebar">
            <h3>Tutor Dashboard</h3>
            <ul>
                <li><a href="profile.php">Display Profile</a></li>
                <li><a href="personal.php">Personal Details</a></li>
                <li><a href="session.php">Manage Sessions</a></li>
                <li><a href="student-reviews.html">Student Reviews</a></li>
                <li><a href="bookingrequest.php">Booking Request</a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="content">
            <h1>Welcome, <?php echo htmlspecialchars($tutorName); ?></h1>
            <p>Here, you can manage your profile, sessions, and more.</p>

            <!-- Upcoming Tutoring Sessions Section -->
            <div class="table-container">
                <h2>Upcoming Tutoring Sessions</h2>
                <?php if (!empty($upcoming_sessions)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Subject</th>
                                <th>Session Date</th>
                                <th>Session Time</th>
                                <th>Class</th> 
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcoming_sessions as $session): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($session['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($session['subject']); ?></td>
                                    <td><?php echo htmlspecialchars($session['session_date']); ?></td>
                                    <td><?php echo htmlspecialchars($session['session_time']); ?></td>
                                    <td>
                                        <form action="tutclass.php" method="get">
                                            <input type="hidden" name="session_id" value="<?php echo htmlspecialchars($session['session_id']); ?>">
                                            <button type="submit">View Class</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No upcoming tutoring sessions found.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>