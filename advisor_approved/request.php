<?php
session_start();
include('../components/navbar.php');
include('../server.php'); // เชื่อมต่อฐานข้อมูล

if (isset($_POST['logout'])) {
    session_destroy();
    header('location: /AdvisorHub/login');
}

if (isset($_POST['profile'])) {
    header('location: /AdvisorHub/profile');
}

if (empty($_SESSION['username'])) {
    header('location: /AdvisorHub/login');
}

// ตรวจสอบว่าเป็นอาจารย์หรือไม่
if (isset($_SESSION['username']) && $_SESSION['role'] == 'admin') {
    header('location: /AdvisorHub/advisor');
}

$id = $_SESSION['id'];

?>

<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการคำร้องทั้งหมด</title>
    <link rel="stylesheet" href="style_request.css">
    <link rel="icon" href="../Logo.png">
</head>
<body>
    <?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'Teams'])?>

    <div class="container">
        <?php
        //ถ้าเป็น advisor
        if ($_SESSION['role'] == 'advisor') {
            // ดึงข้อมูลเฉพาะคำร้องที่ advisor_id ตรงกับอาจารย์ที่ล็อกอิน
            $sql = "SELECT id, student_id, thesis_topic_thai, is_advisor_approved, is_admin_approved, time_stamp 
            FROM advisor_request 
            WHERE advisor_id = ? AND is_advisor_approved = 0
            ORDER BY time_stamp DESC";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
                    // ตรวจสอบว่า student_id เป็น JSON หรือ String
                    $student_ids = json_decode($row['student_id'], true);
                    if (!is_array($student_ids)) {
                        $student_ids = [$row['student_id']];
                    }
                    ?>
                    <div class="card">
                        <h5 class="card-title">หัวข้อวิทยานิพนธ์: <?php echo htmlspecialchars($row["thesis_topic_thai"]); ?></h5>
                        <ul class="list-group">
                            <?php foreach ($student_ids as $student_id): ?>
                                <li class="list-group-item">
                                    <strong>รหัสนิสิต:</strong> <?php echo htmlspecialchars($student_id); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="d-flex align-items-center mt-3">
                            <a href="details.php?id=<?php echo $row['id']; ?>" class="btn-orange">รายละเอียด</a>
                            <span class="timestamp"> <?php echo $row["time_stamp"]; ?></span>
                        </div>
                    </div>
                <?php endwhile;
            else: ?>
                <p class="no-request">ไม่มีข้อมูลคำร้อง</p>
            <?php endif; ?>

        <?php 
            $stmt->close();
            $conn->close();

        } elseif ($_SESSION['role'] == 'student') {
            // ดึงข้อมูลคำร้องที่เกี่ยวข้องกับ student_id
            $sql = "SELECT id, thesis_topic_thai, student_id, is_advisor_approved, is_admin_approved, time_stamp 
                    FROM advisor_request 
                    WHERE JSON_CONTAINS(student_id, ?) 
                    ORDER BY time_stamp DESC";
        
            $stmt = $conn->prepare($sql);
            // ใช้ json_encode เพื่อแปลง $id เป็น JSON string
            $json_id = json_encode([$id]); 
            $stmt->bind_param("s", $json_id); // ค่าของ student_id ที่เป็น JSON
            $stmt->execute();
            $result = $stmt->get_result();
        
            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
                    // กำหนดสถานะคำร้อง
                    if ($row['is_advisor_approved'] == 0) {
                        $advisor_status = '<span class="status waiting">Waiting</span>';
                    } elseif ($row['is_advisor_approved'] == 1) {
                        $advisor_status = '<span class="status approved">Approved</span>';
                    } elseif ($row['is_advisor_approved'] == 2) {
                        $advisor_status = '<span class="status rejected">Rejected</span>';
                    }
                    
                    if ($row['is_admin_approved'] == 0) {
                        $admin_status = '<span class="status waiting">Waiting</span>';
                    } elseif ($row['is_admin_approved'] == 1) {
                        $admin_status = '<span class="status approved">Approved</span>';
                    } elseif ($row['is_admin_approved'] == 2) {
                        $admin_status = '<span class="status rejected">Rejected</span>';
                    }
                    ?>
                    <div class="request-card">
                        <h3 class="request-title">หัวข้อวิทยานิพนธ์: <?php echo htmlspecialchars($row["thesis_topic_thai"]); ?></h3>
                        <div class="request-info">
                            <p><strong>Advisor Approval Status:</strong> <?php echo $advisor_status; ?></p>
                            <p><strong>Admin Approval Status:</strong> <?php echo $admin_status; ?></p>
                        </div>
                        <div class="request-footer">
                            <span class="timestamp"><?php echo $row["time_stamp"]; ?></span>
                        </div>
                    </div>
                <?php endwhile;
            else: ?>
                <div class="no-request-message">
                    <p>คุณยังไม่มีคำร้อง</p>
                </div>
            <?php endif; ?>
        
        <?php
            $stmt->close();
            $conn->close();
        }
        ?>
    </div>
</body>
</html>
