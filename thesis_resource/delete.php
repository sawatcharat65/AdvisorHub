<?php
session_start();
require('../server.php');

$file_id = $_POST['file_id'] ?? null;
if (!$file_id) {
   echo json_encode(['error' => 'File ID not provided']);
   exit;
}

try {
   // ตรวจสอบสิทธิ์ทั้ง advisor และ student
   $sql = "SELECT tr.*, ar.advisor_id, ar.student_id
           FROM thesis_resource tr
           JOIN advisor_request ar ON tr.advisor_request_id = ar.advisor_request_id
           WHERE tr.thesis_resource_id = ?";
   $stmt = $conn->prepare($sql);
   $stmt->bind_param("i", $file_id);
   $stmt->execute();
   $result = $stmt->get_result();
   $file = $result->fetch_assoc();

   if (!$file) {
       throw new Exception('File not found');
   }
   
   // ตรวจสอบข้อมูลผู้ใช้ปัจจุบัน
   $current_user_role = $_SESSION['role'];
   $current_username = $_SESSION['username'];
   
   // เริ่มจากกำหนดว่ายังไม่มีสิทธิ์
   $can_delete = false;
   
   // เพิ่มการแสดงข้อมูลเพื่อดีบัก
   $debug_info = [
       'role' => $current_user_role,
       'username' => $current_username,
       'advisor_id' => $file['advisor_id'],
       'student_ids' => $file['student_id']
   ];

   // กรณีเป็นอาจารย์
   if ($current_user_role === 'advisor') {
       // ดึง advisor_id จาก first_name
       $advisor_query = "SELECT advisor_id FROM advisor WHERE advisor_first_name = ?";
       $stmt = $conn->prepare($advisor_query);
       $stmt->bind_param("s", $current_username);
       $stmt->execute();
       $advisor_result = $stmt->get_result();
       $advisor_data = $advisor_result->fetch_assoc();
       
       $debug_info['advisor_data'] = $advisor_data;
       
       // เช็คว่าเป็นอาจารย์ที่ปรึกษาของงานนี้หรือไม่
       if ($advisor_data && $file['advisor_id'] === $advisor_data['advisor_id']) {
           $can_delete = true;
       }
   } 
   // กรณีเป็นนักศึกษา
   elseif ($current_user_role === 'student') {
       // ดึง student_id จาก first_name
       $student_query = "SELECT student_id FROM student WHERE student_first_name = ?";
       $stmt = $conn->prepare($student_query);
       $stmt->bind_param("s", $current_username);
       $stmt->execute();
       $student_result = $stmt->get_result();
       $student_data = $student_result->fetch_assoc();
       
       $debug_info['student_data'] = $student_data;
       
       // เช็คว่าเป็นนักศึกษาที่ทำงานนี้หรือไม่
       if ($student_data) {
           $student_ids = json_decode($file['student_id'], true);
           if (in_array($student_data['student_id'], $student_ids)) {
               $can_delete = true;
           }
       }
   }
   // กรณีเป็น admin (เพิ่มเติม)
   elseif ($current_user_role === 'admin') {
       $can_delete = true;
   }

   // เพิ่มข้อมูลว่ามีสิทธิ์ลบหรือไม่
   $debug_info['can_delete'] = $can_delete;

   // ดำเนินการลบไฟล์
   if ($can_delete) {
       $sql = "DELETE FROM thesis_resource WHERE thesis_resource_id = ?";
       $stmt = $conn->prepare($sql);
       $stmt->bind_param("i", $file_id);
       
       if (!$stmt->execute()) {
           throw new Exception('Failed to delete file: ' . $conn->error);
       }
       
       echo json_encode(['success' => true]);
   } else {
       // ส่งข้อมูลดีบักกลับไปด้วย
       throw new Exception('Permission denied: ' . json_encode($debug_info));
   }
   
} catch (Exception $e) {
   echo json_encode(['error' => $e->getMessage()]);
}
?>