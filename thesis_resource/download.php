<?php 
session_start();
require('../server.php'); 

if (!isset($_POST['file_id'])) {
    exit('File ID not provided');
}

$file_id = intval($_POST['file_id']); 

// SQL Query เพื่อดึงข้อมูลไฟล์จากฐานข้อมูล
$sql = "SELECT file_name, file_data, file_type FROM thesis_resource WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $file_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // ดึงข้อมูลไฟล์
    $stmt->bind_result($file_name, $file_data, $file_type);
    $stmt->fetch();

    // กำหนด Content-Type ตามประเภทไฟล์
    switch ($file_type) {
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

    // กำหนด Headers สำหรับการดาวน์โหลด
    header("Content-Disposition: attachment; filename=\"" . basename($file_name) . "\"");
    header("Content-Length: " . strlen($file_data));
    header("Cache-Control: private, max-age=0, must-revalidate");
    header("Pragma: public");

    // ส่งข้อมูลไฟล์
    echo $file_data;
    exit;
} else {
    exit("File not found");
}
?>