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
    <title>Files List</title>
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

<?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'Teams'])?>

    <div class="container my-4">
        <h1 class="mb-4">CS Student Files</h1>
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search students...">
                    <button class="btn btn-primary" type="button">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <div class="col">
                <a href="/AdvisorHub/thesis_resource/thesis_resource.php" class="card-link">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">65312894 นาย ปราโมทย์ นุ่มเอี่ยม</h5>
                            <p class="student-info mb-0">CS ชั้นปีที่3</p>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-end">
                            <i class="bi bi-eye view-icon fs-4"></i>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col">
                <a href="/AdvisorHub/thesis_resource/thesis_resource.php" class="card-link">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">12345678 นาย สมชาย ใจดี</h5>
                            <p class="student-info mb-0">CS ชั้นปีที่2</p>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-end">
                            <i class="bi bi-eye view-icon fs-4"></i>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col">
                <a href="/AdvisorHub/thesis_resource/thesis_resource.php" class="card-link">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">12345678 นาย สมชาย ใจดี</h5>
                            <p class="student-info mb-0">CS ชั้นปีที่2</p>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-end">
                            <i class="bi bi-eye view-icon fs-4"></i>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col">
                <a href="/AdvisorHub/thesis_resource/thesis_resource.php" class="card-link">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">12345678 นาย สมชาย ใจดี</h5>
                            <p class="student-info mb-0">CS ชั้นปีที่2</p>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-end">
                            <i class="bi bi-eye view-icon fs-4"></i>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col">
                <a href="/AdvisorHub/thesis_resource/thesis_resource.php" class="card-link">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">12345678 นาย สมชาย ใจดี</h5>
                            <p class="student-info mb-0">CS ชั้นปีที่2</p>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-end">
                            <i class="bi bi-eye view-icon fs-4"></i>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col">
                <a href="/AdvisorHub/thesis_resource/thesis_resource.php" class="card-link">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">87654321 นางสาว มณีรัตน์ สุขสันต์</h5>
                            <p class="student-info mb-0">CS ชั้นปีที่4</p>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-end">
                            <i class="bi bi-eye view-icon fs-4"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div aria-label="Page divigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                </li>
                <li class="page-item"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Next</a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>