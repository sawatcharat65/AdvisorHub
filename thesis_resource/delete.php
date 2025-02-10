<?php
session_start();
require('../server.php');

if (empty($_SESSION['username'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$file_id = $_POST['file_id'] ?? null;
if (!$file_id) {
    echo json_encode(['error' => 'File ID not provided']);
    exit;
}

try {
    // ตรวจสอบสิทธิ์ในการลบไฟล์
    $sql = "SELECT tr.*, ar.advisor_id, ar.student_id, ac.role
            FROM thesis_resource tr
            JOIN advisor_request ar ON tr.advisor_request_id = ar.id
            JOIN account ac ON tr.uploader_id = ac.id
            WHERE tr.id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Database preparation failed');
    }
    
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc();
    
    if (!$file) {
        throw new Exception('File not found');
    }
    
    // ตรวจสอบว่าเป็นเจ้าของไฟล์หรือไม่
    $current_user_id = $_SESSION['username'];
    if ($file['uploader_id'] !== $current_user_id) {
        throw new Exception('Permission denied');
    }
    
    // ลบไฟล์
    $sql = "DELETE FROM thesis_resource WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $file_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to delete file');
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}
?>