<?php
session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pomodoro Timer</title>
</head>
<body>

<h2>🍅 Pomodoro Timer</h2>

<p id="mode">Study Time</p>
<h1 id="time">25:00</h1>

<button onclick="startTimer()">Start</button>
<button onclick="pauseTimer()">Pause</button>
<button onclick="resetTimer()">Reset</button>

<p><a href="../dashboard.php">⬅ Back to Dashboard</a></p>

<input type="hidden" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

<script>
let studyTime = 25 * 60;
let breakTime = 5 * 60;
let timeLeft = studyTime;
let timer = null;
let isStudy = true;

function updateDisplay() {
    let minutes = Math.floor(timeLeft / 60);
    let seconds = timeLeft % 60;
    document.getElementById("time").textContent =
        String(minutes).padStart(2, '0') + ":" +
        String(seconds).padStart(2, '0');
}

function saveSession(type, duration) {
    fetch("save_session.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
            csrf_token: document.getElementById("csrf_token").value,
            session_type: type,
            duration: duration
        })
    });
}

function startTimer() {
    if (timer !== null) return;

    timer = setInterval(() => {
        timeLeft--;

        if (timeLeft <= 0) {
            clearInterval(timer);
            timer = null;

            if (isStudy) {
                saveSession("study", studyTime);
                alert("Study time done! Break time!");

                isStudy = false;
                timeLeft = breakTime;
                document.getElementById("mode").textContent = "Break Time";
            } else {
                saveSession("break", breakTime);
                alert("Break finished! Back to study!");

                isStudy = true;
                timeLeft = studyTime;
                document.getElementById("mode").textContent = "Study Time";
            }
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
}

updateDisplay();
</script>

</body>
</html>
