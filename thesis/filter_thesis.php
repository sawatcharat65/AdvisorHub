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
    $search_query = '%' . $_POST['search_query'] . '%';
    $view_mode = $_POST['view_mode'];

    $stmt = $conn->prepare("SELECT * FROM thesis 
                            WHERE advisor_id = ? 
                            AND (title LIKE ? OR keywords LIKE ?) 
                            AND issue_date BETWEEN ? AND ? 
                            ORDER BY issue_date DESC");
    $stmt->bind_param("sssss", $advisor_id, $search_query, $search_query, $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $thesis_link = "thesis_info.php?id=" . $row['id'];
            $issue_date = date('d M, Y', strtotime($row['issue_date']));
            $title = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
            $keywords = isset($row['keywords']) ? htmlspecialchars($row['keywords'], ENT_QUOTES, 'UTF-8') : '';

            if ($view_mode === 'grid') {
                echo "<div class='col-md-4 thesis-item'>";
                echo "<div class='card position-relative'>";
                echo "<a href='$thesis_link' class='stretched-link text-decoration-none'></a>";
                echo "<div class='card-body'>";
                echo "<h6 class='text-muted'><i class='bi bi-calendar-event'></i> $issue_date</h6>";
                echo "<h5 class='mb-1'>$title</h5>";
                echo "<p class='text-muted mb-1'>$keywords</p>";
                echo "</div></div></div>";
            } else {
                echo "<div class='col-12 thesis-item'>";
                echo "<div class='card position-relative'>";
                echo "<a href='$thesis_link' class='stretched-link text-decoration-none'></a>";
                echo "<div class='card-body'>";
                echo "<h6 class='text-muted'><i class='bi bi-calendar-event'></i> $issue_date</h6>";
                echo "<h5 class='mb-1'>$title</h5>";
                echo "<p class='text-muted mb-1'>$keywords</p>";
                echo "</div></div></div>";
            }
        }
    } else {
        echo "<p class='text-center'>ไม่พบข้อมูลวิทยานิพนธ์</p>";
    }

    $stmt->close();
}
