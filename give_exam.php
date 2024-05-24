<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once("connecting_database.php");

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: landing.html');
    exit();
}

// Initialize session variables if not already initialized
if (!isset($_SESSION['answered_questions'])) {
    $_SESSION['answered_questions'] = [];
}

$sid = $_SESSION['id'] ?? '';
$name = $_SESSION['name'] ?? '';

$eid = $_GET['eid'] ?? '';
$_SESSION['eid'] = $eid;

$dates = $_GET['dates'] ?? '';
$starttime = strtotime($_GET['starttime'] ?? '');
$endtime = strtotime($_GET['endtime'] ?? '');

// Fetch questions for the selected exam
$stmt = $conn->prepare("SELECT q.qid, q.qcontent, q.qexp FROM question q INNER JOIN has_questions hq ON q.qid = hq.qid WHERE hq.eid = ?");
$stmt->bind_param("s", $eid);
$stmt->execute();
$questionsResult = $stmt->get_result();

// Store questions in an array for later use
$questions = [];
while ($row = $questionsResult->fetch_assoc()) {
    $questions[] = $row;
}

$stmt->close();

// Determine the current question
$current_question_index = isset($_GET['question_index']) ? intval($_GET['question_index']) : 0;
$current_question = isset($questions[$current_question_index]) ? $questions[$current_question_index] : null;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Store the submitted answer
    $answer = $_POST['answer'] ?? '';
    $qid = $_POST['qid'] ?? '';
    $start_time = $_POST['start_time'] ?? 0; // Get the start time from the hidden input field
    $time_taken = time() - $start_time; // Calculate time taken for this question

    // Insert submission into the database
    $stmt = $conn->prepare("INSERT INTO submissions (eid, qid, sid, answer, time_taken) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $eid, $qid, $sid, $answer, $time_taken);
    $stmt->execute();

    // Check for errors
    if ($stmt->error) {
        echo "Error: " . $stmt->error;
    } else {
        $_SESSION['answered_questions'][] = $qid;
        $stmt->close();

        // Move to the next question or redirect to the result page if all questions have been answered
        $next_question_index = $current_question_index + 1;
        if ($next_question_index < count($questions)) {
            header("Location: give_exam.php?eid=$eid&dates=$dates&starttime=$starttime&endtime=$endtime&question_index=$next_question_index");
            exit();
        } else {
            // Redirect to the result page with exam ID and question ID
            header("Location: result_page.php?eid=$eid");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Exam - <?php echo $eid; ?></title>
</head>

<body>
    <div class="container">
        <h1>Welcome, <?php echo $name; ?>!</h1>
        <h2>Exam: <?php echo $eid; ?></h2>
        <h3>Date: <?php echo $dates; ?></h3>
        <h3>Time Remaining: <span id="timer"></span></h3>
        <?php if ($current_question) : ?>

            <div class="question">

                <form id="examForm" action="" method="post">
                    <h3>Question <?php echo $current_question_index + 1; ?>:</h3>
                    <p><?php echo $current_question['qcontent']; ?></p>
                    <input type="hidden" name="qid" value="<?php echo $current_question['qid']; ?>">
                    <input type="hidden" name="start_time" value="<?php echo time(); ?>"> <!-- Hidden input field to store start time -->
                    <textarea name="answer" placeholder="Your answer..."></textarea>
                    <p><strong>Full Marks:</strong> <?php echo $current_question['qexp']; ?></p>
                    <input type="submit" value="Submit Answer">
                </form>
            </div>
        <?php else : ?>
            <p>No more questions to display.</p>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var startTime = <?php echo json_encode($starttime); ?>;
            var endTime = <?php echo json_encode($endtime); ?>;
            var duration = endTime - startTime;

            var display = document.getElementById('timer');

            // Function to start the timer
            function startTimer(duration, display) {
                var timer = duration;
                setInterval(function() {
                    var hours = parseInt(timer / 3600, 10);
                    var minutes = parseInt((timer % 3600) / 60, 10);
                    var seconds = parseInt(timer % 60, 10);

                    hours = hours < 10 ? "0" + hours : hours;
                    minutes = minutes < 10 ? "0" + minutes : minutes;
                    seconds = seconds < 10 ? "0" + seconds : seconds;

                    display.textContent = hours + ":" + minutes + ":" + seconds;

                    // Store current timer value in localStorage
                    localStorage.setItem('timerValue', display.textContent);

                    if (--timer < 0) {
                        clearInterval(timer);
                        document.getElementById("examForm").submit();
                    }
                }, 1000);
            }

            // Retrieve timer value from localStorage or use default duration
            var storedTimerValue = localStorage.getItem('timerValue');
            if (storedTimerValue) {
                display.textContent = storedTimerValue;
                var remainingTime = getRemainingTime(storedTimerValue);
                startTimer(remainingTime, display);
            } else {
                startTimer(duration, display);
            }

            // Function to convert timer value to remaining time in seconds
            function getRemainingTime(timerValue) {
                var timeParts = timerValue.split(":");
                var hours = parseInt(timeParts[0]);
                var minutes = parseInt(timeParts[1]);
                var seconds = parseInt(timeParts[2]);
                return hours * 3600 + minutes * 60 + seconds;
            }
            
        });
    </script>
</body>

</html>
