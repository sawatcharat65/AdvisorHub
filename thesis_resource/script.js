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

    // ป้องกันไม่ให้เลือกวันที่สิ้นสุดก่อนวันที่เริ่มต้น
    if (dateFrom && dateTo) {
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
    }

    // Apply filters when any filter changes
    function applyFilters() {
        console.log("Applying filters...");

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
        const fromDate = dateFrom && dateFrom.value ? new Date(dateFrom.value) : null;
        const toDate = dateTo && dateTo.value ? new Date(dateTo.value) : null;

        // รับรายการไฟล์ล่าสุด
        const fileItems = document.querySelectorAll('.file-item');
        
        // ทำการกรองแต่ละไฟล์
        fileItems.forEach(item => {
            try {
                // ดึงข้อมูลไฟล์
                const fileName = item.querySelector('.fw-bold').textContent.trim();
                
                // ดึงข้อมูลผู้อัปโหลด
                let uploaderName = "";
                
                // ค้นหาข้อความ "Uploaded by:" ในทุกๆ small elements
                const smallElements = item.querySelectorAll('small');
                for (const el of smallElements) {
                    if (el.textContent.includes('Uploaded by:')) {
                        uploaderName = el.textContent.split('Uploaded by:')[1].trim();
                        break;
                    }
                }
                
                // ดึงวันที่อัปโหลด
                let uploadDate = new Date();
                for (const el of smallElements) {
                    if (el.textContent.includes('Upload time:')) {
                        const dateStr = el.textContent.split('Upload time:')[1].trim();
                        uploadDate = new Date(dateStr);
                        break;
                    }
                }
                
                // ตรวจสอบประเภทไฟล์
                let fileType = 'other';
                const lowerFileName = fileName.toLowerCase();
                
                if (lowerFileName.includes('.pdf')) fileType = 'pdf';
                else if (lowerFileName.includes('.doc') || lowerFileName.includes('.docx')) fileType = 'doc';
                else if (lowerFileName.includes('.ppt') || lowerFileName.includes('.pptx')) fileType = 'ppt';
                else if (lowerFileName.includes('.xls') || lowerFileName.includes('.xlsx')) fileType = 'xls';
                else if (lowerFileName.includes('.jpg') || lowerFileName.includes('.jpeg') || lowerFileName.includes('.png')) fileType = 'jpg';
                else if (lowerFileName.includes('.zip') || lowerFileName.includes('.rar')) fileType = 'zip';
                
                console.log(`File: ${fileName}, Type: ${fileType}, Uploader: ${uploaderName}`);
                
                // ตรวจสอบการตรงกับตัวกรอง
                let matchesFileType = true;
                let matchesUploader = true;
                let matchesDateRange = true;
                
                // ตรวจสอบประเภทไฟล์ - ถ้ามีการเลือกประเภทไฟล์
                if (selectedFileTypes.length > 0) {
                    matchesFileType = selectedFileTypes.includes(fileType);
                    console.log(`File type match: ${matchesFileType} (${fileType} in [${selectedFileTypes}])`);
                }
                
                // ตรวจสอบผู้อัปโหลด - ถ้ามีการเลือกผู้อัปโหลด
                if (selectedUploaders.length > 0) {
                    matchesUploader = selectedUploaders.includes(uploaderName);
                    console.log(`Uploader match: ${matchesUploader} (${uploaderName} in [${selectedUploaders}])`);
                }
                
                // ตรวจสอบวันที่
                if (fromDate) {
                    const isAfterFromDate = uploadDate >= fromDate;
                    matchesDateRange = matchesDateRange && isAfterFromDate;
                    console.log(`Date after ${fromDate}: ${isAfterFromDate}`);
                }
                
                if (toDate) {
                    const adjustedToDate = new Date(toDate);
                    adjustedToDate.setDate(adjustedToDate.getDate() + 1);
                    const isBeforeToDate = uploadDate < adjustedToDate;
                    matchesDateRange = matchesDateRange && isBeforeToDate;
                    console.log(`Date before ${adjustedToDate}: ${isBeforeToDate}`);
                }
                
                // ตัดสินใจว่าจะแสดงไฟล์หรือไม่
                const showFile = matchesFileType && matchesUploader && matchesDateRange;
                console.log(`Show file ${fileName}: ${showFile}`);
                
                // แสดงหรือซ่อนไฟล์
                if (showFile) {
                    item.classList.remove('d-none');
                    item.style.display = '';
                } else {
                    item.classList.add('d-none');
                    item.style.display = 'none';
                }
                
            } catch (error) {
                console.error("Error processing file:", error);
                // ถ้าเกิดข้อผิดพลาดให้แสดงไฟล์ไว้
                item.style.display = '';
            }
        });
    }

    // Reset all filters
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function() {
            console.log("Resetting filters");
            fileTypeFilters.forEach(checkbox => checkbox.checked = false);
            uploaderFilters.forEach(checkbox => checkbox.checked = false);
            
            if (dateFrom) dateFrom.value = '';
            if (dateTo) dateTo.value = '';
            if (dateFrom) dateFrom.max = '';
            if (dateTo) dateTo.min = '';

            // แสดงไฟล์ทั้งหมด
            document.querySelectorAll('.file-item').forEach(item => {
                item.classList.remove('d-none');
                item.style.display = '';
            });
        });
    }

    // เพิ่ม event listeners
    fileTypeFilters.forEach(checkbox => {
        checkbox.addEventListener('change', applyFilters);
    });

    uploaderFilters.forEach(checkbox => {
        checkbox.addEventListener('change', applyFilters);
    });
});