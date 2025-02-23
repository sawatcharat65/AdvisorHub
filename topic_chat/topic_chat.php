<?php
session_start();
require('../server.php');
include('../components/navbar.php');

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('location: /AdvisorHub/login');
    exit();
}

// Check if logged in
if (empty($_SESSION['username'])) {
    header('location: /AdvisorHub/login');
    exit();
}

// Redirect to profile
if (isset($_POST['profile'])) {
    header('location: /AdvisorHub/profile');
    exit();
}

// Validate receiver_id
if (empty($_SESSION['receiver_id']) || $_SESSION['receiver_id'] == $_SESSION['account_id']) {
    header('location: /AdvisorHub/advisor');
    exit();
}

// Handle profileInbox
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

// Handle search
$search_query = "";
$search_condition = ""; // กำหนดค่าเริ่มต้นเป็นสตริงว่าง
if (isset($_POST['search'])) {
    $search_query = $_POST['search'];
    $search_condition = " AND message_title LIKE '%$search_query%' ";
}

// Fetch receiver info
$receiver_id = $_SESSION['receiver_id'];
$sql = "SELECT advisor_first_name, advisor_last_name FROM advisor WHERE advisor_id = '$receiver_id' 
        UNION 
        SELECT student_first_name, student_last_name FROM student WHERE student_id = '$receiver_id'";
$result = $conn->query($sql);
$receiver = $result->fetch_assoc();

// Fetch approval timestamp from advisor_request
$id = $_SESSION['account_id'];
$sql = "SELECT time_stamp FROM advisor_request 
        WHERE ((requester_id = '$id' AND advisor_id = '$receiver_id') 
        OR (student_id = '$id' AND advisor_id = '$receiver_id'))
        AND is_advisor_approved = 1 
        AND is_admin_approved = 1 
        ORDER BY time_stamp ASC LIMIT 1";
$result = $conn->query($sql);
$approval_timestamp = $result->num_rows > 0 ? $result->fetch_assoc()['time_stamp'] : null;

// ถ้ายังไม่มี approval_timestamp หรือคำขอยังไม่สมบูรณ์ ให้ถือว่าทั้งหมดเป็น "Before"
$before_messages = [];
$after_messages = [];

$sql = "
    SELECT message_title, MAX(time_stamp) AS latest_time
    FROM messages
    WHERE (sender_id = '$id' AND receiver_id = '$receiver_id') 
    OR (sender_id = '$receiver_id' AND receiver_id = '$id')
    $search_condition
    GROUP BY message_title
    ORDER BY latest_time DESC
";
$messages_result = $conn->query($sql);

while ($row = $messages_result->fetch_assoc()) {
    $message = [
        'title' => $row['message_title'],
        'timestamp' => $row['latest_time'],
        'unread' => $conn->query("SELECT DISTINCT is_read FROM messages 
                                WHERE receiver_id = '$id' 
                                AND sender_id = '$receiver_id' 
                                AND is_read = 0 
                                AND message_title = '{$row['message_title']}'")->num_rows > 0
    ];

    // ถ้าไม่มี approval_timestamp หรือ timestamp ของข้อความ <= approval_timestamp ให้อยู่ใน Before
    if ($approval_timestamp === null || $row['latest_time'] <= $approval_timestamp) {
        $before_messages[] = $message;
    } else {
        $after_messages[] = $message;
    }
}

// Handle delete
if (isset($_POST['delete'])) {
    $title = $_POST['title'];
    $sql = "DELETE FROM messages WHERE message_title = '$title' AND ((sender_id = '$id' AND receiver_id = '$receiver_id') OR (sender_id = '$receiver_id' AND receiver_id = '$id'))";
    $conn->query($sql);
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
    <link rel="stylesheet" href="topic_chat.css">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../Logo.png">
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

        <div class='after-approve'>
            <h3>After Becoming an Advisor</h3>
            <?php if (empty($after_messages)): ?>
                <p>No messages found.</p>
            <?php else: ?>
                <?php foreach ($after_messages as $message): ?>
                    <div class='message'>
                        <div>
                            <div class='sender'><?php echo htmlspecialchars($message['title']); ?></div>
                            <div class='message-date'><?php echo $message['timestamp']; ?></div>
                        </div>
                        <div class="message-actions">
                            <form action='../chat/index.php' method='post' class='form-chat'>
                                <input type='hidden' name='title' value='<?php echo htmlspecialchars($message['title']); ?>'>
                                <button name='chat' class='menu-button' value='<?php echo $receiver_id; ?>'><i class='bx bxs-message-dots'></i></button>
                                <?php if ($message['unread']): ?>
                                    <span class='unread-indicator'><i class='bx bxs-circle'></i></span>
                                <?php endif; ?>
                            </form>
                            <div class="menu-container" data-title="<?php echo htmlspecialchars($message['title']); ?>">
                                <button type="button" class="menu-button"><i class='bx bx-dots-vertical-rounded'></i></button>
                                <div class="dropdown-menu">
                                    <form action="" method="post" onsubmit="return confirm('Are you sure you want to delete this topic?');">
                                        <input type="hidden" name="title" value="<?php echo htmlspecialchars($message['title']); ?>">
                                        <button type="submit" name="delete">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class='before-approve'>
            <h3>Before Becoming an Advisor</h3>
            <?php if (empty($before_messages)): ?>
                <p>No messages found.</p>
            <?php else: ?>
                <?php foreach ($before_messages as $message): ?>
                    <div class='message'>
                        <div>
                            <div class='sender'><?php echo htmlspecialchars($message['title']); ?></div>
                            <div class='message-date'><?php echo $message['timestamp']; ?></div>
                        </div>
                        <div class="message-actions">
                            <form action='../chat/index.php' method='post' class='form-chat'>
                                <input type='hidden' name='title' value='<?php echo htmlspecialchars($message['title']); ?>'>
                                <button name='chat' class='menu-button' value='<?php echo $receiver_id; ?>'><i class='bx bxs-message-dots'></i></button>
                                <?php if ($message['unread']): ?>
                                    <span class='unread-indicator'><i class='bx bxs-circle'></i></span>
                                <?php endif; ?>
                            </form>
                            <div class="menu-container" data-title="<?php echo htmlspecialchars($message['title']); ?>">
                                <button type="button" class="menu-button"><i class='bx bx-dots-vertical-rounded'></i></button>
                                <div class="dropdown-menu">
                                    <form action="" method="post" onsubmit="return confirm('Are you sure you want to delete this topic?');">
                                        <input type="hidden" name="title" value="<?php echo htmlspecialchars($message['title']); ?>">
                                        <button type="submit" name="delete">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuButtons = document.querySelectorAll('.menu-button');
        menuButtons.forEach(button => {
            button.addEventListener('click', function() {
                const menuContainer = this.closest('.menu-container');
                const dropdownMenu = menuContainer.querySelector('.dropdown-menu');
                dropdownMenu.classList.toggle('active');
                document.querySelectorAll('.dropdown-menu.active').forEach(menu => {
                    if (menu !== dropdownMenu) {
                        menu.classList.remove('active');
                    }
                });
            });
        });

        document.addEventListener('click', function(event) {
            if (!event.target.closest('.menu-container')) {
                document.querySelectorAll('.dropdown-menu.active').forEach(menu => {
                    menu.classList.remove('active');
                });
            }
        });
    });
</script>

</html>