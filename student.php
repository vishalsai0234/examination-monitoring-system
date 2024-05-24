<?php

session_start();
require_once("connecting_database.php");
if (!isset($_SESSION['id'])) {
    header('Location: landing.html');
    exit();
}


$sid = $_SESSION['id'] ?? '';
$name = $_SESSION['name'] ?? '';

$phone = '';

if (!empty($sid)) {
    $stmt = $conn->prepare("SELECT phno FROM Student WHERE sid = ?");
    $stmt->bind_param("s", $sid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $phone = $row['phno']; // Assign fetched phone number
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check what form is submitted
    if (isset($_POST['updatePhone'])) {
        $newPhone = $_POST['phone'];
        // Update phone number
        $stmt = $conn->prepare("UPDATE Student SET phno = ? WHERE sid = ?");
        $stmt->bind_param("ss", $newPhone, $sid);
        $stmt->execute();
        $phone = $newPhone; // Update the phone variable to reflect the change
        echo "<script>alert('Phone number updated successfully!');</script>";
    } elseif (isset($_POST['registerExam'])) {
        $eid = $_POST['exam'];
        // Register for exam
        $stmt = $conn->prepare("INSERT INTO Register (sid, eid) VALUES (?, ?)");
        $stmt->bind_param("ss", $sid, $eid);
        $stmt->execute();
        echo "<script>alert('Registered for exam successfully!');</script>";
    }
    elseif(isset($_POST['chooseSlot'])) {
        // add to choose table did and sid
        $did = $_POST['examSlot'];
        $stmt = $conn->prepare("INSERT INTO choose (sid, did) VALUES (?, ?)");
        $stmt->bind_param("ss", $sid, $did);
        $stmt->execute();
        echo "<script>alert('Slot chosen successfully!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Student Dashboard</title>

</head>

<body>
    <center>
        <div style="padding: 20px;">
            <p><strong>SID:</strong> <?php echo $sid; ?></p>
            <p><strong>Name:</strong> <?php echo $name; ?></p>
            <p><strong>Phone Number:</strong> <?php echo $phone; ?></p>
            <button onclick="window.location.href='landing.html';" style="padding: 5px; margin: 10px">Log Out</button>
        </div>
    </center>
    <h2>Update Phone Number</h2>
    <form method="post">
        <input type="text" name="phone" placeholder="New Phone Number" required>
        <button type="submit" style="padding: 5px; margin: 10px" name="updatePhone">Update Phone</button>
    </form>

    <h2>Register for Exam</h2>
    <form method="post">
        <select name="exam" required>
            <?php
            // add default option
            echo "<option value=''>Select Exam</option>";
            // Fetch available exams 
            $stmt = $conn->prepare("SELECT eid, ename FROM Exam");
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['eid'] . "'>" . $row['ename'] . "</option>";
                }
            }
            $stmt->close();
            ?>
        </select>
        <button type="submit" style="padding: 5px; margin: 10px" name="registerExam">Register</button>
    </form>
    <h2>Choose exam time and dates</h2>
    <form method="post">
        <select name="registeredExam" id="registeredExam" required>
            <?php
            // add default option
            echo "<option value=''>Select Exam</option>";
            // Fetch exams registered by the student
            $stmt = $conn->prepare("SELECT Exam.eid, Exam.ename FROM Exam INNER JOIN Register ON Exam.eid = Register.eid WHERE Register.sid = ?");
            $stmt->bind_param("s", $sid);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['eid'] . "'>" . $row['ename'] . "</option>";
                }
            }
            $stmt->close();
            ?>
        </select>
        <select name="examSlot" id="examSlot" required>
            <!-- Options will be populated based on the selected exam using JavaScript -->
        </select>
        <button type="submit" style="padding: 5px; margin: 10px" name="chooseSlot">Choose Slot</button>
    </form>

    <h2>Select Exam to take</h2>
    <!-- Show all registered exams and their time slot -->
    <!-- create button having content to show that all available registered exams with time slot and on click redirect you to exam page -->
    <div>
        <ul>
            <!-- php to show registered exams -->
            <?php
            $stmt = $conn->prepare("SELECT exam.eid, exam.ename, dates.dates, dates.starttime, dates.endtime
           FROM exam
           INNER JOIN has_dates ON exam.eid = has_dates.eid
           INNER JOIN dates ON has_dates.did = dates.did
           WHERE exam.eid IN (
               SELECT register.eid
               FROM register
               INNER JOIN choose ON register.sid = choose.sid AND has_dates.did = choose.did
               WHERE register.sid = ?
           )");
            $stmt->bind_param("s", $sid);
            $stmt->execute();
            $stmt->bind_result($eid, $ename, $dates, $starttime, $endtime);
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Check if the exam has already been taken
                    $taken_stmt = $conn->prepare("SELECT * FROM submissions WHERE eid = ? AND sid = ?");
                    $taken_stmt->bind_param("ss", $row['eid'], $sid);
                    $taken_stmt->execute();
                    $taken_result = $taken_stmt->get_result();
                    if ($taken_result->num_rows === 0) {
                        // Exam not taken yet
                        $href = "give_exam.php?eid=" . $row['eid'] . "&dates=" . $row['dates'] . "&starttime=" . $row['starttime'] . "&endtime=" . $row['endtime'];
                        echo "<li><a href=" . $href . ">" . $row['ename'] . " - " . $row['dates'] . " " . $row['starttime'] . " - " . $row['endtime'] . "</a></li>";
                    } else {
                        // Exam already taken, disable link
                        echo "<li>" . $row['ename'] . " - " . $row['dates'] . " " . $row['starttime'] . " - " . $row['endtime'] . " (Exam Taken)</li>";
                    }
                    $taken_stmt->close();
                }
            } else {
                echo "<li>No exams available.</li>";
            }
            $stmt->close();
            ?>
        </ul>
    </div>
    
    <!-- Add the result link here -->
    <h2>View Exam Results</h2>
    <div>
        <ul>
            <?php
            $stmt = $conn->prepare("SELECT exam.eid, exam.ename FROM exam INNER JOIN register ON exam.eid = register.eid WHERE register.sid = ?");
            $stmt->bind_param("s", $sid);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $taken_stmt = $conn->prepare("SELECT * FROM submissions WHERE eid = ? AND sid = ?");
                    $taken_stmt->bind_param("ss", $row['eid'], $sid);
                    $taken_stmt->execute();
                    $taken_result = $taken_stmt->get_result();
                    if ($taken_result->num_rows > 0) {
                        echo "<li><a href='result_page.php?eid=" . $row['eid'] . "'>" . $row['ename'] . " Results</a></li>";
                    }
                    $taken_stmt->close();
                }
            } else {
                echo "<li>No exam results available.</li>";
            }
            $stmt->close();
            ?>
        </ul>
    </div>
    <script>
        document.getElementById('registeredExam').addEventListener('change', function() {
            var examId = this.value;
            var slotSelect = document.getElementById('examSlot');
            slotSelect.innerHTML = ''; // Clear previous options more efficiently

            fetch('fetch_slots.php?examId=' + examId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    data.forEach(slot => {
                        var option = document.createElement('option');
                        option.value = slot.did;
                        option.text = slot.dates + ' ' + slot.starttime + ' - ' + slot.endtime;
                        slotSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching slots:', error);
                    // Optionally, inform the user that an error occurred.
                });
        });
    </script>


</body>

</html>

