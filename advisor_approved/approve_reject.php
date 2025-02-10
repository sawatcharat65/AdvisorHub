<?php
session_start();
include('../server.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];
    
    if (isset($_POST['approve'])) {
        $is_advisor_approved = 1; // อนุมัติ
    } elseif (isset($_POST['reject'])) {
        $is_advisor_approved = 2; // ปฏิเสธ
    } else {
        header("Location: request.php");
        exit();
    }

    // อัปเดตสถานะในฐานข้อมูล
    $sql = "UPDATE advisor_request SET is_advisor_approved = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $is_advisor_approved, $request_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "อัปเดตสถานะสำเร็จ!";
    } else {
        $_SESSION['message'] = "เกิดข้อผิดพลาด: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    // รีเฟรชกลับไปยัง details.php ของคำร้องที่เพิ่งอนุมัติ/ปฏิเสธ
    header("Location: details.php?id=" . $request_id);
    exit();
}
?>