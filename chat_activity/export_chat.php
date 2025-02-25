<?php
session_start();
require('../server.php');
if (isset($_SESSION['username']) && $_SESSION['role'] != 'admin' || empty($_SESSION['username'])) {
    header('location: /AdvisorHub/login');
    exit();
}

// Check if POST data is received
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['selected_pairs'])) {
    die('Invalid request.');
}

$selectedPairs = json_decode($_POST['selected_pairs'], true);
if (empty($selectedPairs)) {
    die('No pairs selected.');
}

// Prepare CSV data
$output = fopen('php://output', 'w');

// Improved headers for clarity
fputcsv($output, [
    'Student Name',
    'Advisor Name',
    'Conversation Title',
    'Message Content',
    'Sent By',
    'Date and Time',
    'Attached File',
    'File Type'
]);

// Fetch and write messages for each selected pair
foreach ($selectedPairs as $index => $pair) {
    $student_id = $pair['student_id'];
    $advisor_id = $pair['advisor_id'];

    // Add a separator row before each pair (except the first)
    if ($index > 0) {
        fputcsv($output, ['---', '---', '---', '---', '---', '---', '---', '---']);
    }

    $sql = "
        SELECT 
            CONCAT(s.student_first_name, ' ', s.student_last_name) AS student_name,
            CONCAT(a.advisor_first_name, ' ', a.advisor_last_name) AS advisor_name,
            m.message_title,
            m.message,
            CASE 
                WHEN m.sender_id = s.student_id THEN CONCAT(s.student_first_name, ' ', s.student_last_name)
                WHEN m.sender_id = a.advisor_id THEN CONCAT(a.advisor_first_name, ' ', a.advisor_last_name)
            END AS sender_name,
            m.time_stamp,
            m.message_file_name,
            m.message_file_type
        FROM 
            messages m
        LEFT JOIN 
            student s ON s.student_id = m.sender_id OR s.student_id = m.receiver_id
        LEFT JOIN 
            advisor a ON a.advisor_id = m.sender_id OR a.advisor_id = m.receiver_id
        WHERE 
            ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
        ORDER BY 
            m.time_stamp ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $student_id, $advisor_id, $advisor_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['student_name'],
            $row['advisor_name'],
            $row['message_title'],
            $row['message'],
            $row['sender_name'],
            date('d-M-Y H:i:s', strtotime($row['time_stamp'])), // Human-readable date
            $row['message_file_name'] ?? 'None',
            $row['message_file_type'] ?? 'N/A'
        ]);
    }

    $stmt->close();
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="chat_export_' . date('Y-m-d_H-i-s') . '.csv"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

fclose($output);
exit();
?>