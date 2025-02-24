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

if (isset($_GET['thesis_id'])) {
    $thesis_id = $_GET['thesis_id'];

    // ดึงข้อมูลจากตาราง thesis
    $sql = "SELECT * FROM thesis WHERE thesis_id = '$thesis_id'";
    $result = $conn->query($sql);
    $row_thesis = $result->fetch_assoc();

    if ($row_thesis) {
        $advisor_id = $row_thesis['advisor_id'];
        $thesis_id = $row_thesis['thesis_id'];
        $thesis_title = $row_thesis['thesis_title'];
        $authors = nl2br(implode("\n", explode(',', $row_thesis['authors'])));
        $keywords = str_replace(['[', ']'], '', $row_thesis['keywords']);
        $issue_date = $row_thesis['issue_date'];
        $publisher = $row_thesis['publisher'];
        $abstract = $row_thesis['abstract'];
        $thesis_file = $row_thesis['thesis_file'];
        $thesis_file_type = $row_thesis['thesis_file_type'];
    } else {
        echo "ไม่พบข้อมูลวิทยานิพนธ์";
    }

    // ลบเครื่องหมาย " และเพิ่มช่องว่างหลังจุลภาค
    $keyword_array = explode(',', str_replace('"', '', $keywords));

    // ใช้ Regular Expression แยกภาษาไทย และภาษาอังกฤษ
    $title_parts = preg_split('/(?=[A-Z])/', $thesis_title, 2); // แยกเมื่อเจออักษรภาษาอังกฤษตัวใหญ่

    $thai_title = trim($title_parts[0]); // ส่วนของภาษาไทย
    $english_title = isset($title_parts[1]) ? trim($title_parts[1]) : ""; // ส่วนของภาษาอังกฤษ (ถ้ามี)

    // ดึงข้อมูลของที่ปรึกษา (ชื่อ-นามสกุล)
    $sql_advisor = "SELECT advisor_first_name, advisor_last_name FROM advisor WHERE advisor_id = '$advisor_id'";
    $result_advisor = $conn->query($sql_advisor);
    $row_advisor = $result_advisor->fetch_assoc();

    if ($row_advisor) {
        $advisor_name = $row_advisor['advisor_first_name'] . " " . $row_advisor['advisor_last_name'];
    } else {
        $advisor_name = "ไม่พบชื่อที่ปรึกษา";
    }
} else {
    echo "ไม่พบข้อมูล";
}

$uri = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
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

    <?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'Teams']) ?>

    <div class="container">
        <h1>Thesis Information</h1>
        <div class="info">Title: <span><?php echo $thai_title; ?></span><br>
            <span><?php echo $english_title; ?></span>
        </div>
        <div class="info">Authors: <span><?php echo $authors; ?></span></div>
        <div class="info">Advisor Name: <span><?php echo $advisor_name; ?></span></div>
        <div class="info">Keywords:<br>
            <span><?php echo implode('<br>', $keyword_array); ?></span>
        </div>
        <div class="info">Issue Date: <span><?php echo $issue_date; ?></span></div>
        <div class="info">Publisher: <span><?php echo $publisher; ?></span></div>
        <div class="info">Abstract: <span><?php echo $abstract; ?></span></div>
        <div class="info">URI: <span><a href="<?php echo $uri; ?>"><?php echo $uri; ?></a></span></div>

        <div class="downloadf button mt-4">
            <a href="download.php?thesis_id=<?php echo $row_thesis['thesis_id']; ?>" class="btn">
                Download Thesis File
            </a>
        </div>
    </div>

</body>

</html>