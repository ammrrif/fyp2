<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    header('Location: Login.html');
    exit();
}

$host = 'localhost';
$dbname = 'fyp';
$user = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_id = $_POST['payment_id'];
    $status = $_POST['status'];

    $query = "UPDATE bookings SET status = :status WHERE payment_id = :payment_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':payment_id', $payment_id);

    $stmt->execute();
}

$sql = "SELECT b.session_id, s.name as student_name, ts.subject, ts.session_time, b.receipt_path, b.status, b.payment_id 
        FROM bookings b
        JOIN student s ON b.student_id = s.id
        JOIN tutor_sessions ts ON b.session_id = ts.session_id
        WHERE b.tutor_id = :tutor_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':tutor_id', $_SESSION['UserID']);
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$allApproved = true;
foreach ($bookings as $booking) {
    if ($booking['status'] != 'approved') {
        $allApproved = false;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Requests</title>
    <link rel="stylesheet" href="bookingrequest.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="logo">
            <img src="unitenlogo.png" alt="University Logo">
            <h2 style="margin: 0; color: #1E4DB7;">ONLINE TUTORING SYSTEM</h2>
        </div>
        <nav class="links">
            <a href="tutmain.html">Home</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <aside class="sidebar">
            <h3>Tutor Dashboard</h3>
            <ul>
                <li><a href="profile.php">Display Profile</a></li>
                <li><a href="personal.php">Personal Details</a></li>
                <li><a href="session.php">Manage Sessions</a></li>
                <li><a href="bookingrequest.php">Booking Request</a></li>
            </ul>
        </aside>

        <main class="content">
            <h1>Booking Requests</h1>
            <?php if ($allApproved || empty($bookings)): ?>
                <p>You don't have any booking requests.</p>
            <?php else: ?>
                <table border="1">
                    <thead>
                        <tr>
                            <th>Session ID</th>
                            <th>Student Name</th>
                            <th>Subject Name</th>
                            <th>Session Time</th>
                            <th>Receipt</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?= htmlspecialchars($booking['session_id']) ?></td>
                                <td><?= htmlspecialchars($booking['student_name']) ?></td>
                                <td><?= htmlspecialchars($booking['subject']) ?></td>
                                <td><?= htmlspecialchars($booking['session_time']) ?></td>
                                <td><a href="<?= htmlspecialchars($booking['receipt_path']) ?>" target="_blank">View Receipt</a></td>
                                <td>
                                    <form method="POST" action="">
                                        <input type="hidden" name="payment_id" value="<?= htmlspecialchars($booking['payment_id']) ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="pending" <?= $booking['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="approved" <?= $booking['status'] == 'approved' ? 'selected' : '' ?>>Approved</option>
                                            <option value="declined" <?= $booking['status'] == 'declined' ? 'selected' : '' ?>>Declined</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>