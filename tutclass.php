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

    $show_form = true;
    $edit_mode = false;
    $existing_data = [];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['edit'])) {
            $show_form = true;
            $edit_mode = true;

            // Fetch existing data for the form
            $stmt = $conn->prepare("SELECT * FROM class WHERE tutor_id = :tutor_id");
            $stmt->bindParam(':tutor_id', $_SESSION['UserID']);
            $stmt->execute();
            $existing_data = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $tutor_id = $_SESSION['UserID'];
            $session_id = isset($_POST['session_id']) ? $_POST['session_id'] : null;
            $meeting_link = isset($_POST['meeting_link']) ? $_POST['meeting_link'] : null;
            $recording_link = isset($_POST['recording_link']) ? $_POST['recording_link'] : null;
            $class_status = isset($_POST['class_status']) ? $_POST['class_status'] : null;

            // Debugging: Log form data
            error_log("Form Data: tutor_id=$tutor_id, session_id=$session_id, meeting_link=$meeting_link, recording_link=$recording_link, class_status=$class_status");

            if ($session_id && $meeting_link) {
                // Check if the record already exists
                $stmt = $conn->prepare("SELECT COUNT(*) FROM class WHERE tutor_id = :tutor_id AND session_id = :session_id");
                $stmt->bindParam(':tutor_id', $tutor_id);
                $stmt->bindParam(':session_id', $session_id);
                $stmt->execute();
                $record_exists = $stmt->fetchColumn() > 0;

                if ($record_exists) {
                    // Update existing record
                    $stmt = $conn->prepare("UPDATE class SET meeting_link = :meeting_link, recording_link = :recording_link, class_status = :class_status WHERE tutor_id = :tutor_id AND session_id = :session_id");
                } else {
                    // Insert new record
                    $stmt = $conn->prepare("INSERT INTO class (tutor_id, session_id, meeting_link, recording_link, class_status) VALUES (:tutor_id, :session_id, :meeting_link, :recording_link, :class_status)");
                }

                $stmt->bindParam(':tutor_id', $tutor_id);
                $stmt->bindParam(':session_id', $session_id);
                $stmt->bindParam(':meeting_link', $meeting_link);
                $stmt->bindParam(':recording_link', $recording_link);
                $stmt->bindParam(':class_status', $class_status);

                if ($stmt->execute()) {
                    // Handle file uploads
                    if (isset($_FILES['resource']) && $_FILES['resource']['error'] == UPLOAD_ERR_OK) {
                        $resource_path = 'uploads/' . basename($_FILES['resource']['name']);
                        if (move_uploaded_file($_FILES['resource']['tmp_name'], $resource_path)) {
                            $stmt = $conn->prepare("UPDATE class SET resource_path = :resource_path WHERE tutor_id = :tutor_id AND session_id = :session_id");
                            $stmt->bindParam(':resource_path', $resource_path);
                            $stmt->bindParam(':tutor_id', $tutor_id);
                            $stmt->bindParam(':session_id', $session_id);
                            if (!$stmt->execute()) {
                                $_SESSION['upload_error'] = "Failed to update resource path.";
                            }
                        } else {
                            $_SESSION['upload_error'] = "Failed to upload resource.";
                        }
                    }

                    if (!isset($_SESSION['upload_error'])) {
                        $_SESSION['upload_success'] = "Resources uploaded successfully!";
                        $show_form = false;
                    }
                } else {
                    $_SESSION['upload_error'] = "Failed to update class details.";
                }
            } else {
                $_SESSION['upload_error'] = "Session ID and Meeting Link are required.";
            }
        }
    }

    // Fetch inserted details from class table
    $stmt = $conn->prepare("SELECT * FROM class WHERE tutor_id = :tutor_id");
    $stmt->bindParam(':tutor_id', $_SESSION['UserID']);
    $stmt->execute();
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Hide form if there are resources to display and not in edit mode
    if (!empty($resources) && !$edit_mode) {
        $show_form = false;
    }

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutoring Session</title>
    <meta charset="UTF-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="tutclass.css">
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
    <div class="layout">
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
        <div class="main-content">
            <?php if ($show_form): ?>
                <div class="container" id="form-container">
                    <h1>Tutoring Session</h1>
                    <?php
                    if (isset($_SESSION['upload_success'])) {
                        echo "<div class='message success'>" . $_SESSION['upload_success'] . "</div>";
                        unset($_SESSION['upload_success']);
                    }
                    if (isset($_SESSION['upload_error'])) {
                        echo "<div class='message error'>" . $_SESSION['upload_error'] . "</div>";
                        unset($_SESSION['upload_error']);
                    }
                    ?>
                    <form action="tutclass.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="session_id" value="<?php echo isset($existing_data['session_id']) ? htmlspecialchars($existing_data['session_id']) : (isset($_GET['session_id']) ? htmlspecialchars($_GET['session_id']) : ''); ?>">
                        <label for="meeting_link">Meeting Link:</label>
                        <input type="text" id="meeting_link" name="meeting_link" required value="<?php echo isset($existing_data['meeting_link']) ? htmlspecialchars($existing_data['meeting_link']) : ''; ?>">

                        <label for="recording_link">Recording Link:</label>
                        <input type="text" id="recording_link" name="recording_link" value="<?php echo isset($existing_data['recording_link']) ? htmlspecialchars($existing_data['recording_link']) : ''; ?>">

                        <label for="resource_path">Resource Path:</label>
                        <input type="file" id="resource_path" name="resource">

                        <label for="class_status">Class Status:</label>
                        <select name="class_status" id="class_status">
                            <option value="upcoming" <?php if (isset($existing_data['class_status']) && $existing_data['class_status'] == 'upcoming') echo 'selected'; ?>>Upcoming</option>
                            <option value="completed" <?php if (isset($existing_data['class_status']) && $existing_data['class_status'] == 'completed') echo 'selected'; ?>>Completed</option>
                        </select>

                        <button type="submit">Submit</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="resources-container" id="resources-container">
                    <h2>Class Details</h2>
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
                    <form action="tutclass.php" method="post">
                        <input type="hidden" name="edit" value="1">
                        <button type="submit">Edit</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>