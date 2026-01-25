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

        <!-- Create Question Form -->
        <div class="question-form-card">
            <div class="form-section-title">
                <i class='bx bxs-plus-circle'></i>
                <h5 class="mb-0 fw-bold">สร้างโจทย์ใหม่</h5>
            </div>

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
        function editQuestion(data) {
            document.getElementById('edit_q_id').value = data.question_id;
            document.getElementById('edit_text').value = data.question_text;
            document.getElementById('edit_opt_1').value = data.option_1;
            document.getElementById('edit_opt_2').value = data.option_2;
            document.getElementById('edit_opt_3').value = data.option_3;
            document.getElementById('edit_opt_4').value = data.option_4;
            
            // ติ๊กถูกที่ Radio ของเฉลยเดิม
            document.getElementById('edit_opt_radio_' + data.correct_option).checked = true;

            // สั่งเปิด Modal
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
    </script>
</body>

</html>