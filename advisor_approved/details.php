<?php 
session_start();
include('../components/navbar.php');
include('../server.php'); 

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

if(isset($_SESSION['username']) && ($_SESSION['role'] == 'student' || $_SESSION['role'] == 'admin')){
    header('location: /AdvisorHub/advisor');
}

date_default_timezone_set("Asia/Bangkok"); 
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดคำร้อง</title>
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
                if(isset($_POST['id'])) {
                    $id = $_POST['id'];

                    // ดึงข้อมูลคำร้องจากฐานข้อมูล
                    $sql = "SELECT student_id, thesis_topic_thai, thesis_topic_eng, thesis_description, time_stamp, is_advisor_approved 
                            FROM advisor_request 
                            WHERE id = ?";

                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $student_ids = json_decode($row['student_id'], true);

                        echo "<h3>หัวข้อวิทยานิพนธ์: " . htmlspecialchars($row['thesis_topic_thai']) . "</h3>";
                        echo "<h4>หัวข้อภาษาอังกฤษ: " . htmlspecialchars($row['thesis_topic_eng']) . "</h4>";
                        echo "<p><strong>รายละเอียดวิทยานิพนธ์:</strong> " . nl2br(htmlspecialchars($row['thesis_description'])) . "</p>";

                        echo "<ul>";
                        foreach ($student_ids as $student_id) {
                            $sql = "SELECT * FROM student WHERE id = '$student_id'";
                            $result = $conn->query($sql);
                            $row_name = $result->fetch_assoc();
                            echo "<li><strong>ID</strong> " . htmlspecialchars($student_id) .' <strong>Name</strong> '. $row_name['first_name'].' '. $row_name['last_name'] ."</li>";
                        }
                        echo "</ul>";
                        echo "<p><strong>วันที่ส่งคำร้อง:</strong> " . htmlspecialchars($row['time_stamp']) . "</p>";

                        // แสดงสถานะ
                        if ($row['is_advisor_approved'] == 1) {
                            echo "<p style='color: green; font-weight: bold;'>✅ อาจารย์ที่ปรึกษาอนุมัติแล้ว</p>";
                        } elseif ($row['is_advisor_approved'] == 2) {
                            echo "<p style='color: red; font-weight: bold;'>❌ อาจารย์ที่ปรึกษาปฏิเสธ</p>";
                        } else {
                            echo "<p style='color: gray; font-weight: bold;'>⏳ รอการอนุมัติจากอาจารย์</p>";
                        }

                    } else {
                        echo "<p style='color: red;'>ไม่พบข้อมูลคำร้อง</p>";
                    }
                    $stmt->close();
                }
            ?>
        </div>

        <form action="approve_reject.php" method="POST">
            <input type="hidden" name="request_id" value="<?= htmlspecialchars($id) ?>">

            <button class="button-accept" name="approve"><i class='bx bx-check'></i></button>
            <button class="button-reject" name="reject"><i class='bx bx-x'></i></button>
        </form>

        <a href="request.php">
            <button class="button-back"><i class='bx bx-arrow-back'></i></button>
        </a>
    </div>

</body>
</html>