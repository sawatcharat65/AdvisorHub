<?php
session_start();
require('../server.php');

if (isset($_POST['type']) && isset($_POST['offset']) && isset($_POST['receiver_id'])) {
    $type = $_POST['type'];
    $offset = (int)$_POST['offset'];
    $receiver_id = $_POST['receiver_id'];
    $id = $_SESSION['account_id'];
    $messages_per_page = 5;

    $sql = "SELECT time_stamp FROM advisor_request 
            WHERE (
                (JSON_CONTAINS(student_id, '\"$id\"') AND advisor_id = '$receiver_id')
                OR 
                (advisor_id = '$id' AND JSON_CONTAINS(student_id, '\"$receiver_id\"'))
            ) 
            AND is_advisor_approved = 1 
            AND is_admin_approved = 1 
            ORDER BY time_stamp ASC LIMIT 1";
    $result = $conn->query($sql);
    $approval_timestamp = $result->num_rows > 0 ? $result->fetch_assoc()['time_stamp'] : null;

    $sql = "
        SELECT message_title, MAX(time_stamp) AS latest_time
        FROM messages
        WHERE ((sender_id = '$id' AND receiver_id = '$receiver_id') 
        OR (sender_id = '$receiver_id' AND receiver_id = '$id'))
        GROUP BY message_title
        ORDER BY latest_time DESC
        LIMIT $offset, $messages_per_page
    ";
    $messages_result = $conn->query($sql);

    $messages = [];
    if ($messages_result) {
        while ($row = $messages_result->fetch_assoc()) {
            $message = [
                'title' => $row['message_title'],
                'timestamp' => $row['latest_time'],
                'unread' => $conn->query("SELECT DISTINCT is_read FROM messages 
                                        WHERE receiver_id = '$id' 
                                        AND sender_id = '$receiver_id' 
                                        AND is_read = 0 
                                        AND message_title = '" . $conn->real_escape_string($row['message_title']) . "'")->num_rows > 0
            ];
            if (($type === 'before' && ($approval_timestamp === null || $row['latest_time'] <= $approval_timestamp)) ||
                ($type === 'after' && $approval_timestamp !== null && $row['latest_time'] > $approval_timestamp)
            ) {
                $messages[] = $message;
            }
        }
    }

    foreach ($messages as $message) {
        echo "
        <div class='message' data-title='" . htmlspecialchars($message['title']) . "'>
            <div>
                <div class='sender'>" . htmlspecialchars($message['title']) . "</div>
                <div class='message-date'>" . $message['timestamp'] . "</div>
            </div>
            <div class='message-actions'>
                <form action='../chat/index.php' method='post' class='form-chat'>
                    <input type='hidden' name='title' value='" . htmlspecialchars($message['title']) . "'>
                    <button name='chat' class='menu-button' value='$receiver_id'><i class='bx bxs-message-dots'></i></button>";
        if ($message['unread']) {
            echo "<span class='unread-indicator'><i class='bx bxs-circle'></i></span>";
        }
        echo "
                </form>
                <div class='menu-container' data-title='" . htmlspecialchars($message['title']) . "'>
                    <button type='button' class='menu-button'><i class='bx bx-dots-vertical-rounded'></i></button>
                    <div class='dropdown-menu'>
                        <button type='button' class='delete-button' data-title='" . htmlspecialchars($message['title']) . "'>Delete</button>
                    </div>
                </div>
            </div>
        </div>";
    }
}
