<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    header('Location: Login.html');
    exit();
}

header("Cache-Control: no-cache, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

$host = "localhost";
$user = "root";
$pass = "";  
$db = "fyp";  

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userID = $_SESSION['UserID'];
$query = "SELECT ID FROM tutor WHERE ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $userID);
$stmt->execute();
$result = $stmt->get_result();
$tutor = $result->fetch_assoc();
$tutorID = $tutor['ID'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bankName = $_POST['bank_name'];
    $accountNumber = $_POST['account_number'];
    $qrCode = $_FILES['qr_code']['name'];
    $targetDir = "uploads/";
    $targetFile = $targetDir . basename($qrCode);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    $check = getimagesize($_FILES['qr_code']['tmp_name']);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    if ($_FILES['qr_code']['size'] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    if ($imageFileType != "jpg" && $imageFileType != "jpeg") {
        echo "Sorry, only JPG, JPEG files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES['qr_code']['tmp_name'], $targetFile)) {
            $sql = "INSERT INTO personal_details (tutor_id, bank_name, account_number, qr_code) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $tutorID, $bankName, $accountNumber, $targetFile);

            if ($stmt->execute()) {
                echo "The file ". htmlspecialchars(basename($qrCode)). " has been uploaded and details saved.";
                header('Location: personal.php');
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }

            $stmt->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Details</title>
    <meta charset="UTF-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="personal.css">
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

        <main class="content">
            <h1>Personal Details</h1>
            <form action="personal.php" method="post" enctype="multipart/form-data">
                <div>
                    <label for="bank_name">Bank Name:</label>
                    <select id="bank_name" name="bank_name" required>
                        <option value="Maybank (Malayan Banking Berhad)">Maybank (Malayan Banking Berhad)</option>
                        <option value="CIMB Bank">CIMB Bank</option>
                        <option value="Public Bank">Public Bank</option>
                        <option value="RHB Bank">RHB Bank</option>
                        <option value="Hong Leong Bank">Hong Leong Bank</option>
                        <option value="AmBank (AmBank Group)">AmBank (AmBank Group)</option>
                        <option value="Alliance Bank">Alliance Bank</option>
                        <option value="Agrobank">Agrobank</option>
                        <option value="Bank Muamalat">Bank Muamalat</option>
                        <option value="Bank Islam Malaysia">Bank Islam Malaysia</option>
                    </select>
                </div>
                <div>
                    <label for="account_number">Account Number:</label>
                    <input type="text" id="account_number" name="account_number" required>
                </div>
                <div>
                    <label for="qr_code">QR Code (jpg,jpeg):</label>
                    <input type="file" id="qr_code" name="qr_code" accept="image/*" required>
                </div>
                <div>
                    <button type="submit">Save Details</button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>