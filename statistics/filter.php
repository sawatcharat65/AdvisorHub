<?php
require('../server.php');

$selectedKeywords = isset($_POST['keywords']) ? json_decode($_POST['keywords'], true) : [];
$keyword_counts = [];

if (!empty($selectedKeywords)) {
    // กรณีมีคีย์เวิร์ดที่เลือก: ค้นหาเฉพาะ Thesis ที่มี keywords ตรงกับที่เลือก
    $conditions = [];
    foreach ($selectedKeywords as $keyword) {
        $conditions[] = "JSON_CONTAINS(keywords, '\"$keyword\"')";
    }
    $sql = "SELECT keywords FROM thesis WHERE " . implode(" OR ", $conditions);
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $keywords = json_decode($row['keywords'], true);
        foreach ($keywords as $keyword) {
            // เช็คว่า keyword นี้อยู่ใน list ที่เลือกไว้หรือไม่
            if (in_array($keyword, $selectedKeywords)) {
                if (!isset($keyword_counts[$keyword])) {
                    $keyword_counts[$keyword] = 0;
                }
                $keyword_counts[$keyword]++;
            }
        }
    }
} else {
    // กรณีไม่มีคีย์เวิร์ดที่เลือก: ดึงข้อมูลทั้งหมด
    $sql = "SELECT keywords FROM thesis";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $keywords = json_decode($row['keywords'], true);
        foreach ($keywords as $keyword) {
            if (!isset($keyword_counts[$keyword])) {
                $keyword_counts[$keyword] = 0;
            }
            $keyword_counts[$keyword]++;
        }
    }
}

arsort($keyword_counts); // เรียงจากมากไปน้อย

foreach ($keyword_counts as $topic => $count) {
    echo "<tr><td>$topic</td><td>$count</td></tr>";
}
