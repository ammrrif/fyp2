<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="tutreg.css">
    <title>Create Your Account</title>
</head>

<body>
    <div class="login-outer-container">
        <div class="login-container">
            <div class="login-area">
                <h2>Sign Up As Tutor</h2>

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
                    $prog = $_POST['prog'];  // Programme of study
                    $password = $_POST['password'];  // Storing the password as plain text
                    $hourly_rate = $_POST['hourly_rate'];
                    $bio = $_POST['bio'];
                    $profile_picture = $_FILES['profile_picture']['name'];
                    $profile_tmp_name = $_FILES['profile_picture']['tmp_name'];

                    // Validate inputs
                    if (!preg_match("/^[A-Za-z0-9]+$/", $id)) { // Alphanumeric check for ID
                        echo "<script>alert('Invalid Student ID. Please enter only letters and numbers.');</script>";
                    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // Validate email
                        echo "<script>alert('Invalid email format.');</script>";
                    } elseif (!is_numeric($hourly_rate) || $hourly_rate <= 0) { // Validate hourly rate
                        echo "<script>alert('Invalid hourly rate. Please enter a positive number.');</script>";
                    } else {
                        // Move uploaded file to the 'uploads' directory
                        $upload_dir = "uploads/";
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        $profile_target = $upload_dir . basename($profile_picture);
                        move_uploaded_file($profile_tmp_name, $profile_target);

                        // Prepare SQL query to insert data into the tutor table
                        $query = "INSERT INTO tutor (ID, Name, Email, Programme, Password, Rate, Bio, Picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

                        // Prepare the statement
                        $stmt = $conn->prepare($query);

                        // Bind parameters
                        $stmt->bind_param("sssssdss", $id, $name, $email, $prog, $password, $hourly_rate, $bio, $profile_picture);

                        // Execute the query
                        if ($stmt->execute()) {
                            // Redirect to the login page if registration is successful
                            echo "<script>alert('Registration successful! You can now login.'); window.location.href='  Login.html';</script>";
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

                <form class="login-items" method="POST" enctype="multipart/form-data">
                    <label for="name">Name</label>
                    <input type="text" class="login" name="name" placeholder="Your full name" required />

                    <label for="id">Student ID</label>
                    <input type="text" class="login" name="id" placeholder="Your student ID" required />

                    <label for="email">Email</label>
                    <input type="email" class="login" name="email" placeholder="your-email@gmail.com" required />

                    <label for="prog">Programme</label>
                    <select id="prog" name="prog" required>
                        <option value="" disabled selected>Select your programme</option>
                        <!-- Foundation Level -->
                        <option value="Foundation in Engineering ">Foundation in Engineering </option>
                        <option value="Foundation in Computer Science ">Foundation in Computer Science </option>
                        <option value="Foundation in Information Technology ">Foundation in Information Technology </option>
                        <option value="Foundation in Management ">Foundation in Management </option>
                        <option value="Foundation in Accounting ">Foundation in Accounting  </option>
                        <option value="Foundation in Business Administration ">Foundation in Business Administration </option>
                        <option value="Tahfiz UNITEN">Tahfiz UNITEN </option>

                        <!-- Diploma Level -->
                        <option value="Diploma in Mechanical Engineering ">Diploma in Mechanical Engineering</option>
                        <option value="Diploma in Electrical Engineering  ">Diploma in Electrical Engineering</option>
                        <option value="Diploma in Computer Science ">Diploma in Computer Science</option>
                        <option value="Diploma of Accountancy ">Diploma of Accountancy</option>
                        <option value="Diploma in Business Studies ">Diploma in Business Studies</option>
                        <option value="Diploma in Digital Business">Diploma in Digital Business</option>
                        <option value="Diploma in Financial Technology ">Diploma in Financial Technology</option>

                        <!-- Bachelor's Level -->
                        <option value="Bachelor of Electrical and Electronics Engineering (Hons) ">Bachelor of Electrical and Electronics Engineering (Hons)</option>
                        <option value="Bachelor of Computer Science (Systems and Networking) (Hons) ">Bachelor of Computer Science (Systems and Networking) (Hons)</option>
                        <option value="Bachelor in Software Engineering (Honours) ">Bachelor in Software Engineering (Honours)</option>
                        <option value="Bachelor of Computer Science (Cyber Security) (Honours)">Bachelor of Computer Science (Cyber Security) (Honours)</option>
                        <option value="Bachelor in Information Systems (Business Analytics) (Honours)">Bachelor in Information Systems (Business Analytics) (Honours)</option>
                    </select>

                    <label for="hourly_rate">Hourly Rate</label>
                    <input type="number" class="login" name="hourly_rate" placeholder="e.g., 50" required />

                    <label for="bio">Short Bio</label>
                    <textarea name="bio" class="login" rows="4" placeholder="Write a short bio about yourself..." required></textarea>

                    <label for="password">Password</label>
                    <input type="password" class="login" name="password" placeholder="Enter password" required />

                    <label for="profile-picture">Profile Picture</label>
                    <input type="file" id="profile-picture" name="profile_picture" class="profile-picture" required />

                    <input type="submit" class="login-btn" value="Register" />
                </form>

                <div class="account-link">
                    <p>Already have an account? <a class="a" href="Login.html">Please Login</a></p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
