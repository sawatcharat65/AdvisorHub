<?php
    session_start();
    require ('../server.php');

    if(isset($_POST['logout'])){
        session_destroy();
        header('location: /AdvisorHub/login');
    }

    if(empty($_SESSION['username'])){
        header('location: /AdvisorHub/login');
    }

    if(isset($_POST['profile'])){
        header('location: /AdvisorHub/profile');
    }

    if(isset($_POST['chat'])){
        $_SESSION['receiver_id'] = $_POST['chat'];
        header('location: /AdvisorHub/chat');
    }

    if(isset($_POST['profileInbox'])){
        $user_id = $_POST['profileInbox'];
        $_SESSION['profileInbox'] = $user_id;
        
        $sql = "SELECT role FROM advisor WHERE id = '$user_id'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();

        if($row['role'] == 'advisor'){
            header('location: /ThesisAdvisorHub/advisor_profile');
        }else{
            header('location: /ThesisAdvisorHub/student_profile');
        }

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
    </nav>>
    <div class="inbox-container">
        <div class="inbox-head">
            <h2>Inbox</h2>
        </div>
        <div class="inbox">

        <?php
            $user_id = $_SESSION['id'];

            $sql = "SELECT DISTINCT receiver_id FROM messages WHERE sender_id = $user_id UNION
                    SELECT DISTINCT sender_id FROM messages WHERE receiver_id = $user_id ";
            $result = $conn->query($sql);

            while($row = $result->fetch_assoc()){
                
                if(isset($row['receiver_id'])){
                    $receiver_id = $row['receiver_id'];
                    $sql = "SELECT * FROM advisor WHERE id = '$receiver_id'";
                    $result2 = $conn->query($sql);
                    $row2 = $result2->fetch_assoc();

                    if(empty($row2['username'])){
                        $sql = "SELECT * FROM student WHERE id = '$receiver_id'";
                        $result4 = $conn->query($sql);
                        $row4 = $result4->fetch_assoc();

                        $username = $row4['username'];
                        $chat_id = $row4['id'];
                    }else{
                        $username = $row2['username'];
                        $chat_id = $row2['id'];
                    }
                    
                    echo 
                    "
                    <div class='message'>
                        <div class='sender'>$username</div>
                            <form action='' method='post' class='form-chat'>
                                <button name='profileInbox' class='profileInbox' value='$chat_id'><i class='bx bxs-user-pin'></i></button>
                                <button name='chat' class='chat-button' value='$chat_id'><i class='bx bxs-message-dots'></i></button>
                    ";

                    $sqlReadCheck = "SELECT DISTINCT * FROM messages WHERE receiver_id = '$user_id' AND is_read = 0 AND sender_id = '$chat_id'";
                    $resultReadCheck = $conn->query($sqlReadCheck);
                    $rowReadCheck = $resultReadCheck->fetch_assoc();

                    if(isset($rowReadCheck['id'])){
                        echo "<i class='bx bxs-circle'></i>";
                    }

                    echo 
                    "
                            </form>
                        </div>
                    ";

                }elseif(isset($row['sender_id'])){
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

                    if(isset($rowReadCheck['id'])){
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
        
    <footer>
        <p>&copy; 2024 Naresuan University.</p>
    </footer>
</body>
</html>