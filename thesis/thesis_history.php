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
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thesis Information</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="../Logo.png">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'file']) ?>

    <div class="container">
        <h1>Thesis Information</h1>
        <div class="info"><strong>Advisor Name:</strong> Dr. John Doe</div>
        <div class="info"><strong>Title:</strong> AI-Enhanced Audio Processing</div>
        <div class="info"><strong>Authors:</strong> Parisa T</div>
        <div class="info"><strong>Keywords:</strong> AI, Audio Enhancement, Deep Learning</div>
        <div class="info"><strong>Issue Date:</strong> 2025-02-04</div>
        <div class="info"><strong>Publisher:</strong> University of Technology</div>
        <div class="info abstract">
            <strong>Abstract:</strong> This thesis explores AI-driven techniques to improve audio quality, making unintelligible sound clear and comprehensible.
        </div>
        <div class="info"><strong>URI:</strong> <a href="#">http://example.com/thesis/123</a></div>
        <a href="thesis.pdf" class="download">Download Thesis Fileaa</a>
    </div>
</body>

</html>