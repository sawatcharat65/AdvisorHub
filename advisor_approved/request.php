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

if (isset($_POST['accept'])) {
    $advisor_req_id = $_POST['accept'];
    $sql = "UPDATE advisor_request SET partner_accepted = 1 WHERE advisor_request_id = '$advisor_req_id'";
    $result = $conn->query($sql);
    header('location: /AdvisorHub/advisor_approved/request.php');
    exit();
}

if (isset($_POST['reject'])) {
    $advisor_req_id = $_POST['reject'];
    $sql = "UPDATE advisor_request SET partner_accepted = 2 WHERE advisor_request_id = '$advisor_req_id'";
    $result = $conn->query($sql);
    header('location: /AdvisorHub/advisor_approved/request.php');
    exit();
}

$id = $_SESSION['account_id'];

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
    <?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'Teams']) ?>

    <div class="container">
        <?php
        //ถ้าเป็น advisor
        if ($_SESSION['role'] == 'advisor') {
            // ดึงข้อมูลเฉพาะคำร้องที่ advisor_id ตรงกับอาจารย์ที่ล็อกอิน
            $sql = "SELECT advisor_request_id, student_id, thesis_topic_thai, is_advisor_approved, is_admin_approved, time_stamp 
            FROM advisor_request 
            WHERE advisor_id = ? AND is_advisor_approved = 0 AND is_admin_approved != 2 AND partner_accepted = 1
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
                            <?php foreach ($student_ids as $student_id):
                                $sql = "SELECT * from student WHERE student_id = '$student_id'";
                                $result = $conn->query($sql);
                                $row_name = $result->fetch_assoc();

                            ?>

                                <li class="list-group-item">
                                    <strong>รหัสนิสิต:</strong> <?php echo htmlspecialchars($student_id); ?>
                                    <strong>ชื่อ:</strong> <?php echo htmlspecialchars($row_name['student_first_name'])." ". htmlspecialchars($row_name['student_last_name']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="wrap-foot d-flex align-items-center mt-3">
                            <form action="details.php" method="POST">
                                <input type="hidden" name='advisor_request_id' value=<?php echo $row['advisor_request_id'] ?>>
                                <button type="submit"  class="btn-orange">รายละเอียด</button>
                            </form>
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

            //ถ้าเป็นนักเรียน
        } elseif ($_SESSION['role'] == 'student') {
            // ดึงข้อมูลคำร้องที่เกี่ยวข้องกับ student_id
            $sql = "SELECT *
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
                    $requester_id = $row['requester_id'];
                    $partner_accepted = $row['partner_accepted'];
                    $advisor_req_id = $row['advisor_request_id'];
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
                        <?php if ($requester_id != $id && $partner_accepted == 0) {
                            echo
                            "
                                    <form action='' method='post' class='form-choose'>
                                        <div class='wrapChoose'>
                                            <button name='accept' class='accept' value='$advisor_req_id'>Accept Partner Request</button>
                                        </div>
                                        <div class='wrapChoose'>
                                            <button name='reject' class='reject' value='$advisor_req_id'>Reject Partner Request</button>
                                        </div>
                                    </form>
                                    ";
                        } elseif ($requester_id != $id && $partner_accepted == 1) {
                            echo "<div class='status-partner'><h3 class='accept-text'>You Accepted Partner</h3></div>";
                        } elseif ($requester_id != $id && $partner_accepted == 2) {
                            echo "<div class='status-partner'><h3 class='reject-text'>You Rejected Partner</h3></div>";
                        }
                        ?>
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