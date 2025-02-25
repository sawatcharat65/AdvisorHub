<?php
require('../server.php');

//กำหนดให้เบราว์เซอร์รับข้อมูลเป็น HTML และใช้การเข้ารหัส UTF-8
//header('Content-Type: text/html; charset=UTF-8');

$data = json_decode(file_get_contents("php://input"), true);
$expertise = isset($data['expertise']) ? $data['expertise'] : [];

if (!empty($expertise)) {
    $expertise_json = json_encode($expertise);
    $sql = "
        SELECT * 
        FROM advisor a 
        JOIN advisor_profile ap ON a.advisor_id = ap.advisor_id 
        WHERE JSON_CONTAINS(ap.expertise, '$expertise_json')
    ";
} else {
    $sql = "SELECT * FROM advisor_profile";
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $advisor_id = $row['advisor_id'];
        $expertise = $row['expertise'];
        $img = $row['img'];

        $sql = "SELECT * FROM advisor WHERE advisor_id = '$advisor_id'";
        $result_advisor = $conn->query($sql);
        $row_advisor = $result_advisor->fetch_assoc();

        $first_name = $row_advisor['advisor_first_name'];
        $last_name = $row_advisor['advisor_last_name'];
        $email = $row_advisor['advisor_email'];
        $tel = $row_advisor['advisor_tel'];

        echo "
        <div class='advisorCard'>
            <img src='$img' alt=''>
            <div class='details'>
                <p>$first_name $last_name</p>
                <p>Email : $email</p>
                <p>Tel : $tel</p>
                <form action='../info/index.php' method='post'>
                    <button name='info' value='$advisor_id'><i class='bx bx-info-circle'></i></button>
                </form>
            </div>
        </div>
        </div>
        ";
    }
} else {
    echo "<h3 class='not-match'>No advisors found matching your search criteria.</h3>";
}
