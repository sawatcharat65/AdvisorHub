<?php
include('../server.php');

if (isset($_GET['studentID'])) {
    $studentID = mysqli_real_escape_string($conn, $_GET['studentID']);
    $sql = "SELECT first_name, last_name, department, tel, email FROM student WHERE id = '$studentID'";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode([
            'success' => true,
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'branch' => ($row['department'] == 'Computer Science') ? 'CS' : 'IT',
            'phone' => $row['tel'],
            'email' => $row['email']
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
}

mysqli_close($conn);
