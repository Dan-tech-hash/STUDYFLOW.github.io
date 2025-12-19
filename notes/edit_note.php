<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check note ID in URL
if (!isset($_GET['id'])) {
    header("Location: ../dashboard.php");
    exit;
}

$note_id = $_GET['id'];

// Fetch note for form
$stmt = $conn->prepare("SELECT * FROM notes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $note_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Note not found or access denied.");
}

$note = $result->fetch_assoc();

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* =====================
   UPDATE NOTE (POST)
   ===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $title = $_POST['title'];
    $content = $_POST['content'];

    $update = $conn->prepare(
        "UPDATE notes SET title = ?, content = ? WHERE id = ? AND user_id = ?"
    );
    $update->bind_param("ssii", $title, $content, $note_id, $user_id);
    $update->execute();

    header("Location: ../dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Note</title>
</head>
<body>
<h2>Edit Note</h2>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

    <label>Title:</label><br>
    <input type="text" name="title" value="<?php echo htmlspecialchars($note['title']); ?>" required><br><br>

    <label>Content:</label><br>
    <textarea name="content"><?php echo htmlspecialchars($note['content']); ?></textarea><br><br>

    <button type="submit">Update Note</button>
</form>

<p><a href="../dashboard.php">⬅ Back to Dashboard</a></p>
</body>
</html>
