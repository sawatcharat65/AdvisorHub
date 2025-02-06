<?php
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'thesis_hub';

    $conn = mysqli_connect($host, $username, $password, $database);
    if($conn){
        
    }else{
        die(mysqli_connect_error());
    }


?>