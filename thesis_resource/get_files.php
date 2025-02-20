<?php
session_start();
require('../server.php');

if (empty($_SESSION['username'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$thesis_id = $_POST['thesis_id'] ?? null;
if (!$thesis_id) {
    echo json_encode(['error' => 'Thesis ID not provided']);
    exit;
}

try {
    $sql = "SELECT tr.thesis_resource_id, tr.thesis_resource_file_name, tr.uploader_id, tr.time_stamp,
                   CASE 
                       WHEN ac.role = 'advisor' THEN CONCAT(a.first_name, ' ', a.last_name)
                       WHEN ac.role = 'student' THEN CONCAT(s.first_name, ' ', s.last_name)
                       ELSE 'Unknown'
                   END as uploader_name,
                   ac.role as uploader_role
            FROM thesis_resource tr
            LEFT JOIN account ac ON tr.uploader_id = ac.id
            LEFT JOIN advisor a ON tr.uploader_id = a.id
            LEFT JOIN student s ON tr.uploader_id = s.id
            WHERE tr.advisor_request_id = ?
            ORDER BY tr.time_stamp DESC";
            
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Database preparation failed');
    }
    
    $stmt->bind_param("i", $thesis_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $files = [];
    while ($row = $result->fetch_assoc()) {
        $files[] = [
            'id' => $row['thesis_resource_id'],
            'file_name' => $row['thesis_resource_file_name'],
            'uploader_id' => $row['uploader_id'],
            'uploader_name' => $row['uploader_name'],
            'uploader_role' => $row['uploader_role'],
            'upload_time' => $row['time_stamp']
        ];
    }
    
    echo json_encode($files);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}
?>