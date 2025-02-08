<?php 

    session_start();
    include('../components/navbar.php');
    if(isset($_POST['logout'])){
        session_destroy();
        header('location: /AdvisorHub/login');
    }

    if(isset($_POST['profile'])){
        header('location: /AdvisorHub/profile');
    }

    if(empty($_SESSION['username'])){
        header('location: /AdvisorHub/login');
    }

    if(isset($_SESSION['username']) && $_SESSION['role'] == 'student' || $_SESSION['role'] == 'admin'){
        header('location: /AdvisorHub/advisor');
    }

    date_default_timezone_set("Asia/Bangkok"); 

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อนุมัติคำร้อง</title>
    <link rel="icon" href="../Logo.png">
    <link rel="stylesheet" href="style_details.css">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

    <?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'Teams'])?>

    <div class="container">
        <h1>รายละเอียดคำร้องขอแต่งตั้งอาจารย์ที่ปรึกษา</h1>
        <div class="details">
            <?php
                if(isset($_GET['id'])) {
                    $id = $_GET['id'];
                    $students = [
                        "1" => [
                            ["name" => "aaaa aaa", "id" => "123456789", "program" => "it", "topic" => "การพัฒนาโปรแกรมจัดการข้อมูล", "topic_en" => "Development of a Data Management System", "research" => "การศึกษาการออกแบบและพัฒนาเว็บแอปพลิเคชันสำหรับจัดการข้อมูลอย่างมีประสิทธิภาพ โดยใช้ HTML, CSS, JavaScript และฐานข้อมูล MySQL"],
                            ["name" => "aabb aba", "id" => "123459876", "program" => "it", "topic" => "การพัฒนาโปรแกรมจัดการข้อมูล", "topic_en" => "Development of a Data Management System", "research" => "การศึกษาการออกแบบและพัฒนาเว็บแอปพลิเคชันสำหรับจัดการข้อมูลอย่างมีประสิทธิภาพ โดยใช้ HTML, CSS, JavaScript และฐานข้อมูล MySQL"]
                        ],
                        "2" => [
                            ["name" => "bbbb bbb", "id" => "987654321", "program" => "cs", "topic" => "การพัฒนา AI สำหรับคัดกรองเอกสาร", "topic_en" => "AI-Based Document Filtering System", "research" => "      "],
                        ],
                        "3" => [
                            ["name" => "cccc ccc", "id" => "112233445", "program" => "cs", "topic" => "ระบบจัดเก็บข้อมูลออนไลน์อัจฉริยะ", "topic_en" => "Intelligent Online Data Storage System", "research" => "              "],
                            ["name" => "ccdd cdc", "id" => "554433221", "program" => "cs", "topic" => "ระบบจัดเก็บข้อมูลออนไลน์อัจฉริยะ", "topic_en" => "Intelligent Online Data Storage System", "research" => "              "]
                        ],
                        "4" => [
                            ["name" => "dddd ddd", "id" => "556677889", "program" => "it", "topic" => "การวิเคราะห์ข้อมูลขนาดใหญ่", "topic_en" => "Big Data Analysis", "research" => "      "],
                        ],
                    ];
                    if(isset($students[$id])) {
                        foreach($students[$id] as $student) {
                            echo "<p><strong>ชื่อ:</strong> {$student['name']} <strong>รหัสนิสิต:</strong> {$student['id']} <strong>สาขา:</strong> {$student['program']}</p>";
                        }
                        $firstStudent = $students[$id][0];
                        echo "<p><strong>หัวข้อวิจัยภาษาไทย:</strong> {$firstStudent['topic']}</p>";
                        echo "<p><strong>หัวข้อวิจัยภาษาอังกฤษ:</strong> {$firstStudent['topic_en']}</p>";
                        echo "<p><strong>รายละเอียดงานวิจัย:</strong> {$firstStudent['research']}</p>";
                        echo "<p><strong></strong> " . date("Y-m-d H:i:s") . "</p>";
                    }
                }
            ?>
        </div>
        <div class="button-container">
            <button class="button-accept"><i class='bx bx-check' ></i></button>
            <button class="button-reject"><i class='bx bx-x' ></i></button>
        </div>
        <a href="request.php">
            <button class="button-back"><i class='bx bx-arrow-back' ></i></button>
        </a>
        <button class="button-chat"><i class='bx bxs-message-dots'></i></button>
        
    </div>

</body>
</html>
