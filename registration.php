<?php
session_start();
require_once("connecting_database.php");

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password_data = $_POST['password'];
    $role = $_POST['role'];
    $conn->begin_transaction();

    try {
        // Check if username already exists in USERS table
        $stmt_check_username = $conn->prepare("SELECT username FROM USERS WHERE username = ?");
        $stmt_check_username->bind_param("s", $username);
        $stmt_check_username->execute();
        $result_username = $stmt_check_username->get_result();
        if ($result_username->num_rows > 0) {
            throw new Exception("Username already exists in USERS. Please choose a different username.");
        }
        $stmt_check_username->close();

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO USERS (name, username, password_data, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $username, $password_data, $role);
        $stmt->execute();
        if ($role === 'Student') {
            $stmt_student = $conn->prepare("INSERT INTO Student (sid, sname) VALUES (?, ?)");
            $stmt_student->bind_param("ss", $username, $name);
            $stmt_student->execute();
            $stmt_student->close();
        }
        $conn->commit();

        $_SESSION['name'] = $name;
        $_SESSION['role'] = $role;

        $stmt->close();
        $conn->close();

        echo "<script>
                alert('Registration successful!');
                window.location.href = 'landing.html';
              </script>";
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Registration failed: " . $e->getMessage();
        exit();
    }
}
?>
