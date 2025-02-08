<?php
    session_start();
    if(isset($_SESSION['username'])){
        header('location: /AdvisorHub/home');
    }
    include('../components/navbar.php');
    require('../server.php');

    if(isset($_POST['login'])){
        $username = filter_input(INPUT_POST,'username',FILTER_SANITIZE_SPECIAL_CHARS);
        $password = filter_input(INPUT_POST,'password',FILTER_SANITIZE_SPECIAL_CHARS);

        $sql = "SELECT * FROM account WHERE id = '$username'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();

        if(isset($row['id']) && $password == $row['password']){
            $_SESSION['role'] = $row['role'];
            //ถ้าเป็นนักศึกษาให้ไปดึงจาก student table 
            if($row['role'] == 'student'){
                $id = $row['id'];
                $sql = "SELECT * FROM student WHERE id = '$id'";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                $_SESSION['username'] = $row['first_name'];
                $_SESSION['id'] = $row['id'];
            //ถ้าเป็นอาจารย์ให้ไปดึง table จาก advisor
            }elseif($row['role'] == 'advisor'){
                $id = $row['id'];
                $sql = "SELECT * FROM advisor WHERE id = '$id'";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                $_SESSION['username'] = $row['first_name'];
                $_SESSION['id'] = $row['id'];
            //ถ้าเป็น admin ให้ไปดึง table จาก admin
            }elseif($row['role'] == 'admin'){
                $id = $row['id'];
                $sql = "SELECT * FROM admin WHERE id = '$id'";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                $_SESSION['id'] = $row['id'];
                $_SESSION['username'] = $row['first_name'];
            }
            header('location: /AdvisorHub/advisor');

        //ถ้ารหัสและ username ไม่ถูกต้องให้แจ้ง error 
        }else{
            $_SESSION['error'] = 'Username or password is incorrect';
            header('location: /AdvisorHub/login');
            exit();
        }

        
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../Logo.png">

</head>
<body>

    <?php renderNavbar(['home', 'login'])?>
    
    <div class="wrap">
        <form action="" method="post">
            <i class='bx bxs-user-circle' ></i>
            <div class="inputWrap">
                <input type="text" placeholder="Username" name="username" required> 
            </div>

            <div class="inputWrap">
                <input type="password" placeholder="Password" name="password" required> 
            </div>

            <div class="buttonWrap">
                <button name="login">Login</button>
            </div>

            <div class="errorMessage">
                <?php
                    if(isset($_SESSION['error'])){
                        echo "<h3> {$_SESSION['error']}</h3>";
                        unset($_SESSION['error']);
                    }
                ?>
            </div>
        </form>
    </div>
    <footer>
        <p>&copy; 2024 Naresuan University.</p>
    </footer>
</body>
</html>
