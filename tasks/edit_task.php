<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if task id exists in URL
if (!isset($_GET['id'])) {
    header("Location: ../dashboard.php");
    exit;
}

$task_id = $_GET['id'];

// Fetch task for the form (GET)
$stmt = $conn->prepare(
    "SELECT * FROM tasks WHERE id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Task not found or access denied.");
}

$task = $result->fetch_assoc();

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* =====================
   UPDATE TASK (POST)
   ===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF Check
    if (!isset($_POST['csrf_token']) || 
        $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $title = $_POST['title'];
    $description = $_POST['description'];

    $update = $conn->prepare(
        "UPDATE tasks SET title = ?, description = ? WHERE id = ? AND user_id = ?"
    );
    $update->bind_param("ssii", $title, $description, $task_id, $user_id);
    $update->execute();

    header("Location: ../dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Task</title>
</head>
<body>
<h2>Edit Task</h2>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

    <label>Title:</label><br>
    <input type="text" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required><br><br>

    <label>Description:</label><br>
    <textarea name="description"><?php echo htmlspecialchars($task['description']); ?></textarea><br><br>

    <button type="submit">Update Task</button>
</form>

<p><a href="../dashboard.php">⬅ Back to Dashboard</a></p>
</body>
</html>
