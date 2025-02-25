<?php
session_start();
require('../server.php');
include('../components/navbar.php');

if (isset($_POST['view_chat'])) {
    list($sender_id, $receiver_id, $title) = explode("|", $_POST['view_chat']);
    $_SESSION['account_id'] = $sender_id;
    $_SESSION['receiver_id'] = $receiver_id;
    $_SESSION['title'] = $title;
    header('location: /AdvisorHub/chat/');
    exit(); // **สำคัญมาก! เพื่อหยุดการทำงานของ PHP หลัง redirect**
}

if (isset($_POST['logout'])) {
    session_destroy();
    header('location: /AdvisorHub/login');
}

$search_query = "";

if (isset($_POST['search'])) {
    $search_query = $_POST['search'];  // รับค่าคำค้นจากฟอร์ม
}

// ตรวจสอบสิทธิ์ว่าเป็น admin หรือไม่
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('location: /AdvisorHub/advisor');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Inbox</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../Logo.png">
</head>
<body>

<?php renderNavbar(['home', 'advisor', 'statistics', 'admin_inbox']) ?>

<div class="inbox-container">
    <div class="inbox-head">
        <h2>Admin Inbox</h2>
    </div>
    <div class="inbox">
    <?php
        // ดึงหัวข้อแชททั้งหมด พร้อมชื่อของนักเรียนและอาจารย์
        $sql = "SELECT DISTINCT 
            m.message_title AS topic, 
            GREATEST(a1.account_id, a2.account_id) AS id1, 
            LEAST(a1.account_id, a2.account_id) AS id2
        FROM messages m
        JOIN account a1 ON m.sender_id = a1.account_id
        JOIN account a2 ON m.receiver_id = a2.account_id";
        
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $advisor_id = $row['id1']; // ใช้ id1 เป็น advisor_id
                $student_id = $row['id2']; // ใช้ id2 เป็น student_id

                // ดึงข้อมูลนักเรียน
                $sql_student = "SELECT * FROM student WHERE student_id = '$student_id'";
                $result_student = $conn->query($sql_student);
                $row_student = $result_student->fetch_assoc();

                // ดึงข้อมูลที่ปรึกษา
                $sql_advisor = "SELECT * FROM advisor WHERE advisor_id = '$advisor_id'";
                $result_advisor = $conn->query($sql_advisor);
                $row_advisor = $result_advisor->fetch_assoc();

                // แสดงหัวข้อแชท, ชื่อนักเรียน และชื่อที่ปรึกษา
                echo "<div class='message'>
                        <div class='sender'>
                            <strong>หัวข้อ: {$row['topic']}</strong><br>
                            นิสิต : " . ($row_student ? $row_student['student_first_name'] . ' ' . $row_student['student_last_name'] : 'ไม่พบข้อมูลนักเรียน') . "<br>
                            อาจารย์ที่ปรึกษา : " . ($row_advisor ? $row_advisor['advisor_first_name'] . ' ' . $row_advisor['advisor_last_name'] : 'ไม่พบข้อมูลที่ปรึกษา') . "<br>
                        </div>
                        <form action='' method='post' class='form-chat'>
                            <button name='view_chat' class='chat-button' value='{$row['id1']}|{$row['id2']}|{$row['topic']}'>
                                <i class='bx bxs-message-dots'></i>
                            </button>
                        </form>
                    </div>";
            }
        } else {
            echo "ไม่มีข้อมูลหัวข้อแชท";
        }
    ?>
    </div>
</div>

<footer>
    <p>© 2024 Naresuan University.</p>
</footer>

</body>
</html>
