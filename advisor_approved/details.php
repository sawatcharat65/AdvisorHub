<?php
session_start();
include('../components/navbar.php');
include('../server.php');

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

if (isset($_SESSION['username']) && ($_SESSION['role'] == 'student' || $_SESSION['role'] == 'admin')) {
    header('location: /AdvisorHub/advisor');
}

if(empty($_POST['advisor_request_id'])){
    header('location: /AdvisorHub/advisor_approved/request.php');
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

    <?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'Teams']) ?>

    <div class="container">
        <h1>Details of the Request for Advisor Appointment</h1>
        <div class="details">
            <?php
            if (isset($_POST['advisor_request_id'])) {
                $advisor_request_id = $_POST['advisor_request_id'];

                // ดึงข้อมูลคำร้องจากฐานข้อมูล
                $sql = "SELECT student_id, thesis_topic_thai, thesis_topic_eng, thesis_description, time_stamp, is_advisor_approved 
                            FROM advisor_request 
                            WHERE advisor_request_id = ?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $advisor_request_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $student_ids = json_decode($row['student_id'], true);

                    echo "<h3>หัวข้อวิทยานิพนธ์: " . htmlspecialchars($row['thesis_topic_thai']) . "</h3>";
                    echo "<h3>หัวข้อภาษาอังกฤษ: " . htmlspecialchars($row['thesis_topic_eng']) . "</h3>";
                    echo "<h3><strong>รายละเอียดวิทยานิพนธ์:</strong> " . nl2br(htmlspecialchars($row['thesis_description'])) . "</h3>";

                    echo "<ul>";
                    foreach ($student_ids as $student_id) {
                        $sql = "SELECT student_first_name, student_last_name FROM student WHERE student_id = ?";
                        $stmt2 = $conn->prepare($sql);
                        $stmt2->bind_param("s", $student_id);
                        $stmt2->execute();
                        $result2 = $stmt2->get_result();
                        if ($row_name = $result2->fetch_assoc()) {
                            echo "<li><h2><strong>ID</strong> " . htmlspecialchars($student_id) . ' <strong>Name</strong> ' . htmlspecialchars($row_name['student_first_name']) . ' ' . htmlspecialchars($row_name['student_last_name']) . "</h2></li>";
                        }
                        $stmt2->close();
                    }
                    echo "</ul>";
                    echo "<h3><strong>Date:</strong> " . htmlspecialchars($row['time_stamp']) . "</h4>";

                    // แสดงสถานะ
                    if ($row['is_advisor_approved'] == 1) {
                        echo "<p style='color: green; font-weight: bold;'> อาจารย์ที่ปรึกษาอนุมัติแล้ว</p>";
                    } elseif ($row['is_advisor_approved'] == 2) {
                        echo "<p style='color: red; font-weight: bold;'> อาจารย์ที่ปรึกษาปฏิเสธ</p>";
                    } else {
                        echo "<p style='color: gray; font-weight: bold;'> รอการอนุมัติจากอาจารย์</p>";
                    }
                } else {
                    echo "<p style='color: red;'>ไม่พบข้อมูลคำร้อง</p>";
                }
                $stmt->close();
            } else {
                echo "<p style='color: red;'>ไม่พบข้อมูลคำร้อง</p>";
            }
            ?>
        </div>

        <form action="approve_reject.php" method="POST">
            <input type="hidden" name="request_id" value="<?= htmlspecialchars($advisor_request_id) ?>">
            <button class="button-accept" name="approve"><i class='bx bx-check'></i></button>
            <button class="button-reject" name="reject"><i class='bx bx-x'></i></button>
        </form>

        <a href="request.php">
            <button class="button-back"><i class='bx bx-arrow-back'></i></button>
        </a>
    </div>

</body>

</html>