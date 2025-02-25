<?php
session_start();
require('../server.php');
if (isset($_SESSION['username']) && $_SESSION['role'] != 'admin' || empty($_SESSION['username'])) {
    header('location: /AdvisorHub/login');
    exit();
}

// ตรวจสอบว่ามีการร้องขอแบบ POST และผู้ใช้ล็อกอินอยู่หรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['account_id']) && isset($_POST['message_id'])) {
    $messageId = $conn->real_escape_string($_POST['message_id']);
    $userId = $_SESSION['account_id'];

   // ตรวจสอบว่าผู้ใช้เป็นผู้ส่งหรือผู้รับ
    $sql = "SELECT message_file_name, message_file_data, message_file_type 
            FROM messages 
            WHERE message_id = '$messageId' ";

    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        $fileName = $row['message_file_name'];
        $fileData = $row['message_file_data'];
        $fileType = $row['message_file_type'];

       // กำหนด Content-Type ตามประเภทของไฟล์
        switch ($fileType) {
            case 'image/jpeg':
                header("Content-Type: image/jpeg");
                break;
            case 'image/png':
                header("Content-Type: image/png");
                break;
            case 'application/pdf':
                header("Content-Type: application/pdf");
                break;
            case 'application/msword':
                header("Content-Type: application/msword");
                break;
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
                break;
            case 'application/vnd.ms-powerpoint':
                header("Content-Type: application/vnd.ms-powerpoint");
                break;
            case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
                header("Content-Type: application/vnd.openxmlformats-officedocument.presentationml.presentation");
                break;
            case 'application/zip':
                header("Content-Type: application/zip");
                break;
            case 'text/plain':
                header("Content-Type: text/plain");
                break;
            default:
                header("Content-Type: application/octet-stream");
        }

       // กำหนดส่วน headers  สำหรับการดาวน์โหลด
        header("Content-Disposition: attachment; filename=\"" . basename($fileName) . "\"");
        header("Content-Length: " . strlen($fileData));
        header("Cache-Control: private, max-age=0, must-revalidate");
        header("Pragma: public");

        // Output file data
        echo $fileData;
        exit;
    } else {
        echo "File not found or you don't have permission.";
    }
} else {
    echo "Invalid request or access denied.";
}
?>