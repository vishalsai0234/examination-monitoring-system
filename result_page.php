<?php
// Retrieve exam ID and question ID from URL parameters
$eid = $_GET['eid'] ?? '';
$qid = $_GET['qid'] ?? '';

// Check if the user is logged in
session_start();
if (!isset($_SESSION['id'])) {
    header('Location: landing.html');
    exit();
}

// Include database connection
require_once("connecting_database.php");

// Fetch exam name
$stmt_exam_name = $conn->prepare("SELECT ename FROM exam WHERE eid = ?");
$stmt_exam_name->bind_param("s", $eid);
$stmt_exam_name->execute();
$result_exam_name = $stmt_exam_name->get_result();
$exam_name_row = $result_exam_name->fetch_assoc();
$exam_name = ($exam_name_row) ? $exam_name_row['ename'] : 'Unknown Exam'; // Provide a default value if exam name is null
$stmt_exam_name->close();

// Fetch details of student's submissions along with relevant question details
$stmt = $conn->prepare("SELECT q.qid, q.qcontent, q.qsolutions, q.difficulty, q.qexp, s.answer AS user_answer, s.time_taken FROM submissions s INNER JOIN question q ON s.qid = q.qid WHERE s.eid = ? AND s.sid = ?");
$stmt->bind_param("ss", $eid, $_SESSION['id']);
$stmt->execute();
$results = $stmt->get_result();


// Calculate total marks scored and count correct and wrong answers
$total_marks = 0;
$correct_answers = 0;
$wrong_answers = 0;

$answers_details = array();

while ($row = $results->fetch_assoc()) {
    $answers_details[] = $row;

    if ($row['user_answer'] === $row['qsolutions']) {
        $correct_answers++;
        $total_marks += $row['qexp'];
    } else {
        $wrong_answers++;
    }
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f5f5f5;
        }
    </style>
    <title>Exam Result - <?php echo $exam_name; ?></title>
</head>

<body>
    <div class="container">
        <h1>Exam Result - <?php echo $exam_name; ?></h1>
        <h2>Total Marks Scored: <?php echo $total_marks; ?></h2>
        <h3>Correct Answers: <?php echo $correct_answers; ?></h3>
        <h3>Wrong Answers: <?php echo $wrong_answers; ?></h3>

        <h2>Answers Details</h2>
        <table>
            <tr>
                <th>Question</th>
                <th>Solution</th>
                <th>Difficulty</th>
                <th>User Answer</th>
                <th>Scored Marks</th>
                <th>Time(sec)</th>
            </tr>
            <?php foreach ($answers_details as $detail) : ?>
                <tr>
                    <td><?php echo $detail['qcontent']; ?></td>
                    <td><?php echo $detail['qsolutions']; ?></td>
                    <td><?php echo $detail['difficulty']; ?></td>
                    <td><?php echo $detail['user_answer']; ?></td>
                    <td><?php echo ($detail['user_answer'] === $detail['qsolutions']) ? $detail['qexp'] : 0; ?></td>
                    <td><?php echo $detail['time_taken']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <button onclick="window.location.href = 'student.php';" style="padding: 5px; margin: 10px">Back to Student Dashboard</button>
    </div>
</body>

</html>

