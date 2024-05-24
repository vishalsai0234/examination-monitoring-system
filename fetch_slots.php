<?php
// Start session and include database connection
session_start();
require_once("connecting_database.php");

// Check if the user is logged in and the examId is provided
if (!isset($_SESSION['id']) || !isset($_GET['examId'])) {
    echo json_encode(array('error' => 'Unauthorized access or missing parameters'));
    exit();
}

$examId = $_GET['examId'];

// Prepare SQL statement to fetch slots
$stmt = $conn->prepare("SELECT dates.did, dates.dates, dates.starttime, dates.endtime FROM has_dates INNER JOIN dates ON has_dates.did = dates.did WHERE has_dates.eid = ?");
$stmt->bind_param("s", $examId);
$stmt->execute();
$result = $stmt->get_result();

$slots = array();

// Fetch the data and store it in an array
while ($row = $result->fetch_assoc()) {
    $slots[] = array(
        'did' => $row['did'],
        'dates' => $row['dates'],
        'starttime' => $row['starttime'],
        'endtime' => $row['endtime']
    );
}
// Close the statement
$stmt->close();

// Set header to application/json for proper client-side handling
header('Content-Type: application/json');
echo json_encode($slots);
?>
