<?php
session_start();
require('../server.php');
include('../components/navbar.php');
if (isset($_POST['logout'])) {
    session_destroy();
    header('location: /AdvisorHub/login');
}

if (empty($_SESSION['username'])) {
    header('location: /AdvisorHub/login');
}

if (isset($_POST['profile'])) {
    header('location: /AdvisorHub/profile');
}

if (isset($_POST['chat'])) {
    $_SESSION['receiver_id'] = $_POST['chat'];
    header('location: /AdvisorHub/chat');
}

if (isset($_POST['profileInbox'])) {
    $user_id = $_POST['profileInbox'];
    $_SESSION['profileInbox'] = $user_id;

    $sql = "SELECT role FROM advisor WHERE id = '$user_id'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if ($row['role'] == 'advisor') {
        header('location: /AdvisorHub/advisor_profile');
    } else {
        header('location: /AdvisorHub/student_profile');
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link rel="stylesheet" href="topic_chat.css">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../Logo.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>

    <?php renderNavbar(['home', 'advisor', 'inbox', 'thesis', 'statistics', 'file'])?>

    <div class="topic-container">
        <div class="topic-head">
            <h2>ชื่อคน</h2>
            <a href="topic_create.php" class="fa-solid fa-circle-plus"></a>
        </div>
        <div class="topic-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="ค้นหาหัวข้อ">
        </div>
        <div class="topic-status">
            <button class="active">กำลังดำเนินการ</button>
            <button>เสร็จสิ้น</button>
        </div>

        <div class="divider"></div>

        <?php
        $user_id = $_SESSION['id'];

        $sql = "SELECT DISTINCT receiver_id FROM messages WHERE sender_id = $user_id UNION
                    SELECT DISTINCT sender_id FROM messages WHERE receiver_id = $user_id ";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {

            if (isset($row['receiver_id'])) {
                $receiver_id = $row['receiver_id'];
                $sql = "SELECT * FROM advisor WHERE id = '$receiver_id'";
                $result2 = $conn->query($sql);
                $row2 = $result2->fetch_assoc();

                if (empty($row2['username'])) {
                    $sql = "SELECT * FROM student WHERE id = '$receiver_id'";
                    $result4 = $conn->query($sql);
                    $row4 = $result4->fetch_assoc();

                    $username = $row4['username'];
                    $chat_id = $row4['id'];
                } else {
                    $username = $row2['username'];
                    $chat_id = $row2['id'];
                }

                echo
                "
                    <div class='message'>
                        <div>
                            <div class='sender'>ชื่อหัวข้อ</div>
                            <div class='message-date'>2024-12-10 07:40:16</div>
                        </div>
                            <form action='' method='post' class='form-chat'>
                                <button name='chat' class='chat-button' value='$chat_id'><i class='bx bxs-message-dots'></i></button>
                    ";

                $sqlReadCheck = "SELECT DISTINCT * FROM messages WHERE receiver_id = '$user_id' AND is_read = 0 AND sender_id = '$chat_id'";
                $resultReadCheck = $conn->query($sqlReadCheck);
                $rowReadCheck = $resultReadCheck->fetch_assoc();

                if (isset($rowReadCheck['id'])) {
                    echo "<i class='bx bxs-circle'></i>";
                }

                echo
                "
                            </form>
                        </div>
                    ";
            } elseif (isset($row['sender_id'])) {
                $sender_id = $row['sender_id'];
                $sql = "SELECT * FROM student WHERE id = '$sender_id'";
                $result3 = $conn->query($sql);
                $row3 = $result3->fetch_assoc();

                $username = $row3['username'];
                $chat_id = $row3['id'];

                echo
                "
                    <div class='message'>
                        <div class='sender'>$username</div>
                            <form action='' method='post' class='form-chat'>
                                <button name='chat' class='chat-button' value='$chat_id'><i class='bx bxs-message-dots'></i></button>
                                <button name='profileInbox' value='$chat_id'><i class='bx bxs-user-pin'></i></button>
                    ";

                $sqlReadCheck = "SELECT DISTINCT * FROM messages WHERE receiver_id = '$user_id' AND is_read = 0 AND sender_id = '$chat_id'";
                $resultReadCheck = $conn->query($sqlReadCheck);
                $rowReadCheck = $resultReadCheck->fetch_assoc();

                if (isset($rowReadCheck['id'])) {
                    echo "<i class='bx bxs-circle'></i>";
                }

                echo
                "
                            </form>
                        </div>
                    ";
            }
        }

        ?>
    </div>

    

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const buttons = document.querySelectorAll(".topic-status button");

            buttons.forEach(button => {
                button.addEventListener("click", function() {
                    // ลบ class 'active' ออกจากปุ่มทั้งหมด
                    buttons.forEach(btn => btn.classList.remove("active"));

                    // เพิ่ม class 'active' ให้ปุ่มที่ถูกคลิก
                    this.classList.add("active");

                    // เพิ่ม animation เล็กน้อย
                    this.style.animation = "none"; // รีเซ็ต animation ก่อน
                    setTimeout(() => {
                        this.style.animation = "fadeIn 0.3s ease-in-out";
                    }, 10);
                });
            });
        });
    </script>

</body>

</html>