<?php
session_start();
require('../server.php');

if (isset($_POST['title']) && isset($_POST['receiver_id'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $receiver_id = $conn->real_escape_string($_POST['receiver_id']);
    $id = $_SESSION['account_id'];

    $sql = "DELETE FROM messages WHERE message_title = '$title' 
            AND ((sender_id = '$id' AND receiver_id = '$receiver_id') 
            OR (sender_id = '$receiver_id' AND receiver_id = '$id'))";

    if ($conn->query($sql) === TRUE) {
        echo 'success';
    } else {
        echo 'error';
    }
} else {
    echo 'error';
}
