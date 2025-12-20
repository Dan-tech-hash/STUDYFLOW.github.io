<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Only logged-in users can export
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch pomodoro sessions for this user
$stmt = $conn->prepare("
    SELECT session_type, duration, created_at
    FROM pomodoro_sessions
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Set CSV headers
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="pomodoro_report.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// CSV column headers
fputcsv($output, ['Date', 'Session Type', 'Duration (minutes)']);

// Write rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        date("Y-m-d H:i", strtotime($row['created_at'])),
        $row['session_type'],
        floor($row['duration'] / 60)
    ]);
}

fclose($output);
exit;
