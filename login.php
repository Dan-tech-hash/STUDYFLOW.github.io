<?php
// login.php
session_start();
require 'config/db.php';

$message = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare statement to find user
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Store user info in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; 

            // Redirect to dashboard
           if($user['role'] === 'admin') {
    header("Location: admin_dashboard.php");
} else {
    header("Location: dashboard.php");
}
exit;
        } else {
            $message = "Wrong password!";
        }
    } else {
        $message = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - StudyFlow</title>
</head>
<body>
    <h2>Login</h2>
    <?php if ($message != '') { echo "<p>$message</p>"; } ?>
    <form method="POST" action="">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <input type="submit" value="Login">
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a></p>
</body>
</html>
