<?php
// admin_dashboard.php
session_start();
require 'config/db.php';

// Check if user is logged in and is an admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Get username from session
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - StudyFlow</title>
</head>
<body>
    <h2>Welcome Admin, <?php echo htmlspecialchars($username); ?>!</h2>
    <p>This is the admin dashboard.</p>

    <ul>
        <li><a href="#">Manage Users (Coming Soon)</a></li>
        <li><a href="#">Manage Tasks & Notes (Coming Soon)</a></li>
        <li><a href="#">View Reports (Coming Soon)</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</body>
</html>
