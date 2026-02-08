<?php
session_start();

// Debug: แสดง session ใน console
error_log("upload_files.php - Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));
error_log("upload_files.php - Session role: " . ($_SESSION['role'] ?? 'NOT SET'));

// ตรวจสอบ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Users CSV | TaskBoard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>

    <style>
        .page-header {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: none;
        }

        .drop-zone {
            border: 3px dashed #6c63ff;
            border-radius: 15px;
            padding: 3rem;
            text-align: center;
            background-color: #f8f9ff;
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .drop-zone:hover,
        .drop-zone.dragover {
            background-color: #e8e9ff;
            border-color: #5a52d3;
            transform: scale(1.02);
        }

        .drop-zone i {
            font-size: 3rem;
            color: #6c63ff;
            margin-bottom: 1rem;
        }

        .file-input {
            display: none;
        }

        .btn-primary {
            background-color: #6c63ff;
            border-color: #6c63ff;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #5a52d3;
            border-color: #5a52d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-secondary {
            background-color: #e0e0e0;
            border-color: #e0e0e0;
            border-radius: 10px;
            color: #333;
        }

        .btn-secondary:hover {
            background-color: #d0d0d0;
            border-color: #d0d0d0;
        }

        .file-info {
            background-color: #f0f4ff;
            border-left: 4px solid #6c63ff;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            display: none;
        }

        .file-info.show {
            display: block;
        }

        .preview-table {
            font-size: 0.85rem;
            margin-top: 2rem;
            display: none;
        }

        .preview-table.show {
            display: table;
        }

        .preview-table th {
            background-color: #6c63ff;
            color: white;
            font-weight: 600;
            padding: 0.75rem;
        }

        .preview-table td {
            padding: 0.5rem;
            border-color: #e0e0e0;
        }

        .preview-table tbody tr:hover {
            background-color: #f5f5f5;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 0.5rem;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .sample-template {
            background-color: #fef5e7;
            border-left: 4px solid #f39c12;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .success-message {
            color: #28a745;
            display: none;
        }

        .success-message.show {
            display: block;
        }

        .loading-spinner {
            display: none;
        }

        .loading-spinner.show {
            display: inline-block;
        }
    </style>
</head>

<body>
    <?php include('../../include/NavbarAdmin.php'); ?>

    <div class="container mt-4 mb-5 w-75">

        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1 fw-bold text-dark">
                        <i class='bx bx-upload me-2'></i>อัพโหลดรายชื่อผู้ใช้
                    </h2>
                    <p class="text-muted mb-0">อัพโหลดไฟล์ CSV เพื่อเพิ่มผู้ใช้จำนวนมากในครั้งเดียว</p>
                </div>

                <!-- ปุ่มกลับไป -->
                <div>
                    <a href="CreateUser.php" class="btn btn-secondary">
                        <i class='bx bx-arrow-back me-1'></i>กลับไป
                    </a>
                </div>
            </div>
        </div>

        <!-- ตัวอย่างเทมเพลต -->
        <div class="sample-template">
            <h5 class="mb-2"><i class='bx bxs-info-circle me-2'></i>รูปแบบไฟล์ CSV</h5>
            <p class="mb-2">ไฟล์ CSV ต้องมีหัวแถวดังนี้ (ตามลำดับ):</p>
            <code>role,email,password,phone,first_name,last_name,gender,student_code,major,year,class_id,department</code>
            <p class="mt-2 mb-0 text-sm"><strong>หมายเหตุ:</strong> เฉพาะเอาเฉพาะฟิลด์ที่ต้องการ เช่น นักศึกษาใช้ student_code, major เป็นต้น</p>
            <a href="javascript:void(0)" onclick="downloadTemplate()" class="btn btn-sm btn-outline-primary mt-2">
                <i class='bx bx-download me-1'></i>ดาวน์โหลดเทมเพลต
            </a>
        </div>

        <div class="card p-5">
            <form id="uploadForm" enctype="multipart/form-data" action="../../controllers/UploadUsersController.php" method="POST">

                <!-- Drop Zone -->
                <div class="drop-zone" id="dropZone">
                    <i class='bx bx-cloud-upload'></i>
                    <h5>ลากไฟล์มาวางตรงนี้หรือคลิก</h5>
                    <p class="text-muted">สนับสนุนไฟล์ CSV เท่านั้น</p>
                    <input type="file" id="csvFile" class="file-input" accept=".csv" required>
                </div>

                <!-- ข้อมูลไฟล์ -->
                <div class="file-info" id="fileInfo">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1"><strong>ไฟล์ที่เลือก:</strong> <span id="fileName"></span></p>
                            <p class="mb-0"><strong>จำนวนแถว:</strong> <span id="rowCount"></span> แถว</p>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetForm()">
                                <i class='bx bx-x me-1'></i>เลือกไฟล์ใหม่
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ข้อมูลการตรวจสอบ -->
                <div id="errorMessage" class="error-message"></div>
                <div id="successMessage" class="success-message alert alert-success"></div>

                <!-- ตัวอย่างข้อมูล -->
                <div class="table-responsive">
                    <table class="table table-bordered preview-table" id="previewTable">
                        <thead>
                            <tr id="headerRow"></tr>
                        </thead>
                        <tbody id="previewBody"></tbody>
                    </table>
                </div>

                <!-- ปุ่มส่ง -->
                <div class="text-end mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-secondary me-2" onclick="resetForm()">
                        <i class='bx bx-x me-1'></i>ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        <span class="loading-spinner" id="spinner">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        </span>
                        <i class='bx bx-upload me-1'></i>อัพโหลดและสร้างบัญชี
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const dropZone = document.getElementById('dropZone');
        const csvFile = document.getElementById('csvFile');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const rowCount = document.getElementById('rowCount');
        const previewTable = document.getElementById('previewTable');
        const headerRow = document.getElementById('headerRow');
        const previewBody = document.getElementById('previewBody');
        const errorMessage = document.getElementById('errorMessage');
        const successMessage = document.getElementById('successMessage');
        const submitBtn = document.getElementById('submitBtn');
        const uploadForm = document.getElementById('uploadForm');

        // Drag and drop events
        dropZone.addEventListener('click', () => csvFile.click());

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                csvFile.files = files;
                handleFileSelect();
            }
        });

        csvFile.addEventListener('change', handleFileSelect);

        function handleFileSelect() {
            const file = csvFile.files[0];
            if (!file) return;

            // ตรวจสอบ extension
            if (!file.name.endsWith('.csv')) {
                showError('⚠️ กรุณาเลือกไฟล์ CSV เท่านั้น');
                resetForm();
                return;
            }

            // อ่านไฟล์
            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    const csv = e.target.result;
                    const lines = csv.split('\n').map(line => line.trim()).filter(line => line);

                    if (lines.length < 2) {
                        showError('⚠️ ไฟล์ต้องมีอย่างน้อย 2 แถว (หัวแถว + ข้อมูล)');
                        resetForm();
                        return;
                    }

                    // แสดงข้อมูล
                    fileName.textContent = file.name;
                    rowCount.textContent = lines.length - 1;
                    fileInfo.classList.add('show');
                    errorMessage.classList.remove('show');

                    // แสดง preview
                    displayPreview(lines);
                    submitBtn.disabled = false;

                } catch (error) {
                    showError('❌ เกิดข้อผิดพลาดในการอ่านไฟล์: ' + error.message);
                    resetForm();
                }
            };

            reader.readAsText(file);
        }

        function displayPreview(lines) {
            const headers = parseCSVLine(lines[0]);
            
            // สร้างหัวแถว
            headerRow.innerHTML = '';
            headers.forEach(header => {
                const th = document.createElement('th');
                th.textContent = header;
                headerRow.appendChild(th);
            });

            // แสดงเพียง 5 แถวแรก
            previewBody.innerHTML = '';
            const maxRows = Math.min(5, lines.length - 1);
            for (let i = 1; i <= maxRows; i++) {
                const values = parseCSVLine(lines[i]);
                const tr = document.createElement('tr');
                values.forEach(value => {
                    const td = document.createElement('td');
                    td.textContent = value || '-';
                    tr.appendChild(td);
                });
                previewBody.appendChild(tr);
            }

            if (lines.length > 6) {
                const tr = document.createElement('tr');
                const td = document.createElement('td');
                td.colSpan = headers.length;
                td.className = 'text-center text-muted';
                td.textContent = `... และอีก ${lines.length - 6} แถว`;
                tr.appendChild(td);
                previewBody.appendChild(tr);
            }

            previewTable.classList.add('show');
        }

        function parseCSVLine(line) {
            const result = [];
            let current = '';
            let insideQuotes = false;

            for (let i = 0; i < line.length; i++) {
                const char = line[i];

                if (char === '"') {
                    insideQuotes = !insideQuotes;
                } else if (char === ',' && !insideQuotes) {
                    result.push(current.trim());
                    current = '';
                } else {
                    current += char;
                }
            }

            result.push(current.trim());
            return result;
        }

        function showError(message) {
            errorMessage.textContent = message;
            errorMessage.classList.add('show');
            successMessage.classList.remove('show');
        }

        function resetForm() {
            csvFile.value = '';
            fileInfo.classList.remove('show');
            previewTable.classList.remove('show');
            errorMessage.classList.remove('show');
            successMessage.classList.remove('show');
            submitBtn.disabled = true;
        }

        // Submit form
        uploadForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const file = csvFile.files[0];
            if (!file) {
                showError('⚠️ กรุณาเลือกไฟล์');
                return;
            }

            console.log('File selected:', file.name, file.size, file.type);  // ← Debug

            const formData = new FormData();
            formData.append('csv_file', file);

            console.log('FormData keys:', Array.from(formData.keys()));  // ← Debug
            console.log('Fetching to: ../../controllers/UploadUsersController.php');  // ← Debug

            submitBtn.disabled = true;
            document.getElementById('spinner').classList.add('show');

            try {
                // ลองหลายแบบ - path ไปยัง controllers
                const paths = [
                    '../../controllers/UploadUsersController.php',
                    '../../../controllers/UploadUsersController.php',
                ];
                
                let response = null;
                let lastError = null;
                
                for (let path of paths) {
                    try {
                        console.log('Trying path:', path);
                        response = await fetch(path, {
                            method: 'POST',
                            body: formData
                        });
                        
                        if (response.status !== 404) {
                            console.log('Path worked:', path);
                            break;
                        }
                    } catch (e) {
                        lastError = e;
                    }
                }
                
                if (!response || response.status === 404) {
                    showError('❌ ไม่สามารถหา Controller - ตรวจสอบ path');
                    return;
                }

                const result = await response.json();

                console.log('Response Result:', result);  // ← เพิ่มบรรทัดนี้

                if (result.success) {
                    successMessage.innerHTML = `
                        <strong><i class='bx bx-check-circle me-2'></i>สำเร็จ!</strong>
                        <br>สร้างบัญชีผู้ใช้ ${result.created_count} รายแล้ว
                        ${result.skipped_count > 0 ? `<br><small>ข้ามไป ${result.skipped_count} รายเนื่องจากมีข้อผิดพลาด</small>` : ''}
                    `;
                    successMessage.classList.add('show');
                    resetForm();
                    
                    // เปลี่ยนหน้าหลังจากเสร็จ
                    setTimeout(() => {
                        window.location.href = 'CreateUser.php';
                    }, 2000);
                } else {
                    showError(`❌ ${result.message || 'เกิดข้อผิดพลาด'}`);
                }
            } catch (error) {
                showError('❌ เกิดข้อผิดพลาดในการส่งข้อมูล: ' + error.message);
            } finally {
                document.getElementById('spinner').classList.remove('show');
                submitBtn.disabled = false;
            }
        });

        // ดาวน์โหลดเทมเพลต
        function downloadTemplate() {
            const headers = ['role', 'email', 'password', 'phone', 'first_name', 'last_name', 'gender', 'student_code', 'major', 'year', 'class_id', 'department'];
            const sampleData = [
                ['student', 'student1@example.com', 'pass123', '0812345678', 'สมชาย', 'ใจดี', 'Male', 'ST001', 'เทคโนโลยีสารสนเทศ', 'ปวช. 1', 'สทส.67.1', ''],
                ['student', 'student2@example.com', 'pass123', '0812345679', 'สมหญิง', 'สวยงาม', 'Female', 'ST002', 'เทคโนโลยีสารสนเทศ', 'ปวช. 1', 'สทส.67.1', ''],
                ['teacher', 'teacher1@example.com', 'pass123', '0812345680', 'นายอาจารย์', 'สอนดี', 'Male', '', '', '', '', 'เทคโนโลยีสารสนเทศ']
            ];

            let csv = headers.join(',') + '\n';
            sampleData.forEach(row => {
                csv += row.map(cell => cell.includes(',') ? `"${cell}"` : cell).join(',') + '\n';
            });

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);

            link.setAttribute('href', url);
            link.setAttribute('download', 'template_users.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>

    <?php include('../../include/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>