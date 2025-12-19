<?php
// admin_register.php
session_start();
require 'config/db.php';

$message = '';

// Secret key to access this page
$access_password = "pass"; // change this
if (!isset($_GET['key']) || $_GET['key'] !== $access_password) {
    die("Access denied!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if username or email already exists
    $check = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $result_check = $check->get_result();

    if ($result_check->num_rows > 0) {
        $message = "Username or email already exists!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert admin user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
        $stmt->bind_param("sss", $username, $email, $hashed_password);

        if ($stmt->execute()) {
            $message = "Admin registered successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Registration - StudyFlow</title>
</head>
<body>
    <h2>Admin Registration</h2>
    <?php if ($message != '') { echo "<p>$message</p>"; } ?>
    <form method="POST" action="">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <input type="submit" value="Register Admin">
    </form>
</body>
</html>
