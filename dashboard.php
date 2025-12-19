<?php
// dashboard.php
session_start();

//CSRF Implementation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require 'config/db.php';

// Check if user is logged in and is a normal user
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

// Get username from session
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard - StudyFlow</title>
</head>
<body>

    <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
    <p>This is your dashboard.</p>

    <hr>

    <!-- TASKS SECTION -->
    <h2>Your Tasks</h2>

    <?php
        // This line loads and runs fetch_tasks.php
        include 'tasks/fetch_tasks.php';
    ?>

    <p>
        <a href="tasks/add_task.php">➕ Add New Task</a>
    </p>

    <hr>

    <ul>
       <h2>Your Notes</h2>
<?php include 'notes/fetch_notes.php'; ?>
<p><a href="notes/add_note.php">Add New Notes</a></p>
        <li><a href="#">Pomodoro Timer (Coming Soon)</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>

</body>
</html>
