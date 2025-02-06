<?php

include("../server.php");

session_start();

$academic_year = mysqli_real_escape_string($conn, $_POST['academic_year']);
$semester = mysqli_real_escape_string($conn, $_POST['semester']);

$thesisType = mysqli_real_escape_string($conn, $_POST['thesisType']);

if ($thesisType == 'single') {
    $singleName = mysqli_real_escape_string($conn, $_POST['singleName']);
    $singleStudentID = mysqli_real_escape_string($conn, $_POST['singleStudentID']);
    $singleBranch = mysqli_real_escape_string($conn, $_POST['singleBranch']);
    $singlePhone = mysqli_real_escape_string($conn, $_POST['singlePhone']);
    $singleEmail = mysqli_real_escape_string($conn, $_POST['singleEmail']);
} else {
    $pairName1 = mysqli_real_escape_string($conn, $_POST['pairName1']);
    $pairStudentID1 = mysqli_real_escape_string($conn, $_POST['pairStudentID1']);
    $pairBranch1 = mysqli_real_escape_string($conn, $_POST['pairBranch1']);
    $pairPhone1 = mysqli_real_escape_string($conn, $_POST['pairPhone1']);
    $pairEmail1 = mysqli_real_escape_string($conn, $_POST['pairEmail1']);

    $pairName2 = mysqli_real_escape_string($conn, $_POST['pairName2']);
    $pairStudentID2 = mysqli_real_escape_string($conn, $_POST['pairStudentID2']);
    $pairBranch2 = mysqli_real_escape_string($conn, $_POST['pairBranch2']);
    $pairPhone2 = mysqli_real_escape_string($conn, $_POST['pairPhone2']);
    $pairEmail2 = mysqli_real_escape_string($conn, $_POST['pairEmail2']);
}

$advisorName = mysqli_real_escape_string($conn, $_POST['advisorName']);
$thesisTitleThai = mysqli_real_escape_string($conn, $_POST['thesisTitleThai']);
$thesisTitleEnglish = mysqli_real_escape_string($conn, $_POST['thesisTitleEnglish']);
$thesisDescription = mysqli_real_escape_string($conn, $_POST['thesisDescription']);



?>