<?php
$userID = $_POST['id'];  // Get the student ID entered by the user
$userPWD = $_POST['pwd'];  // Get the password entered by the user

// Declare DB connection variables
$host = "localhost";
$user = "root";
$pass = "";  // Enter your DB password if any
$db = "fyp"; // Your database name

// Create a connection with the DB
$conn = new mysqli($host, $user, $pass, $db);

// Check if DB connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); // Display MySQL connection error
} else {
    // Check if student ID exists in either student or tutor table
    $queryCheckStudent = "SELECT * FROM student WHERE ID = '".$userID."'";
    $queryCheckTutor = "SELECT * FROM tutor WHERE ID = '".$userID."'";

    // Query for student
    $resultCheckStudent = $conn->query($queryCheckStudent);
    if ($resultCheckStudent === false) {
        echo "Error in student query: " . $conn->error;  // Display query error if any
    } else {
        if ($resultCheckStudent->num_rows > 0) {
            // If student exists, fetch the data
            $row = $resultCheckStudent->fetch_assoc();

            // Check if password matches
            if ($row["Password"] == $userPWD) {
                session_start();
                $_SESSION["UserID"] = $userID;  // Keep for consistency
                $_SESSION["UserType"] = 'Student';  // Set user type to student
                $_SESSION["StudentName"] = $row["Name"];  // Store student name in session
                header("Location: mainstud.php");  // Redirect to student home page
            } else {
                echo "<p style='color:red;'>Wrong password!!!</p>";
            }
        } else {
            // Query for tutor if student doesn't exist
            $resultCheckTutor = $conn->query($queryCheckTutor);
            if ($resultCheckTutor === false) {
                echo "Error in tutor query: " . $conn->error;  // Display query error if any
            } else {
                if ($resultCheckTutor->num_rows > 0) {
                    // If tutor exists, fetch the data
                    $row = $resultCheckTutor->fetch_assoc();

                    // Check if password matches
                    if ($row["Password"] == $userPWD) {
                        session_start();
                        $_SESSION["UserID"] = $userID;
                        $_SESSION["tutor_id"] = $userID; // Fix here
                        $_SESSION["UserType"] = 'Tutor';  // Set user type to tutor
                        $_SESSION["TutorName"] = $row["Name"];  // Store tutor name in session
                        header("Location: tutmain.php");  // Redirect to tutor home page
                    } else {
                        echo "<p style='color:red;'>Wrong password!!!</p>";
                    }
                } else {
                    echo "<p style='color:red;'>User ID does not exist or has been blocked.</p>";
                }
            }
        }
    }
}

$conn->close();
?>
