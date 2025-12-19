<?php
// dashboard.php
session_start();
require 'config/db.php';

// Check if user is logged in and role is 'user'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

// Get username from session
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - StudyFlow</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
    <p>This is your dashboard.</p>

    <!-- Placeholders for future features -->
    <h3>Tasks</h3>
    <p>Here you will see your tasks.</p>

    <h3>Notes</h3>
    <p>Here you will see your notes.</p>

    <h3>Pomodoro Timer</h3>
    <p>Timer will appear here.</p>

    <p><a href="logout.php">Logout</a></p>
</body>
</html>
