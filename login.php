<?php
    session_start();
    require_once("connecting_database.php");
     
    if($_SERVER['REQUEST_METHOD']=='POST'){
        $username=$_POST['username'];
        $password_data=$_POST['password'];

        $sql = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $sql->bind_param("s", $username);
        $sql->execute();
        $result = $sql->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($password_data==$row['password_data']) {
                $_SESSION['id'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['name'] = $row['name'];
                switch ($row['role']) {
                    case 'Student':
                        header('Location: student.php');
                        break;
                    case 'Admin':
                        header('Location: admin.php');
                        break;
                    default:
                        header('Location: error.html' );
                        break;
                }
            } else {
                echo  "<script>
                alert('Invalid password!');
                window.location.href = 'landing.html';
              </script>";
            }
        } else {
            echo  "<script>
                alert('Invalid username!');
                window.location.href = 'landing.html';
              </script>";
        }
    
        $sql->close();
        $conn->close();

    }
?>