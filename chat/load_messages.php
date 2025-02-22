<?php
session_start();
require('../server.php');


$sender_id = $_SESSION['id'];
$receiver_id = $_SESSION['receiver_id'];
$title = $_SESSION['title'];

$sql = "UPDATE messages SET is_read = 1 WHERE receiver_id = '$sender_id' AND sender_id = '$receiver_id' AND is_read = 0 AND message_title = '$title'";
$result = $conn->query($sql);

$sql = "SELECT * FROM messages WHERE (receiver_id = '$receiver_id' AND sender_id = '$sender_id' AND message_title = '$title')
        OR (receiver_id = '$sender_id' AND sender_id = '$receiver_id' AND message_title = '$title')
        ORDER BY time_stamp ASC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $message = nl2br($row['message']);
    $time = $row['time_stamp'];

    if ($sender_id == $row['sender_id']) {
        echo "
        <div class='message message-sent'>
            <div class='message-content'>$message</div>
            <div class='message-time'>$time</div>
        </div>
        ";
    } else {
        echo "
        <div class='message message-received'>
            <div class='message-content'>$message</div>
            <div class='message-time'>$time</div>
        </div>
        ";
    }
}
?>


