<?php
// tasks/fetch_tasks.php

require_once __DIR__ . '/../config/db.php';


if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h3>Your Tasks</h3>
<?php if($result->num_rows > 0): ?>
    <ul>
    <?php while($task = $result->fetch_assoc()): ?>
     <li>
    <strong><?php echo htmlspecialchars($task['title']); ?></strong>
    <br>
    <?php echo htmlspecialchars($task['description']); ?>
    <br>

    Status: <strong><?php echo $task['status']; ?></strong><br>

  <form method="POST" action="tasks/toggle_status.php" style="display:inline;">
    <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <button type="submit">
        <?php echo ($task['status'] === 'pending') ? '✔ Mark as Completed' : '↩ Mark as Pending'; ?>
    </button>
</form>

    |
    <a href="tasks/edit_task.php?id=<?php echo $task['id']; ?>">Edit</a>
    |
    <a href="tasks/delete_task.php?id=<?php echo $task['id']; ?>"
       onclick="return confirm('Delete this task?');">
       Delete
    </a>
    

    
</li>
    <?php endwhile; ?>
    </ul>
<?php else: ?>
    <p>No tasks yet.</p>
<?php endif; ?>
