<?php
ob_start(); // เริ่ม Output Buffering
session_start();
require('../server.php');
include('../components/navbar.php');

// ตรวจสอบการ logout หรือ redirect ก่อน output
if (isset($_POST['logout'])) {
    session_destroy();
    ob_end_clean();
    header('location: /AdvisorHub/login');
    exit;
}

//ไม่ให้ admin เข้าถึง
if(isset($_SESSION['username']) && $_SESSION['role'] == 'admin'){
    header('location: /AdvisorHub/advisor');
}

if (isset($_POST['profile'])) {
    ob_end_clean();
    header('location: /AdvisorHub/profile');
    exit;
}

if (empty($_SESSION['username'])) {
    ob_end_clean();
    header('location: /AdvisorHub/login');
    exit;
}

// แก้ไขโปรไฟล์อาจารย์
if (isset($_POST['edit'])) {
    $id = $_SESSION['account_id'];
    $expertise = json_encode($_POST['expertise'] ?? []);
    $interests = htmlspecialchars($_POST['interests'] ?? '', ENT_QUOTES, 'UTF-8');

    $sql = "SELECT img FROM advisor_profile WHERE advisor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($old_img);
    $stmt->fetch();
    $stmt->close();

    $target_dir = "../uploads/";
    $uploadOk = 1;
    $img = $old_img;

    if (isset($_FILES["img"]) && $_FILES["img"]["error"] == 0) {
        $imageFileType = strtolower(pathinfo($_FILES["img"]["name"], PATHINFO_EXTENSION));
        $new_file_name = uniqid() . "." . $imageFileType;
        $target_file = $target_dir . $new_file_name;

        $check = getimagesize($_FILES["img"]["tmp_name"]);
        if ($check === false) {
            $error = "File is not an image.";
            $uploadOk = 0;
        } elseif ($_FILES["img"]["size"] > 5000000) {
            $error = "Sorry, your file is too large.";
            $uploadOk = 0;
        } elseif (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["img"]["tmp_name"], $target_file)) {
                if ($old_img && file_exists($old_img)) {
                    unlink($old_img);
                }
                $img = $target_file;
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        }
    }

    if (!isset($error)) {
        $sql = "UPDATE advisor_profile SET expertise = ?, advisor_interests = ?, img = ? WHERE advisor_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $expertise, $interests, $img, $id);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            ob_end_clean();
            header('location: /AdvisorHub/profile');
            exit;
        } else {
            $error = "Error: " . $stmt->error;
            $stmt->close();
        }
    }
    $conn->close();
}

// แก้ไขโปรไฟล์นักศึกษา
if (isset($_POST['editStudentProfile'])) {
    $id = $_SESSION['account_id'];
    $interests = htmlspecialchars($_POST['student_interests'] ?? '', ENT_QUOTES, 'UTF-8');

    $sql_update = "UPDATE student_profile SET student_interests = ? WHERE student_id = ?";
    $stmt = $conn->prepare($sql_update);

    if ($stmt) {
        $stmt->bind_param("si", $interests, $id);
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            ob_end_clean();
            header('location: /AdvisorHub/profile');
            exit;
        } else {
            $error = "Error updating profile: " . $stmt->error;
            $stmt->close();
        }
    } else {
        $error = "Error preparing statement: " . $conn->error;
    }
    $conn->close();
}

// ดึงข้อมูลสำหรับแสดงฟอร์ม
$username = $_SESSION['username'];
$role = $_SESSION['role'];
$id = $_SESSION['account_id'];
$profile_data = null;

if ($role == 'advisor') {
    $sql = "SELECT * FROM advisor_profile WHERE advisor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $profile_data = $result->fetch_assoc();
    $stmt->close();
    if (!$profile_data) {
        $conn->close();
        ob_end_clean();
        header('location: /AdvisorHub/profile');
        exit;
    }
} elseif ($role == 'student') {
    $sql = "SELECT * FROM student_profile WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $profile_data = $result->fetch_assoc();
    $stmt->close();
    if (!$profile_data) {
        $conn->close();
        ob_end_clean();
        header('location: /AdvisorHub/profile');
        exit;
    }
}
$conn->close();
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
    <?php echo renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'Teams']); ?>

    <?php if (isset($error)): ?>
        <script>alert('<?php echo addslashes($error); ?>');</script>
    <?php endif; ?>

    <?php if ($role == 'advisor'): ?>
        <form action="" method="post" class="profile-form" enctype="multipart/form-data">
            <div class="wrap">
                <h2>Edit Profile</h2>
                <h3>Expertise</h3>
                <div class="wrapCheckbox">
                    <?php
                    $expertise_options = [
                        'Artificial Intelligence (AI)', 'Machine Learning', 'Deep Learning',
                        'Natural Language Processing (NLP)', 'Computer Vision', 'Data Science',
                        'Big Data', 'Cybersecurity', 'Blockchain Technology', 'Internet of Things (IoT)',
                        'Cloud Computing', 'Edge Computing', 'Quantum Computing', 'Human-Computer Interaction (HCI)',
                        'Robotics', 'Software Engineering', 'Augmented Reality (AR) and Virtual Reality (VR)',
                        'Digital Twin', 'Computational Biology', 'Ethical AI'
                    ];
                    $current_expertise = json_decode($profile_data['expertise'] ?? '[]', true);
                    foreach ($expertise_options as $option) {
                        $id = strtolower(str_replace(' ', '', $option));
                        $checked = in_array($option, $current_expertise) ? 'checked' : '';
                        echo "<label for='$id'>$option</label>";
                        echo "<input type='checkbox' id='$id' class='filter-checkbox' name='expertise[]' value='$option' $checked><br/>";
                    }
                    ?>
                </div>
                <div class="wrapInputInterests">
                    <textarea name="interests" placeholder="Interests" required><?php echo htmlspecialchars($profile_data['advisor_interests'] ?? ''); ?></textarea>
                </div>
                <div class="wrapInput">
                    <input type="file" id="fileInput" name="img">
                    <label for="fileInput" class="file-upload-btn">Choose Profile Image</label>
                    <p class="file-name" id="fileName"></p>
                </div>
                <div class="wrapInput">
                    <button name="edit">Edit Profile</button>
                </div>
            </div>
        </form>
    <?php elseif ($role == 'student'): ?>
        <form action="" method="post" class="profile-form" enctype="multipart/form-data">
            <div class="wrap">
                <h2>Edit Student Profile</h2>
                <div class="wrapInput">
                    <textarea name="student_interests" placeholder="Interests" required><?php echo htmlspecialchars($profile_data['student_interests'] ?? ''); ?></textarea>
                </div>
                <div class="wrapInput">
                    <button name="editStudentProfile">Edit</button>
                </div>
            </div>
        </form>
    <?php endif; ?>

    <script>
        document.getElementById("fileInput").addEventListener("change", function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : "ไม่มีไฟล์เลือก";
            document.getElementById("fileName").textContent = fileName;
        });
    </script>
</body>
</html>
<?php ob_end_flush(); // ส่ง output และปิด buffer ?>