<?php
session_start();
require('../server.php');


$account_id = $_SESSION['account_id'];
$receiver_id = $_SESSION['receiver_id'];
$title = $_SESSION['title'];

//เอาเครื่องหมายอ่านออก
$sql = "UPDATE messages SET is_read = 1 WHERE receiver_id = '$account_id' AND sender_id = '$receiver_id' AND is_read = 0 AND message_title = '$title'";
$result = $conn->query($sql);

$sql = "SELECT * FROM messages WHERE (receiver_id = '$receiver_id' AND sender_id = '$account_id' AND message_title = '$title')
        OR (receiver_id = '$account_id' AND sender_id = '$receiver_id' AND message_title = '$title')
        ORDER BY time_stamp ASC";
$result = $conn->query($sql);

//แสดงข้อความ
while($row = $result->fetch_assoc()){
    $message_id = $row['message_id'];
    $message = $row['message'];
    $time = $row['time_stamp'];
    $fileName = $row['message_file_name'];
    $fileData = $row['message_file_data'];
    $fileType = $row['message_file_type'];

    //ถ้า sender_id คือคนที่กำลัง login แปลว่าข้อความที่ดึงมาเป็นของคนที่ login อยู่
    if($account_id == $row['sender_id']){ 
            if (isset($message) && empty($fileName)) {
                echo 
                "
                    <div class='message message-sent'>
                        <div class='message-content'>".  nl2br(string: htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) ."</div>
                        <div class='message-time'>$time</div>
                    </div>
                ";
            }elseif(empty($message) && isset($fileName)){
                echo 
                "
                    <div class='message message-sent'>
                        <div class='message-content'>
                            <form action='download.php' method='POST'>
                            <button name='file-clicked-message-id' value='" . htmlspecialchars($message_id, ENT_QUOTES, 'UTF-8') . "'>
                                <i class='bx bx-file-blank'></i>
                                <span>" . htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') . "</span>
                            </button>
                        </form>
                        </div>
                        <div class='message-time'>" . $time . "</div>
                    </div>
                ";
            }elseif(isset($message) && isset($fileName)){
                echo 
                "
                    <div class='message message-sent'>
                        <div class='message-content'>" . nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) . "</div>
                        <div class='message-content'>
                            <form action='download.php' method='POST'>
                            <button name='file-clicked-message-id' value='" . htmlspecialchars($message_id, ENT_QUOTES, 'UTF-8') . "'>
                                <i class='bx bx-file-blank'></i>
                                <span>" . htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') . "</span>
                            </button>
                        </form>
                        </div>
                        <div class='message-time'>" . $time . "</div>
                    </div>
                ";
            }

    } else { //ถ้า sender_id ไม่ใช่คนที่กำลัง login แปลว่าข้อความที่ดึงมาเป็นของอีกฝั่ง
        if (isset($message) && empty($fileName)) {
        echo 
        "
            <div class='message message-received'>
                <div class='message-content'>".  nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) ."</div>
                <div class='message-time'>$time</div>
            </div>
        ";
        }elseif(empty($message) && isset($fileName)){
            echo 
            "
                <div class='message message-received'>
                    <div class='message-content'>
                        <form action='download.php' method='POST'>
                            <button name='file-clicked-message-id' value='" . htmlspecialchars($message_id, ENT_QUOTES, 'UTF-8') . "'>
                                <i class='bx bx-file-blank'></i>
                                <span>" . htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') . "</span>
                            </button>
                        </form>
                    </div>
                    <div class='message-time'>" . $time . "</div>
                </div>
                ";
        }elseif(isset($message) && isset($fileName)){ 
            echo 
            "
                <div class='message message-received'>
                    <div class='message-content'>" . nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) . "</div>
                    <div class='message-content'>
                        <form action='download.php' method='POST'>
                            <button name='file-clicked-message-id' value='" . htmlspecialchars($message_id, ENT_QUOTES, 'UTF-8') . "'>
                                <i class='bx bx-file-blank'></i>
                                <span>" . htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') . "</span>
                            </button>
                        </form>
                    </div>
                    <div class='message-time'>" . $time . "</div>
                </div>
            ";
        }
    }
}
?>


