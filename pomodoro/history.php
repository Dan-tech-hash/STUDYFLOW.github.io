<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Auth check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// CSRF token for AJAX
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch today summary
$today = date('Y-m-d');
$summaryStmt = $conn->prepare("
    SELECT 
        session_type,
        SUM(duration) AS total_seconds
    FROM pomodoro_sessions
    WHERE user_id = ?
      AND DATE(created_at) = ?
    GROUP BY session_type
");
$summaryStmt->bind_param("is", $user_id, $today);
$summaryStmt->execute();
$summaryResult = $summaryStmt->get_result();

$studySeconds = 0;
$breakSeconds = 0;
while ($row = $summaryResult->fetch_assoc()) {
    if ($row['session_type'] === 'study') $studySeconds = $row['total_seconds'];
    else $breakSeconds = $row['total_seconds'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pomodoro Timer</title>
</head>
<body>

<h2>🍅 Pomodoro Timer</h2>

<!-- Custom Timer Settings -->
<h3>Customize Timer</h3>
<form id="timer-settings">
    <label>Study Duration (minutes):</label>
    <input type="number" id="studyDuration" min="1" value="25"><br><br>

    <label>Break Duration (minutes):</label>
    <input type="number" id="breakDuration" min="1" value="5"><br><br>

    <button type="button" onclick="applySettings()">Apply</button>
</form>

<hr>

<p id="mode">Study Time</p>
<h1 id="time">25:00</h1>

<button onclick="startTimer()">Start</button>
<button onclick="pauseTimer()">Pause</button>
<button onclick="resetTimer()">Reset</button>

<p><a href="../dashboard.php">⬅ Back to Dashboard</a></p>
<input type="hidden" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

<hr>

<h3>📚 Today’s Summary</h3>
<ul>
    <li>Study Time: <?php echo floor($studySeconds / 60); ?> minutes</li>
    <li>Break Time: <?php echo floor($breakSeconds / 60); ?> minutes</li>
</ul>

<script>
let studyTime = 25 * 60;
let breakTime = 5 * 60;
let timeLeft = studyTime;
let timer = null;
let isStudy = true;
let sessionStartTime = null;
let currentSessionType = 'study';

function updateDisplay() {
    let minutes = Math.floor(timeLeft / 60);
    let seconds = timeLeft % 60;
    document.getElementById("time").textContent =
        String(minutes).padStart(2, '0') + ":" +
        String(seconds).padStart(2, '0');
}

function applySettings() {
    const studyInput = parseInt(document.getElementById('studyDuration').value);
    const breakInput = parseInt(document.getElementById('breakDuration').value);

    if (studyInput > 0) studyTime = studyInput * 60;
    if (breakInput > 0) breakTime = breakInput * 60;

    resetTimer();
}

function startTimer() {
    if (timer !== null) return;

    if (!sessionStartTime) {
        sessionStartTime = Date.now();
        currentSessionType = isStudy ? 'study' : 'break';
    }

    timer = setInterval(() => {
        timeLeft--;

        if (timeLeft <= 0) {
            endSession(); // Save session
            switchMode();
        }

        updateDisplay();
    }, 1000);
}

function pauseTimer() {
    clearInterval(timer);
    timer = null;
}

function resetTimer() {
    clearInterval(timer);
    timer = null;
    isStudy = true;
    timeLeft = studyTime;
    document.getElementById("mode").textContent = "Study Time";
    updateDisplay();
    sessionStartTime = null;
}

function switchMode() {
    clearInterval(timer);
    timer = null;

    if (isStudy) {
        alert("Study done! Break time!");
        isStudy = false;
        timeLeft = breakTime;
        document.getElementById("mode").textContent = "Break Time";
    } else {
        alert("Break finished! Back to study!");
        isStudy = true;
        timeLeft = studyTime;
        document.getElementById("mode").textContent = "Study Time";
    }
}

function endSession() {
    if (!sessionStartTime) return;

    let durationSeconds = Math.floor((Date.now() - sessionStartTime) / 1000);
    saveSession(currentSessionType, durationSeconds);
    sessionStartTime = null;
}

function saveSession(type, duration) {
    fetch("save_session.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body:
            "session_type=" + type +
            "&duration=" + duration +
            "&csrf_token=" + document.getElementById("csrf_token").value
    }).then(res => res.text())
      .then(res => console.log("Session saved:", res))
      .catch(err => console.error(err));
}

window.addEventListener("beforeunload", function () {
    endSession();
});

updateDisplay();
</script>

</body>
</html>
