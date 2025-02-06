<?php

include("../server.php");

session_start();

if (empty($_POST['academic_year']) && empty($_POST['semester'])) {
    header('location: /AdvisorHub/advisor');
}

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
    $singleStudentID = mysqli_real_escape_string($conn, $_POST['singleStudentID']);
} else {
    $pairStudentID1 = mysqli_real_escape_string($conn, $_POST['pairStudentID1']);
    $pairStudentID2 = mysqli_real_escape_string($conn, $_POST['pairStudentID2']);

}

$thesisTitleThai = mysqli_real_escape_string($conn, $_POST['thesisTitleThai']);
$thesisTitleEnglish = mysqli_real_escape_string($conn, $_POST['thesisTitleEnglish']);
$thesisDescription = mysqli_real_escape_string($conn, $_POST['thesisDescription']);

$sql = "SELECT * FROM advisor_request WHERE student_id = '$singleStudentID'";
$result = $conn->query($sql);

// เช็คว่าส่งคำร้องซ้ำไหม
if ($result->num_rows > 0) {
    echo "ไม่สามารถส่งคำร้องซ้ำได้";
} else {
    if ($thesisType == 'single') {
        $is_even = 0;
        $sql = "INSERT INTO advisor_request (student_id, advisor_id, thesis_topic_thai, 
                                             thesis_topic_eng, thesis_description, is_even, 
                                             semester, academic_year, is_advisor_approved, 
                                             is_admin_approved, time_stamp) 
                VALUES('{$singleStudentID}', '{$_POST["advisor_id"]}', '{$thesisTitleThai}', 
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
                VALUES('{$student_ids_json}', '{$_POST["advisor_id"]}', '{$thesisTitleThai}', 
                       '{$thesisTitleEnglish}', '{$thesisDescription}', {$is_even}, 
                       {$semester}, {$academic_year}, 
                       0, 0, NOW())";
    
        if ($query = mysqli_query($conn, $sql)) {
            echo"success";
        } else {
            echo "failed";
        }
    }
}

?>