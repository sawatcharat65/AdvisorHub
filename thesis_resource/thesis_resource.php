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
               a.first_name as advisor_fname, 
               a.last_name as advisor_lname,
               ac.role as advisor_role
        FROM advisor_request ar
        LEFT JOIN Advisor a ON ar.advisor_id = a.id
        LEFT JOIN account ac ON a.id = ac.id
        WHERE ar.id = ?";

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
                       FROM Student s 
                       LEFT JOIN account ac ON s.id = ac.id 
                       WHERE s.id = ?";
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
$current_user_id = $_SESSION['username']; // ตอนนี้ได้ค่า "John"
$current_user_role = $_SESSION['role'];

$can_upload = false;
$is_owner = false;

// Debug information
echo "<div style='margin: 20px; padding: 20px; background: #f0f0f0;'>";
echo "<h4>Debug Information:</h4>";
echo "<pre>";
echo "Current User ID: " . $current_user_id . "\n";
echo "Current User Role: " . $current_user_role . "\n";
echo "Thesis Advisor ID: " . $thesis['advisor_id'] . "\n";
echo "Student IDs: " . print_r($student_ids, true) . "\n";

// ดึงรหัสนักศึกษาจากชื่อ
$student_id_query = "SELECT id FROM student WHERE first_name = ?";
$stmt = $conn->prepare($student_id_query);
$stmt->bind_param("s", $current_user_id);
$stmt->execute();
$student_result = $stmt->get_result();
$student_data = $student_result->fetch_assoc();
$actual_student_id = $student_data ? $student_data['id'] : null;

echo "Found Student ID from name: " . ($actual_student_id ?? 'Not found') . "\n";

// Check if user is advisor of this thesis
if ($current_user_role === 'advisor' && $thesis['advisor_id'] === $current_user_id) {
    echo "Checking advisor permission: YES\n";
    $can_upload = true;
    $is_owner = true;
} else {
    echo "Checking advisor permission: NO\n";
}

// Check if user is one of the students of this thesis
if ($current_user_role === 'student' && $actual_student_id) {
    echo "Checking student permission:\n";
    echo "Looking for ID: " . $actual_student_id . " in student list\n";
    
    if (is_array($student_ids)) {
        foreach ($student_ids as $id) {
            echo "Comparing with: " . $id . "\n";
            if ($id === $actual_student_id) {
                echo "MATCH FOUND!\n";
                $can_upload = true;
                $is_owner = true;
                break;
            }
        }
    }
}
// Check if user is advisor of this thesis
if ($current_user_role === 'advisor') {
    $advisor_id_query = "SELECT id FROM advisor WHERE first_name = ?";
    $stmt = $conn->prepare($advisor_id_query);
    $stmt->bind_param("s", $current_user_id);
    $stmt->execute();
    $advisor_result = $stmt->get_result();
    $advisor_data = $advisor_result->fetch_assoc();
    $actual_advisor_id = $advisor_data ? $advisor_data['id'] : null;

    if ($actual_advisor_id === $thesis['advisor_id']) {
        $can_upload = true;
        $is_owner = true;
    }
}

echo "Final Results:\n";
echo "Can Upload: " . ($can_upload ? 'true' : 'false') . "\n";
echo "Is Owner: " . ($is_owner ? 'true' : 'false') . "\n";
echo "</pre>";
echo "</div>";

// Fetch existing files for this thesis
$files_sql = "SELECT tr.*, ac.role
              FROM thesis_resource tr
              LEFT JOIN account ac ON tr.uploader_id = ac.id
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
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../thesis_resource_list/style.css">
</head>
<body class="bg-light">
    <?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'Teams']); ?>

    <div class="container my-4">
        <!-- Thesis Information -->
        <div class="card mb-4">
            
            <div class="card-body">
                <h2 class="card-title mb-4"><?php echo htmlspecialchars($thesis['thesis_topic_thai']); ?></h2>
                <h4 class="text-muted mb-4"><?php echo htmlspecialchars($thesis['thesis_topic_eng']); ?></h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3">Students:</h5>
                        <?php foreach ($students as $student): ?>
                            <p>
                                <?php echo htmlspecialchars($student['id'] . ' ' . 
                                                          $student['first_name'] . ' ' . 
                                                          $student['last_name']); ?>
                            </p>
                        <?php endforeach; ?>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3">Advisor:</h5>
                        <p><?php echo htmlspecialchars($thesis['advisor_fname'] . ' ' . $thesis['advisor_lname']); ?></p>
                        <h5 class="mb-3">Semester:</h5>
                        <p><?php echo htmlspecialchars($thesis['semester'] . '/' . $thesis['academic_year']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($can_upload): ?>
        <!-- File Upload Area -->
        <!-- File Upload Area -->
        <div class="card mb-4" style="display: <?php echo $can_upload ? 'block' : 'none'; ?>">
            <div class="card-body">
                <h5 class="card-title">Upload Files</h5>
                <form id="uploadForm" class="mt-3">
                    <div class="mb-3">
                        <input type="file" id="fileInput" class="form-control" multiple>
                        <input type="hidden" id="thesisId" value="<?php echo $thesis_id; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">
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
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Uploaded Files</h5>
                <div id="filesList">
                    <?php if (empty($files)): ?>
                        <p class="text-muted text-center">No files uploaded yet</p>
                    <?php else: ?>
                        <?php foreach ($files as $file): ?>
                            <div class="file-item p-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark me-3 fs-4"></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold"><?php echo htmlspecialchars($file['file_name']); ?></div>
                                        <small class="text-muted">Uploaded by: <?php echo htmlspecialchars($file['uploader_id']); ?></small><br>
                                        <small class="text-muted">Upload time: <?php echo date('M d, Y H:i', strtotime($file['time_stamp'])); ?></small>
                                    </div>
                                    <div class="btn-group">
                                        <form method="POST" action="download.php" style="display: inline;">
                                            <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                            <input type="hidden" name="thesis_id" value="<?php echo $thesis_id; ?>">
                                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-download me-1"></i>Download
                                            </button>
                                        </form>
                                        <?php if ($is_owner && $file['uploader_id'] === $current_user_id): ?>
                                            <form method="POST" action="delete.php" style="display: inline;">
                                                <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                                <input type="hidden" name="thesis_id" value="<?php echo $thesis_id; ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm ms-2" 
                                                        onclick="return confirm('Are you sure you want to delete this file?')">
                                                    <i class="bi bi-trash me-1"></i>Delete
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
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
                <button type="submit" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to List
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
        formData.append('thesis_id', <?php echo $thesis_id; ?>);

        console.log('Uploading file:', file.name);
        console.log('Thesis ID:', <?php echo $thesis_id; ?>);

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
    </script>
</body>
</html>