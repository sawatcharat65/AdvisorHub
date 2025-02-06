<?php
    session_start();
    require ('../server.php');
    include('../components/navbar.php');
    if(isset($_POST['logout'])){
        session_destroy();
        header('location: /AdvisorHub/login');
        exit();
    }

    if(empty($_SESSION['username'])){
        header('location: /AdvisorHub/login');
        exit();
    }

    if(isset($_POST['profile'])){
        header('location: /AdvisorHub/profile');
        exit();
    }

    if(isset($_POST['chat'])){
        $_SESSION['receiver_id'] = $_POST['chat'];
        header('location: /AdvisorHub/chat');
        exit();
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../Logo.jpg">
</head>
<body>

    <?php renderNavbar(['home', 'advisor', 'inbox', 'thesis', 'statistics', 'file'])?>

    <?php
        if(isset($_SESSION['receiver_id'])){
            $receiver_id = $_SESSION['receiver_id'];
            $sender_id = $_SESSION['id'];

            // เมื่ออ่านแล้วเอาเครื่องหมายยังไม่อ่านออก
            $sql = "UPDATE messages SET is_read = 1 WHERE receiver_id = '$sender_id' AND sender_id = '$receiver_id' AND is_read = 0";
            $result = $conn->query($sql);

            $sql = "SELECT * FROM advisor WHERE id = '$receiver_id'";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();

            //เช็คว่า receiver เป็นใคร advisor หรือ student
            if(isset($row['first_name'])){
                $first_name = $row['first_name'];
                $last_name = $row['last_name'];
            } else {
                $sql = "SELECT * FROM student WHERE id = '$receiver_id'";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                $first_name = $row['first_name'];
                $last_name = $row['last_name'];
            }

            // ตรวจสอบว่ามีการส่งข้อความหรือไม่
            if(isset($_POST['send'])){
                $message = $_POST['message'];

                // ตรวจสอบว่า message ไม่ใช่ค่าว่าง
                if (!empty($message)) {
                    // ป้องกัน SQL Injection โดยใช้ mysqli_real_escape_string
                    $message = $conn->real_escape_string($message);
                    $sql = "INSERT INTO messages(sender_id, receiver_id, title, message) VALUES('$sender_id', '$receiver_id', '$message')";
                    $result = $conn->query($sql);
                } else {
                    
                }
            }

            echo "
                <div class='chat-container'>
                    <div class='chat-header'>
                        <h2>$first_name $last_name</h2>
                    </div>
                    <div class='chat-box'>
                        <div class='message-container'>
            ";

            $sql = "SELECT * FROM messages WHERE receiver_id = '$receiver_id' AND sender_id = '$sender_id' UNION
                    SELECT * FROM messages WHERE receiver_id = '$sender_id' AND sender_id = '$receiver_id'
                    ORDER BY time_stamp ASC";
            $result = $conn->query($sql);

            while($row = $result->fetch_assoc()){
                $message = $row['message'];
                $time = $row['time_stamp'];
                if($sender_id == $row['sender_id']){
                    echo "
                    <div class='message message-sent'>
                        <div class='message-content'>".  nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) ."</div>
                        <div class='message-time'>$time</div>
                    </div>";
                } else {
                    echo "
                    <div class='message message-received'>
                        <div class='message-content'>".  nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) ."</div>
                        <div class='message-time'>$time</div>
                    </div>";
                }
            }

            echo "
                        </div>
                    </div>
                    <form action='' method='post' class='form-send'>
                        <div class='chat-input'>
                            <input type='text' class='input-message' name='message' placeholder='Type a message...' />
                            <button class='send-button' name='send'>Send</button>
                        </div>
                    </form>
            ";

        }
    
    ?>
    <button class="scroll-to-bottom"><i class='bx bx-down-arrow-alt'></i></button>

    </div>
    <footer>
        <p>&copy; 2024 Naresuan University.</p>
    </footer>
    <script src="script.js"></script>
</body>
</html>
