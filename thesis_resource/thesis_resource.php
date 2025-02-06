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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View File</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="../Logo.png">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <!-- Navbar -->
    <?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'file'])?>

    <div class="container my-4">
        <div class="row">
            <div class="col-12">
                <div class="student-info">
                    <h2>65312894 นาย ปราโมทย์ นุ่มเอี่ยม</h2>
                    <p>CS ชั้นปีที่3</p>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <h3>Files:</h3>
                <ul class="file-list">
                    <li>
                        <i class="bi bi-file-earmark-pdf"></i>
                        <a href="#">Project_Proposal.pdf</a>
                    </li>
                    <li>
                        <i class="bi bi-file-earmark-word"></i>
                        <a href="#">Research_Paper.docx</a>
                    </li>
                    <li>
                        <i class="bi bi-file-earmark-ppt"></i>
                        <a href="#">Presentation_Slides.pptx</a>
                    </li>
                    <li>
                        <i class="bi bi-file-earmark-excel"></i>
                        <a href="#">Data_Analysis.xlsx</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="container">
        <a class="btn btn-primary" href="files-list.html">ย้อนกลับ</a>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>