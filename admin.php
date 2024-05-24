<?php
// Start or resume a session
session_start();
require_once("connecting_database.php");

$name = $_SESSION['name'] ?? ''; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['createExam'])) {
        // Create new exam
        $eid = $_POST['eid'];
        $ename = $_POST['ename'];
        $fees = $_POST['fees'];
        $stmt = $conn->prepare("INSERT INTO Exam (eid, ename, fees) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $eid, $ename, $fees);
        if ($stmt->execute()) {
            echo "<script>alert('Exam created successfully!');</script>";
        } else {
            echo "<script>alert('Failed to create exam.');</script>";
        }
        $stmt->close();
    } elseif (isset($_POST['setQuestion'])) {
        // Set question for exam
        $eid = $_POST['eid'];
        $qid = $_POST['qid'];
        $qcontent = $_POST['qcontent'];
        $qsolution = $_POST['qsolution'];
        $difficulty = $_POST['difficulty'];
        $qexp = $_POST['qexp'];
        // 
        $stmt = $conn->prepare("INSERT INTO Question (qid, qcontent, qsolutions, difficulty, qexp) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $qid, $qcontent, $qsolution, $difficulty, $qexp);
        if ($stmt->execute()) {
            $stmt = $conn->prepare("INSERT INTO has_questions (eid, qid) VALUES (?, ?)");
            $stmt->bind_param("ss", $eid, $qid);
            if ($stmt->execute()) { 
                echo "<script>alert('Question set successfully!');</script>";
            } else {
                echo "<script>alert('Failed to set question.');</script>";
            }
        } else {
            echo "<script>alert('Failed to set question.');</script>";
        }
        $stmt->close();
    } elseif (isset($_POST['addslot'])) {
        // Add slot for exam
        $eid = $_POST['eid'];
        $did = $_POST['did'];
        $stmt = $conn->prepare("INSERT INTO has_dates (eid, did) VALUES (?, ?)");
        $stmt->bind_param("ss", $eid, $did);
        if ($stmt->execute()) {
            echo "<script>alert('Slot added successfully!');</script>";
        } else {
            echo "<script>alert('Failed to add slot.');</script>";
        }
        $stmt->close();
    } elseif (isset($_POST['setDateTime'])) {
        // Set date and time for exam
        $did = $_POST['did'];
        $date = $_POST['date'];
        $starttime = $_POST['starttime'];
        $endtime = $_POST['endtime'];
        $stmt = $conn->prepare("INSERT INTO dates (did, dates, starttime, endtime) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $did, $date, $starttime, $endtime);
        if ($stmt->execute()) {
            echo "<script>alert('Date and time set for exam successfully!');</script>";
        } else {
            echo "<script>alert('Failed to set date and time.');</script>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Admin Dashboard</title>
</head>

<body>
    <div style=" top: 0; left: 0; padding: 20px;">
    <center>
        <p><strong>Name:</strong> <?php echo $name; ?></p>
        <button onclick="window.location.href='landing.html';" style="padding: 5px; margin: 10px">Log Out</button>
    </center>

    </div>
    <h2>Create Exam</h2>
    <form method="post">
        <input type="text" name="eid" placeholder="Exam ID" required>
        <input type="text" name="ename" placeholder="Exam Name" required>
        <input type="integer" name="fees" placeholder="Fees" required>
        <center><button type="submit" style="padding: 5px; margin: 10px" name="createExam">Create Exam</button></center>
        <!-- <input type="submit" name="createExam" value="Create Exam"> -->
    </form>
    <h2>Add available Dates & timeslots</h2>
    <form method="post">
        <input type="text" name="did" placeholder="Date ID" required>
        <input type="date" name="date" required>
        <input type="time" name="starttime" required>
        <input type="time" name="endtime" required>
        <center><button type="submit" style="padding: 5px; margin: 10px" name="setDateTime">Set Date & Time</button></center>
    </form>
    <h2>Add slots for exam</h2>
    <form method="post">
        <select name="eid" required>
            <?php
            // Fetch exams
            $result = $conn->query("SELECT eid, ename FROM Exam");
            while ($row = $result->fetch_assoc()) {
                echo "<option value=\"{$row['eid']}\">{$row['ename']}</option>";
            }
            ?>
        </select>
        <select name="did" required>
            <?php
            // fetch dates and time slots
            $result = $conn->query("SELECT did, dates, starttime, endtime FROM dates");
            while ($row = $result->fetch_assoc()) {
                echo "<option value=\"{$row['did']}\">{$row['dates']} {$row['starttime']} {$row['endtime']}</option>";
            }
            ?>
        </select>
        <center><button type="submit" style="padding: 5px; margin: 10px" name="addslot">Add Slot</button></center>
    </form>
    <h2>Set Questions for Exam</h2>
    <form method="post">
        <select name="eid" required>
            <?php
            // Fetch exams
            $result = $conn->query("SELECT eid, ename FROM Exam");
            while ($row = $result->fetch_assoc()) {
                echo "<option value=\"{$row['eid']}\">{$row['ename']}</option>";
            }
            ?>
        </select>
        <input type="text" name="qid" placeholder="Question ID" required>
        <input type="text" name="qcontent" placeholder="Question" required>
        <input type="text" name="qsolution" placeholder="Solution" required>
        <p>Difficulty</p>
        <input type="range" min="1" max="10" value="5" name="difficulty" id="difficulty" oninput="updateValue(this.value)" required>
        <span id="difficultyValue">5</span>
        <script>
            function updateValue(value) {
                document.getElementById('difficultyValue').textContent = value;
            }
        </script>
        <br>
        <input type="integer" name="qexp" placeholder="marks" required>
        <center><button type="submit" style="padding: 5px; margin: 10px" name="setQuestion">Set Question</button></center>

    </form>
</body>

</html>