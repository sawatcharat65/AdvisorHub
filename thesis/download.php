<?php
require('../server.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);  // รับค่า ID ของวิทยานิพนธ์

    $sql = "SELECT thesis_file, title, thesis_file_type FROM thesis WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($thesis_file, $title, $thesis_file_type);
        $stmt->fetch();

        // กำหนดชื่อไฟล์ใหม่โดยใช้ title
        $filename = $title . $thesis_file_type;

        // ส่งข้อมูลไฟล์เป็น BLOB กลับไปยัง JavaScript
        header("Content-Type: application/".$thesis_file_type);  // ประเภทไฟล์ ZIP
        header("Content-Disposition: attachment; filename=\"" . $filename . "\"");  // กำหนดชื่อไฟล์
        echo $thesis_file;
        exit;
    } else {
        echo "ไม่พบไฟล์สำหรับดาวน์โหลด";
    }
}
