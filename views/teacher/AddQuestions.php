<?php
session_start();
include_once('../../config/Database.php');
include_once('../../models/ExamModel.php');

$db = (new Database())->getConnection();
$examModel = new ExamModel($db);

$exam_id = $_GET['exam_id'] ?? null;
$course_id = $_GET['course_id'] ?? null;

// ดึงโจทย์ที่เคยเพิ่มไปแล้วมาโชว์ (ถ้ามี)
$questions = $examModel->getQuestionsByExam($exam_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มโจทย์ - Exam System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <style>
        body { background: #f4f7f6; }
        .question-card { background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .option-input { margin-bottom: 10px; }
        .correct-radio { width: 20px; height: 20px; cursor: pointer; }
    </style>
</head>
<body>
    <?php include('../../include/NavbarTeacher.php'); ?>

    <div class="container mt-4 mb-5 w-75">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class='bx bx-edit'></i> เพิ่มโจทย์ข้อสอบ</h4>
            <a href="ExamManager.php?course_id=<?= $course_id ?>" class="btn btn-secondary btn-sm">เสร็จสิ้น/กลับหน้าหลัก</a>
        </div>

        <div class="question-card mb-4">
            <h6 class="fw-bold text-primary mb-3">สร้างโจทย์ใหม่</h6>
            <form action="../../controllers/AddQuestionController.php" method="POST">
                <input type="hidden" name="exam_id" value="<?= $exam_id ?>">
                <input type="hidden" name="course_id" value="<?= $course_id ?>">
                
                <div class="mb-3">
                    <label class="form-label small fw-bold">คำถาม (Question)</label>
                    <textarea name="question_text" class="form-control" rows="2" required placeholder="พิมพ์โจทย์ที่นี่..."></textarea>
                </div>

                <div class="row">
                    <?php for($i=1; $i<=4; $i++): ?>
                    <div class="col-md-6 option-input">
                        <div class="input-group">
                            <div class="input-group-text">
                                <input class="form-check-input correct-radio" type="radio" name="correct_option" value="<?= $i ?>" required>
                                <small class="ms-2">เฉลย</small>
                            </div>
                            <input type="text" name="option_<?= $i ?>" class="form-control" placeholder="ตัวเลือกที่ <?= $i ?>" required>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>

                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <small class="text-muted">* เลือกปุ่ม Radio หน้าข้อที่ต้องการให้เป็นคำตอบที่ถูกต้อง</small>
                    <button type="submit" class="btn btn-primary px-4">บันทึกข้อนี้</button>
                </div>
            </form>
        </div>

        <hr>
        <h6 class="fw-bold mb-3">โจทย์ที่เพิ่มแล้ว (<?= count($questions) ?> ข้อ)</h6>
        <?php foreach ($questions as $index => $q): ?>
            <div class="card mb-2 p-3 border-0 shadow-sm">
                <div class="d-flex justify-content-between">
                    <div>
                        <strong><?= $index + 1 ?>. <?= htmlspecialchars($q['question_text']) ?></strong>
                        <div class="small text-success mt-1">เฉลย: ข้อ <?= $q['correct_option'] ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>