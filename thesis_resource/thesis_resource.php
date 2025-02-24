<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require('../server.php');
include('../components/navbar.php');

// Check session and handle redirects
if (isset($_POST['logout'])) {
    session_destroy();
    header('location: /AdvisorHub/login');
}

if (empty($_SESSION['username'])) {
    header('location: /AdvisorHub/login');
}

if (isset($_POST['profile'])) {
    header('location: /AdvisorHub/profile');
}

// Get thesis details
$thesis_id = isset($_POST['thesis_id']) ? $_POST['thesis_id'] : null;
if (!$thesis_id) {
    header('location: ../thesis_resource_list/thesis_resource_list.php');
    exit;
}

// Fetch thesis data with advisor details
$sql = "SELECT ar.*, 
               a.advisor_first_name, 
               a.advisor_last_name,
               ac.role as advisor_role
        FROM advisor_request ar
        LEFT JOIN advisor a ON ar.advisor_id = a.advisor_id
        LEFT JOIN account ac ON a.advisor_id = ac.account_id
        WHERE ar.advisor_request_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $thesis_id);
$stmt->execute();
$result = $stmt->get_result();
$thesis = $result->fetch_assoc();

if (!$thesis) {
    header('location: ../thesis_resource_list/thesis_resource_list.php');
    exit;
}

// Get student details
$student_ids = json_decode($thesis['student_id'], true);
$students = [];
if (is_array($student_ids)) {
    foreach ($student_ids as $student_id) {
        $student_sql = "SELECT s.*, ac.role as student_role 
                       FROM student s 
                       LEFT JOIN account ac ON s.student_id = ac.account_id 
                       WHERE s.student_id = ?";
        $stmt = $conn->prepare($student_sql);
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $student_result = $stmt->get_result();
        if ($student = $student_result->fetch_assoc()) {
            $students[] = $student;
        }
    }
}

// Check if current user has permission to upload
$current_user_id = $_SESSION['username'];
$current_user_role = $_SESSION['role'];

$can_upload = false;
$is_owner = false;

// Get student ID from name
$student_id_query = "SELECT student_id FROM student WHERE student_first_name = ?";
$stmt = $conn->prepare($student_id_query);
$stmt->bind_param("s", $current_user_id);
$stmt->execute();
$student_result = $stmt->get_result();
$student_data = $student_result->fetch_assoc();
$actual_student_id = $student_data ? $student_data['student_id'] : null;

// Check if user is advisor of this thesis
if ($current_user_role === 'advisor') {
    $advisor_id_query = "SELECT advisor_id FROM advisor WHERE advisor_first_name = ?";
    $stmt = $conn->prepare($advisor_id_query);
    $stmt->bind_param("s", $current_user_id);
    $stmt->execute();
    $advisor_result = $stmt->get_result();
    $advisor_data = $advisor_result->fetch_assoc();
    $actual_advisor_id = $advisor_data ? $advisor_data['advisor_id'] : null;

    if ($actual_advisor_id === $thesis['advisor_id']) {
        $can_upload = true;
        $is_owner = true;
    }
}

// Check if user is one of the students of this thesis
if ($current_user_role === 'student' && $actual_student_id) {
    if (is_array($student_ids)) {
        foreach ($student_ids as $id) {
            if ($id === $actual_student_id) {
                $can_upload = true;
                $is_owner = true;
                break;
            }
        }
    }
}

// Fetch existing files for this thesis
$files_sql = "SELECT tr.*, 
              ac.role,
              CASE 
                WHEN ac.role = 'student' THEN (SELECT student_first_name FROM student WHERE student_id = tr.uploader_id)
                WHEN ac.role = 'advisor' THEN (SELECT advisor_first_name FROM advisor WHERE advisor_id = tr.uploader_id)
                ELSE tr.uploader_id
              END AS uploader_name
              FROM thesis_resource tr
              LEFT JOIN account ac ON tr.uploader_id = ac.account_id
              WHERE tr.advisor_request_id = ?
              ORDER BY tr.time_stamp DESC";
$stmt = $conn->prepare($files_sql);
$stmt->bind_param("i", $thesis_id);
$stmt->execute();
$files_result = $stmt->get_result();
$files = $files_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thesis Resources</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="../Logo.png">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .thesis-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .thesis-card {
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            background-color: white;
            border: none;
        }

        .thesis-title {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 0.75rem;
            font-size: 1.5rem;
        }

        .thesis-subtitle {
            color: #7f8c8d;
            font-weight: 500;
            margin-bottom: 1.5rem;
            font-size: 1.125rem;
        }

        .section-title {
            color: #34495e;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .section-content {
            color: #2c3e50;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .file-item {
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .file-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .upload-card {
            background-color: #f8f9fa;
            border-radius: 12px;
            border: 2px dashed #3498db;
        }

        .upload-btn {
            background-color: #3498db;
            color: white;
            border: none;
            transition: all 0.3s ease;
        }

        .upload-btn:hover {
            background-color: #2980b9;
        }

        .action-btn {
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 6px;
            border: none;
            transition: all 0.3s ease;
            padding: 0;
        }

        .download-btn {
            background-color: #2ecc71;
            color: white;
        }

        .download-btn:hover {
            background-color: #27ae60;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: white;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        .btn-group>.action-btn {
            margin-left: 8px;
        }

        .back-btn {
            background-color: #34495e;
            color: white;
            border: none;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background-color: #2c3e50;
        }

        @media (max-width: 768px) {
            .thesis-title {
                font-size: 1.25rem;
            }

            .thesis-subtitle {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'Teams']); ?>

    <div class="container-fluid thesis-container">
        <!-- Thesis Information -->
        <div class="thesis-card mb-4">
            <div class="card-body p-5">
                <h2 class="thesis-title"><?php echo htmlspecialchars($thesis['thesis_topic_thai']); ?></h2>
                <h4 class="thesis-subtitle"><?php echo htmlspecialchars($thesis['thesis_topic_eng']); ?></h4>

                <div class="row">
                    <div class="col-md-6">
                        <div class="section-title mb-3">Students</div>
                        <?php foreach ($students as $student): ?>
                            <div class="section-content mb-2">
                                <?php echo htmlspecialchars($student['student_id'] . ' ' .
                                    $student['student_first_name'] . ' ' .
                                    $student['student_last_name']); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="col-md-6">
                        <div class="section-title mb-3">Advisor</div>
                        <div class="section-content mb-3">
                            <?php echo htmlspecialchars($thesis['advisor_first_name'] . ' ' . $thesis['advisor_last_name']); ?>
                        </div>
                        <div class="section-title mb-3">Semester</div>
                        <div class="section-content">
                            <?php echo htmlspecialchars($thesis['semester'] . '/' . $thesis['academic_year']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($can_upload): ?>
            <!-- File Upload Area -->
            <div class="card upload-card mb-4">
                <div class="card-body p-5">
                    <h5 class="section-title mb-4">Upload Files</h5>
                    <form id="uploadForm">
                        <div class="mb-3">
                            <input type="file" id="fileInput" class="form-control" multiple>
                            <input type="hidden" id="thesisId" value="<?php echo $thesis_id; ?>">
                        </div>
                        <button type="submit" class="btn upload-btn">
                            <i class="bi bi-upload me-2"></i>Upload
                        </button>
                    </form>
                    <div class="progress mt-3" style="display: none;">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- File List -->
        <div class="thesis-card">
            <div class="card-body p-5">
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="section-title mb-0">Uploaded Files</h5>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse"
                            data-bs-target="#filterCollapse">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                    </div>

                    <div class="collapse mb-3" id="filterCollapse">
                        <div class="card card-body">
                            <div class="row">
                                <!-- File Type Filters -->
                                <div class="col-md-4">
                                    <h6 class="mb-2">File Type</h6>
                                    <div class="d-flex flex-wrap">
                                        <div class="form-check me-3 mb-2">
                                            <input class="form-check-input file-type-filter" type="checkbox" value="pdf"
                                                id="pdfFilter">
                                            <label class="form-check-label" for="pdfFilter">PDF</label>
                                        </div>
                                        <div class="form-check me-3 mb-2">
                                            <input class="form-check-input file-type-filter" type="checkbox" value="doc"
                                                id="docFilter">
                                            <label class="form-check-label" for="docFilter">DOC/DOCX</label>
                                        </div>
                                        <div class="form-check me-3 mb-2">
                                            <input class="form-check-input file-type-filter" type="checkbox" value="ppt"
                                                id="pptFilter">
                                            <label class="form-check-label" for="pptFilter">PPT/PPTX</label>
                                        </div>
                                        <div class="form-check me-3 mb-2">
                                            <input class="form-check-input file-type-filter" type="checkbox" value="xls"
                                                id="xlsFilter">
                                            <label class="form-check-label" for="xlsFilter">XLS/XLSX</label>
                                        </div>
                                        <div class="form-check me-3 mb-2">
                                            <input class="form-check-input file-type-filter" type="checkbox" value="jpg"
                                                id="jpgFilter">
                                            <label class="form-check-label" for="jpgFilter">JPEG/PNG</label>
                                        </div>
                                        <div class="form-check me-3 mb-2">
                                            <input class="form-check-input file-type-filter" type="checkbox" value="zip"
                                                id="zipFilter">
                                            <label class="form-check-label" for="zipFilter">ZIP/RAR</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Uploader Filters -->
                                <div class="col-md-4">
                                    <h6 class="mb-2">Uploader</h6>
                                    <div class="d-flex flex-wrap">
                                        <?php foreach ($students as $student): ?>
                                        <div class="form-check me-3 mb-2">
                                            <input class="form-check-input uploader-filter" type="checkbox" value="<?php echo $student['student_id']; ?>" id="uploader<?php echo $student['student_id']; ?>">
                                            <label class="form-check-label" for="uploader<?php echo $student['student_id']; ?>">
                                                <?php echo $student['student_first_name']; ?>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>

                                        <div class="form-check me-3 mb-2">
                                            <input class="form-check-input uploader-filter" type="checkbox" value="<?php echo $thesis['advisor_id']; ?>" id="uploader<?php echo $thesis['advisor_id']; ?>">
                                            <label class="form-check-label" for="uploader<?php echo $thesis['advisor_id']; ?>">
                                                <?php echo $thesis['advisor_first_name']; ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Date Range Filter -->
                                <div class="col-md-4">
                                    <h6 class="mb-2">Date Range</h6>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text">From</span>
                                        <input type="date" class="form-control" id="dateFrom">
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text">To</span>
                                        <input type="date" class="form-control" id="dateTo">
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-3">
                                <button id="resetFilters" class="btn btn-sm btn-outline-secondary">Reset
                                    Filters</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="filesList">
                    <?php if (empty($files)): ?>
                        <div class="text-center text-muted p-4">
                            <i class="bi bi-file-earmark me-2"></i>
                            No files uploaded yet
                        </div>
                    <?php else: ?>
                        <?php foreach ($files as $file): ?>
                            <div class="file-item p-4 d-flex align-items-center">
                                <i class="bi bi-file-earmark me-4 fs-3"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-bold"><?php echo htmlspecialchars($file['thesis_resource_file_name']); ?>
                                    </div>
                                    <small class="text-muted d-block">
                                        Uploaded by:
                                        <?php echo htmlspecialchars($file['uploader_name'] ?: $file['uploader_id']); ?>
                                    </small>
                                    <small class="text-muted">
                                        Upload time: <?php echo date('M d, Y H:i', strtotime($file['time_stamp'])); ?>
                                    </small>
                                </div>
                                <div class="btn-group">
                                    <form method="POST" action="download.php" style="display: inline;">
                                        <input type="hidden" name="file_id" value="<?php echo $file['thesis_resource_id']; ?>">
                                        <button type="submit" class="action-btn download-btn">
                                            <i class="bi bi-download"></i>
                                        </button>
                                    </form>
                                    <?php if ($is_owner): ?>
                                        <button class="action-btn delete-btn"
                                            onclick="deleteFile(<?php echo $file['thesis_resource_id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- File List -->
        <div class="thesis-card">
            <div class="card-body p-5">
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="section-title mb-0">Uploaded Files From Chat</h5>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse"
                            data-bs-target="#filterCollapse">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                    </div>

                    <div class="collapse mb-3" id="filterCollapse">
                        <div class="card card-body">
                            <div class="row">
                                <!-- File Type Filters -->
                                <div class="col-md-4">
                                    <h6 class="mb-2">File Type</h6>
                                    <div class="d-flex flex-wrap">
                                        <div class="form-check me-3 mb-2">
                                            <input class="form-check-input file-type-filter" type="checkbox" value="pdf"
                                                id="pdfFilter">
                                            <label class="form-check-label" for="pdfFilter">PDF</label>
                                        </div>
                                        <div class="form-check me-3 mb-2">
                                            <input class="form-check-input file-type-filter" type="checkbox" value="doc"
                                                id="docFilter">
                                            <label class="form-check-label" for="docFilter">DOC/DOCX</label>
                                        </div>
                                        <div class="form-check me-3 mb-2">
                                            <input class="form-check-input file-type-filter" type="checkbox" value="ppt"
                                                id="pptFilter">
                                            <label class="form-check-label" for="pptFilter">PPT/PPTX</label>
                                        </div>
                                        <div class="form-check me-3 mb-2">
                                            <input class="form-check-input file-type-filter" type="checkbox" value="xls"
                                                id="xlsFilter">
                                            <label class="form-check-label" for="xlsFilter">XLS/XLSX</label>
                                        </div>
                                        <div class="form-check me-3 mb-2">
                                            <input class="form-check-input file-type-filter" type="checkbox" value="jpg"
                                                id="jpgFilter">
                                            <label class="form-check-label" for="jpgFilter">JPEG/PNG</label>
                                        </div>
                                        <div class="form-check me-3 mb-2">
                                            <input class="form-check-input file-type-filter" type="checkbox" value="zip"
                                                id="zipFilter">
                                            <label class="form-check-label" for="zipFilter">ZIP/RAR</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Uploader Filters -->
                                <div class="col-md-4">
                                    <h6 class="mb-2">Uploader</h6>
                                    <div class="d-flex flex-wrap">
                                        <?php foreach ($students as $student): ?>
                                            <div class="form-check me-3 mb-2">
                                                <input class="form-check-input uploader-filter" type="checkbox"
                                                    value="<?php echo $student['student_id']; ?>"
                                                    id="uploader<?php echo $student['student_id']; ?>">
                                                <label class="form-check-label"
                                                    for="uploader<?php echo $student['student_id']; ?>">
                                                    <?php echo $student['student_first_name']; ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>

                                        <div class="form-check me-3 mb-2">
                                            <input class="form-check-input uploader-filter" type="checkbox"
                                                value="<?php echo $thesis['advisor_id']; ?>"
                                                id="uploader<?php echo $thesis['advisor_id']; ?>">
                                            <label class="form-check-label"
                                                for="uploader<?php echo $thesis['advisor_id']; ?>">
                                                <?php echo $thesis['advisor_first_name']; ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Date Range Filter -->
                                <div class="col-md-4">
                                    <h6 class="mb-2">Date Range</h6>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text">From</span>
                                        <input type="date" class="form-control" id="dateFrom">
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text">To</span>
                                        <input type="date" class="form-control" id="dateTo">
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-3">
                                <button id="resetFilters" class="btn btn-sm btn-outline-secondary">Reset
                                    Filters</button>
                            </div>
                        </div>
                    </div>
                </div>

                <?php

                $message_files_sql = "SELECT messages.*, account.role,
                    CASE 
                        WHEN account.role = 'student' THEN (SELECT student_first_name FROM student WHERE student_id = messages.sender_id)
                        WHEN account.role = 'advisor' THEN (SELECT advisor_first_name FROM advisor WHERE advisor_id = messages.sender_id)
                        ELSE messages.sender_id
                    END AS uploader_name
                    FROM messages
                    LEFT JOIN account ON messages.sender_id = account.account_id
                    WHERE (messages.sender_id = ? OR messages.receiver_id = ?) AND messages.message_file_name IS NOT NULL 
                    ORDER BY messages.time_stamp DESC";
                $stmt = $conn->prepare($message_files_sql);
                $stmt->bind_param("ii", $_SESSION['account_id'], $_SESSION['account_id']);
                $stmt->execute();
                $messages_files_result = $stmt->get_result();
                $messages_files = $messages_files_result->fetch_all(MYSQLI_ASSOC);

                ?>

                <div>
                    <?php if (empty($messages_files)): ?>
                        <div class="text-center text-muted p-4">
                            <i class="bi bi-file-earmark me-2"></i>
                            No files uploaded yet
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages_files as $file): ?>
                            <div class="file-item p-4 d-flex align-items-center">
                                <i class="bi bi-file-earmark me-4 fs-3"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-bold"><?php echo htmlspecialchars($file['message_file_name']); ?>
                                    </div>
                                    <small class="text-muted d-block">
                                        Uploaded by:
                                        <?php echo htmlspecialchars($file['uploader_name'] ?: $file['uploader_id']); ?>
                                    </small>
                                    <small class="text-muted">
                                        Upload time: <?php echo date('M d, Y H:i', strtotime($file['time_stamp'])); ?>
                                    </small>
                                </div>
                                <div class="btn-group">
                                    <form method="POST" action="download.php" style="display: inline;">
                                        <input type="hidden" name="file_id" value="<?php echo $file['message_id']; ?>">
                                        <button type="submit" class="action-btn download-btn">
                                            <i class="bi bi-download"></i>
                                        </button>
                                    </form>
                                    <?php if ($is_owner): ?>
                                        <button class="action-btn delete-btn"
                                            onclick="deleteFile(<?php echo $file['message_id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-4">
            <form method="POST" action="../thesis_resource_list/thesis_resource_list.php">
                <button type="submit" class="btn back-btn">
                    <i class="bi bi-arrow-left me-2"></i>ย้อนกลับ
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Upload functionality
        const uploadForm = document.getElementById('uploadForm');
        const fileInput = document.getElementById('fileInput');
        const thesisId = document.getElementById('thesisId').value;
        const progressBar = document.querySelector('.progress-bar');
        const progress = document.querySelector('.progress');

        uploadForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const files = fileInput.files;
            if (files.length > 0) {
                Array.from(files).forEach(handleFile);
            } else {
                alert('Please select files to upload');
            }
        });

        function handleFile(file) {
            const allowedTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'image/jpeg',
                'image/png',
                'application/zip',
                'application/x-rar-compressed',
                'text/plain'
            ];

            if (allowedTypes.includes(file.type)) {
                uploadFile(file);
            } else {
                alert(`File type not allowed: ${file.name}\nAllowed types: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, PNG, ZIP, RAR, TXT`);
            }
        }

        function uploadFile(file) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('thesis_id', thesisId);

            fetch('upload.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(text => {
                    console.log('Raw response:', text);
                    return JSON.parse(text);
                })
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Upload failed: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Upload failed');
                });
        }

        function deleteFile(fileId) {
            if (confirm('Are you sure you want to delete this file?')) {
                fetch('delete.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'file_id=' + fileId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Delete failed: ' + data.error);
                        }
                    });
            }
        }

        // File filtering functionality
        document.addEventListener('DOMContentLoaded', function() {
            const fileTypeFilters = document.querySelectorAll('.file-type-filter');
            const uploaderFilters = document.querySelectorAll('.uploader-filter');
            const dateFrom = document.getElementById('dateFrom');
            const dateTo = document.getElementById('dateTo');
            const resetFiltersBtn = document.getElementById('resetFilters');
            
            // เก็บ HTML ต้นฉบับไว้
            const filesList = document.getElementById('filesList');
            const originalHTML = filesList.innerHTML;
            
            // Apply filters when any filter changes
            function applyFilters() {
                console.log("Applying filters with class toggling...");
                
                // Get selected filters
                const selectedFileTypes = Array.from(fileTypeFilters)
                    .filter(checkbox => checkbox.checked)
                    .map(checkbox => checkbox.value);
                console.log("Selected file types:", selectedFileTypes);
                
                const selectedUploaders = Array.from(uploaderFilters)
                    .filter(checkbox => checkbox.checked)
                    .map(checkbox => checkbox.value);
                console.log("Selected uploaders:", selectedUploaders);
                
                // Get date range
                const fromDate = dateFrom.value ? new Date(dateFrom.value) : null;
                const toDate = dateTo.value ? new Date(dateTo.value) : null;
                
                // รับรายการไฟล์ล่าสุด (กรณีมีการเปลี่ยนแปลง DOM)
                const fileItems = document.querySelectorAll('.file-item');
                
                // ทำการกรองแต่ละไฟล์
                fileItems.forEach(item => {
                    // ดึงข้อมูลไฟล์
                    const fileName = item.querySelector('.fw-bold').textContent;
                    const uploaderText = item.querySelector('.text-muted').textContent;
                    const uploaderId = uploaderText.split('Uploaded by: ')[1].trim().split('\n')[0].trim();
                    
                    // ดึงวันที่อัปโหลด
                    const uploadTimeText = item.querySelectorAll('.text-muted')[1].textContent;
                    const uploadDateStr = uploadTimeText.replace('Upload time:', '').trim();
                    const uploadDate = new Date(uploadDateStr);
                    
                    // กำหนดประเภทไฟล์จากนามสกุล
                    let fileType = 'other';
                    const lowerFileName = fileName.toLowerCase();
                    if (lowerFileName.endsWith('.pdf')) fileType = 'pdf';
                    else if (lowerFileName.endsWith('.doc') || lowerFileName.endsWith('.docx')) fileType = 'doc';
                    else if (lowerFileName.endsWith('.ppt') || lowerFileName.endsWith('.pptx')) fileType = 'ppt';
                    else if (lowerFileName.endsWith('.xls') || lowerFileName.endsWith('.xlsx')) fileType = 'xls';
                    else if (lowerFileName.endsWith('.jpg') || lowerFileName.endsWith('.jpeg') || lowerFileName.endsWith('.png')) fileType = 'jpg';
                    else if (lowerFileName.endsWith('.zip') || lowerFileName.endsWith('.rar')) fileType = 'zip';
                    
                    console.log(`File: ${fileName}, Type: ${fileType}, Uploader: ${uploaderId}`);
                    
                    // ตรวจสอบว่าตรงกับตัวกรองหรือไม่
                    const matchesFileType = selectedFileTypes.length === 0 || selectedFileTypes.includes(fileType);
                    const matchesUploader = selectedUploaders.length === 0 || selectedUploaders.includes(uploaderId);
                    
                    // ตรวจสอบช่วงวันที่
                    let matchesDateRange = true;
                    if (fromDate) {
                        matchesDateRange = matchesDateRange && uploadDate >= fromDate;
                    }
                    if (toDate) {
                        const adjustedToDate = new Date(toDate);
                        adjustedToDate.setDate(adjustedToDate.getDate() + 1);
                        matchesDateRange = matchesDateRange && uploadDate < adjustedToDate;
                    }
                    
                    // ซ่อน/แสดงไฟล์ด้วยคลาส
                    if (matchesFileType && matchesUploader && matchesDateRange) {
                        item.classList.remove('d-none');
                    } else {
                        item.classList.add('d-none');
                        console.log(`Hiding file: ${fileName}`);
                    }
                });
            }
            
            // Reset all filters
            resetFiltersBtn.addEventListener('click', function() {
                console.log("Resetting filters");
                fileTypeFilters.forEach(checkbox => checkbox.checked = false);
                uploaderFilters.forEach(checkbox => checkbox.checked = false);
                dateFrom.value = '';
                dateTo.value = '';
                
                // แสดงไฟล์ทั้งหมด
                document.querySelectorAll('.file-item').forEach(item => {
                    item.classList.remove('d-none');
                });
            });
            
            // เพิ่ม event listeners สำหรับตัวกรองทั้งหมด
            fileTypeFilters.forEach(checkbox => {
                checkbox.addEventListener('change', applyFilters);
            });
            
            uploaderFilters.forEach(checkbox => {
                checkbox.addEventListener('change', applyFilters);
            });
            
            dateFrom.addEventListener('change', applyFilters);
            dateTo.addEventListener('change', applyFilters);
        });
    </script>
</body>
</html>