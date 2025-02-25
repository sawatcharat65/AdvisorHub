<?php
session_start();
require('../server.php');
include('../components/navbar.php');

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('location: /AdvisorHub/login');
}

//ไม่ให้ admin เข้าถึง
if(isset($_SESSION['username']) && $_SESSION['role'] == 'admin'){
    header('location: /AdvisorHub/advisor');
}
// Check if session is empty
if (empty($_SESSION['username'])) {
    header('location: /AdvisorHub/login');
}

// Handle profile and chat redirects
if (isset($_POST['profile'])) {
    header('location: /AdvisorHub/profile');
}

if (isset($_POST['chat'])) {
    $_SESSION['receiver_id'] = $_POST['chat'];
    header('location: /AdvisorHub/topic_chat/topic_chat.php');
}

if (isset($_POST['profileInbox'])) {
    $id = $_POST['profileInbox'];
    $_SESSION['profileInbox'] = $id;
    $_SESSION['advisor_info_id'] = $id;
    $role = getUserRole($id);

    if ($role == 'advisor') {
        header('location: /AdvisorHub/info');
    } else {
        header('location: /AdvisorHub/student_profile');
    }
}

// Helper function to get user role
function getUserRole($id) {
    global $conn;
    $sql = "SELECT role FROM account WHERE account_id = '$id'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['role'];
}

// Helper function to get user information (advisor or student) with normalized keys
function getUserInfo($id) {
    global $conn;
    // Check if advisor
    $sql = "SELECT advisor_id AS id, advisor_first_name AS first_name, advisor_last_name AS last_name 
            FROM advisor WHERE advisor_id = '$id'";
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()) {
        return $row; // Return advisor info with normalized keys
    }

    // Check if student
    $sql = "SELECT student_id AS id, student_first_name AS first_name, student_last_name AS last_name 
            FROM student WHERE student_id = '$id'";
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()) {
        return $row; // Return student info with normalized keys
    }

    return null; // Return null if no user found
}

// Helper function to check unread messages
function checkUnreadMessages($receiver_id, $sender_id) {
    global $conn;
    $sql = "SELECT DISTINCT * FROM messages WHERE receiver_id = '$receiver_id' AND is_read = 0 AND sender_id = '$sender_id'";
    $result = $conn->query($sql);
    return $result->fetch_assoc(); // Return result if unread messages exist
}

// Function to display user details (advisor or student)
function displayUserDetails($userInfo) {
    if (!$userInfo) return; // Skip if no user info

    echo "<div class='message'>
            <div class='sender'>{$userInfo['first_name']} {$userInfo['last_name']}</div>
            <form action='' method='post' class='form-chat'>
                <button name='profileInbox' class='profileInbox' value='{$userInfo['id']}'><i class='bx bxs-user-pin'></i></button>
                <button name='chat' class='chat-button' value='{$userInfo['id']}'><i class='bx bxs-message-dots'></i></button>";

    // Check for unread messages
    $unreadMessages = checkUnreadMessages($_SESSION['account_id'], $userInfo['id']);
    if ($unreadMessages) {
        echo "<i class='bx bxs-circle'></i>";
    }

    echo "</form></div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../Logo.png">
</head>
<body>

<?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'Teams']) ?>
    <div class="inbox-container">
        <div class="inbox-head">
            <h2>Inbox</h2>
        </div>
        <div class="inbox">
        <?php
            $id = $_SESSION['account_id'];

            // Query for distinct receiver and sender ids
            $sql = "SELECT DISTINCT receiver_id FROM messages WHERE sender_id = '$id' 
                    UNION SELECT DISTINCT sender_id FROM messages WHERE receiver_id = '$id'";
            $result = $conn->query($sql);

            while ($row = $result->fetch_assoc()) {
                $user_id = $row['receiver_id'] ?? $row['sender_id']; // Get the ID from either column
                $userInfo = getUserInfo($user_id);

                // Call function to display user details
                displayUserDetails($userInfo);
            }
        ?>
        </div>
    </div>

    <footer>
        <p>© 2024 Naresuan University.</p>
    </footer>
</body>
</html>