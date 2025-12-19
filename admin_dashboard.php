<?php
// admin_dashboard.php
session_start();
require 'config/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Get username from session
$username = $_SESSION['username'];

// ===============================
// Fetch Pomodoro analytics for all normal users (Top 5)
// ===============================
$analytics_stmt = $conn->prepare("
    SELECT u.username, 
           COUNT(p.id) AS total_sessions, 
           SUM(p.duration) AS total_seconds
    FROM users u
    LEFT JOIN pomodoro_sessions p 
           ON u.id = p.user_id AND p.session_type = 'study'
    WHERE u.role = 'user'
    GROUP BY u.id
    ORDER BY total_seconds DESC
    LIMIT 5
");
$analytics_stmt->execute();
$analytics_result = $analytics_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - StudyFlow</title>
</head>
<body>
    <h2>Welcome Admin, <?php echo htmlspecialchars($username); ?>!</h2>
    <p>This is the admin dashboard.</p>

    <ul>
        <li><a href="#">Manage Users (Coming Soon)</a></li>
        <li><a href="#">Manage Tasks & Notes (Coming Soon)</a></li>
        <li><a href="#">View Reports / Full Analytics (Coming Soon)</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>

    <hr>

    <!-- Admin Analytics Dashboard -->
    <h3>📊 Pomodoro Analytics (Top 5 Users)</h3>

    <?php if ($analytics_result->num_rows === 0): ?>
        <p>No Pomodoro data yet.</p>
    <?php else: ?>
    <table border="1" cellpadding="8">
        <tr>
            <th>Username</th>
            <th>Total Sessions</th>
            <th>Total Study Time</th>
        </tr>
        <?php while ($row = $analytics_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo $row['total_sessions']; ?></td>
                <td>
                    <?php
                    $hours = floor(($row['total_seconds'] ?? 0) / 3600);
                    $minutes = floor((($row['total_seconds'] ?? 0) % 3600) / 60);
                    echo $hours . "h " . $minutes . "m";
                    ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <?php endif; ?>

</body>
</html>
