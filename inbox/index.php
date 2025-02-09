<?php
    session_start();
    require ('../server.php');
    include('../components/navbar.php');

    // Handle logout
    if(isset($_POST['logout'])){
        session_destroy();
        header('location: /AdvisorHub/login');
    }

    // Check if session is empty
    if(empty($_SESSION['username'])){
        header('location: /AdvisorHub/login');
    }

    // Handle profile and chat redirects
    if(isset($_POST['profile'])){
        header('location: /AdvisorHub/profile');
    }

    if(isset($_POST['chat'])){
        $_SESSION['receiver_id'] = $_POST['chat'];
        header('location: /AdvisorHub/chat');
    }

    if(isset($_POST['profileInbox'])){
        $id = $_POST['profileInbox'];
        $_SESSION['profileInbox'] = $id;
        
        $role = getUserRole($id); // Use helper function to get user role

        if($role == 'advisor'){
            header('location: /AdvisorHub/advisor_profile');
        } else {
            header('location: /AdvisorHub/student_profile');
        }
    }

    // Helper function to get user role
    function getUserRole($id) {
        global $conn;
        $sql = "SELECT role FROM account WHERE id = '$id'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['role'];
    }

    // Helper function to get user information (advisor or student)
    function getUserInfo($id) {
        global $conn;
        // Check if advisor
        $sql = "SELECT * FROM advisor WHERE id = '$id'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        if($row) {
            return $row; // Return advisor info if exists
        }

        // Check if student
        $sql = "SELECT * FROM student WHERE id = '$id'";
        $result = $conn->query($sql);
        return $result->fetch_assoc(); // Return student info if exists
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
        // Display user information and action buttons
        echo "<div class='message'>
                <div class='sender'>{$userInfo['first_name']} {$userInfo['last_name']}</div>
                <form action='' method='post' class='form-chat'>
                    <button name='profileInbox' class='profileInbox' value='{$userInfo['id']}'><i class='bx bxs-user-pin'></i></button>
                    <button name='chat' class='chat-button' value='{$userInfo['id']}'><i class='bx bxs-message-dots'></i></button>";
        
        // Check for unread messages
        $unreadMessages = checkUnreadMessages($_SESSION['id'], $userInfo['id']);
        if($unreadMessages) {
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

<?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'Teams'])?>
    <div class="inbox-container">
        <div class="inbox-head">
            <h2>Inbox</h2>
        </div>
        <div class="inbox">

        <?php
            $id = $_SESSION['id'];
            
            // Query for distinct receiver and sender ids
            $sql = "SELECT DISTINCT receiver_id FROM messages WHERE sender_id = '$id' UNION
                    SELECT DISTINCT sender_id FROM messages WHERE receiver_id = '$id'";
            $result = $conn->query($sql);

            while($row = $result->fetch_assoc()){
                // Check if receiver_id is set
                if(isset($row['receiver_id'])){
                    $receiver_id = $row['receiver_id'];
                    $userInfo = getUserInfo($receiver_id);

                    // Call function to display user details
                    displayUserDetails($userInfo);

                } elseif(isset($row['sender_id'])){
                    $sender_id = $row['sender_id'];
                    $userInfo = getUserInfo($sender_id);

                    // Call function to display user details
                    displayUserDetails($userInfo);
                }
            }
        ?>

    </div>

    <footer>
        <p>&copy; 2024 Naresuan University.</p>
    </footer>
</body>
</html>
