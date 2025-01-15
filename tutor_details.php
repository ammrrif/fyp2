<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['UserID'])) {
    header('Location: Login.html');
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tutor_name = isset($_GET['tutor_name']) ? $_GET['tutor_name'] : 'N/A';
$subject = isset($_GET['subject']) ? $_GET['subject'] : 'N/A';
$currentMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
$currentYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

$firstDayOfMonth = strtotime("$currentYear-$currentMonth-01");
$lastDayOfMonth = strtotime("last day of $currentYear-$currentMonth");
$daysInMonth = date('t', $firstDayOfMonth);
$firstDayOfWeek = date('w', $firstDayOfMonth);

$tutor_sql = "SELECT * FROM tutor WHERE Name = ?";
$tutor_stmt = $conn->prepare($tutor_sql);
$tutor_stmt->bind_param("s", $tutor_name);
$tutor_stmt->execute();
$tutor_result = $tutor_stmt->get_result();
$tutor_details = $tutor_result->fetch_assoc();

$sql = "SELECT * FROM tutor_sessions WHERE tutor_name = ? AND subject = ? AND MONTH(session_date) = ? AND YEAR(session_date) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $tutor_name, $subject, $currentMonth, $currentYear);
$stmt->execute();
$result = $stmt->get_result();

$sessionDates = [];
$bookedDates = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $date = date('d', strtotime($row['session_date']));
        $sessionDates[$date][] = $row;
    }
}

// Get the student ID from the session
$student_id = $_SESSION['UserID'];

// Get the booked sessions for the student
$booked_sql = "SELECT session_id FROM bookings WHERE student_id = ?";
$booked_stmt = $conn->prepare($booked_sql);
$booked_stmt->bind_param("i", $student_id);
$booked_stmt->execute();
$booked_result = $booked_stmt->get_result();

$booked_sessions = [];
if ($booked_result->num_rows > 0) {
    while ($row = $booked_result->fetch_assoc()) {
        $booked_sessions[] = $row['session_id'];
    }
}

$prevMonth = date('m', strtotime('-1 month', $firstDayOfMonth));
$prevYear = date('Y', strtotime('-1 month', $firstDayOfMonth));
$nextMonth = date('m', strtotime('+1 month', $firstDayOfMonth));
$nextYear = date('Y', strtotime('+1 month', $firstDayOfMonth));

if (isset($_GET['session_date'])) {
    $selected_date = $_GET['session_date'];
    $selected_sessions_sql = "SELECT * FROM tutor_sessions WHERE tutor_name = ? AND subject = ? AND session_date = ?";
    $selected_sessions_stmt = $conn->prepare($selected_sessions_sql);
    $selected_sessions_stmt->bind_param("sss", $tutor_name, $subject, $selected_date);
    $selected_sessions_stmt->execute();
    $selected_sessions_result = $selected_sessions_stmt->get_result();
    $selected_sessions = $selected_sessions_result->fetch_all(MYSQLI_ASSOC);
    $_SESSION['selected_sessions'] = $selected_sessions;
} else {
    unset($_SESSION['selected_sessions']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutor Details - Online Tutoring System</title>
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
        <section class="tutor-details">
            <h1 class="tutor-title">Tutor Details</h1>
            <div class="tutor-info">
                <img src="uploads/<?php echo htmlspecialchars($tutor_details['Picture']); ?>" alt="Profile Picture" class="tutor-picture">
                <div class="tutor-description">
                    <h2><?php echo htmlspecialchars($tutor_name); ?></h2>
                    <p><strong>Subject:</strong> <?php echo htmlspecialchars($subject); ?></p>
                    <p><strong>Rate:</strong> RM<?php echo htmlspecialchars($tutor_details['Rate']); ?> per hour</p>
                    <p><strong>Bio:</strong> <?php echo htmlspecialchars($tutor_details['Bio']); ?></p>
                </div>
            </div>
           
        </section>

        <section class="availability-calendar">
            <div class="calendar">
                <div class="calendar-header">
                    <a href="?tutor_name=<?php echo urlencode($tutor_name); ?>&subject=<?php echo urlencode($subject); ?>&month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="prev-month">&#8249;</a>
                    <span class="month-year"><?php echo date('F Y', strtotime("$currentYear-$currentMonth-01")); ?></span>
                    <a href="?tutor_name=<?php echo urlencode($tutor_name); ?>&subject=<?php echo urlencode($subject); ?>&month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="next-month">&#8250;</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Sunday</th>
                            <th>Monday</th>
                            <th>Tuesday</th>
                            <th>Wednesday</th>
                            <th>Thursday</th>
                            <th>Friday</th>
                            <th>Saturday</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <?php for ($i = 0; $i < $firstDayOfWeek; $i++) { echo '<td class="day empty"></td>'; } ?>
                            <?php
                            for ($day = 1; $day <= $daysInMonth; $day++) {
                                $dayFormatted = str_pad($day, 2, '0', STR_PAD_LEFT);
                                $date = "$currentYear-$currentMonth-$dayFormatted";
                                
                                echo "<td class='day'>";
                                echo "<span class='date-number'>$day</span>";
                                
                                if (isset($sessionDates[$dayFormatted])) {
                                    $session_id = $sessionDates[$dayFormatted][0]['session_id'];
                                    if (in_array($session_id, $booked_sessions)) {
                                        echo "<button class='na-btn'>Already Booked</button>";
                                    } else {
                                        echo "<a href='?tutor_name=" . urlencode($tutor_name) . "&subject=" . urlencode($subject) . "&session_date=$date' class='book-btn'>Book Now</a>";
                                    }
                                } else {
                                    echo "<button class='na-btn'>N/A</button>";
                                }
                                echo "</td>";

                                if (($day + $firstDayOfWeek) % 7 == 0 && $day != $daysInMonth) {
                                    echo "</tr><tr>";
                                }
                            }

                            $remainingDays = (7 - (($daysInMonth + $firstDayOfWeek) % 7)) % 7;
                            for ($i = 0; $i < $remainingDays; $i++) { echo '<td class="day empty"></td>'; }
                        ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>

<!-- Selected Session Section -->
<div class="selected-session">
    <h2><?php echo isset($_SESSION['selected_sessions']) ? 'Selected date' : 'No date selected'; ?></h2>
    <?php if (isset($_SESSION['selected_sessions'])): ?>
        <table>
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Session Date</th>
                    <th>Session Time</th>
                    <th>Duration</th> <!-- New column for duration -->
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['selected_sessions'] as $session): ?>
                <tr>
                    <td><?php echo htmlspecialchars($session['subject']); ?></td>
                    <td><?php echo htmlspecialchars($session['session_date']); ?></td>
                    <td><?php echo htmlspecialchars($session['session_time']); ?></td>
                    <td><?php echo htmlspecialchars($session['duration']); ?> hour<?php echo $session['duration'] > 1 ? 's' : ''; ?></td> <!-- Display duration -->
                    <td>
                        <form action="payment.php" method="post">
                            <input type="hidden" name="session_id" value="<?php echo htmlspecialchars($session['session_id']); ?>">
                            <input type="hidden" name="subject" value="<?php echo htmlspecialchars($session['subject']); ?>">
                            <input type="hidden" name="session_date" value="<?php echo htmlspecialchars($session['session_date']); ?>">
                            <input type="hidden" name="session_time" value="<?php echo htmlspecialchars($session['session_time']); ?>">
                            <input type="hidden" name="duration" value="<?php echo htmlspecialchars($session['duration']); ?>"> <!-- Include duration in form -->
                            <button type="submit" class="book-btn pay-now-btn">Pay Now</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
$conn->close();
?>
</body>
</html>