<?php

include("../server.php");

session_start();

if(isset($_POST['logout'])){
    session_destroy();
    header('location: /AdvisorHub/login');
}

if(empty($_SESSION['username'])){
    header('location: /AdvisorHub/login');
}

if(isset($_POST['profile'])){
    header('location: /AdvisorHub/profile');
}

$academic_year = (int) mysqli_real_escape_string($conn, $_POST['academic_year']);
$semester = (int) mysqli_real_escape_string($conn, $_POST['semester']);

$thesisType = mysqli_real_escape_string($conn, $_POST['thesisType']);

if ($thesisType == 'single') {
    // $singleName = mysqli_real_escape_string($conn, $_POST['singleName']);
    $singleStudentID = mysqli_real_escape_string($conn, $_POST['singleStudentID']);
    // $singleBranch = mysqli_real_escape_string($conn, $_POST['singleBranch']);
    // $singlePhone = mysqli_real_escape_string($conn, $_POST['singlePhone']);
    // $singleEmail = mysqli_real_escape_string($conn, $_POST['singleEmail']);
} else {
    // $pairName1 = mysqli_real_escape_string($conn, $_POST['pairName1']);
    $pairStudentID1 = mysqli_real_escape_string($conn, $_POST['pairStudentID1']);
    // $pairBranch1 = mysqli_real_escape_string($conn, $_POST['pairBranch1']);
    // $pairPhone1 = mysqli_real_escape_string($conn, $_POST['pairPhone1']);
    // $pairEmail1 = mysqli_real_escape_string($conn, $_POST['pairEmail1']);

    // $pairName2 = mysqli_real_escape_string($conn, $_POST['pairName2']);
    $pairStudentID2 = mysqli_real_escape_string($conn, $_POST['pairStudentID2']);
    // $pairBranch2 = mysqli_real_escape_string($conn, $_POST['pairBranch2']);
    // $pairPhone2 = mysqli_real_escape_string($conn, $_POST['pairPhone2']);
    // $pairEmail2 = mysqli_real_escape_string($conn, $_POST['pairEmail2']);
}

$advisorName = mysqli_real_escape_string($conn, $_POST['advisorName']); //แก้ชชชชชชชชชชชชชชชชชชชชชชชชชชชชชชชชชชชชชชชชชชชชชชช
$thesisTitleThai = mysqli_real_escape_string($conn, $_POST['thesisTitleThai']);
$thesisTitleEnglish = mysqli_real_escape_string($conn, $_POST['thesisTitleEnglish']);
$thesisDescription = mysqli_real_escape_string($conn, $_POST['thesisDescription']);

if ($thesisType == 'single') {
    $is_even = 0;
    $sql = "INSERT INTO advisor_request (student_id, advisor_id, thesis_topic_thai, 
                                         thesis_topic_eng, thesis_description, is_even, 
                                         semester, academic_year, is_advisor_approved, 
                                         is_admin_approved, time_stamp) 
            VALUES('{$singleStudentID}', 'F05003', '{$thesisTitleThai}', 
                   '{$thesisTitleEnglish}', '{$thesisDescription}', {$is_even}, 
                   {$semester}, {$academic_year}, 
                   0, 0, NOW())";
                   
    if ($query = mysqli_query($conn, $sql)) {
        echo"success";
    } else {
        echo "failed";
    }
} else {
    $is_even = 1;
    $student_ids = [$pairStudentID1, $pairStudentID2];
    $student_ids_json = json_encode($student_ids);
    $sql = "INSERT INTO advisor_request (student_id, advisor_id, thesis_topic_thai, 
                                         thesis_topic_eng, thesis_description, is_even, 
                                         semester, academic_year, is_advisor_approved, 
                                         is_admin_approved, time_stamp) 
            VALUES('{$student_ids_json}', 'F05003', '{$thesisTitleThai}', 
                   '{$thesisTitleEnglish}', '{$thesisDescription}', {$is_even}, 
                   {$semester}, {$academic_year}, 
                   0, 0, NOW())";

    if ($query = mysqli_query($conn, $sql)) {
        echo"success";
    } else {
        echo "failed";
    }
}





?>