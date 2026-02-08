<?php
session_start();
include_once('../../config/Database.php');
include_once('../../models/ExamModel.php');

$db = (new Database())->getConnection();
$examModel = new ExamModel($db);

$exam_id = $_GET['exam_id'] ?? null;
$course_id = $_GET['course_id'] ?? null;

// ดึงโจทย์ทั้งหมดมาโชว์
$questions = $examModel->getQuestionsByExam($exam_id);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการโจทย์ข้อสอบ - TaskBoard</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Boxicons -->
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>

    <style>
        body {
            background: linear-gradient(135deg, #f5f0ff 0%, #e8dcff 50%, #ddc3ff 100%);
            min-height: 100vh;
        }

        .page-header {
            background: white;
            border-radius: 15px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .btn {
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .sample-template {
            background-color: #fef5e7;
            border-left: 4px solid #f39c12;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .question-form-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .form-section-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .form-section-title i {
            font-size: 1.5rem;
            color: #0d6efd;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }

        .option-input {
            margin-bottom: 1rem;
        }

        .option-input .input-group {
            border-radius: 10px;
            overflow: hidden;
        }

        .option-input .input-group-text {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-right: none;
            padding: 0.75rem 1rem;
        }

        .option-input .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }

        .correct-radio {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #198754;
        }

        .questions-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .question-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid #0d6efd;
            transition: all 0.3s ease;
        }

        .question-item:hover {
            background: #e7f1ff;
            border-left-color: #667eea;
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .question-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: #0d6efd;
            color: white;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            margin-right: 0.75rem;
        }

        .question-text {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .correct-answer {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            background: #d8ffdeff;
            color: #198754;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e0;
            margin-bottom: 1rem;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
        }

        .modal-header {
            border-bottom: 2px solid #f0f0f0;
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 2px solid #f0f0f0;
            padding: 1.5rem;
        }

        .stats-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: #f2f2f2;
            color: black;
            border-radius: 20px;
            font-weight: 600;
            padding: 0.5rem 1rem;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #64748b;
            font-weight: 600;
            border-bottom: 3px solid transparent;
            border-radius: 0;
            padding: 1rem 0;
            margin-right: 2rem;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            border-bottom-color: #6c63ff;
            color: #6c63ff;
        }

        .nav-tabs .nav-link.active {
            color: #6c63ff;
            border-bottom-color: #6c63ff;
            background: none;
        }

        .tab-content {
            background: white;
            border-radius: 0 0 15px 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .drop-zone {
            border: 2px dashed #6c63ff;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            background-color: #f8f9ff;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .drop-zone:hover,
        .drop-zone.dragover {
            background-color: #e8e9ff;
            border-color: #5a52d3;
        }

        .file-input {
            display: none;
        }

        .upload-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>
    <?php include('../../include/NavbarTeacher.php'); ?>

    <div class="container mt-4 mb-5 w-75">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1 fw-bold text-dark d-flex align-items-center">
                        <i class='bx bxs-edit me-2'></i> จัดการโจทย์ข้อสอบ
                    </h2>
                    <p class="text-muted mb-0">สร้างและจัดการข้อสอบสำหรับนักเรียน</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="stats-badge">
                        <i class='bx bxs-file'></i>
                        <small><span><?= count($questions) ?> ข้อ</span></small>
                    </div>
                    <a href="ExamManager.php?course_id=<?= $course_id ?>" class="btn btn-secondary">
                        <i class='bx bx-check me-1'></i>เสร็จสิ้น
                    </a>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-0" role="tablist" style="background: white; border-radius: 15px 15px 0 0; padding: 0 2rem; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual-pane" type="button" role="tab" aria-controls="manual-pane" aria-selected="true" style="border: none; border-bottom: 3px solid transparent; color: #64748b; font-weight: 600; padding: 1rem 0; margin-right: 2rem;">
                    <i class='bx bxs-plus-circle me-2'></i>สร้างโจทย์ด้วยตนเอง
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-pane" type="button" role="tab" aria-controls="upload-pane" aria-selected="false" style="border: none; border-bottom: 3px solid transparent; color: #64748b; font-weight: 600; padding: 1rem 0; margin-right: 2rem;">
                    <i class='bx bx-cloud-upload me-2'></i>อัพโหลด CSV
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" style="background: white; border-radius: 0 0 15px 15px; padding: 2rem; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); margin-bottom: 2rem;">
            
            <!-- Tab 1: Manual Creation -->
            <div class="tab-pane fade show active" id="manual-pane" role="tabpanel" aria-labelledby="manual-tab">
                <form action="../../controllers/AddQuestionController.php" method="POST">
                    <input type="hidden" name="exam_id" value="<?= $exam_id ?>">
                    <input type="hidden" name="course_id" value="<?= $course_id ?>">
                    
                    <div class="mb-4">
                        <label class="form-label">
                            <i class='bx bx-help-circle me-1'></i>คำถาม (Question)
                        </label>
                        <textarea name="question_text" 
                                  class="form-control" 
                                  rows="3" 
                                  required 
                                  placeholder="พิมพ์โจทย์คำถาม..."></textarea>
                        <small class="text-muted">
                            <i class='bx bx-info-circle me-1'></i>
                            พิมพ์คำถามที่ต้องการให้นักเรียนตอบ
                        </small>
                    </div>

                    <label class="form-label mb-3">
                        <i class='bx bx-list-check me-1'></i>ตัวเลือกคำตอบ (เลือกคำตอบที่ถูกต้อง)
                    </label>
                    <div class="row">
                        <?php for($i=1; $i<=4; $i++): ?>
                        <div class="col-md-6 option-input">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <input class="form-check-input correct-radio" 
                                           type="radio" 
                                           name="correct_option" 
                                           value="<?= $i ?>" 
                                           required
                                           title="เลือกเป็นคำตอบที่ถูก">
                                </span>
                                <input type="text" 
                                       name="option_<?= $i ?>" 
                                       class="form-control" 
                                       placeholder="ตัวเลือกที่ <?= $i ?>" 
                                       required>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                    
                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-success px-4">
                            <i class='bx bx-save me-1'></i>บันทึกข้อสอบ
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab 2: CSV Upload -->
            <div class="tab-pane fade" id="upload-pane" role="tabpanel" aria-labelledby="upload-tab">
                
                <!-- Sample Template -->
                <div class="sample-template">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class='bx bxs-info-circle me-2'></i>รูปแบบไฟล์ CSV</h6>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleTemplate" title="ปิด/เปิด">
                            <i class='bx bx-chevron-down'></i>
                        </button>
                    </div>
                    
<div id="templateContent" class="mt-3">
    <p class="mb-2"><strong>ไฟล์ CSV ต้องมีหัวแถวดังนี้:</strong></p>
    <code style="font-size: 0.8rem;">question_text,option_1,option_2,option_3,option_4,correct_option</code>
    
    <div class="mt-3 mb-3">
        <a href="data:text/csv;charset=utf-8,%EF%BB%BFquestion_text,option_1,option_2,option_3,option_4,correct_option%0A%22%E0%B8%84%E0%B8%B3%E0%B8%96%E0%B8%B2%E0%B8%A1%E0%B8%82%E0%B9%89%E0%B8%AD%E0%B8%97%E0%B8%B5%E0%B9%82%201%22,%22%E0%B8%95%E0%B8%B1%E0%B8%A7%E0%B9%80%E0%B8%A5%E0%B8%B7%E0%B8%AD%E0%B8%81%20A%22,%22%E0%B8%95%E0%B8%B1%E0%B8%A7%E0%B9%80%E0%B8%A5%E0%B8%B7%E0%B8%AD%E0%B8%81%20B%22,%22%E0%B8%95%E0%B8%B1%E0%B8%A7%E0%B9%80%E0%B8%A5%E0%B8%B7%E0%B8%AD%E0%B8%81%20C%22,%22%E0%B8%95%E0%B8%B1%E0%B8%A7%E0%B9%80%E0%B8%A5%E0%B8%B7%E0%B8%AD%E0%B8%81%20D%22,1%0A%22%E0%B8%84%E0%B8%B3%E0%B8%96%E0%B8%B2%E0%B8%A1%E0%B8%82%E0%B9%89%E0%B8%AD%E0%B8%97%E0%B8%B5%E0%B9%82%202%22,%22%E0%B8%95%E0%B8%B1%E0%B8%A7%E0%B9%80%E0%B8%A5%E0%B8%B7%E0%B8%AD%E0%B8%81%20A%22,%22%E0%B8%95%E0%B8%B1%E0%B8%A7%E0%B9%80%E0%B8%A5%E0%B8%B7%E0%B8%AD%E0%B8%81%20B%22,%22%E0%B8%95%E0%B8%B1%E0%B8%A7%E0%B9%80%E0%B8%A5%E0%B8%B7%E0%B8%AD%E0%B8%81%20C%22,%22%E0%B8%95%E0%B8%B1%E0%B8%A7%E0%B9%80%E0%B8%A5%E0%B8%B7%E0%B8%AD%E0%B8%81%20D%22,2%0A%22%E0%B8%84%E0%B8%B3%E0%B8%96%E0%B8%B2%E0%B8%A1%E0%B8%82%E0%B9%89%E0%B8%AD%E0%B8%97%E0%B8%B5%E0%B9%82%203%22,%22%E0%B8%95%E0%B8%B1%E0%B8%A7%E0%B9%80%E0%B8%A5%E0%B8%B7%E0%B8%AD%E0%B8%81%20A%22,%22%E0%B8%95%E0%B8%B1%E0%B8%A7%E0%B9%80%E0%B8%A5%E0%B8%B7%E0%B8%AD%E0%B8%81%20B%22,%22%E0%B8%95%E0%B8%B1%E0%B8%A7%E0%B9%80%E0%B8%A5%E0%B8%B7%E0%B8%AD%E0%B8%81%20C%22,%22%E0%B8%95%E0%B8%B1%E0%B8%A7%E0%B9%80%E0%B8%A5%E0%B8%B7%E0%B8%AD%E0%B8%81%20D%22,3" 
           download="example_questions.csv" 
           class="btn btn-sm btn-warning">
            <i class='bx bxs-download me-1'></i> ดาวน์โหลดไฟล์ตัวอย่าง (.csv)
        </a>
    </div>

    <p class="mt-2 mb-0 text-sm"><strong>ตัวอย่างข้อมูลในไฟล์:</strong></p>
    <code style="font-size: 0.75rem; display: block; margin-top: 0.5rem;">
        What is 2+2?,3,4,5,6,2<br>
        Capital of Thailand?,Bangkok,Phuket,Chiang Mai,Rayong,1
    </code>
</div>
                </div>

                <!-- Upload Form -->
<form id="uploadForm" enctype="multipart/form-data" action="../../controllers/UploadQuestionsController.php" method="POST">
    <input type="hidden" name="exam_id" value="<?= $exam_id ?>">
    <input type="hidden" name="course_id" value="<?= $course_id ?>">

    <div class="drop-zone" id="dropZone">
        <i class='bx bx-cloud-upload' style="font-size: 2.5rem; color: #6c63ff; margin-bottom: 1rem;"></i>
        <h6>ลากไฟล์ CSV มาวางที่นี่ หรือ คลิกเพื่อเลือกไฟล์</h6>
        <small class="text-muted">รองรับไฟล์ .csv เท่านั้น</small>
        
        <input type="file" id="csvFile" name="csv_file" class="file-input" accept=".csv" required>
    </div>

    <div id="uploadStatus" style="margin-top: 1rem;"></div>

    <div class="text-end mt-3">
        <button type="button" class="btn btn-outline-secondary me-2" id="resetBtn" style="display: none;">
            <i class='bx bx-x me-1'></i>ยกเลิก
        </button>
        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
            <i class='bx bx-upload me-1'></i>อัพโหลดโจทย์
        </button>
    </div>
</form>
            </div>

        </div>

        <!-- Questions List -->
        <div class="questions-section">
            <div class="section-header">
                <div class="d-flex align-items-center gap-3">
                    <i class='bx bxs-file-archive fs-3 text-primary'></i>
                    <div>
                        <h5 class="mb-0 fw-bold">โจทย์ทั้งหมด</h5>
                        <small class="text-muted">รายการข้อสอบที่สร้างแล้ว</small>
                    </div>
                    <span class="badge bg-primary"><?= count($questions) ?></span>
                </div>
            </div>

            <?php if (empty($questions)): ?>
                <div class="empty-state">
                    <i class='bx bx-file'></i>
                    <p class="text-muted mb-0 fw-semibold">ยังไม่มีโจทย์</p>
                    <small class="text-muted">เริ่มสร้างข้อสอบแรกของคุณได้เลย</small>
                </div>
            <?php else: ?>
                <?php foreach ($questions as $index => $q): ?>
                    <div class="question-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-start mb-2">
                                    <span class="question-number"><?= $index + 1 ?></span>
                                    <div class="flex-grow-1">
                                        <div class="question-text"><?= htmlspecialchars($q['question_text']) ?></div>
                                        <div class="mt-2">
                                            <span class="correct-answer">
                                                <i class='bx bx-check-circle me-1'></i>
                                                เฉลย: ตัวเลือกที่ <?= $q['correct_option'] ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2 ms-3">
                                <button class="btn btn-sm btn-warning" 
                                        onclick="editQuestion(<?= htmlspecialchars(json_encode($q)) ?>)"
                                        title="แก้ไข">
                                    <i class='bx bx-edit-alt'></i>
                                </button>
                                <a href="../../controllers/DeleteQuestionController.php?question_id=<?= $q['question_id'] ?>&exam_id=<?= $exam_id ?>&course_id=<?= $course_id ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('ยืนยันการลบโจทย์ข้อนี้?')"
                                   title="ลบ">
                                    <i class='bx bx-trash'></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

    <!-- Edit Question Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form action="../../controllers/UpdateQuestionController.php" method="POST" class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold mb-1">แก้ไขโจทย์ข้อสอบ</h5>
                        <small class="text-muted">แก้ไขคำถามและตัวเลือกคำตอบ</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="question_id" id="edit_q_id">
                    <input type="hidden" name="exam_id" value="<?= $exam_id ?>">
                    <input type="hidden" name="course_id" value="<?= $course_id ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold">คำถาม</label>
                        <textarea name="question_text" id="edit_text" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <label class="form-label fw-bold mb-3">ตัวเลือกคำตอบ</label>
                    <div class="row">
                        <?php for($i=1; $i<=4; $i++): ?>
                        <div class="col-md-6 option-input">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <input class="form-check-input correct-radio" 
                                           type="radio" 
                                           name="correct_option" 
                                           value="<?= $i ?>" 
                                           id="edit_opt_radio_<?= $i ?>">
                                </span>
                                <input type="text" 
                                       name="option_<?= $i ?>" 
                                       id="edit_opt_<?= $i ?>" 
                                       class="form-control" 
                                       required>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class='bx bx-x me-1'></i>ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-warning px-4">
                        <i class='bx bx-save me-1'></i>บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php include('../../include/alert.php'); ?>
    <?php include('../../include/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle Template
        document.getElementById('toggleTemplate').addEventListener('click', function() {
            const content = document.getElementById('templateContent');
            const btn = this;
            
            content.style.display = content.style.display === 'none' ? 'block' : 'none';
            btn.innerHTML = content.style.display === 'none' ? '<i class="bx bx-chevron-up"></i>' : '<i class="bx bx-chevron-down"></i>';
        });

        // File Upload Handler
        const dropZone = document.getElementById('dropZone');
        const csvFile = document.getElementById('csvFile');
        const uploadForm = document.getElementById('uploadForm');
        const submitBtn = document.getElementById('submitBtn');
        const resetBtn = document.getElementById('resetBtn');
        const uploadStatus = document.getElementById('uploadStatus');

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
                uploadStatus.innerHTML = '<div class="alert alert-warning">⚠️ กรุณาเลือกไฟล์ CSV เท่านั้น</div>';
                csvFile.value = '';
                submitBtn.disabled = true;
                return;
            }

            uploadStatus.innerHTML = `<div class="alert alert-info"><i class='bx bx-check-circle me-2'></i>ไฟล์ที่เลือก: <strong>${file.name}</strong> (${(file.size / 1024).toFixed(2)} KB)</div>`;
            submitBtn.disabled = false;
            resetBtn.style.display = 'inline-block';
        }

        resetBtn.addEventListener('click', () => {
            csvFile.value = '';
            uploadStatus.innerHTML = '';
            submitBtn.disabled = true;
            resetBtn.style.display = 'none';
        });

        uploadForm.addEventListener('submit', (e) => {
            const file = csvFile.files[0];
            if (!file) {
                e.preventDefault();
                uploadStatus.innerHTML = '<div class="alert alert-danger">❌ กรุณาเลือกไฟล์</div>';
            }
        });

        function editQuestion(data) {
            document.getElementById('edit_q_id').value = data.question_id;
            document.getElementById('edit_text').value = data.question_text;
            document.getElementById('edit_opt_1').value = data.option_1;
            document.getElementById('edit_opt_2').value = data.option_2;
            document.getElementById('edit_opt_3').value = data.option_3;
            document.getElementById('edit_opt_4').value = data.option_4;
            
            document.getElementById('edit_opt_radio_' + data.correct_option).checked = true;

            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
    </script>
</body>

</html>