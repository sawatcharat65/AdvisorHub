<?php
    session_start();
    
    require('../server.php');
    include('../components/navbar.php');
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
    <link rel="icon" href="../Logo.png">
</head>
<body>

    <?php renderNavbar(['home', 'advisor', 'inbox', 'thesis', 'statistics', 'file'])?>
    
    <form action="" method="post">
        <div class="search">
            <input type="text" name="search" placeholder="Search Advisor..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button><i class='bx bx-search'></i></button>
        </div>
    </form>
    
    
    <div class="filter-wrap">
        <!-- Filter Section -->
        <form method='post' class='filter-container'>
        

        <label for='ai'>Artificial Intelligence (AI)</label>
        <input type='checkbox' id='ai' class='filter-checkbox' name='expertise[]' value='Artificial Intelligence (AI)'>
        
        <label for='ml'>Machine Learning</label>
        <input type='checkbox' id='ml' class='filter-checkbox' name='expertise[]' value='Machine Learning'>

        <label for='dl'>Deep Learning</label>
        <input type='checkbox' id='dl' class='filter-checkbox' name='expertise[]' value='Deep Learning'>

        <label for='nlp'>Natural Language Processing (NLP)</label>
        <input type='checkbox' id='nlp' class='filter-checkbox' name='expertise[]' value='Natural Language Processing (NLP)'>

        <label for='cv'>Computer Vision</label>
        <input type='checkbox' id='cv' class='filter-checkbox' name='expertise[]' value='Computer Vision'>

        <label for='ds'>Data Science</label>
        <input type='checkbox' id='ds' class='filter-checkbox' name='expertise[]' value='Data Science'>

        <label for='bigdata'>Big Data</label>
        <input type='checkbox' id='bigdata' class='filter-checkbox' name='expertise[]' value='Big Data'>

        <label for='cyber'>Cybersecurity</label>
        <input type='checkbox' id='cyber' class='filter-checkbox' name='expertise[]' value='Cybersecurity'>

        <label for='blockchain'>Blockchain Technology</label>
        <input type='checkbox' id='blockchain' class='filter-checkbox' name='expertise[]' value='Blockchain Technology'>

        <label for='iot'>Internet of Things (IoT)</label>
        <input type='checkbox' id='iot' class='filter-checkbox' name='expertise[]' value='Internet of Things (IoT)'>

        <label for='cloud'>Cloud Computing</label>
        <input type='checkbox' id='cloud' class='filter-checkbox' name='expertise[]' value='Cloud Computing'>

        <label for='edge'>Edge Computing</label>
        <input type='checkbox' id='edge' class='filter-checkbox' name='expertise[]' value='Edge Computing'>

        <label for='quantum'>Quantum Computing</label>
        <input type='checkbox' id='quantum' class='filter-checkbox' name='expertise[]' value='Quantum Computing'>

        <label for='hci'>Human-Computer Interaction (HCI)</label>
        <input type='checkbox' id='hci' class='filter-checkbox' name='expertise[]' value='Human-Computer Interaction (HCI)'>

        <label for='robotics'>Robotics</label>
        <input type='checkbox' id='robotics' class='filter-checkbox' name='expertise[]' value='Robotics'>

        <label for='software'>Software Engineering</label>
        <input type='checkbox' id='software' class='filter-checkbox' name='expertise[]' value='Software Engineering'>

        <label for='arvr'>Augmented Reality (AR) and Virtual Reality (VR)</label>
        <input type='checkbox' id='arvr' class='filter-checkbox' name='expertise[]' value='Augmented Reality (AR) and Virtual Reality (VR)'>

        <label for='digitwin'>Digital Twin</label>
        <input type='checkbox' id='digitwin' class='filter-checkbox' name='expertise[]' value='Digital Twin'>

        <label for='compbio'>Computational Biology</label>
        <input type='checkbox' id='compbio' class='filter-checkbox' name='expertise[]' value='Computational Biology'>

        <label for='ethicalai'>Ethical AI</label>
        <input type='checkbox' id='ethicalai' class='filter-checkbox' name='expertise[]' value='Ethical AI'>
            
        <div class="wrapFilterButton">
            <button name="filter" class="filterButton">Filter</button>
        </div>
        </form>
    </div>

    <div class="advisorList">
    <?php
        // ป้องกัน SQL Injection ด้วย prepared statement
        if ($search_query != ""){
            $sql = "SELECT *
                    FROM advisor a
                    JOIN advisor_profile ap ON a.id = ap.advisor_id
                    WHERE a.first_name LIKE ? OR a.last_name LIKE ? ";

            $stmt = $conn->prepare($sql);

            // แนบเครื่องหมาย % ก่อนและหลังคำค้นหาสำหรับ LIKE
            $search_param = "%" . $search_query . "%";
            $stmt->bind_param("ss", $search_param, $search_param);

            $stmt->execute();
            $result = $stmt->get_result();
            }elseif(isset($_POST['filter']) && isset($_POST['expertise'])){
                $expertise = json_encode($_POST['expertise']);
                $sql = "
                    SELECT * 
                    FROM advisor a 
                    JOIN advisor_profile ap ON a.id = ap.advisor_id 
                    WHERE JSON_CONTAINS(ap.expertise, '$expertise');
                ";
                $result = $conn->query($sql);
            }
            else {
                // หากไม่มีคำค้นหาให้ดึงข้อมูลทั้งหมด
                $sql = "SELECT * FROM advisor_profile";
                $result = $conn->query($sql);
            }

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()){
                    $advisor_id = $row['advisor_id'];
                    $expertise = $row['expertise'];
                    $interests = $row['interests'];
                    $img = $row['img'];

                    $sql = "SELECT * FROM advisor WHERE id = '$advisor_id'";
                    $result_advisor = $conn->query($sql);
                    $row_advisor = $result_advisor->fetch_assoc();
                    
                    $first_name = $row_advisor['first_name'];
                    $last_name = $row_advisor['last_name'];
                    $tel = $row_advisor['tel'];
                    $email = $row_advisor['email'];

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
                echo "<p>No advisors found matching your search criteria.</p>";
            }
        ?>
    </div>
    
</body>
</html>
