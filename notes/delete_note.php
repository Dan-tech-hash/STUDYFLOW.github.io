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

// CSRF check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid CSRF token");
}

// Check note ID
if (!isset($_POST['id'])) {
    header("Location: ../dashboard.php");
    exit;
}

$note_id = $_POST['id'];

// Delete only if the note belongs to the user
$stmt = $conn->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $note_id, $user_id);
$stmt->execute();

header("Location: ../dashboard.php");
exit;
