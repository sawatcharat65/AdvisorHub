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

    // ป้องกันไม่ให้เลือกวันที่สิ้นสุดก่อนวันที่เริ่มต้น
    dateFrom.addEventListener('change', function() {
        if (dateFrom.value) {
            dateTo.min = dateFrom.value; // กำหนด min ของ dateTo
        } else {
            dateTo.min = ''; // รีเซ็ตถ้าไม่มีค่าใน dateFrom
        }
        applyFilters(); // อัปเดตการกรอง
    });

    // ป้องกันไม่ให้เลือกวันที่เริ่มต้นหลังวันที่สิ้นสุด
    dateTo.addEventListener('change', function() {
        if (dateTo.value) {
            dateFrom.max = dateTo.value; // กำหนด max ของ dateFrom
        } else {
            dateFrom.max = ''; // รีเซ็ตถ้าไม่มีค่าใน dateTo
        }
        applyFilters(); // อัปเดตการกรอง
    });

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
            // เปลี่ยนจาก uploaderId เป็น uploaderName
            const uploaderName = uploaderText.split('Uploaded by: ')[1].trim().split('\n')[0].trim();

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

            // เปลี่ยนจาก uploaderId เป็น uploaderName ในการแสดงผลด้วย
            console.log(`File: ${fileName}, Type: ${fileType}, Uploader: ${uploaderName}`);

            // ตรวจสอบว่าตรงกับตัวกรองหรือไม่
            const matchesFileType = selectedFileTypes.length === 0 || selectedFileTypes.includes(fileType);
            // เปลี่ยนจาก uploaderId เป็น uploaderName ในการตรวจสอบกับตัวกรอง
            const matchesUploader = selectedUploaders.length === 0 || selectedUploaders.includes(uploaderName);

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
        dateFrom.max = ''; // รีเซ็ต max
        dateTo.min = ''; // รีเซ็ต min

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