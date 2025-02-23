<?php
session_start();
require('../server.php');

if (isset($_POST['search'])) {
    $search = $conn->real_escape_string($_POST['search']);
    $id = $_SESSION['account_id'];
    $receiver_id = $_SESSION['receiver_id'];

    // Fetch approval timestamp
    $sql = "SELECT time_stamp FROM advisor_request 
            WHERE ((requester_id = '$id' AND advisor_id = '$receiver_id') 
            OR (student_id = '$id' AND advisor_id = '$receiver_id'))
            AND is_advisor_approved = 1 
            AND is_admin_approved = 1 
            ORDER BY time_stamp ASC LIMIT 1";
    $result = $conn->query($sql);
    $approval_timestamp = $result->num_rows > 0 ? $result->fetch_assoc()['time_stamp'] : null;

    $before_messages = [];
    $after_messages = [];

    // Search messages
    $sql = "
        SELECT message_title, MAX(time_stamp) AS latest_time
        FROM messages
        WHERE ((sender_id = '$id' AND receiver_id = '$receiver_id') 
        OR (sender_id = '$receiver_id' AND receiver_id = '$id'))
        AND message_title LIKE '%$search%'
        GROUP BY message_title
        ORDER BY latest_time DESC
    ";
    $messages_result = $conn->query($sql);
    if (!$messages_result) {
        echo "<p>Error querying database: " . $conn->error . "</p>";
        exit;
    }

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

            if ($approval_timestamp === null || $row['latest_time'] <= $approval_timestamp) {
                $before_messages[] = $message;
            } else {
                $after_messages[] = $message;
            }
        }
    }
?>
    <div class='topic-section after-approve active' data-section="after">
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
                                <form action="/AdvisorHub/topic_chat/topic_chat.php" method="post" onsubmit="return confirm('Are you sure you want to delete this topic?');">
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

    <div class='topic-section before-approve' data-section="before">
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
                                <form action="/AdvisorHub/topic_chat/topic_chat.php" method="post" onsubmit="return confirm('Are you sure you want to delete this topic?');">
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
<?php
}
?>