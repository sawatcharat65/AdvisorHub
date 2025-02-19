<?php
    session_start();
    include('../components/navbar.php');
    require('../server.php');
    if(isset($_POST['logout'])){
        session_destroy();
        header('location: /AdvisorHub/login');
    }

    if(empty($_SESSION['username'])){
        header('location: /AdvisorHub/login');
    }

    if(isset($_POST['edit'])){
        header('location: /AdvisorHub/edit_profile');
    }

    if (isset($_POST['delete'])) {
        // รับ id จาก session
        $advisor_id = $_SESSION['account_id'];
    
        // 1. ค้นหาข้อมูลไฟล์รูปภาพจากฐานข้อมูลก่อนการลบ
        $sql = "SELECT img FROM advisor_profile WHERE advisor_id = '$advisor_id '";
        $result = $conn->query($sql);
    
        // ตรวจสอบว่าได้ผลลัพธ์หรือไม่
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $img = $row['img'];  // ไฟล์รูปภาพที่ต้องการลบ
    
            // 2. ลบไฟล์จากเซิร์ฟเวอร์
            if (file_exists($img)) {
                unlink($img);  // ลบไฟล์จากเซิร์ฟเวอร์
            }
        }
    
        // 3. ลบข้อมูลจากฐานข้อมูล
        $sql_delete = "DELETE FROM advisor_profile WHERE advisor_id = '$advisor_id '";
        if ($conn->query($sql_delete)) {
            header('location: /AdvisorHub/profile');
            exit;
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }

    if (isset($_POST['submit']) && isset($_POST['expertise']) && isset($_FILES['img']) && $_FILES['img']['error'] == UPLOAD_ERR_OK) {
        // รับค่าจากฟอร์ม
        $advisor_id = $_SESSION['account_id'];
        $expertise = json_encode($_POST['expertise']);
        $advisor_interests = $_POST['interests'];

        // จัดการการอัพโหลดไฟล์
        $target_dir = "../uploads/";
        $imageFileType = strtolower(pathinfo($_FILES["img"]["name"], PATHINFO_EXTENSION));
    
        // สร้างชื่อไฟล์ใหม่โดยใช้ uniqid() และต่อท้ายด้วยนามสกุลไฟล์เดิม
        $new_file_name = uniqid() . "." . $imageFileType;
        $target_file = $target_dir . $new_file_name;
    
        $uploadOk = 1;
    
        // ตรวจสอบว่าเป็นไฟล์รูปภาพ
        $check = getimagesize($_FILES["img"]["tmp_name"]);
        if ($check === false) {
            echo "File is not an image.";
            $uploadOk = 0;
        }
    
        // ตรวจสอบขนาดไฟล์ (สามารถปรับขนาดได้ตามที่ต้องการ)
        if ($_FILES["img"]["size"] > 5000000) {  // 5MB
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }
    
        // ตรวจสอบประเภทของไฟล์ (สามารถปรับประเภทไฟล์ได้ตามที่ต้องการ)
        $allowed_types = array("jpg", "jpeg", "png", "gif");
        if (!in_array($imageFileType, $allowed_types)) {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }
    
        // หากทุกอย่างถูกต้องให้ทำการอัพโหลด
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["img"]["tmp_name"], $target_file)) {
                $img = $target_file;
    
                // เชื่อมต่อฐานข้อมูลและเตรียมคำสั่ง SQL
                $sql = "INSERT INTO advisor_profile (advisor_id, expertise, interests, img) 
                        VALUES (?, ?, ?, ?)";
    
                // เตรียมคำสั่ง SQL
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $advisor_id, $expertise, $advisor_interests, $img);
                
                // ดำเนินการคำสั่ง SQL
                if ($stmt->execute()) {
                    header('location: /AdvisorHub/profile');
                    exit;
                } else {
                    echo "Error: " . $stmt->error;
                }
                
                // ปิดการเชื่อมต่อ
                $stmt->close();
            }
        }
        // ปิดการเชื่อมต่อ
        $conn->close();
        
    }elseif(isset($_POST['submit']) && empty($_POST['expertise'])){
        $_SESSION['error'] = "Please choose your expertise at least one.";
    }elseif (isset($_POST['submit'])) {
        $_SESSION['error'] = "Please upload an image.";
    }
    

    if(isset($_POST['addStudentProfile'])){
        $student_id = $_SESSION['account_id'];
        $student_interests = $_POST['interests'];
        $sql = "INSERT INTO student_profile (student_id, interests)
        VALUES (?, ?)";

        // เตรียม statement
        if ($stmt = $conn->prepare($sql)) {
            // ผูกค่าตัวแปรกับคำสั่ง SQL
            $stmt->bind_param("ss", $student_id,$student_interests);
            
            // Execute คำสั่ง SQL
            if ($stmt->execute()) {
                header('location: /AdvisorHub/profile');
            } else {
                echo "Error saving student profile: " . $stmt->error;
            }

            // ปิด statement
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    }

    if(isset($_POST['deleteStudentProfile'])){
        $student_id = $_SESSION['account_id'];
        $sql = "DELETE FROM student_profile WHERE student_id = '$student_id'";

        if($conn->query($sql)){
            header('location: /AdvisorHub/profile');
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../Logo.png">
</head>
<body>

    <?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'Teams'])?>
    
    <?php
        $username = $_SESSION['username'];
        $role = $_SESSION['role'];
        $account_id = $_SESSION['account_id'];
        
        //ถ้าเป็นอาจารย์ 
        if($role == 'advisor'){
            $sql = "SELECT * FROM advisor_profile WHERE advisor_id = '$account_id'";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();

            //ถ้าอาจารย์มี profile อยู่แล้ว
            if(isset($row['id'])){
                $expertise = json_decode($row['expertise']);
                $advisor_interests = $row['advisor_interests'];
                $img = $row['img'];
                
                $sql = "SELECT * FROM advisor WHERE advisor_id = '$account_id'";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();

                $advisor_first_name = $row['advisor_first_name'];
                $advisor_last_name = $row['advisor_last_name'];
                $advisor_tel = $row['advisor_tel'];
                $advisor_email = $row['advisor_email'];

                echo 
                "
                <div class='container'>
                    
                    <div class='profile-info'>
                    <img src= '$img' >
                    <h2>$advisor_first_name $advisor_last_name</h2>
                    </div>

                    
                    <div class='contact-info'>
                        <h3>Contact</h3>
                        <p><strong>Email:</strong> $advisor_email</p>
                        <p><strong>Telephone Number:</strong> $advisor_tel</p>
                        </div>

                        <!-- ข้อมูลหัวข้อวิจัย -->
                        <div class='research-info'>
                        <h3>Expertise</h3>
                    ";
                        foreach($expertise as $item){
                            echo "<p>$item</p>";
                        }

                    echo "
                        <h3>Interests</h3>
                        <p>" . nl2br($advisor_interests) . "</p>
                        
                    </div>
                    <form action='' method='post' class='editForm'>
                        <button name='edit' class='edit'>Edit</button>
                        <button name='delete' class='delete'>Delete</button>
                    </form>
                </div>
                ";
            //ถ้าอาจารย์ไม่มี profile 
            }else{
                echo 
                "
                <form action='' method='post' class='profile-form' enctype='multipart/form-data'>
                    <div class='wrap'>
                        <h2>Advisor Profile</h2>
                        <h3>Expertise (Choose at least one)</h3>
                        <div class='wrapCheckbox'>
                            <label for='ai'>Artificial Intelligence (AI)</label>
                            <input type='checkbox' id='ai' class='filter-checkbox' name='expertise[]' value='Artificial Intelligence (AI)'>
                            <br/>
                            
                            <label for='ml'>Machine Learning</label>
                            <input type='checkbox' id='ml' class='filter-checkbox' name='expertise[]' value='Machine Learning'>
                            <br/>

                            <label for='dl'>Deep Learning</label>
                            <input type='checkbox' id='dl' class='filter-checkbox' name='expertise[]' value='Deep Learning'>
                            <br/>

                            <label for='nlp'>Natural Language Processing (NLP)</label>
                            <input type='checkbox' id='nlp' class='filter-checkbox' name='expertise[]' value='Natural Language Processing (NLP)'>
                            <br/>

                            <label for='cv'>Computer Vision</label>
                            <input type='checkbox' id='cv' class='filter-checkbox' name='expertise[]' value='Computer Vision'>
                            <br/>

                            <label for='ds'>Data Science</label>
                            <input type='checkbox' id='ds' class='filter-checkbox' name='expertise[]' value='Data Science'>
                            <br/>

                            <label for='bigdata'>Big Data</label>
                            <input type='checkbox' id='bigdata' class='filter-checkbox' name='expertise[]' value='Big Data'>
                            <br/>

                            <label for='cyber'>Cybersecurity</label>
                            <input type='checkbox' id='cyber' class='filter-checkbox' name='expertise[]' value='Cybersecurity'>
                            <br/>

                            <label for='blockchain'>Blockchain Technology</label>
                            <input type='checkbox' id='blockchain' class='filter-checkbox' name='expertise[]' value='Blockchain Technology'>
                            <br/>

                            <label for='iot'>Internet of Things (IoT)</label>
                            <input type='checkbox' id='iot' class='filter-checkbox' name='expertise[]' value='Internet of Things (IoT)'>
                            <br/>

                            <label for='cloud'>Cloud Computing</label>
                            <input type='checkbox' id='cloud' class='filter-checkbox' name='expertise[]' value='Cloud Computing'>
                            <br/>

                            <label for='edge'>Edge Computing</label>
                            <input type='checkbox' id='edge' class='filter-checkbox' name='expertise[]' value='Edge Computing'>
                            <br/>

                            <label for='quantum'>Quantum Computing</label>
                            <input type='checkbox' id='quantum' class='filter-checkbox' name='expertise[]' value='Quantum Computing'>
                            <br/>

                            <label for='hci'>Human-Computer Interaction (HCI)</label>
                            <input type='checkbox' id='hci' class='filter-checkbox' name='expertise[]' value='Human-Computer Interaction (HCI)'>
                            <br/>

                            <label for='robotics'>Robotics</label>
                            <input type='checkbox' id='robotics' class='filter-checkbox' name='expertise[]' value='Robotics'>
                            <br/>

                            <label for='software'>Software Engineering</label>
                            <input type='checkbox' id='software' class='filter-checkbox' name='expertise[]' value='Software Engineering'>
                            <br/>

                            <label for='arvr'>Augmented Reality (AR) and Virtual Reality (VR)</label>
                            <input type='checkbox' id='arvr' class='filter-checkbox' name='expertise[]' value='Augmented Reality (AR) and Virtual Reality (VR)'>
                            <br/>

                            <label for='digitwin'>Digital Twin</label>
                            <input type='checkbox' id='digitwin' class='filter-checkbox' name='expertise[]' value='Digital Twin'>
                            <br/>

                            <label for='compbio'>Computational Biology</label>
                            <input type='checkbox' id='compbio' class='filter-checkbox' name='expertise[]' value='Computational Biology'>
                            <br/>

                            <label for='ethicalai'>Ethical AI</label>
                            <input type='checkbox' id='ethicalai' class='filter-checkbox' name='expertise[]' value='Ethical AI'>
                            
                        </div>

                        <div class='wrapInputInterests'>
                            <textarea name='interests' id='' placeholder='Interests' required></textarea>
                        </div>

                        <div class='wrapInput'>
                            <input type='file' id='fileInput' name='img'>
                            <label for='fileInput' class='file-upload-btn'>Choose Profile Image</label>
                            <p class='file-name' id='fileName'></p>
                        </div>

                        <div class='wrapInput'>
                            <button name='submit'>Add Profile</button>
                        </div>
                        <h3 class = 'error'>
                        ";
                        if(isset($_SESSION['error'])){
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                        }
                        echo"
                        </h3>
                    </div>
                </form>
                ";
            }
            //ถ้าเป็นนักเรียน
        }elseif($role = 'student'){
            $sql = "SELECT * FROM student_profile WHERE student_id = '$account_id'";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            //ถ้ามี profile 
            if(isset($row['id'])){
                $student_interests = $row['student_interests'];

                $sql = "SELECT * FROM student WHERE student_id = '$account_id'";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();

                $student_first_name = $row['student_first_name'];
                $student_last_name = $row['student_last_name'];
                $student_tel = $row['student_tel'];
                $student_email = $row['student_email'];
                $student_department = $row['student_department'];

                echo 
                "
                <div class='container'>
                    
                    <div class='profile-info'>
                    <h2>$student_first_name $student_last_name</h2>
                    <p>Department $student_department</p>
                    </div>

                    
                    <div class='contact-info'>
                        <h3>Contact</h3>
                        <p><strong>Email:</strong> $student_email</p>
                        <p><strong>Telephone Number:</strong> $student_tel</p>
                        </div>

                        <!-- ข้อมูลหัวข้อวิจัย -->
                        <div class='research-info'>
                        <h3>Interested research topics</h3>
                        <p>" . nl2br($student_interests) . "</p>
                    </div>
                    <form action='' method='post' class='editForm'>
                        <button name='edit' class='edit'>Edit</button>
                        <button name='deleteStudentProfile' class='delete'>Delete</button>
                    </form>
                </div>
                ";
            //ถ้านักเรียนไม่มี profile
            }else{
                echo 
                "
                <form action='' method='post' class='profile-form' enctype='multipart/form-data'>
                    <div class='wrap'>
                        <h2>Student Profile</h2>
                        
                        <div class='wrapInput'>
                            <textarea name='interests' id='' placeholder='Interests' required></textarea>
                        </div>
                        
                        <div class='wrapInput'>
                            <button name='addStudentProfile'>Add Profile</button>
                        </div>
                        
                    </div>
                </form>
                ";
            }
        }
    
    
    ?>
    
    
    <script>
    // แสดงชื่อไฟล์เมื่อเลือก
        document.getElementById("fileInput").addEventListener("change", function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : "ไม่มีไฟล์เลือก";
            document.getElementById("fileName").textContent = fileName;
        });
    </script>
</body>
</html>

