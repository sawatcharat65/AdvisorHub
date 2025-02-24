<?php
session_start();
require('../server.php');
include('../components/navbar.php');

// จัดการการออกจากระบบ
if (isset($_POST['logout'])) {
    session_destroy();
    header('location: /AdvisorHub/login');
    exit();
}

// ตรวจสอบว่าล็อกอินหรือยัง
if (empty($_SESSION['username'])) {
    header('location: /AdvisorHub/login');
    exit();
}

// เปลี่ยนหน้าไปโปรไฟล์
if (isset($_POST['profile'])) {
    header('location: /AdvisorHub/profile');
    exit();
}

// ตรวจสอบ receiver_id
if (empty($_SESSION['receiver_id']) || $_SESSION['receiver_id'] == $_SESSION['account_id']) {
    header('location: /AdvisorHub/advisor');
    exit();
}

// จัดการ profileInbox
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

// ดึงข้อมูลของ receiver
$receiver_id = $_SESSION['receiver_id'];
$sql = "SELECT advisor_first_name, advisor_last_name FROM advisor WHERE advisor_id = '$receiver_id' 
        UNION 
        SELECT student_first_name, student_last_name FROM student WHERE student_id = '$receiver_id'";
$result = $conn->query($sql);
$receiver = $result->fetch_assoc();

// ตรวจสอบสถานะการอนุมัติ
$id = $_SESSION['account_id'];
$sql = "SELECT COUNT(*) as approved FROM advisor_request 
        WHERE (
            (JSON_CONTAINS(student_id, '\"$id\"') AND advisor_id = '$receiver_id')
            OR 
            (advisor_id = '$id' AND JSON_CONTAINS(student_id, '\"$receiver_id\"'))
        ) 
        AND is_advisor_approved = 1 
        AND is_admin_approved = 1";
$result = $conn->query($sql);
$is_fully_approved = $result->fetch_assoc()['approved'] > 0;

// ดึง timestamp การอนุมัติ
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

$messages_per_page = 5;
$before_messages = [];
$after_messages = [];

// ดึงข้อความทั้งหมด
$sql = "
    SELECT message_title, MAX(time_stamp) AS latest_time
    FROM messages
    WHERE (sender_id = '$id' AND receiver_id = '$receiver_id') 
    OR (sender_id = '$receiver_id' AND receiver_id = '$id')
    GROUP BY message_title
    ORDER BY latest_time DESC
";
$messages_result = $conn->query($sql);

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

$before_messages_total = count($before_messages);
$after_messages_total = count($after_messages);
$before_messages_limited = array_slice($before_messages, 0, $messages_per_page);
$after_messages_limited = array_slice($after_messages, 0, $messages_per_page);
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'Teams']); ?>

    <div class='topic-container'>
        <div class='topic-head'>
            <h2><?php echo $receiver['advisor_first_name'] . ' ' . $receiver['advisor_last_name']; ?></h2>
            <a href="topic_create.php" class="fa-solid fa-circle-plus"></a>
        </div>

        <div class="topic-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="search-input" placeholder="Search topic" value="" />
        </div>

        <div class="topic-status">
            <?php if ($is_fully_approved): ?>
                <button class="active" data-section="after">Post-Approval</button>
                <button data-section="before">Pre-Approval</button>
            <?php else: ?>
                <button class="active" data-section="before">Pre-Approval</button>
            <?php endif; ?>
        </div>

        <div class='divider'></div>

        <div id="search-results">
            <!-- ข้อความหลังอนุมัติ -->
            <div class='topic-section after-approve <?php echo $is_fully_approved ? 'active' : ''; ?>' data-section="after">
                <h3>After Becoming an Advisor</h3>
                <div class="message-container" data-type="after">
                    <?php if (empty($after_messages)): ?>
                        <p>No messages found.</p>
                    <?php else: ?>
                        <?php foreach ($after_messages_limited as $message): ?>
                            <div class='message' data-title="<?php echo htmlspecialchars($message['title']); ?>">
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
                                            <button type="button" class="delete-button" data-title="<?php echo htmlspecialchars($message['title']); ?>">Delete</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if ($after_messages_total > $messages_per_page): ?>
                            <button class="view-more" data-type="after" data-offset="<?php echo $messages_per_page; ?>" data-total="<?php echo $after_messages_total; ?>">View More</button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ข้อความก่อนอนุมัติ -->
            <div class='topic-section before-approve <?php echo !$is_fully_approved ? 'active' : ''; ?>' data-section="before">
                <h3>Before Becoming an Advisor</h3>
                <div class="message-container" data-type="before">
                    <?php if (empty($before_messages)): ?>
                        <p>No messages found.</p>
                    <?php else: ?>
                        <?php foreach ($before_messages_limited as $message): ?>
                            <div class='message' data-title="<?php echo htmlspecialchars($message['title']); ?>">
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
                                            <button type="button" class="delete-button" data-title="<?php echo htmlspecialchars($message['title']); ?>">Delete</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if ($before_messages_total > $messages_per_page): ?>
                            <button class="view-more" data-type="before" data-offset="<?php echo $messages_per_page; ?>" data-total="<?php echo $before_messages_total; ?>">View More</button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // จัดการการคลิกปุ่มสถานะ
            $('.topic-status button').on('click', function() {
                $('.topic-status button').removeClass('active');
                $(this).addClass('active');

                const section = $(this).data('section');
                $('.topic-section').removeClass('active');
                $(`.topic-section[data-section="${section}"]`).addClass('active');
            });

            // ฟังก์ชัน debounce สำหรับการค้นหา
            function debounce(func, wait) {
                let timeout;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            // การค้นหาด้วย AJAX
            const performSearch = debounce(function(searchQuery) {
                $.ajax({
                    url: "search_topic.php",
                    method: "POST",
                    data: {
                        search: searchQuery
                    },
                    success: function(response) {
                        $("#search-results").html(response);
                        const activeSection = $('.topic-status button.active').data('section');
                        $('.topic-section').removeClass('active');
                        $(`.topic-section[data-section="${activeSection}"]`).addClass('active');
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error: ", status, error);
                    }
                });
            }, 100);

            $("#search-input").on("input", function() {
                let searchQuery = $(this).val();
                performSearch(searchQuery);
            });

            // จัดการเมนูดรอปดาวน์
            $(document).on('click', '.menu-button', function() {
                const $menuContainer = $(this).closest('.menu-container');
                const $dropdownMenu = $menuContainer.find('.dropdown-menu');
                $dropdownMenu.toggleClass('active');
                $('.dropdown-menu.active').not($dropdownMenu).removeClass('active');
            });

            $(document).on('click', function(event) {
                if (!$(event.target).closest('.menu-container').length) {
                    $('.dropdown-menu.active').removeClass('active');
                }
            });

            // จัดการปุ่ม View More
            $(document).on('click', '.view-more', function() {
                const $button = $(this);
                const type = $button.data('type');
                const offset = parseInt($button.data('offset'));
                const total = parseInt($button.data('total'));
                const receiver_id = '<?php echo $receiver_id; ?>';

                $.ajax({
                    url: 'load_more_messages.php',
                    method: 'POST',
                    data: {
                        type: type,
                        offset: offset,
                        receiver_id: receiver_id
                    },
                    success: function(response) {
                        $button.before(response);
                        const newOffset = offset + 5;
                        $button.data('offset', newOffset);
                        if (newOffset >= total) {
                            $button.remove();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error: ", status, error);
                    }
                });
            });

            // จัดการการลบด้วย AJAX
            $(document).on('click', '.delete-button', function() {
                const title = $(this).data('title');
                const $message = $(this).closest('.message');
                const $container = $message.closest('.message-container'); // Container ของข้อความ
                const $viewMore = $container.find('.view-more'); // ปุ่ม View More
                const receiver_id = '<?php echo $receiver_id; ?>';
                const messagesPerPage = <?php echo $messages_per_page; ?>; // จำนวนข้อความต่อหน้า (5)

                if (confirm('Are you sure you want to delete this topic?')) {
                    $.ajax({
                        url: 'delete_message.php',
                        method: 'POST',
                        data: {
                            title: title,
                            receiver_id: receiver_id
                        },
                        success: function(response) {
                            if (response === 'success') {
                                $message.remove(); // ลบข้อความออกจากหน้า

                                // จำนวนข้อความที่แสดงอยู่ใน container
                                const remainingMessages = $container.find('.message').length;

                                // ดึงจำนวนข้อความทั้งหมดจาก data-total ของ view-more หรือคำนวณจากเริ่มต้น
                                let totalMessages = $viewMore.length ? parseInt($viewMore.data('total')) : remainingMessages + 1;
                                totalMessages -= 1; // ลดจำนวนลง 1 หลังลบ

                                // อัปเดต data-total ใน view-more ถ้ามี
                                if ($viewMore.length) {
                                    $viewMore.data('total', totalMessages);
                                }

                                // ถ้าจำนวนที่แสดง < 5 และยังมีข้อความเหลือในฐานข้อมูล
                                if (remainingMessages < messagesPerPage && totalMessages > remainingMessages) {
                                    $.ajax({
                                        url: 'load_more_messages.php',
                                        method: 'POST',
                                        data: {
                                            type: $container.data('type'),
                                            offset: remainingMessages, // เริ่มจากตำแหน่งที่เหลือ
                                            receiver_id: receiver_id
                                        },
                                        success: function(response) {
                                            $container.find('.message').last().after(response); // เพิ่มข้อความใหม่ต่อจากข้อความสุดท้าย
                                            // อัปเดต remainingMessages ใหม่หลังโหลด
                                            const newRemainingMessages = $container.find('.message').length;

                                            // จัดการปุ่ม View More
                                            if (totalMessages > newRemainingMessages) {
                                                if (!$viewMore.length) {
                                                    $container.append(
                                                        `<button class="view-more" data-type="${$container.data('type')}" data-offset="${newRemainingMessages}" data-total="${totalMessages}">View More</button>`
                                                    );
                                                }
                                            } else {
                                                $viewMore.remove(); // ลบปุ่มถ้าทุกข้อความแสดงหมดแล้ว
                                            }
                                        },
                                        error: function(xhr, status, error) {
                                            console.error("AJAX Error: ", status, error);
                                        }
                                    });
                                } else {
                                    // จัดการปุ่ม View More ถ้าไม่ต้องโหลดเพิ่ม
                                    if (totalMessages <= remainingMessages) {
                                        $viewMore.remove(); // ลบปุ่มถ้าทุกข้อความแสดงหมดแล้ว
                                    } else if (totalMessages > remainingMessages && !$viewMore.length) {
                                        $container.append(
                                            `<button class="view-more" data-type="${$container.data('type')}" data-offset="${remainingMessages}" data-total="${totalMessages}">View More</button>`
                                        );
                                    }
                                }

                                // ถ้าไม่มีข้อความเหลือ
                                if (remainingMessages === 0) {
                                    $container.html('<p>No messages found.</p>');
                                }
                            } else {
                                alert('Failed to delete the topic.');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error: ", status, error);
                            alert('An error occurred while deleting the topic.');
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>