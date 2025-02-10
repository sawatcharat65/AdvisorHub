<?php
session_start();
require('../server.php');
include('../components/navbar.php');

if (isset($_POST['logout'])) {
    session_destroy();
    header('location: /AdvisorHub/login');
}

if (empty($_SESSION['username'])) {
    header('location: /AdvisorHub/login');
}

if (isset($_POST['profile'])) {
    header('location: /AdvisorHub/profile');
}

// ดึงข้อมูลโดย JOIN ตาราง Advisor และ Student
$sql = "SELECT ar.*, 
               COALESCE(a.first_name, 'N/A') AS advisor_fname, 
               COALESCE(a.last_name, 'N/A') AS advisor_lname, 
               COALESCE(s.first_name, 'N/A') AS student_fname, 
               COALESCE(s.last_name, 'N/A') AS student_lname 
        FROM advisor_request ar
        LEFT JOIN Advisor a ON ar.advisor_id = a.id
        LEFT JOIN Student s ON ar.student_id = s.id
        WHERE ar.is_advisor_approved = 1 AND ar.is_admin_approved = 1";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advisor Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="../Logo.png">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .card { cursor: pointer; }
    </style>
</head>
<body>

<?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'Teams'])?>

<div class="container my-3">
    <h1>CS Student Files</h1>
    <div class="row row-cols-xs-1 row-cols-md-2 row-cols-lg-2 g-4 ">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // แปลง student_id จาก JSON เป็น Array
                $student_ids = json_decode($row['student_id'], true);
                $student_names = [];

                if (is_array($student_ids)) {
                    foreach ($student_ids as $student_id) {
                        // ดึงข้อมูลนักศึกษาจากฐานข้อมูล
                        $student_query = "SELECT first_name, last_name FROM Student WHERE id = '$student_id'";
                        $student_result = $conn->query($student_query);
                        if ($student_row = $student_result->fetch_assoc()) {
                            $student_names[] = "$student_id {$student_row['first_name']} {$student_row['last_name']}";
                        }
                    }
                } else {
                    $student_names[] = "Unknown Student";
                }

                // รวมชื่อของนักศึกษาทุกคนเป็นข้อความเดียว
                $student_list = implode('<br>', $student_names);

                echo "<div class='col d-flex justify-content-center'>
                        <a href='/AdvisorHub/thesis_resource/thesis_resource.php?id={$row['id']}' class='card-link text-decoration-none'>
                            <div class='card h-100 shadow'>
                                <div class='card-body'>
                                    <h5 class='card-title'>{$row['thesis_topic_thai']}</h5>
                                    <h6 class='card-subtitle mb-2 text-muted'>{$row['thesis_topic_eng']}</h6>
                                    <p class='card-text'><strong>Students:</strong> <br> $student_list</p>
                                    <p class='card-text'><strong>Advisor:</strong> {$row['advisor_fname']} {$row['advisor_lname']}</p>
                                    <p class='card-text'><strong>Semester:</strong> {$row['semester']}</p>
                                    <p class='card-text'><strong>Description:</strong> {$row['thesis_description']}</p>
                                </div>
                                <div class='card-footer bg-transparent border-0 text-end'>
                                    <small class='text-muted'>Submitted on: {$row['time_stamp']}</small>
                                    <i class='bi bi-eye view-icon fs-4 ms-2'></i>
                                </div>
                            </div>
                        </a>
                      </div>";
            }
        } else {
            echo "<div class='col-12 text-center'><p>No approved requests found</p></div>";
        }
        ?>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>