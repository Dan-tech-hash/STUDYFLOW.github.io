<?php

require_once __DIR__ . '/../config/db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    echo "Please log in to see your notes.";
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all notes for this user
$stmt = $conn->prepare("SELECT * FROM notes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if any notes exist
if ($result->num_rows === 0) {
    echo "<p>No notes yet. <a href='add_note.php'>Add a note</a></p>";
} else {
    echo "<ul>";
    while ($note = $result->fetch_assoc()) {
        echo "<li>";
        echo "<strong>" . htmlspecialchars($note['title']) . "</strong><br>";
        echo htmlspecialchars($note['content']) . "<br>";
        echo "<small>Created: " . $note['created_at'] . "</small><br>";
      
        
        // Actions: Edit & Delete
        echo "<a href='notes/edit_note.php?id=" . $note['id'] . "'>Edit</a> | ";

        // Delete form with CSRF
        echo "<form method='POST' action='notes/delete_note.php' style='display:inline;'>
                <input type='hidden' name='id' value='" . $note['id'] . "'>
                <input type='hidden' name='csrf_token' value='" . $_SESSION['csrf_token'] . "'>
                <button type='submit' onclick=\"return confirm('Delete this note?');\">Delete</button>
              </form>";

        echo "</li><hr>";
    }
    echo "</ul>";
}
