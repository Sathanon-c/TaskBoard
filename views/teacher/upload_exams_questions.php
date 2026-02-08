<?php
session_start();

// ตรวจสอบว่าเป็น Teacher เท่านั้น
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit;
}

$exam_id = $_GET['exam_id'] ?? null;
$course_id = $_GET['course_id'] ?? null;

if (!$exam_id || !$course_id) {
    header("Location: javascript:history.back()");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Questions CSV | TaskBoard</title>
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
            font-size: 0.75rem;
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
            padding: 0.5rem;
        }

        .error-message {
            color: #dc3545;
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
    <?php include('../../include/NavbarTeacher.php'); ?>

    <div class="container mt-4 mb-5 w-75">

        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1 fw-bold text-dark">
                        <i class='bx bx-upload me-2'></i>อัพโหลดโจทย์
                    </h2>
                    <p class="text-muted mb-0">อัพโหลดไฟล์ CSV เพื่อเพิ่มโจทย์ลงในข้อสอบนี้</p>
                </div>

                <div>
                    <a href="javascript:history.back()" class="btn btn-secondary">
                        <i class='bx bx-arrow-back me-1'></i>กลับไป
                    </a>
                </div>
            </div>
        </div>

        <!-- ตัวอย่างเทมเพลต -->
        <div class="sample-template" id="templateSection">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0"><i class='bx bxs-info-circle me-2'></i>รูปแบบไฟล์ CSV</h5>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleTemplate" title="ปิด/เปิด">
                    <i class='bx bx-chevron-down'></i>
                </button>
            </div>
            
            <div id="templateContent" class="mt-3">
                <p class="mb-2">ไฟล์ CSV ต้องมีหัวแถวดังนี้:</p>
                <code style="font-size: 0.8rem;">question_text,option_1,option_2,option_3,option_4,correct_option</code>
                <p class="mt-2 mb-0 text-sm"><strong>ตัวอย่าง:</strong></p>
                <code style="font-size: 0.75rem; display: block; margin-top: 0.5rem;">
                    What is 2+2?,3,4,5,6,2<br>
                    What is the capital of Thailand?,Bangkok,Phuket,Chiang Mai,Rayong,1
                </code>
                <p class="mt-2 mb-0 text-sm"><strong>หมายเหตุ:</strong> 
                    <ul class="mt-2 mb-0" style="font-size: 0.9rem;">
                        <li><strong>question_text:</strong> ข้อคำถาม</li>
                        <li><strong>option_1 ถึง option_4:</strong> ตัวเลือก 4 ตัวเลือก</li>
                        <li><strong>correct_option:</strong> เลขที่ของคำตอบที่ถูก (1, 2, 3, หรือ 4)</li>
                    </ul>
                </p>
                <a href="javascript:void(0)" onclick="downloadTemplate()" class="btn btn-sm btn-outline-primary mt-2">
                    <i class='bx bx-download me-1'></i>ดาวน์โหลดเทมเพลต
                </a>
            </div>
        </div>

        <div class="card p-5">
            <form id="uploadForm" enctype="multipart/form-data" action="../../controllers/UploadQuestionsController.php" method="POST">

                <input type="hidden" name="exam_id" value="<?= $exam_id ?>">
                <input type="hidden" name="course_id" value="<?= $course_id ?>">

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
                <div id="errorMessage" class="error-message alert alert-danger"></div>
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
                        <i class='bx bx-upload me-1'></i>อัพโหลดโจทย์
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle Template
        document.getElementById('toggleTemplate').addEventListener('click', function() {
            const content = document.getElementById('templateContent');
            const btn = this;
            
            content.style.display = content.style.display === 'none' ? 'block' : 'none';
            btn.innerHTML = content.style.display === 'none' ? '<i class="bx bx-chevron-up"></i>' : '<i class="bx bx-chevron-down"></i>';
        });

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

        // Drag and drop
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
            if (e.dataTransfer.files.length > 0) {
                csvFile.files = e.dataTransfer.files;
                handleFileSelect();
            }
        });

        csvFile.addEventListener('change', handleFileSelect);

        function handleFileSelect() {
            const file = csvFile.files[0];
            if (!file) return;

            if (!file.name.endsWith('.csv')) {
                showError('⚠️ กรุณาเลือกไฟล์ CSV เท่านั้น');
                resetForm();
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    const csv = e.target.result;
                    const lines = csv.split('\n').map(line => line.trim()).filter(line => line);

                    if (lines.length < 2) {
                        showError('⚠️ ไฟล์ต้องมีอย่างน้อย 2 แถว');
                        resetForm();
                        return;
                    }

                    fileName.textContent = file.name;
                    rowCount.textContent = lines.length - 1;
                    fileInfo.classList.add('show');
                    errorMessage.classList.remove('show');

                    displayPreview(lines);
                    submitBtn.disabled = false;
                } catch (error) {
                    showError('❌ เกิดข้อผิดพลาด: ' + error.message);
                    resetForm();
                }
            };

            reader.readAsText(file);
        }

        function displayPreview(lines) {
            const headers = parseCSVLine(lines[0]);
            
            headerRow.innerHTML = '';
            headers.forEach(header => {
                const th = document.createElement('th');
                th.textContent = header;
                headerRow.appendChild(th);
            });

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

        // Submit
        uploadForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const file = csvFile.files[0];
            if (!file) {
                showError('⚠️ กรุณาเลือกไฟล์');
                return;
            }

            const formData = new FormData(uploadForm);
            formData.append('csv_file', file);

            submitBtn.disabled = true;
            document.getElementById('spinner').classList.add('show');

            try {
                const response = await fetch(uploadForm.action, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    successMessage.innerHTML = `
                        <strong><i class='bx bx-check-circle me-2'></i>สำเร็จ!</strong>
                        <br>เพิ่มโจทย์ ${result.created_questions} ข้อแล้ว
                        ${result.skipped_count > 0 ? `<br><small>ข้ามไป ${result.skipped_count} รายเนื่องจากมีข้อผิดพลาด</small>` : ''}
                    `;
                    successMessage.classList.add('show');
                    resetForm();
                    
                    setTimeout(() => {
                        window.location.href = 'AddQuestions.php?exam_id=<?= $exam_id ?>&course_id=<?= $course_id ?>';
                    }, 2000);
                } else {
                    showError(`❌ ${result.message || 'เกิดข้อผิดพลาด'}`);
                }
            } catch (error) {
                showError('❌ เกิดข้อผิดพลาด: ' + error.message);
            } finally {
                document.getElementById('spinner').classList.remove('show');
                submitBtn.disabled = false;
            }
        });

        // Download template
        function downloadTemplate() {
            const headers = ['question_text', 'option_1', 'option_2', 'option_3', 'option_4', 'correct_option'];
            const sampleData = [
                ['What is 2+2?', '3', '4', '5', '6', '2'],
                ['What is the capital of Thailand?', 'Bangkok', 'Phuket', 'Chiang Mai', 'Rayong', '1'],
                ['Which programming language is known for web development?', 'Python', 'JavaScript', 'C++', 'Java', '2']
            ];

            let csv = headers.join(',') + '\n';
            sampleData.forEach(row => {
                csv += row.map(cell => cell.includes(',') ? `"${cell}"` : cell).join(',') + '\n';
            });

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);

            link.setAttribute('href', url);
            link.setAttribute('download', 'template_questions.csv');
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