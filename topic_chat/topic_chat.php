<?php
session_start();
require('../server.php');
include('../components/navbar.php');

// การจัดการ logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('location: /AdvisorHub/login');
    exit();
}

// ตรวจสอบการ login
if (empty($_SESSION['username'])) {
    header('location: /AdvisorHub/login');
    exit();
}

// การไปหน้า profile
if (isset($_POST['profile'])) {
    header('location: /AdvisorHub/profile');
    exit();
}

// ตรวจสอบ receiver_id
if (empty($_SESSION['receiver_id']) || $_SESSION['receiver_id'] == $_SESSION['account_id']) {
    header('location: /AdvisorHub/advisor');
    exit();
}

// การจัดการ profileInbox
if (isset($_POST['profileInbox'])) {
    $user_id = $_POST['profileInbox'];
    $_SESSION['profileInbox'] = $user_id;

    $sql = "SELECT role FROM advisor WHERE advisor_id = '$user_id'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if ($row['role'] == 'advisor') {
        header('location: /AdvisorHub/advisor_profile');
    } else {
        header('location: /AdvisorHub/student_profile');
    }
    exit();
}

// การจัดการการค้นหา
$search_query = "";
if (isset($_POST['search'])) {
    $search_query = $_POST['search'];
}

// ดึงข้อมูล receiver
$receiver_id = $_SESSION['receiver_id'];
$sql = "SELECT advisor_first_name, advisor_last_name FROM advisor WHERE advisor_id = '$receiver_id' 
        UNION 
        SELECT student_first_name, student_last_name FROM student WHERE student_id = '$receiver_id'";
$result = $conn->query($sql);
$receiver = $result->fetch_assoc();

// ดึงข้อมูล messages
$id = $_SESSION['account_id'];
$search_condition = !empty($search_query) ? " AND message_title LIKE '%$search_query%' " : "";

$sql = "
    SELECT message_title, MAX(time_stamp) AS latest_time
    FROM messages
    WHERE (sender_id = '$id' AND receiver_id = '$receiver_id') 
    OR (sender_id = '$receiver_id' AND receiver_id = '$id')
    $search_condition
    GROUP BY message_title
    ORDER BY 
        CASE 
            WHEN message_title LIKE '%$search_query%' THEN 1 
            ELSE 2 
        END,
        latest_time DESC
";
$messages_result = $conn->query($sql);
$messages = [];
while ($row = $messages_result->fetch_assoc()) {
    $messages[] = [
        'title' => $row['message_title'],
        'timestamp' => $row['latest_time'],
        'unread' => $conn->query("SELECT DISTINCT is_read FROM messages 
                                WHERE receiver_id = '$id' 
                                AND sender_id = '$receiver_id' 
                                AND is_read = 0 
                                AND message_title = '{$row['message_title']}'")->num_rows > 0
    ];
}
?>

<!-- เริ่มส่วน HTML -->
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
    <?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'Teams']); ?>

    <div class='topic-container'>
        <div class='topic-head'>
            <h2><?php echo $receiver['advisor_first_name'] . ' ' . $receiver['advisor_last_name']; ?></h2>
            <a href="topic_create.php" class="fa-solid fa-circle-plus"></a>
        </div>

        <form method="POST" class="topic-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" placeholder="Search topic" value="<?php echo htmlspecialchars($search_query); ?>" />
            <button type="submit"><i class='bx bx-search'></i></button>
        </form>

        <div class="topic-status">
            <button class="active">In progress</button>
            <button>Completed</button>
        </div>

        <div class='divider'></div>

        <?php foreach ($messages as $message): ?>
            <div class='message'>
                <div>
                    <div class='sender'><?php echo htmlspecialchars($message['title']); ?></div>
                    <div class='message-date'><?php echo $message['timestamp']; ?></div>
                </div>
                <form action='../chat/index.php' method='post' class='form-chat'>
                    <input type='hidden' name='title' value='<?php echo htmlspecialchars($message['title']); ?>'>
                    <button name='chat' class='chat-button' value='<?php echo $receiver_id; ?>'><i class='bx bxs-message-dots'></i></button>
                    <?php if ($message['unread']): ?>
                        <i class='bx bxs-circle'></i>
                    <?php endif; ?>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>

</html>