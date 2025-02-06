<?php
session_start();
require('../server.php');

if (isset($_POST['logout'])) {
    session_destroy();
    header('location: /ThesisAdvisorHub/login');
}

if (empty($_SESSION['username'])) {
    header('location: /ThesisAdvisorHub/login');
}

if (isset($_POST['profile'])) {
    header('location: /ThesisAdvisorHub/profile');
}

if (isset($_POST['chat'])) {
    $_SESSION['receiver_id'] = $_POST['chat'];
    header('location: /ThesisAdvisorHub/chat');
}

if (isset($_POST['profileInbox'])) {
    $user_id = $_POST['profileInbox'];
    $_SESSION['profileInbox'] = $user_id;

    $sql = "SELECT role FROM advisor WHERE id = '$user_id'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if ($row['role'] == 'advisor') {
        header('location: /ThesisAdvisorHub/advisor_profile');
    } else {
        header('location: /ThesisAdvisorHub/student_profile');
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
    <nav>
        <div class="logo">
            <img src="../CSIT.png" alt="" width="250px">
        </div>
        <ul>
            <li><a href="/AdvisorHub/home">Home</a></li>
            <li><a href='/AdvisorHub/advisor'>Advisor</a></li>
            <li><a href='/AdvisorHub/inbox'>Inbox</a></li>
            <li><a href='/AdvisorHub/thesis/thesis.php'>Thesis</a></li>
            <li><a href='/AdvisorHub/statistics'>Statistics</a></li>
            <li><a href='/AdvisorHub/thesis_resource_list/thesis_resource_list.php'>File</a></li>
        </ul>
        <div class="userProfile">
            <?php
                if(isset($_SESSION['username'])){
                    echo '<h2>'.$_SESSION['username'].'<h2/>';
                    echo "<i class='bx bxs-user-circle' ></i>";
                    echo "<div class='dropdown'>
                            <form action='' method='post'>
                                <button name='profile'>Profile</button>
                                <button name='logout'>Logout</button>
                            </form>
                        </div>";
                }
            ?>
        </div>
    </nav>

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