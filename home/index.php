<?php
    session_start();
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
    <title>Home</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../Logo.png">

</head>
<body>
    <?php 
        if(isset($_SESSION['username']) && $_SESSION['role'] != 'admin'){
            renderNavbar(allowedPages: ['home', 'advisor', 'inbox', 'statistics', 'Teams']);
        }elseif(isset($_SESSION['username']) && $_SESSION['role'] == 'admin'){
            renderNavbar(allowedPages: ['home', 'advisor', 'statistics', 'admin_inbox']);
        }
        else{
            renderNavbar(allowedPages: ['home', 'login', 'advisor', 'statistics']);
        }
    ?>


    <img src="nu.jpg" alt="" width="100%">

    <footer>
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-about">
                    <h3>เกี่ยวกับเรา</h3>
                    <p>ThesisAdvisorHub เป็นเว็บไซต์ที่ช่วยให้นักศึกษาและผู้ที่ต้องการคำปรึกษาในการวิจัยสามารถค้นหาและติดต่ออาจารย์ที่ปรึกษาได้สะดวกมากขึ้น</p>
                </div>
                
                <div class="footer-contact">
                    <h3>ติดต่อเรา</h3>
                    <ul>
                        <li> <a href="mailto:contact@advisorhub.com">ThesisAdvisorHub@gmail.com</a></li>
                        <li>055-123-4561</li>
                        <li> มหาวิทยาลัยนเรศวร, ตำบลบางกระทุ่ม, อำเภอเมือง, จังหวัดพิษณุโลก</li>
                    </ul>
                </div>
                
                <div class="footer-social">
                    <h3>ติดตามเรา</h3>
                    <ul>
                        <li><a href="https://www.facebook.com/nu.university"><i class='bx bxl-facebook-circle'></i></a></li>
                        <li><a href="#"><i class='bx bxl-twitter'></i></a></li>
                        <li><a href="#"><i class='bx bxl-instagram-alt' ></i></a></li>
                    </ul>
                </div>
            </div>


        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 ThesisAdvisorHub | All Rights Reserved</p>
        </div>
    </footer>
</body>
</html>