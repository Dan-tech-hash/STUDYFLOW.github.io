<?php
// tasks/add_task.php
session_start();
require '../config/db.php';



// Only logged-in users can add tasks
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    //CSRF IMPLEMENTATION
if (!isset($_POST['csrf_token']) || 
    $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid CSRF token");
}

    $title = $_POST['title'];
    $description = $_POST['description'];
    $user_id = $_SESSION['user_id'];

    // Insert task into database
    $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $title, $description);

    if($stmt->execute()) {
        $message = "Task added successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Task - StudyFlow</title>
</head>
<body>
    <h2>Add Task</h2>
    <?php if($message != '') { echo "<p>$message</p>"; } ?>
    <form method="POST" action="">
        <label>Title:</label><br>
        <!--CSRF IMPLEMENTATION-->
        <input type="hidden" name="csrf_token" 
       value="<?php echo $_SESSION['csrf_token']; ?>">

        <input type="text" name="title" required><br><br>

        <label>Description:</label><br>
        <textarea name="description"></textarea><br><br>

        <input type="submit" value="Add Task">
    </form>
    <p><a href="../dashboard.php">Back to Dashboard</a></p>
</body>
</html>
