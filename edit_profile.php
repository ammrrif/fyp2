<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    header('Location: Login.html');
    exit();
}

$user_id = $_SESSION['UserID'];

$host = "localhost";
$user = "root";
$pass = "";
$db = "fyp";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT * FROM tutor WHERE ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$tutor_data = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $prog = $_POST['prog'];
    $password = $_POST['password'];
    $hourly_rate = $_POST['hourly_rate'];
    $bio = $_POST['bio'];
    $profile_picture = $_FILES['profile_picture']['name'];
    $profile_tmp_name = $_FILES['profile_picture']['tmp_name'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.');</script>";
    } elseif (!is_numeric($hourly_rate) || $hourly_rate <= 0) {
        echo "<script>alert('Invalid hourly rate. Please enter a positive number.');</script>";
    } else {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $profile_target = $upload_dir . basename($profile_picture);
        move_uploaded_file($profile_tmp_name, $profile_target);

        $query = "UPDATE tutor SET Name=?, Email=?, Programme=?, Password=?, Rate=?, Bio=?, Picture=? WHERE ID=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssdsss", $name, $email, $prog, $password, $hourly_rate, $bio, $profile_picture, $user_id);

        if ($stmt->execute()) {
            echo "<script>alert('Profile updated successfully!'); window.location.href='profile.php';</script>";
            exit();
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="profile.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@700&display=swap" rel="stylesheet">
    <title>Edit Profile</title>
    <style>
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .form-group input[type="file"] {
            padding: 3px;
        }
        .form-group input[type="submit"] {
            width: auto;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            cursor: pointer;
        }
        .form-group input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
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

    <div class="container">
        <aside class="sidebar">
            <h3>Tutor Dashboard</h3>
            <ul>
                <li><a href="profile.php">Display Profile</a></li>
                <li><a href="session.php">Manage Sessions</a></li>
                <li><a href="student-reviews.html">Student Reviews</a></li>
                <li><a href="bookingrequest.php">Booking Request</a></li>
            </ul>
        </aside>

        <main class="content">
            <h2>Edit Profile</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($tutor_data['Name']); ?>" required />
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($tutor_data['Email']); ?>" required />
                </div>
                <div class="form-group">
                    <label for="prog">Programme</label>
                    <select id="prog" name="prog" required>
                        <option value="" disabled>Select your programme</option>
                        <option value="Foundation in Engineering" <?php if ($tutor_data['Programme'] == 'Foundation in Engineering') echo 'selected'; ?>>Foundation in Engineering</option>
                        <!-- Add other options here -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="hourly_rate">Hourly Rate</label>
                    <input type="number" name="hourly_rate" value="<?php echo htmlspecialchars($tutor_data['Rate']); ?>" required />
                </div>
                <div class="form-group">
                    <label for="bio">Short Bio</label>
                    <textarea name="bio" rows="4" required><?php echo htmlspecialchars($tutor_data['Bio']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" value="<?php echo htmlspecialchars($tutor_data['Password']); ?>" required />
                </div>
                <div class="form-group">
                    <label for="profile-picture">Profile Picture</label>
                    <input type="file" id="profile-picture" name="profile_picture" required />
                </div>
                <div class="form-group">
                    <input type="submit" value="Update Profile" />
                </div>
            </form>
        </main>
    </div>
</body>
</html>