<?php
session_start();
require('../server.php');
include('../components/navbar.php');

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: /AdvisorHub/login');
    exit;
}

if (empty($_SESSION['username'])) {
    header('Location: /AdvisorHub/login');
    exit;
}

if (isset($_POST['profile'])) {
    header('Location: /AdvisorHub/profile');
    exit;
}

if (isset($_POST['chat'])) {
    $_SESSION['receiver_id'] = $_POST['chat'];
    header('Location: /AdvisorHub/topic_chat/topic_chat.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ที่ปรึกษา</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="../Logo.png">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <style>
        .profile-header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .publication-row {
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .publication-row:last-child {
            border-bottom: none;
        }

        .profile-container, .chart-container {
            padding: 0 7.5px; /* ลดระยะห่างขอบซ้ายขวาเหลือครึ่งหนึ่ง (จาก 15px เป็น 7.5px) */
        }

        .container {
            padding-left: 7.5px; /* ลดระยะห่างขอบซ้ายของ container */
            padding-right: 7.5px; /* ลดระยะห่างขอบขวาของ container */
        }

        @media (max-width: 768px) {
            .profile-container, .chart-container {
                width: 100%;
                padding: 0; /* ลบ padding ในมือถือเพื่อให้เต็มจอ */
            }
            .container {
                padding-left: 0;
                padding-right: 0;
            }
        }
    </style>
</head>

<body>
    <?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'Teams']); ?>

    <div class="container mt-5">
        <?php
        if (isset($_SESSION['advisor_id'])) {
            $advisor_id = mysqli_real_escape_string($conn, $_SESSION['advisor_id']);

            // ดึงข้อมูลโปรไฟล์ที่ปรึกษา
            $sql = "SELECT * FROM advisor_profile WHERE advisor_id = '$advisor_id'";
            $result = $conn->query($sql);
            if ($result && $row = $result->fetch_assoc()) {
                $expertise = json_decode($row['expertise'], true) ?: [];
                $advisor_interests = $row['advisor_interests'] ?: '';
                $img = $row['img'] ?: '../default-profile.png';

                // ดึงข้อมูลจากตาราง advisor
                $sql = "SELECT * FROM advisor WHERE advisor_id = '$advisor_id'";
                $result_advisor = $conn->query($sql);
                $row_advisor = $result_advisor->fetch_assoc();

                $advisor_first_name = htmlspecialchars($row_advisor['advisor_first_name']);
                $advisor_last_name = htmlspecialchars($row_advisor['advisor_last_name']);
                $advisor_tel = htmlspecialchars($row_advisor['advisor_tel']);
                $advisor_email = htmlspecialchars($row_advisor['advisor_email']);

                // ดึงข้อมูลผลงานตีพิมพ์
                $sql = "SELECT * FROM thesis WHERE advisor_id = '$advisor_id' ORDER BY issue_date DESC";
                $result_thesis = $conn->query($sql);
                $thesis_count = $result_thesis->num_rows;

                // ดึงจำนวนนักเรียน (ไม่ใช้ในตัวอย่างนี้ แต่เก็บไว้เพื่อความสมบูรณ์)
                $sql = "SELECT COUNT(*) + SUM(CASE WHEN is_even = 1 THEN 1 ELSE 0 END) AS student_count
                        FROM advisor_request
                        WHERE advisor_id = '$advisor_id'
                        AND is_advisor_approved = 1
                        AND is_admin_approved = 1";
                $result_student = $conn->query($sql);
                $row_student = $result_student->fetch_assoc();
                $student_count = $row_student['student_count'] ?? 0;
            } else {
                echo "<div class='alert alert-warning'>ไม่พบข้อมูลที่ปรึกษา</div>";
                exit;
            }
        } else {
            echo "<div class='alert alert-danger'>กรุณาล็อกอินด้วยบัญชีที่ปรึกษา</div>";
            exit;
        }
        ?>

        <div class="row">
            <div class="col-md-12">
                <!-- ส่วนหัวโปรไฟล์ -->
                <div class="profile-header">
                    <div class="d-flex align-items-center gap-4">
                        <img src="<?php echo $img; ?>" class="rounded-circle" alt="โปรไฟล์" style="width: 150px; height: 150px; object-fit: cover;">
                        <div>
                            <h2><?php echo "$advisor_first_name $advisor_last_name"; ?></h2>
                            <p class="text-muted"><?php echo implode(', ', $expertise); ?></p>
                        </div>
                    </div>
                </div>

                <!-- ผลงานตีพิมพ์และกราฟการอ้างอิง -->
                <div class="row">
                    <div class="col-md-8 profile-container">
                        <!-- ผลงานตีพิมพ์ -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">ผลงานตีพิมพ์ (<?php echo $thesis_count; ?>)</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ชื่อเรื่อง</th>
                                                <th>ชื่อนิสิต</th>
                                                <th>รหัสนิสิต</th>
                                                <th>ปีที่พิมพ์</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if ($thesis_count > 0) {
                                                while ($row_thesis = $result_thesis->fetch_assoc()) {
                                                    $thesis_title = htmlspecialchars($row_thesis['thesis_title']);
                                                    $student_name = htmlspecialchars($row_thesis['student_name'] ?? 'N/A'); // สมมติฟิลด์สำหรับชื่อนิสิต
                                                    $student_id = htmlspecialchars($row_thesis['student_id'] ?? 'N/A'); // สมมติฟิลด์สำหรับรหัสนิสิต
                                                    $issue_date = htmlspecialchars($row_thesis['issue_date']);
                                                    $citations = $row_thesis['citations'] ?? 0;
                                            ?>
                                                    <tr class="publication-row">
                                                        <td><?php echo $thesis_title; ?></td>
                                                        <td><?php echo $student_name; ?></td>
                                                        <td><?php echo $student_id; ?></td>
                                                        <td><?php echo $issue_date; ?></td>
                                                    </tr>
                                            <?php
                                                }
                                            } else {
                                                echo "<tr><td colspan='4' class='text-muted'>ไม่พบผลงานตีพิมพ์</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 chart-container">
                        <!-- กราฟการอ้างอิงตามกาลเวลา -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">การอ้างอิงตามกาลเวลา</h4>
                                <canvas id="citationsChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS และ dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('citationsChart').getContext('2d');
        const citationsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['2018', '2019', '2020', '2021', '2022', '2023', '2024', '2025'],
                datasets: [{
                    label: 'การอ้างอิง',
                    data: [10, 20, 30, 40, 50, 40, 30, 20], // ข้อมูลใหม่ที่มีค่าสูงสุดที่ 50
                    backgroundColor: 'rgba(108, 117, 125, 0.2)', // สีเทาเหมือนในภาพ
                    borderColor: 'rgba(108, 117, 125, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 50, // กำหนดขีดสูงสุดของกราฟเป็น 50
                        title: { display: true, text: 'การอ้างอิง' },
                        ticks: {
                            stepSize: 10 // กำหนดขั้นตอนของค่าบนแกน Y เป็น 10
                        }
                    },
                    x: {
                        title: { display: true, text: 'ปี' }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    </script>
</body>

</html>