<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Only logged-in users
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// CSRF check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    exit;
}

$user_id = $_SESSION['user_id'];
$session_type = $_POST['session_type']; // study or break
$duration = (int)$_POST['duration']; // seconds

$stmt = $conn->prepare(
    "INSERT INTO pomodoro_sessions (user_id, session_type, duration)
     VALUES (?, ?, ?)"
);
$stmt->bind_param("isi", $user_id, $session_type, $duration);
$stmt->execute();

echo "saved";
