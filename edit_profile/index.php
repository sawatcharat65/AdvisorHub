<?php
    session_start();
    require('../server.php');
    if(isset($_POST['logout'])){
        session_destroy();
        header('location: /AdvisorHub/login');
    }

    if(isset($_POST['profile'])){
        header('location: /AdvisorHub/profile');
    }

    if(empty($_SESSION['username'])){
        header('location: /AdvisorHub/login');
    }

    if (isset($_POST['edit'])) {
        $id = $_SESSION['id'];  
        $expertise = json_encode($_POST['expertise']);
        $interests = $_POST['interests'];

        // เชื่อมต่อฐานข้อมูลและดึงข้อมูลรูปโปรไฟล์เก่าจากฐานข้อมูล
        $sql = "SELECT img FROM advisor_profile WHERE advisor_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($old_img);
        $stmt->fetch();
        $stmt->close();
    
        // จัดการการอัพโหลดไฟล์ (ถ้ามีการเลือกไฟล์ใหม่)
        $target_dir = "../uploads/";
        $uploadOk = 1;
        $new_file_name = null;
    
        if (isset($_FILES["img"]) && $_FILES["img"]["error"] == 0) {
            // ตรวจสอบว่าเป็นไฟล์รูปภาพ
            $imageFileType = strtolower(pathinfo($_FILES["img"]["name"], PATHINFO_EXTENSION));
            $new_file_name = uniqid() . "." . $imageFileType;
            $target_file = $target_dir . $new_file_name;
    
            $check = getimagesize($_FILES["img"]["tmp_name"]);
            if ($check === false) {
                echo "File is not an image.";
                $uploadOk = 0;
            }
    
            // ตรวจสอบขนาดไฟล์
            if ($_FILES["img"]["size"] > 5000000) {  // 5MB
                echo "Sorry, your file is too large.";
                $uploadOk = 0;
            }
    
            // ตรวจสอบประเภทไฟล์
            $allowed_types = array("jpg", "jpeg", "png", "gif");
            if (!in_array($imageFileType, $allowed_types)) {
                echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                $uploadOk = 0;
            }
    
            // หากทุกอย่างถูกต้องให้ทำการอัพโหลด
            if ($uploadOk == 1) {
                if (move_uploaded_file($_FILES["img"]["tmp_name"], $target_file)) {
                    // ลบไฟล์เก่า (ถ้ามี)
                    if ($old_img && file_exists($old_img)) {
                        unlink($old_img);  // ลบไฟล์เก่า
                    }
                    $img = $target_file; // เก็บชื่อไฟล์ใหม่
                } else {
                    echo "Sorry, there was an error uploading your file.";
                    exit;
                }
            }
        } else {
            // ถ้าไม่มีการอัพโหลดไฟล์ใหม่ ให้ใช้ไฟล์เดิม
            $img = $old_img;
        }
    
        // อัพเดทข้อมูลในฐานข้อมูล
        $sql = "UPDATE advisor_profile SET 
                expertise = ?, 
                interests = ?,
                img = ?
                WHERE advisor_id = ?";
    
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $expertise, $interests, $img, $id);
    
        if ($stmt->execute()) {
            header('location: /AdvisorHub/profile');  // รีไดเร็กต์ไปที่หน้าประวัติ
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }
    
        $stmt->close();
        $conn->close();
    }
    

    if (isset($_POST['editStudentProfile'])) {
        $id = $_SESSION['id'];
        $interests = $_POST['interests'];

        // คำสั่ง SQL สำหรับการอัพเดตข้อมูล
        $sql_update = "UPDATE student_profile SET interests = ? WHERE student_id = ?";

        // เตรียมคำสั่ง SQL
        if ($stmt = $conn->prepare($sql_update)) {
            // ผูกค่าตัวแปรกับคำสั่ง SQL
            $stmt->bind_param("ss", $interests, $id);

            // Execute คำสั่ง SQL
            if ($stmt->execute()) {
                header('location: /AdvisorHub/profile');
            } else {
                echo "Error updating profile: " . $stmt->error;
            }

            // ปิด statement
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../Logo.png">
</head>
<body>
<nav>
        <div class="logo">
            <img src="../CSIT.png" alt="" width="250px">
        </div>
        <ul>
            <li><a href="/AdvisorHub/home">Home</a></li>
            
            <?php
                if(isset($_SESSION['username'])){
                    echo 
                    "
                    <li><a href='/AdvisorHub/advisor'>Advisor</a></li>
                    <li><a href='/AdvisorHub/inbox'>Inbox</a></li>
                    <li><a href='/AdvisorHub/thesis/thesis.php'>Thesis</a></li>
                    <li><a href='/AdvisorHub/statistics'>Statistics</a></li>
                    <li><a href='/AdvisorHub/thesis_resource_list/thesis_resource_list.php'>File</a></li>
                    ";
                }else{
                    echo "<li><a href='/AdvisorHub/login'>Login</a></li>";
                }
            ?>
        </ul>

        <div class="userProfile">
            <?php
                if(isset($_SESSION['username'])){
                    echo '<h2>'.$_SESSION['username'].'<h2/>';
                    echo "<i class='bx bxs-user-circle' ></i>";
                    echo "<div class='dropdown'>
                            <form action='' method='post'>
                                <button name='profile'>Profile</button>
                                <button name='logout'>Logout</button>
                            </form>
                        </div>";
                }
            ?>
        </div>
    </nav>

    <?php
        $username = $_SESSION['username'];
        $role = $_SESSION['role'];
        $id = $_SESSION['id'];

        if($role == 'advisor'){
            $sql = "SELECT * FROM advisor_profile WHERE advisor_id = '$id'";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            
            if(isset($row['id'])){
                $interests = $row['interests'];

                echo 
                "
                <form action='' method='post' class='profile-form' enctype='multipart/form-data'>
                    <div class='wrap'>
                        <h2>Edit Profile</h2>
                        <h3>Expertise</h3>
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
                            <textarea name='interests' id='' placeholder='Interests' required>$interests</textarea>
                        </div>

                        <div class='wrapInput'>
                            <input type='file' id='fileInput' name='img'>
                            <label for='fileInput' class='file-upload-btn'>Choose Profile Image</label>
                            <p class='file-name' id='fileName'></p>
                        </div>

                        <div class='wrapInput'>
                            <button name='edit'>Edit Profile</button>
                        </div>
                        
                    </div>
                </form>
                ";
            }else{
                header('location: /AdvisorHub/profile');
            }
        }elseif($role == 'student'){
            $user_id = $_SESSION['id'];
            $sql = "SELECT * FROM student_profile WHERE student_id = '$id'";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();


            if(isset($row['id'])){
                $interests = $row['interests'];

                echo 
                "
                <form action='' method='post' class='profile-form' enctype='multipart/form-data'>
                    <div class='wrap'>
                        <h2>Edit Student Profile</h2>
                        
                        <div class='wrapInput'>
                            <textarea name='interests' id='' placeholder='Interests' required>$interests</textarea>
                        </div>

                        <div class='wrapInput'>
                            <button name='editStudentProfile'>Edit</button>
                        </div>
                        
                    </div>
                </form>
                ";
            }else{
                header('location: /AdvisorHub/profile');
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