<?php
require('../server.php');

if (isset($_GET['thesis_id'])) {
    $thesis_id = intval($_GET['thesis_id']);  // รับค่า thesis_id ของวิทยานิพนธ์

    $sql = "SELECT thesis_title, thesis_file, thesis_file_type FROM thesis WHERE thesis_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $thesis_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($thesis_title, $thesis_file, $thesis_file_type);
        $stmt->fetch();

        // กำหนดชื่อไฟล์ใหม่โดยใช้ title
        $filename = $thesis_title . $thesis_file_type;

        // ส่งข้อมูลไฟล์เป็น BLOB กลับไปยัง JavaScript
        header("Content-Type: application/" . $thesis_file_type);
        header("Content-Disposition: attachment; filename=\"" . $filename . "\"");  // กำหนดชื่อไฟล์
        echo $thesis_file;
        exit;
    } else {
        echo "ไม่พบไฟล์สำหรับดาวน์โหลด";
    }
}
