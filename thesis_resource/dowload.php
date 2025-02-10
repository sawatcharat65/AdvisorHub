<?php
session_start();
require('../server.php');

if (empty($_SESSION['username'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$file_id = $_POST['file_id'] ?? null;
$thesis_id = $_POST['thesis_id'] ?? null;

if (!$file_id || !$thesis_id) {
    http_response_code(400);
    exit('Missing required parameters');
}

try {
    // ดึงข้อมูลไฟล์และตรวจสอบการมีอยู่ของไฟล์
    $sql = "SELECT tr.* 
            FROM thesis_resource tr
            WHERE tr.id = ? AND tr.advisor_request_id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Database preparation failed');
    }
    
    $stmt->bind_param("ii", $file_id, $thesis_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc();
    
    if (!$file) {
        throw new Exception('File not found');
    }
    
    // ตั้งค่า headers สำหรับการดาวน์โหลด
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file['file_name'] . '"');
    header('Content-Length: ' . strlen($file['file_data']));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    echo $file['file_data'];
    
} catch (Exception $e) {
    http_response_code(500);
    exit($e->getMessage());
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}
?>