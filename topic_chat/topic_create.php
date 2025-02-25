<?php
session_start();
require('../server.php');
include('../components/navbar.php');

// ตรวจสอบสถานะของ session
if (empty($_SESSION['username'])) {
    header('location: /AdvisorHub/login');
    exit;
}

if (isset($_POST['logout'])) {
    session_destroy();
    header('location: /AdvisorHub/login');
    exit;
}

if (isset($_POST['profile'])) {
    header('location: /AdvisorHub/profile');
    exit;
}

// ตรวจสอบการเข้าถึงแชท
if (empty($_SESSION['receiver_id']) || $_SESSION['receiver_id'] == $_SESSION['account_id']) {
    header('location: /AdvisorHub/advisor');
    exit;
}

// ฟังก์ชันสำหรับการตรวจสอบและส่งข้อความ
function sendMessage($conn, $sender_id, $receiver_id, $title, $message)
{
    // ตรวจสอบว่า message ไม่ใช่ค่าว่าง
    if (!empty($message)) {
        // ใช้ prepared statement เพื่อลดความเสี่ยงจาก SQL Injection
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message_title, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $sender_id, $receiver_id, $title, $message);
        $stmt->execute();
        $stmt->close();
    }
}

// ตรวจสอบว่า user กดปุ่มส่งข้อความหรือไม่
if (isset($_POST['submit'])) {
    $message = $_POST['message'];
    $title = $_POST['message_title'];
    $sender_id = $_SESSION['account_id'];
    $receiver_id = $_SESSION['receiver_id'];

    sendMessage($conn, $sender_id, $receiver_id, $title, $message);
    header('location: /AdvisorHub/topic_chat/topic_chat.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link rel="stylesheet" href="assets/css/topic_create.css">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../Logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>

    <?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'Teams']) ?>

    <form method='post' class="consultation-form-container">
        <div class="form-header">
            <a href="topic_chat.php" class="fa-solid fa-arrow-left"></a>
            <h1 class="form-title">Create Topic</h1>
        </div>
        <div class="form-field">
            <label for="consultation-topic" class="form-label">Topic</label>
            <input type="text" id="consultation-topic" class="form-input" name='message_title' placeholder="Fill your topic here" required>
        </div>
        <div class="form-field">
            <label for="consultation-details" class="form-label">Details</label>
            <textarea id="consultation-details" class="form-textarea" name='message' placeholder="Fill your details here" required></textarea>
        </div>
        <div class="form-button-container">
            <button type="submit" name='submit' class="form-submit-btn"><i class='bx bx-mail-send'></i></button>
        </div>
    </form>
</body>

</html>