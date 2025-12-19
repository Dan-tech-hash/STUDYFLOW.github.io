<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if task ID exists
if (!isset($_GET['id'])) {
    header("Location: ../dashboard.php");
    exit;
}

$task_id = $_GET['id'];

// Delete ONLY if task belongs to user
$stmt = $conn->prepare(
    "DELETE FROM tasks WHERE id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();

// Go back to dashboard
header("Location: ../dashboard.php");
exit;
