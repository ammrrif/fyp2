<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="studreg.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
    <title>Create Your Account</title>
</head>

<body>
    <div class="login-outer-container">
        <div class="login-container">
            <div class="login-area">
                <h2>Sign Up As Student</h2>

                <?php
                // Declare DB connection variables
                $host = "localhost";
                $user = "root";
                $pass = "";  // Enter your DB password if any
                $db = "fyp";  // Database name

                // Create a connection with the database
                $conn = new mysqli($host, $user, $pass, $db);

                // Check if the connection was successful
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Check if the form has been submitted
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    // Retrieve form data
                    $id = $_POST['id'];  // Student ID
                    $name = $_POST['name'];
                    $email = $_POST['email'];
                    $password = $_POST['password'];  // Storing the password as plain text (not recommended)

                    // Validate inputs
                    if (!preg_match("/^[A-Za-z0-9]+$/", $id)) { // Alphanumeric check for ID
                        echo "<script>alert('Invalid Student ID. Please enter only letters and numbers.');</script>";
                    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // Validate email
                        echo "<script>alert('Invalid email format.');</script>";
                    } else {
                        // Prepare SQL query to insert data into the student table
                        $query = "INSERT INTO student (ID, Name, Email, Password) VALUES (?, ?, ?, ?)";

                        // Prepare the statement
                        $stmt = $conn->prepare($query);

                        // Bind parameters (s for string, s for string, s for string, s for string)
                        $stmt->bind_param("ssss", $id, $name, $email, $password);

                        // Execute the query
                        if ($stmt->execute()) {
                            // Redirect to the login page if registration is successful
                            echo "<script>alert('Registration successful! You can now login.'); window.location.href='login.html';</script>";
                            exit(); // Stop further execution
                        } else {
                            // Handle error if registration fails
                            if ($conn->errno === 1062) { // Duplicate entry for primary key
                                echo "<script>alert('Error: This Student ID is already registered.');</script>";
                            } else {
                                echo "<script>alert('Error: " . $stmt->error . "');</script>";
                            }
                        }

                        // Close the statement
                        $stmt->close();
                    }
                }

                // Close the database connection
                $conn->close();
                ?>

                <form class="login-items" method="POST">
                    <label for="name">Name</label>
                    <input type="text" class="login" name="name" placeholder="Your name" required />

                    <label for="id">Student ID</label>
                    <input type="text" class="login" name="id" placeholder="Your Student ID (e.g., DC98783)" required />

                    <label for="email">Email</label>
                    <input type="email" class="login" name="email" placeholder="your-email@gmail.com" required />

                    <label for="password">Password</label>
                    <input type="password" class="login" name="password" placeholder="Enter password" required />

                    <input type="submit" class="login-btn" value="Register" />
                </form>

                <div class="account-link">
                    <p>Already have an account? <a class="a" href="login.html">Please Login</a></p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
