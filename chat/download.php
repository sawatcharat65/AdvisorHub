<?php
session_start();
require('../server.php');

// ตรวจสอบว่ามีการส่ง POST และผู้ใช้ล็อกอินอยู่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['account_id']) && isset($_POST['file-clicked-message-id'])) {
    $messageId = $conn->real_escape_string($_POST['file-clicked-message-id']);
    $userId = $_SESSION['account_id'];

    // ตรวจสอบว่าผู้ใช้มีสิทธิ์ดาวน์โหลดไฟล์นี้ (เป็น sender หรือ receiver)
    $sql = "SELECT message_file_name, message_file_data, message_file_type 
            FROM messages 
            WHERE message_id = '$messageId' 
            AND (sender_id = '$userId' OR receiver_id = '$userId')";
    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        $fileName = $row['message_file_name'];
        $fileData = $row['message_file_data'];
        $fileType = $row['message_file_type'];

        // ตั้งค่า Content-Type ตามประเภทไฟล์
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

        // ตั้งค่า header สำหรับการดาวน์โหลด
        header("Content-Disposition: attachment; filename=\"" . basename($fileName) . "\"");
        header("Content-Length: " . strlen($fileData));
        header("Cache-Control: private, max-age=0, must-revalidate");
        header("Pragma: public");

        // ส่งข้อมูลไฟล์
        echo $fileData;
        exit;
    } else {
        echo "File not found or you don't have permission.";
    }
} else {
    echo "Invalid request or access denied.";
}
?>