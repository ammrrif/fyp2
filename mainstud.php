<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['UserID'])) {
    header('Location: Login.html');
    exit();
}

if (isset($_SESSION['upload_success'])) {
    echo "<script>alert('" . $_SESSION['upload_success'] . "');</script>";
    unset($_SESSION['upload_success']);
}

// Prevent caching
header("Cache-Control: no-cache, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Get student name from session
$studentName = isset($_SESSION['StudentName']) ? $_SESSION['StudentName'] : 'Student';

// Database connection details
$host = 'localhost';
$dbname = 'fyp';
$user = 'root';
$password = '';

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch upcoming tutoring sessions for the logged-in student
    $student_id = $_SESSION['UserID'];
    $stmt = $conn->prepare("SELECT t.Name as tutor_name, ts.subject, ts.session_date, ts.session_time, b.status, ts.session_id
                            FROM bookings b 
                            JOIN tutor_sessions ts ON b.session_id = ts.session_id 
                            JOIN tutor t ON b.tutor_id = t.ID 
                            LEFT JOIN class c ON ts.session_id = c.session_id
                            WHERE b.student_id = :student_id AND ts.session_date >= CURDATE() AND (c.class_status IS NULL OR c.class_status != 'completed') AND b.status = 'approved'
                            ORDER BY ts.session_date, ts.session_time");
    $stmt->bindParam(':student_id', $student_id);
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
    <title>Main Page</title>
    <meta charset="UTF-8"> 
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@700&display=swap" rel="stylesheet">
    <style>
        /* General Page Styles */
        body {
            background-color: #faf5ef;
            font-family: "Raleway", sans-serif; 
            margin: 0;
            padding: 0;
        }

        /* Header Styles */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0px 40px;
            background-color: #fff;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo img {
            width: 150px;
            height: auto;
        }

        .links {
            display: flex;
            gap: 30px;
        }

        .links a {
            text-decoration: none;
            color: #000;
            font-size: 16px;
            font-weight: bold;
        }

        .links a:hover {
            color: #555;
        }

        /* Main Section Styles */
        .main {
            display: flex;
            flex-direction: column;
            align-items: flex-start; /* Align items to the left */
            justify-content: flex-start; /* Align items to the top */
            height: calc(100vh - 150px); /* Subtract header height */
            text-align: left; /* Align text to the left */
            padding: 20px; /* Add padding for better spacing */
        }

        .content h1 {
            font-size: 60px;
            color: #000;
            margin-bottom: 20px; /* Reduce margin for better spacing */
        }

        .content h2 {
            margin-top: 50px; /* Add margin to create a gap */
            font-size: 30px;
;
        }

        /* Search Bar Styles */
        .search-container {
            display: flex;
            justify-content: flex-start; /* Align items to the left */
            align-items: center;
            gap: 15px;
            background-color: #fff;
            padding: 15px;
            border-radius: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 1100px; /* Increase the width of the search bar */
        }

        .search-container input, select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 20px;
            font-size: 14px;
            outline: none;
            flex-grow: 1; /* Allow the input/select to grow */
        }

        #prog {
            width: 100%; /* Make the select elements take full width */
        }   

        .search-container button {
            background-color: #1E4DB7;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            border-radius: 20px;
            cursor: pointer;
        }

        .search-container button:hover {
            background-color: #163D8B;
        }

        /* Table Styles */
        .table-container {
            margin-top: 40px;
            width: 80%;
        }

        .table-container h2 {
            font-size: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

    </style>
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
            <a href="logout.php">Logout</a> <!-- Added logout link -->
        </nav>
    </header>

    <!-- Main Section -->
    <main class="main">
        <div class="content">
            <h1>Welcome <?php echo htmlspecialchars($studentName); ?>!</h1>
            <h2>Begin Your Learning Journey Now!</h2>
            <form action="studsearch.php" method="get">
                <div class="search-container">
                    <select id="prog" name="prog" required>
                        <option value="" disabled selected>Programme</option>
                        <!-- Foundation Level -->
                        <option value="Foundation in Engineering">Foundation in Engineering</option>
                        <option value="Foundation in Computer Science">Foundation in Computer Science</option>
                        <option value="Foundation in Information Technology">Foundation in Information Technology</option>
                        <option value="Foundation in Management">Foundation in Management</option>
                        <option value="Foundation in Accounting">Foundation in Accounting</option>
                        <option value="Foundation in Business Administration">Foundation in Business Administration</option>
                        <option value="Tahfiz UNITEN">Tahfiz UNITEN</option>
                        <!-- Diploma Level -->
                        <option value="Diploma in Mechanical Engineering">Diploma in Mechanical Engineering</option>
                        <option value="Diploma in Electrical Engineering">Diploma in Electrical Engineering</option>
                        <option value="Diploma in Computer Science">Diploma in Computer Science</option>
                        <option value="Diploma of Accountancy">Diploma of Accountancy</option>
                        <option value="Diploma in Business Studies">Diploma in Business Studies</option>
                        <option value="Diploma in Digital Business">Diploma in Digital Business</option>
                        <option value="Diploma in Financial Technology">Diploma in Financial Technology</option>
                        <!-- Bachelor's Level -->
                        <option value="Bachelor of Electrical and Electronics Engineering (Hons)">Bachelor of Electrical and Electronics Engineering (Hons)</option>
                        <option value="Bachelor of Computer Science (Systems and Networking) (Hons)">Bachelor of Computer Science (Systems and Networking) (Hons)</option>
                        <option value="Bachelor in Software Engineering (Honours)">Bachelor in Software Engineering (Honours)</option>
                        <option value="Bachelor of Computer Science (Cyber Security) (Honours)">Bachelor of Computer Science (Cyber Security) (Honours)</option>
                    </select>
                    <button type="submit">Search</button>
                </div>
            </form>
        </div>

        <!-- Upcoming Tutoring Sessions Section -->
        <div class="table-container">
            <h2>Upcoming Tutoring Sessions</h2>
            <?php if (!empty($upcoming_sessions)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Tutor Name</th>
                            <th>Subject</th>
                            <th>Session Date</th>
                            <th>Session Time</th>
                            <th>Status</th>
                            <th>Action</th> <!-- New column for action -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcoming_sessions as $session): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($session['tutor_name']); ?></td>
                                <td><?php echo htmlspecialchars($session['subject']); ?></td>
                                <td><?php echo htmlspecialchars($session['session_date']); ?></td>
                                <td><?php echo htmlspecialchars($session['session_time']); ?></td>
                                <td><?php echo htmlspecialchars($session['status']); ?></td>
                                <td>
                                    <?php if ($session['status'] == 'approved'): ?>
                                        <form action="studclass.php" method="get">
                                            <input type="hidden" name="session_id" value="<?php echo htmlspecialchars($session['session_id']); ?>">
                                            <button type="submit">View Class</button>
                                        </form>
                                    <?php endif; ?>
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
</body>
</html>