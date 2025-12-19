<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../dashboard.php");
    exit;
}

// CSRF Check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid CSRF token");
}

// Task ID from POST
if (!isset($_POST['id'])) {
    header("Location: ../dashboard.php");
    exit;
}

$task_id = $_POST['id'];

// Fetch current status
$stmt = $conn->prepare("SELECT status FROM tasks WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: ../dashboard.php");
    exit;
}

$task = $result->fetch_assoc();

// Toggle status
$new_status = ($task['status'] === 'pending') ? 'completed' : 'pending';

// Update in DB
$update = $conn->prepare(
    "UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?"
);
$update->bind_param("sii", $new_status, $task_id, $user_id);
$update->execute();

header("Location: ../dashboard.php");
exit;
