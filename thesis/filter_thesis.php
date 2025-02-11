<?php
session_start();
require('../server.php');

if (!isset($_SESSION['advisor_id'])) {
    die("Error: Advisor ID not found.");
}

$advisor_id = $_SESSION['advisor_id'];

if (isset($_POST['start_date'], $_POST['end_date'], $_POST['search_query'], $_POST['view_mode'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $search_query = '%' . strtolower($_POST['search_query']) . '%'; // แปลง search_query เป็นตัวพิมพ์เล็ก
    $view_mode = $_POST['view_mode'];

    // แปลง title และ keywords เป็นตัวพิมพ์เล็กก่อนทำการค้นหา
    $stmt = $conn->prepare("SELECT * FROM thesis 
                            WHERE advisor_id = ? 
                            AND (LOWER(title) LIKE ? OR LOWER(keywords) LIKE ?) 
                            AND issue_date BETWEEN ? AND ? 
                            ORDER BY issue_date DESC");
    $stmt->bind_param("sssss", $advisor_id, $search_query, $search_query, $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM thesis 
                            WHERE advisor_id = ? 
                            AND (LOWER(title) LIKE ? OR LOWER(keywords) LIKE ?) 
                            AND issue_date BETWEEN ? AND ?");
    $stmt->bind_param("sssss", $advisor_id, $search_query, $search_query, $start_date, $end_date);
    $stmt->execute();
    $count_result = $stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $thesis_count = $count_row['total'];

    $html = '';
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $thesis_link = "thesis_info.php?id=" . $row['id'];
            $issue_date = date('d M, Y', strtotime($row['issue_date']));
            $title = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
            $keywords = isset($row['keywords']) ? $row['keywords'] : '';
            if (!empty($keywords) && json_decode($keywords) !== null) {
                $decoded_keywords = json_decode($keywords, true);
                if (is_array($decoded_keywords)) {
                    $keywords = implode(', ', $decoded_keywords);
                }
            }
            $keywords = htmlspecialchars($keywords, ENT_QUOTES, 'UTF-8');

            if ($view_mode === 'grid') {
                $html .= "<div class='col-md-4 thesis-item'>";
                $html .= "<div class='card position-relative'>";
                $html .= "<a href='$thesis_link' class='stretched-link text-decoration-none'></a>";
                $html .= "<div class='card-body'>";
                $html .= "<h6 class='text-muted'><i class='bi bi-calendar-event'></i> $issue_date</h6>";
                $html .= "<h5 class='mb-1'>$title</h5>";
                $html .= "<p class='text-muted mb-1'>$keywords</p>";
                $html .= "</div></div></div>";
            } else {
                $html .= "<div class='col-12 thesis-item'>";
                $html .= "<div class='card position-relative'>";
                $html .= "<a href='$thesis_link' class='stretched-link text-decoration-none'></a>";
                $html .= "<div class='card-body'>";
                $html .= "<h6 class='text-muted'><i class='bi bi-calendar-event'></i> $issue_date</h6>";
                $html .= "<h5 class='mb-1'>$title</h5>";
                $html .= "<p class='text-muted mb-1'>$keywords</p>";
                $html .= "</div></div></div>";
            }
        }
    } else {
        $html = "<p class='text-center'>ไม่พบข้อมูลวิทยานิพนธ์</p>";
    }

    $response = [
        'thesis_count' => $thesis_count,
        'html' => $html
    ];

    echo json_encode($response);
    exit;
}
