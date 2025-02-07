<?php
session_start();
include('../components/navbar.php');
require('../server.php');
if (isset($_POST['logout'])) {
    session_destroy();
    header('location: /AdvisorHub/login');
}

if (isset($_POST['profile'])) {
    header('location: /AdvisorHub/profile');
}

if (empty($_SESSION['username'])) {
    header('location: /AdvisorHub/login');
}

if (isset($_POST['chat'])) {
    $_SESSION['receiver_id'] = $_POST['chat'];
    header('location: /AdvisorHub/topic_chat/topic_chat.php');
}

if (isset($_POST['advisor_request'])) {
    $_SESSION['advisor_id'] = $_POST['advisor_request'];
    header('location: /AdvisorHub/request/');
}

if (isset($_POST['thesis'])) {
    $_SESSION['advisor_id'] = $_POST['thesis'];
    header('location: /AdvisorHub/thesis/thesis_history.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advisor List</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../Logo.jpg">
</head>

<body>

    <?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'file']) ?>

    <?php
    if (isset($_POST['info'])) {
        $advisor_id = $_POST['info'];
        $sql = "SELECT * FROM advisor_profile WHERE advisor_id = '$advisor_id'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();

        $advisor_id = $row['advisor_id'];
        $expertise = json_decode($row['expertise']);
        $interests = $row['interests'];
        $img = $row['img'];

        $sql = "SELECT * FROM advisor WHERE id = '$advisor_id'";
        $result_advisor = $conn->query($sql);
        $row_advisor = $result_advisor->fetch_assoc();

        $first_name = $row_advisor['first_name'];
        $last_name = $row_advisor['last_name'];
        $tel = $row_advisor['tel'];
        $email = $row_advisor['email'];

        //นับจำนวนนักศึกษาที่ให้คำปรึกษา
        $sql = "
                SELECT SUM(JSON_LENGTH(student_id)) AS student_count
                FROM advisor_request
                WHERE advisor_id = '$advisor_id'
                AND is_advisor_approved = 1
                AND is_admin_approved = 1
                AND time_stamp >= STR_TO_DATE(CONCAT(YEAR(CURDATE()) - 1, '-11-01'), '%Y-%m-%d')
                AND time_stamp <= STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-10-31'), '%Y-%m-%d');

            ";

        $result = $conn->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            $student_count = $row['student_count'];
        } else {
            $student_count = 0;
        }

        echo
        "
            <div class='container'>
                    
                    <div class='profile-info'>
                    <img src= '$img' >
                    <h2>$first_name $last_name</h2>
                    </div>

                    
                    <div class='contact-info'>
                        <h3>Contact</h3>
                        <p><strong>Email:</strong> $email</p>
                        <p><strong>Telephone Number:</strong> $tel</p>
                        </div>

                        <!-- ข้อมูลหัวข้อวิจัย -->
                        <div class='research-info'>
                        <h3>Expertise</h3>
                        ";
        foreach ($expertise as $item) {
            echo "<p>$item</p>";
        }
        echo "
                        <h3>Interests</h3>
                        <p>" . nl2br($interests) . "</p>
                        <h3>Number of advising students: $student_count</h3>
                    </div>
                    <form action='' method='post' class='nav-form'>
                        <button name = 'advisor_request' value='$advisor_id'><i class='bx bx-highlight'></i></button> 
                        <button name = 'thesis' value='$advisor_id'><i class='bx bx-history'></i></button>
                        <button name='chat' value='$advisor_id'><i class='bx bxs-message-dots'></i></button>
                    </form>
                </div>
            ";
    }
    ?>



</body>

</html>