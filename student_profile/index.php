<?php
    session_start();
    require ('../server.php');
    include('../components/navbar.php');
    if(isset($_POST['logout'])){
        session_destroy();
        header('location: /AdvisorHub/login');
    }

    if(empty($_SESSION['username'])){
        header('location: /AdvisorHub/login');
    }

    if(isset($_POST['profile'])){
        header('location: /AdvisorHub/profile');
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../Logo.jpg">
</head>
<body>
    <?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'Teams'])?>

    <?php
        if(isset($_SESSION['profileInbox'])){
            $student_id = $_SESSION['profileInbox'];
            $sql = "SELECT * FROM student_profile WHERE student_profile_id = '$student_id'";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            //ถ้ามี profile 
            if(isset($row['id'])){
                $interests = $row['interests'];

                $sql = "SELECT * FROM student WHERE student_id = '$student_id'";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();

                $first_name = $row['student_first_name'];
                $last_name = $row['student_last_name'];
                $tel = $row['student_tel'];
                $email = $row['student_email'];
                $department = $row['department'];

                echo 
                "
                <div class='container'>
                    
                    <div class='profile-info'>
                    <h2>$first_name $last_name</h2>
                    <p>Department $department</p>
                    </div>
                    <div class='contact-info'>
                        <h3>Contact</h3>
                        <p><strong>Email:</strong> $email</p>
                        <p><strong>Telephone Number:</strong> $tel</p>
                        </div>

                        <!-- ข้อมูลหัวข้อวิจัย -->
                        <div class='research-info'>
                        <h3>Interested research topics</h3>
                        <p>" . nl2br($interests) . "</p>
                    </div>
                </div>
                ";
            }else{
                echo 
                "
                <div class='profile-not-found'>
                    <h1>This user don't have profile</h1>
                </div>
                ";
            }
        }
        
    ?>

</body>
</html>