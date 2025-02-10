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

// if (isset($_SESSION['advisor_id'])) {
//     $advisor_id = $_SESSION['advisor_id'];
//     $sql = "SELECT * FROM advisor_profile WHERE advisor_id = '$advisor_id'";
//     $result = $conn->query($sql);
//     $row = $result->fetch_assoc();

//     $advisor_id = $row['advisor_id'];
//     $interests = $row['interests'];
//     $img = $row['img'];

//     $sql = "SELECT * FROM advisor WHERE id = '$advisor_id'";
//     $result_advisor = $conn->query($sql);
//     $row_advisor = $result_advisor->fetch_assoc();

//     $first_name = $row_advisor['first_name'];
//     $last_name = $row_advisor['last_name'];
//     $email = $row_advisor['email'];

//     $sql = "SELECT * FROM thesis WHERE advisor_id = '$advisor_id' ORDER BY issue_date DESC";
//     $result_thesis = $conn->query($sql);
//     $thesis_count = $result_thesis->num_rows;

//     if ($thesis_count > 0) {
//         $row_thesis = $result_thesis->fetch_assoc();
//         $title = $row_thesis['title'];
//         $authors = nl2br(implode("\n", explode(',', $row_thesis['authors'])));
//         $keywords = str_replace(['[', ']'], '', $row_thesis['keywords']);
//         $issue_date = $row_thesis['issue_date'];
//         $publisher = $row_thesis['publisher'];
//         $abstract = $row_thesis['abstract'];
//         $uri = $row_thesis['uri'];
//         $thesis_file = $row_thesis['thesis_file'];
//     } else {
//         $title = "ไม่พบข้อมูลวิทยานิพนธ์";
//     }
// } else {
//     echo "ไม่พบข้อมูล";
// }

if (isset($_SESSION['advisor_id'])) {
    $advisor_id = $_SESSION['advisor_id'];

    // ดึงข้อมูลจากตาราง thesis
    $sql = "SELECT * FROM thesis WHERE advisor_id = '$advisor_id'";
    $result = $conn->query($sql);
    $row_thesis = $result->fetch_assoc();

    if ($row_thesis) {
        $id = $row_thesis['id'];
        $title = $row_thesis['title'];
        $authors = nl2br(implode("\n", explode(',', $row_thesis['authors'])));
        $keywords = str_replace(['[', ']'], '', $row_thesis['keywords']);
        $issue_date = $row_thesis['issue_date'];
        $publisher = $row_thesis['publisher'];
        $abstract = $row_thesis['abstract'];
        $uri = $row_thesis['uri'];
        $thesis_file = $row_thesis['thesis_file'];
    } else {
        echo "ไม่พบข้อมูลวิทยานิพนธ์";
    }

    // ดึงข้อมูลของที่ปรึกษา (ชื่อ-นามสกุล)
    $sql_advisor = "SELECT first_name, last_name FROM advisor WHERE id = '$advisor_id'";
    $result_advisor = $conn->query($sql_advisor);
    $row_advisor = $result_advisor->fetch_assoc();

    if ($row_advisor) {
        $advisor_name = $row_advisor['first_name'] . " " . $row_advisor['last_name'];
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
        <div class="info"><strong>Title:</strong> <?php echo $title; ?> </div>
        <div class="info"><strong>Authors:</strong> <?php echo $authors ?> </div>
        <div class="info"><strong>Advisor Name:</strong> <?php echo $advisor_name; ?> </div>
        <div class="info"><strong>Keywords:</strong> <?php echo $keywords; ?> </div>
        <div class="info"><strong>Issue Date:</strong> <?php echo $issue_date; ?> </div>
        <div class="info"><strong>Publisher:</strong> <?php echo $publisher; ?> </div>
        <div class="info abstract">
            <strong>Abstract:</strong> <?php echo $abstract; ?>
        </div>
        <div class="info"><strong>URI:</strong> <a href="<?php echo $uri; ?>"> <?php echo $uri; ?> </a> </div>

        <div class="downloadf button">
            <a href="download.php?id=<?php echo $row_thesis['id']; ?>" class="btn">
                <strong>Download Thesis File</strong>
            </a>
        </div>
    </div>

</body>

</html>