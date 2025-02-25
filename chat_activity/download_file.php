<?php
session_start();
require('../server.php');
if (isset($_SESSION['username']) && $_SESSION['role'] != 'admin' || empty($_SESSION['username'])) {
    header('location: /AdvisorHub/login');
    exit();
}

// Check if POST request and user is logged in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['account_id']) && isset($_POST['message_id'])) {
    $messageId = $conn->real_escape_string($_POST['message_id']);
    $userId = $_SESSION['account_id'];

    // Verify user has permission (sender or receiver)
    $sql = "SELECT message_file_name, message_file_data, message_file_type 
            FROM messages 
            WHERE message_id = '$messageId' ";

    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        $fileName = $row['message_file_name'];
        $fileData = $row['message_file_data'];
        $fileType = $row['message_file_type'];

        // Set Content-Type based on file type
        switch ($fileType) {
            case 'image/jpeg':
                header("Content-Type: image/jpeg");
                break;
            case 'image/png':
                header("Content-Type: image/png");
                break;
            case 'application/pdf':
                header("Content-Type: application/pdf");
                break;
            case 'application/msword':
                header("Content-Type: application/msword");
                break;
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
                break;
            case 'application/vnd.ms-powerpoint':
                header("Content-Type: application/vnd.ms-powerpoint");
                break;
            case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
                header("Content-Type: application/vnd.openxmlformats-officedocument.presentationml.presentation");
                break;
            case 'application/zip':
                header("Content-Type: application/zip");
                break;
            case 'text/plain':
                header("Content-Type: text/plain");
                break;
            default:
                header("Content-Type: application/octet-stream");
        }

        // Set headers for download
        header("Content-Disposition: attachment; filename=\"" . basename($fileName) . "\"");
        header("Content-Length: " . strlen($fileData));
        header("Cache-Control: private, max-age=0, must-revalidate");
        header("Pragma: public");

        // Output file data
        echo $fileData;
        exit;
    } else {
        echo "File not found or you don't have permission.";
    }
} else {
    echo "Invalid request or access denied.";
}
?>