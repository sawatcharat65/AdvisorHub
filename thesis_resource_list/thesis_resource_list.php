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
// เก็บ user id และ role จาก session
$current_user_id = $_SESSION['id'];
$current_user_role = $_SESSION['role'];

// สร้าง query ตาม role
if ($current_user_role === 'student') {
    $sql = "SELECT ar.*, 
                   a.first_name as advisor_fname, 
                   a.last_name as advisor_lname
            FROM advisor_request ar
            LEFT JOIN Advisor a ON ar.advisor_id = a.id
            WHERE ar.is_advisor_approved = 1 
            AND ar.is_admin_approved = 1
            AND JSON_CONTAINS(ar.student_id, ?)";
    $stmt = $conn->prepare($sql);
    $student_id_json = json_encode($current_user_id);
    $stmt->bind_param("s", $student_id_json);
} elseif ($current_user_role === 'advisor') {
    $sql = "SELECT ar.*, 
                   a.first_name as advisor_fname, 
                   a.last_name as advisor_lname
            FROM advisor_request ar
            LEFT JOIN Advisor a ON ar.advisor_id = a.id
            WHERE ar.is_advisor_approved = 1 
            AND ar.is_admin_approved = 1
            AND ar.advisor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $current_user_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CS Student Files</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .thesis-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 2rem;
            background: white;
        }
        .thesis-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        .card-title {
            color: #410690;
            font-size: 1.5rem;
            font-weight: 600;
        }
        .card-subtitle {
            color: #6c757d;
            font-size: 1.2rem;
        }
        .section-title {
            font-weight: 600;
            color: #410690;
            margin-bottom: 0.5rem;
        }
        .section-content {
            color: #495057;
            line-height: 1.6;
        }
        .timestamp {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .view-icon {
            color: #410690;
            transition: transform 0.2s ease;
        }
        .thesis-card:hover .view-icon {
            transform: scale(1.1);
        }
        @media (max-width: 768px) {
            .thesis-card {
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'Teams'])?>

    <div class="container-fluid px-4 py-5">
        <h1 class="text-center mb-5" style="color: #410690;">CS Student Files</h1>
        <div class="row justify-content-center">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $student_ids = json_decode($row['student_id'], true);
                    $student_names = [];

                    if (is_array($student_ids)) {
                        foreach ($student_ids as $student_id) {
                            $student_query = "SELECT first_name, last_name FROM Student WHERE id = ?";
                            $stmt = $conn->prepare($student_query);
                            $stmt->bind_param("s", $student_id);
                            $stmt->execute();
                            $student_result = $stmt->get_result();
                            if ($student_row = $student_result->fetch_assoc()) {
                                $student_names[] = "$student_id {$student_row['first_name']} {$student_row['last_name']}";
                            }
                        }
                    }

                    $student_list = implode('<br>', $student_names);
                    ?>
                    <div class="col-12 col-lg-10">
                        <form action="../thesis_resource/thesis_resource.php" method="POST">
                            <input type="hidden" name="thesis_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="w-100 text-start border-0 p-0 bg-transparent">
                                <div class="thesis-card">
                                    <div class="card-body p-4">
                                        <h2 class="card-title mb-3"><?php echo htmlspecialchars($row['thesis_topic_thai']); ?></h2>
                                        <h3 class="card-subtitle mb-4"><?php echo htmlspecialchars($row['thesis_topic_eng']); ?></h3>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="section-title">Students</div>
                                                <div class="section-content"><?php echo $student_list; ?></div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="section-title">Advisor</div>
                                                <div class="section-content">
                                                    <?php echo htmlspecialchars($row['advisor_fname'] . ' ' . $row['advisor_lname']); ?>
                                                </div>

                                            </div>
                                        </div>

                                        <div class="section-title mt-3">Description</div>
                                        <div class="section-content"><?php echo htmlspecialchars($row['thesis_description']); ?></div>

                                        <div class="d-flex justify-content-between align-items-center mt-4">
                                            <span class="timestamp">Submitted on: <?php echo date('F j, Y', strtotime($row['time_stamp'])); ?></span>
                                            <i class="bi bi-arrow-right-circle-fill view-icon fs-4"></i>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </form>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="col-12 col-lg-10">
                        <div class="alert alert-info text-center p-4">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            No approved requests found
                        </div>
                      </div>';
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>