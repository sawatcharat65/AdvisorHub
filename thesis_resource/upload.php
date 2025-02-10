<?php
session_start();
require('../server.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_FILES['file']) || !isset($_POST['thesis_id'])) {
    echo json_encode(['error' => 'Missing file or thesis ID']);
    exit;
}

$file = $_FILES['file'];
$thesis_id = $_POST['thesis_id'];
$user_id = $_SESSION['id'];

// ตรวจสอบสิทธิ์
$sql = "SELECT * FROM advisor_request WHERE id = ? AND (advisor_id = ? OR student_id LIKE ?)";
$stmt = $conn->prepare($sql);
$search_id = '%' . $user_id . '%';
$stmt->bind_param("iss", $thesis_id, $user_id, $search_id);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

try {
    $file_data = file_get_contents($file['tmp_name']);
    $sql = "INSERT INTO thesis_resource (uploader_id, file_name, file_data, advisor_request_id) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $user_id, $file['name'], $file_data, $thesis_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to save file');
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}