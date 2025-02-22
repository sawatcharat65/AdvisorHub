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

if ($thesisType == 'single') { // กรณีเดี่ยว บันทึก student_id ของนิสิตคนเดียว
    $singleStudentID = mysqli_real_escape_string($conn, $_POST['singleStudentID']);
} else { // กรณีคู่ บันทึก student_id ของนิสิต 2 คน
    $pairStudentID1 = mysqli_real_escape_string($conn, $_POST['pairStudentID1']);
    $pairStudentID2 = mysqli_real_escape_string($conn, $_POST['pairStudentID2']);

}

$thesisTitleThai = mysqli_real_escape_string($conn, $_POST['thesisTitleThai']);
$thesisTitleEnglish = mysqli_real_escape_string($conn, $_POST['thesisTitleEnglish']);
$thesisDescription = mysqli_real_escape_string($conn, $_POST['thesisDescription']);

$sql = "SELECT * FROM advisor_request WHERE JSON_CONTAINS(student_id, '\"{$_SESSION["account_id"]}\"') AND is_advisor_approved != 2 AND is_admin_approved != 2 AND partner_accepted != 2";
$result = $conn->query($sql);

// เช็คว่าส่งคำร้องซ้ำไหม
if ($result->num_rows > 0) {
    $_SESSION["notify_message"] = "ไม่สามารถส่งคำร้องซ้ำได้";
    header("location:http://localhost/AdvisorHub/request/request_details.php");
} else {
    if ($thesisType == 'single') {
        $is_even = 0;
        $requester_id = $_SESSION['account_id'];
        $student_id = [$singleStudentID];
        $student_id_json = json_encode($student_id);
        $sql = "INSERT INTO advisor_request (student_id, requester_id,advisor_id, thesis_topic_thai, 
                                             thesis_topic_eng, thesis_description, is_even, 
                                             semester, academic_year, is_advisor_approved, 
                                             is_admin_approved, partner_accepted,time_stamp) 
                VALUES('{$student_id_json}', '$requester_id','{$_POST["advisor_id"]}', '{$thesisTitleThai}', 
                       '{$thesisTitleEnglish}', '{$thesisDescription}', {$is_even}, 
                       {$semester}, {$academic_year}, 
                       0, 0, 1, NOW())";
                       
        if ($query = mysqli_query($conn, $sql)) {
            $_SESSION["notify_message"] = "ส่งคำร้องสำเร็จ";
            header("location:http://localhost/AdvisorHub/request/request_details.php");
        } else {
            $_SESSION["notify_message"] = "ส่งคำร้องไม่สำเร็จ";
            header("location:http://localhost/AdvisorHub/request/request_details.php");
        }
    } else {
        $is_even = 1;
        $requester_id = $_SESSION['account_id'];
        $student_ids = [$pairStudentID1, $pairStudentID2];
        $student_ids_json = json_encode($student_ids);
        $sql = "INSERT INTO advisor_request (student_id, requester_id, advisor_id, thesis_topic_thai, 
                                             thesis_topic_eng, thesis_description, is_even, 
                                             semester, academic_year, is_advisor_approved, 
                                             is_admin_approved, partner_accepted,time_stamp) 
                VALUES('{$student_ids_json}', '{$requester_id}','{$_POST["advisor_id"]}', '{$thesisTitleThai}', 
                       '{$thesisTitleEnglish}', '{$thesisDescription}', {$is_even}, 
                       {$semester}, {$academic_year}, 
                       0, 0, 0, NOW())";
    
        if ($query = mysqli_query($conn, $sql)) {
            $_SESSION["notify_message"] = "ส่งคำร้องสำเร็จ";
            header("location:http://localhost/AdvisorHub/request/request_details.php");
        } else {
            $_SESSION["notify_message"] = "ส่งคำร้องไม่สำเร็จ";
            header("location:http://localhost/AdvisorHub/request/request_details.php");
        }
    }
}

mysqli_close($conn);
?>