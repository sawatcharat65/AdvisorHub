<?php
session_start();
require('../server.php');
include('../components/navbar.php');
if (isset($_POST['logout'])) {
    session_destroy();
    header('location: /ThesisAdvisorHub/login');
}

if (empty($_SESSION['username'])) {
    header('location: /ThesisAdvisorHub/login');
}

if (isset($_POST['profile'])) {
    header('location: /ThesisAdvisorHub/profile');
}

if (isset($_POST['chat'])) {
    $_SESSION['receiver_id'] = $_POST['chat'];
    header('location: /AdvisorHub/topic_chat/topic_chat.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thesis Information</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="../Logo.png">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css' rel='stylesheet'>
</head>

<body>

    <?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'file']) ?>

    <?php
    if (isset($_SESSION['advisor_id'])) {
        $advisor_id = $_SESSION['advisor_id'];
        $sql = "SELECT * FROM advisor_profile WHERE advisor_id = '$advisor_id'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();

        $advisor_id = $row['advisor_id'];
        $expertise = json_decode($row['expertise']);
        $interests = $row['interests'];
        $img = $row['img'];

        $sql = "SELECT * FROM advisor WHERE id = '$advisor_id'";
        $result_advisor = $conn->query($sql);
        $row_advisor = $result_advisor->fetch_assoc();

        $first_name = $row_advisor['first_name'];
        $last_name = $row_advisor['last_name'];
        $tel = $row_advisor['tel'];
        $email = $row_advisor['email'];

        $sql = "SELECT * FROM thesis WHERE advisor_id = '$advisor_id' ORDER BY issue_date DESC";
        $result_thesis = $conn->query($sql);
        $thesis_count = $result_thesis->num_rows;

        if ($result_thesis->num_rows > 0) {
            $row_thesis = $result_thesis->fetch_assoc();

            $title = $row_thesis['title'];
            $authors = $row_thesis['authors'];
            $keywords = $row_thesis['keywords'];
            $issue_date = $row_thesis['issue_date'];
            $publisher = $row_thesis['publisher'];
            $abstract = $row_thesis['abstract'];
            $uri = $row_thesis['uri'];
            $thesis_file = $row_thesis['thesis_file'];
        }

        $sql = "SELECT 
        COUNT(*) + SUM(CASE WHEN is_even = 1 THEN 1 ELSE 0 END) AS student_count
        FROM advisor_request
        WHERE advisor_id = '$advisor_id'
        AND is_advisor_approved = 1
        AND is_admin_approved = 1";
        $result_student = $conn->query($sql);
        $row_student = $result_student->fetch_assoc();
        $student_count = $row_student['student_count'];
    } else {
        echo "ไม่พบข้อมูล";
    }
    ?>

    <div class="container">
        <div class="row">
            <div class="col-xl">

                <div class="card">
                    <!--img-->
                    <div class="rounded-top-3" style="background-image: url(https://english.nu.ac.th/wp-content/uploads/slider/cache/811ae9168f083da2d84d70eb287510f1/Nu-view01.jpg); background-position: center; background-size: cover; background-repeat: no-repeat; height: 300px"></div>
                    <div class="card-body p-md-5">
                        <div class="d-flex flex-column gap-5">
                            <!--img-->
                            <div class="position-relative">
                                <img src="<?= $img ?>" class="avatar-circle" alt="">
                            </div>
                            <div class="d-flex flex-column gap-5">
                                <div class="d-flex flex-column gap-3">
                                    <div class="d-flex flex-md-row flex-column justify-content-between gap-2">
                                        <!--heading-->
                                        <div>
                                            <h2 class="mt-3 fw-bold"> <?php echo $first_name . " " . $last_name; ?> </h2>
                                            <!--content-->
                                            <div class="d-flex flex-lg-row flex-column gap-2">
                                                <small class="fw-medium text-gray-800">A teacher at Naresuan University</small>
                                                <small class="fw-medium" style="color: #fe7801">Computer Science and Information Technology (CSIT).</small>
                                            </div>
                                        </div>
                                        <!--button-->
                                        <div class="d-flex flex-row gap-3 align-items-center">
                                            <form action="" method="post">
                                                <button name="chat" value="<?= $advisor_id ?>" class="btn btn-outline-info">
                                                    <i class='bx bxs-message-dots fs-6'></i>
                                                    Ask questions
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-md-row flex-column gap-md-4 gap-2">
                                        <div class="d-flex flex-row gap-2 align-items-center lh-1">
                                            <!--icon-->
                                            <span>
                                                <i class="fa-solid fa-envelope"></i>
                                            </span>
                                            <span>
                                                <!--text-->
                                                <span class="text-gray-800"> <?php echo $email ?> </span>
                                            </span>
                                        </div>
                                        <div class="d-flex flex-row gap-2 align-items-center lh-1">
                                            <!--icon-->
                                            <span>
                                                <i class="fa-solid fa-phone"></i>
                                            </span>
                                            <!--text-->
                                            <span>
                                                <span class="text-gray-800"> <?php echo $tel ?> </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <!--heading-->
                                    <h3 class="mb-0">Expertise</h3>

                                    <p class="mb-1 fs-6">
                                        <?php
                                        foreach ($expertise as $exp) {
                                            echo '<span class="badge rounded-pill text-bg-secondary">' . htmlspecialchars($exp) . '</span> ';
                                        }
                                        ?>
                                    </p>
                                </div>
                                <!-- <div>
                                    <span class="badge rounded-pill text-success-emphasis bg-success-subtle border border-success align-items-center">
                                        <span>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-reply-fill me-1 align-text-top" viewBox="0 0 16 16">
                                                <path d="M5.921 11.9 1.353 8.62a.72.72 0 0 1 0-1.238L5.921 4.1A.716.716 0 0 1 7 4.719V6c1.5 0 6 0 7 8-2.5-4.5-7-4-7-4v1.281c0 .56-.606.898-1.079.62z"></path>
                                            </svg>
                                        </span>
                                        Quick Responder
                                    </span>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="tab-content p-4">
                        <div class="tab-pane active show" id="projects-tab" role="tabpanel">
                            <div class="d-flex align-items-center">
                                <div class="d-flex align-items-center">
                                    <h4 class="card-title mb-0 me-2">Thesis</h4>
                                    <span class="badge rounded-pill bg-secondary opacity-75 text-white px-2 py-1">
                                        <?php echo $thesis_count ?>
                                    </span>
                                </div>
                            </div>

                            <div class="row mt-4" id="all-projects">
                                <?php
                                if ($result_thesis->num_rows > 0) {
                                    $result_thesis->data_seek(0);
                                    while ($row_thesis = $result_thesis->fetch_assoc()) {
                                ?>
                                        <div class="row mx-auto" id="project-items-1">
                                            <a href="thesis_info.php?id=<?= $row_thesis['id'] ?>" class="card text-decoration-none">
                                                <div class="card-body">
                                                    <div class="d-flex mb-3">
                                                        <div class="flex-grow-1 align-items-start">
                                                            <div>
                                                                <h6 class="mb-0 text-muted">
                                                                    <i class="mdi mdi-circle-medium text-danger fs-3 align-middle"></i>
                                                                    <span class="team-date"> <?= date('d M, Y', strtotime($row_thesis['issue_date'])) ?> </span>
                                                                </h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-1">
                                                        <h5 class="mb-1 font-size-17 team-title"> <?= $row_thesis['title'] ?> </h5>
                                                        <p class="text-muted mb-0 team-description"> <?= substr($row_thesis['abstract'], 0, 100) ?> </p>
                                                    </div>
                                                </div><!-- end cardbody -->
                                            </a><!-- end card -->
                                        </div><!-- end row -->
                                <?php
                                    }
                                } else {
                                    echo "<p>ไม่พบข้อมูลวิทยานิพนธ์ Thesis</p>";
                                }
                                ?>
                            </div><!-- end row -->

                        </div><!-- end tab pane -->
                    </div>
                </div><!-- end card -->
            </div><!-- end col -->
        </div><!-- end col -->
    </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>