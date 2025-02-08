<?php
    session_start();
    require('../server.php');
    include('../components/navbar.php');
    if(isset($_POST['logout'])){
        session_destroy();
        header('location: /AdvisorHub/login');
    }

    if(isset($_POST['profile'])){
        header('location: /AdvisorHub/profile');
    }

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Topic Statistics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../Logo.png">
</head>
<body>

    <?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'file'])?>
    <div class="container">
        <h1>Research Topic Statistics</h1>

        <!-- Data Table -->
        <table>
            <thead>
                <tr>
                    <th>Research Topic</th>
                    <th>Number of Studies</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $sql = "SELECT keywords FROM thesis";
                    $result = $conn->query($sql);
                    
                    $keyword_counts = [];
                    
                    if ($result->num_rows > 0) {  // ตรวจสอบว่ามีข้อมูลหรือไม่
                        while ($row = $result->fetch_assoc()) {  // วนลูปดึงข้อมูลแต่ละแถวจากฐานข้อมูล
                            $keywords = json_decode($row['keywords'], true); // แปลง JSON เป็น Array
                            foreach ($keywords as $keyword) {  // วนลูปอ่านค่าแต่ละ keyword
                                if (!isset($keyword_counts[$keyword])) {  // ถ้ายังไม่มี keyword นี้ใน array
                                    $keyword_counts[$keyword] = 0;  // ตั้งค่าเริ่มต้นเป็น 0
                                }
                                $keyword_counts[$keyword]++;  // เพิ่มค่าขึ้น 1
                            }
                        }
                    }
                    
                    $conn->close();

                    foreach ($keyword_counts as $topic => $count) {
                        echo "
                        <tr>
                            <td>$topic</td>
                            <td>$count</td>
                        </tr>
                        
                        ";
                    }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
