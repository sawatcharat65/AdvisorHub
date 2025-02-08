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

if(empty($_SESSION['receiver_id']) || $_SESSION['receiver_id'] == $_SESSION['id']){
    header('location: /AdvisorHub/advisor');
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
    <link rel="stylesheet" href="topic_create.css">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../Logo.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>

<?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'Teams'])?>

    <div class="consultation-form-container">
        <div class="form-header">
            <a href="topic_chat.php" class="fa-solid fa-arrow-left"></a>
            <h1 class="form-title">สร้างหัวข้อที่ต้องการปรึกษา</h1>
        </div>
        <div class="form-field">
            <label for="consultation-topic" class="form-label">หัวข้อที่ต้องการปรึกษา</label>
            <input type="text" id="consultation-topic" class="form-input" placeholder="กรอกหัวข้อที่ต้องการปรึกษา">
        </div>
        <div class="form-field">
            <label for="consultation-details" class="form-label">รายละเอียด</label>
            <textarea id="consultation-details" class="form-textarea" placeholder="กรอกรายละเอียด"></textarea>
        </div>
        <div class="form-button-container">
            <button type="submit" class="form-submit-btn">ส่งข้อความ</button>
        </div>
    </div>

    

</body>

</html>