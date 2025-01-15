<?php
session_start();

// Database connection details
$host = 'localhost';
$dbname = 'fyp';
$user = 'root';
$password = '';

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['receipt'])) {
    $student_id = $_SESSION['UserID'];
    $tutor_id = $_POST['tutor_id']; // Assuming tutor_id is passed via POST
    $session_id = $_POST['session_id']; // Assuming session_id is passed via POST
    $target_dir = "uploads/receipts/";

    // Create the directory if it doesn't exist
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_file = $target_dir . basename($_FILES["receipt"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file is a valid image or PDF
    $check = getimagesize($_FILES["receipt"]["tmp_name"]);
    if ($check !== false || $fileType == "pdf") {
        $uploadOk = 1;
    } else {
        echo "File is not an image or PDF.";
        $uploadOk = 0;
    }

    // Check file size (limit to 5MB)
    if ($_FILES["receipt"]["size"] > 5000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($fileType != "jpg" && $fileType != "png" && $fileType != "jpeg" && $fileType != "gif" && $fileType != "pdf") {
        echo "Sorry, only JPG, JPEG, PNG, GIF, and PDF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    // If everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["receipt"]["tmp_name"], $target_file)) {
            // Save the file path to the database
            $stmt = $conn->prepare("INSERT INTO bookings (student_id, tutor_id, session_id, receipt_path, uploaded_at, status) VALUES (:student_id, :tutor_id, :session_id, :receipt_path, NOW(), 'PENDING')");
            $stmt->bindParam(':student_id', $student_id);
            $stmt->bindParam(':tutor_id', $tutor_id);
            $stmt->bindParam(':session_id', $session_id);
            $stmt->bindParam(':receipt_path', $target_file);
            $stmt->execute();

            // Redirect to main page with success message
            $_SESSION['upload_success'] = "Receipt has been uploaded successfully.";
            header('Location: mainstud.php');
            exit();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Fetch session details
$subject = isset($_POST['subject']) ? $_POST['subject'] : 'N/A';
$session_date = isset($_POST['session_date']) ? $_POST['session_date'] : 'N/A';
$session_time = isset($_POST['session_time']) ? $_POST['session_time'] : 'N/A';

// Fetch rate and duration from the database
$session_id = $_POST['session_id']; // Assuming session_id is passed via POST
$sql = "SELECT t.Rate, ts.duration, t.Name as tutor_name, t.ID as tutor_id 
        FROM tutor t 
        JOIN tutor_sessions ts ON t.Name = ts.tutor_name 
        WHERE ts.session_id = :session_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':session_id', $session_id);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$rate = $result['Rate'];
$duration = $result['duration'];
$total_price = $rate * $duration;
$tutor_id = $result['tutor_id'];
$tutor_name = $result['tutor_name'];

// Fetch QR code and bank details from personal_details table
$sql = "SELECT bank_name, account_number, qr_code FROM personal_details WHERE tutor_id = :tutor_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':tutor_id', $tutor_id);
$stmt->execute();
$personal_details = $stmt->fetch(PDO::FETCH_ASSOC);
$qr_code = $personal_details['qr_code'];
$bank_name = $personal_details['bank_name'];
$account_number = $personal_details['account_number'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Page</title>
    <link rel="stylesheet" href="payment.css">
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

    <div class="main-content">
        <div class="left">
            <div class="section">
                <h2>QR Code</h2>
                <div class="qr-code-container">
                    <?php if ($qr_code): ?>
                        <img src="<?= htmlspecialchars($qr_code) ?>" alt="QR Code">
                    <?php else: ?>
                        <p>No QR code available.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="section">
                <h2>Bank Transfer</h2>
                <p><strong>Tutor Name:</strong> <?= htmlspecialchars($tutor_name) ?></p>
                <p><strong>Bank Name:</strong> <?= htmlspecialchars($bank_name) ?></p>
                <p><strong>Account Number:</strong> <?= htmlspecialchars($account_number) ?></p>
            </div>
        </div>
        <div class="right">
            <div class="summary">
                <h2>Session Summary</h2>
                <p>Subject: <?php echo htmlspecialchars($subject); ?></p>
                <p>Session Date: <?php echo htmlspecialchars($session_date); ?></p>
                <p>Session Time: <?php echo htmlspecialchars($session_time); ?></p>
                <p>Total Price: $<?php echo number_format($total_price, 2); ?></p>
            </div>
            <div class="upload-section">
                <h2>Upload Receipt</h2>
                <form class="upload-form" action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="tutor_id" value="<?= htmlspecialchars($tutor_id) ?>">
                    <input type="hidden" name="session_id" value="<?= htmlspecialchars($session_id) ?>">
                    <label for="receipt">Upload Receipt:</label>
                    <input type="file" id="receipt" name="receipt" accept="image/*,application/pdf" required>
                    <button type="submit">Submit</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>