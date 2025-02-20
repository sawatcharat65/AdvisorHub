<?php
session_start();
require('../server.php');

$file_id = $_POST['file_id'] ?? null;
if (!$file_id) {
   echo json_encode(['error' => 'File ID not provided']);
   exit;
}

try {
   $sql = "SELECT tr.*, ar.advisor_id, ar.student_id
           FROM thesis_resource tr
           JOIN advisor_request ar ON tr.advisor_request_id = ar.id
           WHERE tr.thesis_resource_id = ?";
   $stmt = $conn->prepare($sql);
   $stmt->bind_param("i", $file_id);
   $stmt->execute();
   $result = $stmt->get_result();
   $file = $result->fetch_assoc();

   $can_delete = false;

   if ($_SESSION['role'] === 'advisor') {
       $advisor_query = "SELECT id FROM advisor WHERE first_name = ?";
       $stmt = $conn->prepare($advisor_query);
       $stmt->bind_param("s", $_SESSION['username']);
       $stmt->execute();
       $advisor_result = $stmt->get_result();
       $advisor_data = $advisor_result->fetch_assoc();
       if ($file['advisor_id'] === $advisor_data['id']) {
           $can_delete = true;
       }
   } elseif ($_SESSION['role'] === 'student') {
       $student_ids = json_decode($file['student_id'], true);
       if (in_array($_SESSION['id'], $student_ids)) {
           $can_delete = true;
       }
   }

   if ($can_delete) {
       $sql = "DELETE FROM thesis_resource WHERE thesis_resource_id = ?";
       $stmt = $conn->prepare($sql);
       $stmt->bind_param("i", $file_id);
       
       if (!$stmt->execute()) {
           throw new Exception('Failed to delete file');
       }
       
       echo json_encode(['success' => true]);
   } else {
       throw new Exception('Permission denied');
   }
   
} catch (Exception $e) {
   echo json_encode(['error' => $e->getMessage()]);
}
?>