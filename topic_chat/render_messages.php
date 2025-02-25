<?php
function renderMessages($messages, $receiver_id, $total = null, $offset = 0, $type = null, $search_term = '')
{
    $messages_html = '';
    if (empty($messages) && $offset === 0) {
        $messages_html = "<p>No messages found.</p>";
    } else {
        foreach ($messages as $message) {
            $messages_html .= "
            <div class='message' data-title='" . htmlspecialchars($message['title']) . "'>
                <div>
                    <div class='sender'>" . htmlspecialchars($message['title']) . "</div>
                    <div class='message-date'>" . $message['timestamp'] . "</div>
                    <div class='message-options'>
                        <span class='confirm-text'>Do you want to confirm the deletion?</span>
                        <button type='button' class='approve-button' data-title='" . htmlspecialchars($message['title']) . "'><i class='fa-regular fa-circle-check'></i></button>
                        <button type='button' class='reject-button' data-title='" . htmlspecialchars($message['title']) . "'><i class='fa-regular fa-circle-xmark'></i></button>
                    </div>
                    <div class='delete-status'>
                        <span>Delete Status: <span class='status-text'>Waiting</span></span>
                    </div>
                </div>
                <div class='message-actions'>
                    <form action='../chat/index.php' method='post' class='form-chat'>
                        <input type='hidden' name='title' value='" . htmlspecialchars($message['title']) . "'>
                        <button name='chat' class='menu-button' value='$receiver_id'><i class='bx bxs-message-dots'></i></button>";
            if ($message['unread']) {
                $messages_html .= "<span class='unread-indicator'><i class='bx bxs-circle'></i></span>";
            }
            $messages_html .= "
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

    // เพิ่มปุ่ม View More ถ้ามี
    if ($total !== null && $total > ($offset + count($messages))) {
        $new_count = $offset + count($messages);
        $messages_html .= "<button class='view-more' data-type='$type' data-count='$new_count' data-total='$total' data-search='" . htmlspecialchars($search_term) . "'>View More</button>";
    }

    return $messages_html;
}
