<?php
session_start();
require('../server.php');

if (empty($_SESSION['id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_FILES['file']) || !isset($_POST['thesis_id'])) {
    echo json_encode(['error' => 'Missing file or thesis ID']);
    exit;
}

$file = $_FILES['file'];
$thesis_id = $_POST['thesis_id'];
$user_id = $_SESSION['id'];
$file_type = $file['type'];

try {
    // อ่านข้อมูลไฟล์
    $file_data = file_get_contents($file['tmp_name']);
    
    // บันทึกลงฐานข้อมูล
    $sql = "INSERT INTO thesis_resource (uploader_id, file_name, file_data, advisor_request_id, file_type) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Database preparation failed: ' . $conn->error);
    }
    
    $stmt->bind_param("sssis", $user_id, $file['name'], $file_data, $thesis_id, $file_type);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to save to database: ' . $stmt->error);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'File uploaded successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'file_type' => $file_type,
        'thesis_id' => $thesis_id,
        'user_id' => $user_id
    ]);
}
?>