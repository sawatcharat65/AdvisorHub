<?php
session_start();
require('../server.php');


//ไม่ให้ admin เข้าถึง
if(isset($_SESSION['username']) && $_SESSION['role'] == 'admin'){
    header('location: /AdvisorHub/advisor');
}

if (empty($_SESSION['username'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_FILES['file']) || !isset($_POST['thesis_id'])) {
    echo json_encode(['error' => 'Missing file or thesis ID']);
    exit;
}

$file = $_FILES['file'];
$thesis_id = $_POST['thesis_id'];
$username = $_SESSION['username'];

// ดึง account_id จากชื่อผู้ใช้
$account_query = "SELECT account_id FROM account WHERE ";
if ($_SESSION['role'] === 'student') {
    $account_query .= "account_id IN (SELECT student_id FROM student WHERE student_first_name = ?)";
} else if ($_SESSION['role'] === 'advisor') {
    $account_query .= "account_id IN (SELECT advisor_id FROM advisor WHERE advisor_first_name = ?)";
} else {
    echo json_encode(['error' => 'Invalid user role']);
    exit;
}

$stmt = $conn->prepare($account_query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'User not found in database']);
    exit;
}

$account_data = $result->fetch_assoc();
$user_id = $account_data['account_id'];
$file_type = $file['type'];

try {
    // อ่านข้อมูลไฟล์
    $file_data = file_get_contents($file['tmp_name']);
    
    // บันทึกลงฐานข้อมูล
    $sql = "INSERT INTO thesis_resource (uploader_id, thesis_resource_file_name, thesis_resource_file_data, advisor_request_id, thesis_resource_file_type) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Database preparation failed: ' . $conn->error);
    }
    
    $stmt->bind_param("sssis", $user_id, $file['name'], $file_data, $thesis_id, $file_type);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to save to database: ' . $stmt->error . ' (' . $conn->errno . ')');
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