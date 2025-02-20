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
$files_sql = "SELECT tr.*, ac.role
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
    <style>
        /* คงไว้เหมือนเดิม */
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
                <h5 class="section-title mb-4">Uploaded Files</h5>
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
                                    <div class="fw-bold"><?php echo htmlspecialchars($file['thesis_resource_file_name']); ?></div>
                                    <small class="text-muted d-block">
                                        Uploaded by: <?php echo htmlspecialchars($file['uploader_id']); ?>
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
                                        <button class="action-btn delete-btn" onclick="deleteFile(<?php echo $file['thesis_resource_id']; ?>)">
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
    </script>
</body>
</html>