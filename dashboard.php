<?php
// dashboard.php
session_start();
require 'config/db.php';

// Check if user is logged in and is a normal user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get username
$username = $_SESSION['username'];

// ===============================
// Fetch today's study time
// ===============================
$today = date('Y-m-d');

$stmt = $conn->prepare("
    SELECT SUM(duration) AS total_seconds 
    FROM pomodoro_sessions 
    WHERE user_id = ? 
      AND session_type = 'study'
      AND DATE(created_at) = ?
");
$stmt->bind_param("is", $_SESSION['user_id'], $today);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$totalSeconds = $row['total_seconds'] ?? 0;

// Convert seconds to hours & minutes
$hours = floor($totalSeconds / 3600);
$minutes = floor(($totalSeconds % 3600) / 60);

// ===============================
// Weekly study time (Mon–Sun)
// ===============================
$weekStart = date('Y-m-d', strtotime('monday this week'));
$weekEnd   = date('Y-m-d', strtotime('sunday this week'));

$stmt_week = $conn->prepare("
    SELECT SUM(duration) AS total_seconds 
    FROM pomodoro_sessions
    WHERE user_id = ?
      AND session_type = 'study'
      AND DATE(created_at) BETWEEN ? AND ?
");
$stmt_week->bind_param("iss", $_SESSION['user_id'], $weekStart, $weekEnd);
$stmt_week->execute();
$result_week = $stmt_week->get_result();
$row_week = $result_week->fetch_assoc();

$totalSecondsWeek = $row_week['total_seconds'] ?? 0;

// Convert to hours & minutes
$hoursWeek = floor($totalSecondsWeek / 3600);
$minutesWeek = floor(($totalSecondsWeek % 3600) / 60);

// ===============================
// Calculate Current Streak
// ===============================
$user_id = $_SESSION['user_id'];

// Fetch all study session dates (distinct) in descending order
$stmt_streak = $conn->prepare("
    SELECT DISTINCT DATE(created_at) AS session_date
    FROM pomodoro_sessions
    WHERE user_id = ? AND session_type = 'study'
    ORDER BY session_date DESC
");
$stmt_streak->bind_param("i", $user_id);
$stmt_streak->execute();
$result_streak = $stmt_streak->get_result();

$streak = 0;
$today = new DateTime();
$yesterday = new DateTime();
$yesterday->modify('-1 day');

$dates = [];
while ($row = $result_streak->fetch_assoc()) {
    $dates[] = $row['session_date'];
}

// Calculate consecutive days
$current = new DateTime();
foreach ($dates as $date) {
    $sessionDate = new DateTime($date);
    $diff = $current->diff($sessionDate)->days;
    if ($diff === 0 || $diff === 1) {
        $streak++;
        $current = $sessionDate->modify('-1 day');
    } else {
        break; // streak broken
    }
}

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

<!-- STUDY TIME -->
<h3>📚 Today’s Study Time</h3>
<p>
<?php
if ($totalSeconds > 0) {
    echo $hours . " hour(s) " . $minutes . " minute(s)";
} else {
    echo "No study sessions yet today.";
}
?>

<h3>📅 Weekly Study Time</h3>
<p>
<?php
if ($totalSecondsWeek > 0) {
    echo $hoursWeek . " hour(s) " . $minutesWeek . " minute(s)";
} else {
    echo "No study sessions yet this week.";
}
?>

<h3>🔥 Current Study Streak</h3>
<p>
<?php
if ($streak > 0) {
    echo $streak . " day(s)";
} else {
    echo "No streak yet. Start today!";
}
?>
</p>

</p>

</p>

<hr>

<!-- TASKS -->
<h2>Your Tasks</h2>
<?php include 'tasks/fetch_tasks.php'; ?>
<p><a href="tasks/add_task.php">➕ Add New Task</a></p>

<hr>

<!-- NOTES -->
<h2>Your Notes</h2>
<?php include 'notes/fetch_notes.php'; ?>
<p><a href="notes/add_note.php">➕ Add New Note</a></p>

<hr>

<ul>
    <li><a href="pomodoro/timer.php">🍅 Pomodoro Timer</a></li>
    <li><a href="pomodoro/history.php">📊 Pomodoro History</a></li>
</ul>
<h3>📤 Export</h3>
<ul>
    <li><a href="exports/export_pomodoro_csv.php">Download CSV</a></li>
    <li><a href="exports/export_pomodoro_pdf.php">Download PDF</a></li>
</ul>
   <li><a href="logout.php">Logout</a></li>

</body>
</html>

