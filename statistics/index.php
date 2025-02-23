<?php
session_start();
require('../server.php');
include('../components/navbar.php');
if (isset($_POST['logout'])) {
    session_destroy();
    header('location: /AdvisorHub/login');
}

if (isset($_POST['profile'])) {
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <link rel="icon" href="../Logo.png">
</head>

<body>

    <?php 
        if(isset($_SESSION['username'])){
            renderNavbar(allowedPages: ['home', 'advisor', 'inbox', 'statistics', 'Teams']);
        }else{
            renderNavbar(allowedPages: ['home', 'login', 'advisor', 'statistics']);
        }
    ?>
    <div class="container">
        <h1>Research Topic Statistics</h1>

        <!-- Chart Section -->
        <div class="mt-5">
            <canvas id="thesisChart"></canvas>
        </div>

        <div class="p-2">
            <!-- Search -->
            <div class="p-4">
                <h6>Topic input</h6>
                <select id="select-tags" multiple data-placeholder="Filter Topic" class="form-control">
                    <optgroup label="Topic">
                        <?php
                        $keywords = [];

                        // ดึงข้อมูล keyword จากฐานข้อมูล
                        $sql = "SELECT keywords FROM thesis";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $decoded_keywords = json_decode($row['keywords'], true); // แปลง JSON เป็นอาร์เรย์
                                if (is_array($decoded_keywords)) {
                                    $keywords = array_merge($keywords, $decoded_keywords); // รวมข้อมูล
                                }
                            }
                        }

                        // ลบค่าที่ซ้ำกัน และเรียงตามตัวอักษร (ไม่สนตัวพิมพ์ใหญ่/เล็ก)
                        $unique_keywords = array_unique($keywords);
                        natcasesort($unique_keywords);

                        // แสดงผลลัพธ์
                        foreach ($unique_keywords as $keyword) {
                            echo "<option value='" . htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') . "'>$keyword</option>";
                        }
                        ?>
                    </optgroup>
                </select>
            </div>

            <!-- Data Table -->
            <table class="table mt-2">
                <thead>
                    <tr>
                        <th>Thesis Topic</th>
                        <th>Number of Topic</th>
                    </tr>
                </thead>
                <tbody id="thesisTableBody">
                    <?php
                    $sql = "SELECT keywords FROM thesis";
                    $result = $conn->query($sql);

                    $keyword_counts = [];

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $keywords = json_decode($row['keywords'], true);
                            foreach ($keywords as $keyword) {
                                if (!isset($keyword_counts[$keyword])) {
                                    $keyword_counts[$keyword] = 0;
                                }
                                $keyword_counts[$keyword]++;
                            }
                        }
                    }

                    arsort($keyword_counts); // Sort by count in descending order

                    $top_keywords = array_slice($keyword_counts, 0, 5); // Get top 5 Topic

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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    <script>
        // TomSelect for Filter Keywords
        new TomSelect("#select-tags", {
            plugins: ['remove_button'],
            create: true,
            onChange: function(values) {
                console.log("Selected Keywords:", values); // 🔍 ตรวจสอบค่า keywords
                fetch('filter.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'keywords=' + encodeURIComponent(JSON.stringify(values))
                    })
                    .then(response => response.text())
                    .then(data => {
                        console.log("Filter Response:", data); // 🔍 ตรวจสอบตารางที่โหลดมา
                        document.getElementById('thesisTableBody').innerHTML = data;
                    });
            }
        });



        // Chart.js for Top 5 Thesis
        const ctx = document.getElementById('thesisChart').getContext('2d');
        const thesisChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($top_keywords)); ?>,
                datasets: [{
                    label: 'Top 5 Topic',
                    data: <?php echo json_encode(array_values($top_keywords)); ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)', // สีแดง
                        'rgba(54, 162, 235, 0.2)', // สีฟ้า
                        'rgba(255, 206, 86, 0.2)', // สีเหลือง
                        'rgba(75, 192, 192, 0.2)', // สีเขียวน้ำทะเล
                        'rgba(153, 102, 255, 0.2)' // สีม่วง
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)', // สีแดง
                        'rgba(54, 162, 235, 1)', // สีฟ้า
                        'rgba(255, 206, 86, 1)', // สีเหลือง
                        'rgba(75, 192, 192, 1)', // สีเขียวน้ำทะเล
                        'rgba(153, 102, 255, 1)' // สีม่วง
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1, // กำหนดให้เพิ่มทีละ 1
                            precision: 0 // กำหนดให้แสดงเฉพาะจำนวนเต็ม
                        }
                    }
                },
                plugins: {
                    legend: {
                        onClick: (e) => e.stopPropagation(), // ปิดการคลิกที่ Legend
                    }
                }
            }
        });
    </script>

</body>

</html>