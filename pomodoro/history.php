<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Auth check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch pomodoro sessions
$stmt = $conn->prepare("
    SELECT session_type, duration, created_at
    FROM pomodoro_sessions
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 50
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pomodoro History</title>
</head>
<body>

<h2>📊 Pomodoro History</h2>

<p><a href="../dashboard.php">⬅ Back to Dashboard</a></p>

<?php if ($result->num_rows === 0): ?>
    <p>No Pomodoro sessions yet.</p>
<?php else: ?>
<table border="1" cellpadding="8">
    <tr>
        <th>Date</th>
        <th>Type</th>
        <th>Duration</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo date("M d, Y H:i", strtotime($row['created_at'])); ?></td>
            <td><?php echo ucfirst($row['session_type']); ?></td>
            <td><?php echo floor($row['duration'] / 60); ?> min</td>
        </tr>
    <?php endwhile; ?>
</table>
<?php endif; ?>

</body>
</html>
