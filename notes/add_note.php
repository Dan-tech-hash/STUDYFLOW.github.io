<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* =====================
   ADD NOTE (POST)
   ===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF Check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $title = $_POST['title'];
    $content = $_POST['content'];

    // Insert note
    $stmt = $conn->prepare(
        "INSERT INTO notes (user_id, title, content) VALUES (?, ?, ?)"
    );
    $stmt->bind_param("iss", $user_id, $title, $content);
    $stmt->execute();

    header("Location: ../dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Note</title>
</head>
<body>
<h2>Add New Note</h2>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

    <label>Title:</label><br>
    <input type="text" name="title" required><br><br>

    <label>Content:</label><br>
    <textarea name="content"></textarea><br><br>

    <button type="submit">Add Note</button>
</form>

<p><a href="../dashboard.php">⬅ Back to Dashboard</a></p>
</body>
</html>
