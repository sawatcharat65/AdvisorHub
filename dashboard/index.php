<?php

session_start();
include('../components/navbar.php');
require('../server.php');


// คำสั่ง SQL ดึงข้อมูลอาจารย์ที่ปรึกษา
$sql = "
    SELECT 
        a.advisor_id, 
        CONCAT(a.advisor_first_name, ' ', a.advisor_last_name) AS name,
        IFNULL(COUNT(ar.student_id), 0) + IFNULL(SUM(CASE WHEN ar.is_even = 1 THEN 1 ELSE 0 END), 0) AS students, 
        IFNULL(SUM(CASE WHEN ar.is_even = 0 THEN 1 ELSE 0 END), 0) AS single,
        IFNULL(SUM(CASE WHEN ar.is_even = 1 THEN 1 ELSE 0 END), 0) AS pair,
        IFNULL(SUM(CASE WHEN ar.is_even = 0 THEN 1 ELSE 0 END), 0) + IFNULL(SUM(CASE WHEN ar.is_even = 1 THEN 1 ELSE 0 END), 0) AS total
    FROM advisor a
    LEFT JOIN advisor_request ar ON a.advisor_id = ar.advisor_id
    GROUP BY a.advisor_id
";





$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายชื่ออาจารย์ที่ปรึกษา</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f9f9f9; text-align: center; }
        table { width: 80%; margin: auto; border-collapse: collapse; background: white; }
        th, td { border: 1px solid black; padding: 10px; text-align: center; }
        th { background:rgb(246, 197, 2); color: white; }
        .highlight { background: #f9f9f9; font-weight: bold; color: red; }
    </style>
</head>
<body>

<?php renderNavbar(['home', 'advisor',  'statistics', "Dashboard"]); ?>
    <h2>รายชื่ออาจารย์ที่ปรึกษา เเละจำนวนที่รับเป็นอาจารย์ที่ปรึกษา</h2>

    <table>
        <thead>
            <tr>
                <th>ลำดับ</th>
                <th>รายชื่ออาจารย์ที่ปรึกษา</th>
                <th>จำนวนนิสิตที่รับเป็นที่ปรึกษา(คน)</th>
                <th>นิสิตทำแบบเดี่ยว (เรื่อง)</th>
                <th>นิสิตทำแบบคู่ (เรื่อง)</th>
                <th>จำนวนหัวข้อวิทยานิพนธ์ (เรื่อง)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $index = 1;
            $total_students = 0;
            $total_single = 0;
            $total_pair = 0;
            $total_thesis = 0;

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $students = $row["students"] ?? 0;
                    $single = $row["single"] ?? 0;
                    $pair = $row["pair"] ?? 0;
                    $total = $row["total"] ?? 0;
            
                    $total_students += intval($row["students"]);
                    $total_single += $single;
                    $total_pair += $pair;
                    $total_thesis += $total;
                    echo "<tr>
                            <td>{$index}</td>
                            <td style='text-align: left;'>{$row["name"]}</td>
                            <td class='highlight'>{$row["students"]}</td>
                            <td>{$row["single"]}</td>
                            <td>{$row["pair"]}</td>
                            <td>{$row["total"]}</td>
                          </tr>";
                    $index++;
                }
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
            <td colspan="2"><strong>รวมทั้งหมด</strong></td>
            <td class='highlight'><strong><?php echo $total_students; ?></strong></td>
            <td><strong><?php echo $total_single; ?></strong></td>
            <td><strong><?php echo $total_pair; ?></strong></td>
            <td><strong><?php echo $total_thesis; ?></strong></td>
            </tr>
        </tfoot>
    </table>

</body>
</html>

<?php
// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>