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

// ถ้ามีการค้นหา
$search_query = "";

if (isset($_POST['search'])) {
    $search_query = $_POST['search'];  // รับค่าคำค้นจากฟอร์ม

}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advisor</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <link rel="icon" href="../Logo.png">
</head>

<body>

    <?php 
        if(isset($_SESSION['username']) && $_SESSION['role'] != 'admin'){
            renderNavbar(allowedPages: ['home', 'advisor', 'inbox', 'statistics', 'Teams']);
        }elseif(isset($_SESSION['username']) && $_SESSION['role'] == 'admin'){
            renderNavbar(allowedPages: ['home', 'advisor', 'statistics']);
        }
        else{
            renderNavbar(allowedPages: ['home', 'login', 'advisor', 'statistics']);
        }
    ?>

    <form action="" method="post">
        <div class="search">
            <input type="text" name="search" placeholder="Search Advisor..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button><i class='bx bx-search'></i></button>
        </div>
    </form>


    <div class="filter-wrap">
        <!-- Filter Section -->
        <form method="post" class="filter-container">
            <label for="expertise">Expertise input</label>
            <select id="expertise" name="expertise[]" multiple data-placeholder="Filter Expertise" class="expertise-filter">
                <option value="Artificial Intelligence (AI)">Artificial Intelligence (AI)</option>
                <option value="Machine Learning">Machine Learning</option>
                <option value="Deep Learning">Deep Learning</option>
                <option value="Natural Language Processing (NLP)">Natural Language Processing (NLP)</option>
                <option value="Computer Vision">Computer Vision</option>
                <option value="Data Science">Data Science</option>
                <option value="Big Data">Big Data</option>
                <option value="Cybersecurity">Cybersecurity</option>
                <option value="Blockchain Technology">Blockchain Technology</option>
                <option value="Internet of Things (IoT)">Internet of Things (IoT)</option>
                <option value="Cloud Computing">Cloud Computing</option>
                <option value="Edge Computing">Edge Computing</option>
                <option value="Quantum Computing">Quantum Computing</option>
                <option value="Human-Computer Interaction (HCI)">Human-Computer Interaction (HCI)</option>
                <option value="Robotics">Robotics</option>
                <option value="Software Engineering">Software Engineering</option>
                <option value="Augmented Reality (AR) and Virtual Reality (VR)">Augmented Reality (AR) and Virtual Reality (VR)</option>
                <option value="Digital Twin">Digital Twin</option>
                <option value="Computational Biology">Computational Biology</option>
                <option value="Ethical AI">Ethical AI</option>
            </select>
        </form>

    </div>

    <div class="advisorList">
        <?php
        // ป้องกัน SQL Injection ด้วย prepared statement
        if ($search_query != "") {
            $sql = "SELECT *
                    FROM advisor a
                    JOIN advisor_profile ap ON a.advisor_id = ap.advisor_id
                    WHERE a.advisor_first_name LIKE ? OR a.advisor_last_name LIKE ? ";

            $stmt = $conn->prepare($sql);

            // แนบเครื่องหมาย % ก่อนและหลังคำค้นหาสำหรับ LIKE
            $search_param = "%" . $search_query . "%";
            $stmt->bind_param("ss", $search_param, $search_param);

            $stmt->execute();
            $result = $stmt->get_result();
        } elseif (isset($_POST['filter']) && isset($_POST['expertise'])) {
            $expertise = json_encode($_POST['expertise']);
            $sql = "
                    SELECT * 
                    FROM advisor a 
                    JOIN advisor_profile ap ON a.advisor_id = ap.advisor_id 
                    WHERE JSON_CONTAINS(ap.expertise, '$expertise');
                ";
            $result = $conn->query($sql);
        } else {
            // หากไม่มีคำค้นหาให้ดึงข้อมูลทั้งหมด
            $sql = "SELECT * FROM advisor_profile";
            $result = $conn->query($sql);
        }

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $advisor_id = $row['advisor_id'];
                $expertise = $row['expertise'];
                $interests = $row['advisor_interests'];
                $img = $row['img'];

                $sql = "SELECT * FROM advisor WHERE advisor_id = '$advisor_id'";
                $result_advisor = $conn->query($sql);
                $row_advisor = $result_advisor->fetch_assoc();

                $first_name = $row_advisor['advisor_first_name'];
                $last_name = $row_advisor['advisor_last_name'];
                $tel = $row_advisor['advisor_tel'];
                $email = $row_advisor['advisor_email'];

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
            echo "<h3 class='not-match'>No advisors found matching your search criteria.</h3>";
        }
        ?>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let select = new TomSelect("#expertise", {
                plugins: ['remove_button'],
                persist: false,
                create: false,
                onChange: function(values) {
                    fetch('filter_advisors.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                expertise: values
                            })
                        })
                        .then(response => response.text())
                        .then(data => {
                            document.querySelector('.advisorList').innerHTML = data; // อัปเดตรายชื่ออาจารย์
                        })
                        .catch(error => console.error('Error:', error));
                }
            });
        });
    </script>

</body>

</html>