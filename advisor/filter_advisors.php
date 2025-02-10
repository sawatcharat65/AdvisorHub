<?php
require('../server.php');

header('Content-Type: text/html; charset=UTF-8');

$data = json_decode(file_get_contents("php://input"), true);
$expertise = isset($data['expertise']) ? $data['expertise'] : [];

if (!empty($expertise)) {
    $expertise_json = json_encode($expertise);
    $sql = "
        SELECT * 
        FROM advisor a 
        JOIN advisor_profile ap ON a.id = ap.advisor_id 
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

        $sql = "SELECT * FROM advisor WHERE id = '$advisor_id'";
        $result_advisor = $conn->query($sql);
        $row_advisor = $result_advisor->fetch_assoc();

        $first_name = $row_advisor['first_name'];
        $last_name = $row_advisor['last_name'];
        $email = $row_advisor['email'];
        $tel = $row_advisor['tel'];

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
        ";
    }
} else {
    echo "<p>No advisors found matching your filter criteria.</p>";
}
