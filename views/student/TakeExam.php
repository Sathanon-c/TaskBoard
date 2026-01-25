<?php
session_start();
include_once('../../config/Database.php');
include_once('../../models/ExamModel.php');
include_once('../../models/StudentModel.php');

$db = (new Database())->getConnection();
$examModel = new ExamModel($db);
$studentModel = new StudentModel($db);

$exam_id = $_GET['exam_id'] ?? null;
$user_id = $_SESSION['user_id'];
$student_id = $studentModel->getStudentIdByUserId($user_id);

// 1. ตรวจสอบว่าเคยสอบไปหรือยัง
$already_done = $examModel->getStudentResult($exam_id, $student_id);
if ($already_done) {
    die("คุณได้ทำการสอบชุดนี้ไปเรียบร้อยแล้ว");
}

// 2. ดึงรายละเอียดข้อสอบและโจทย์
$questions = $examModel->getQuestionsByExam($exam_id);
// ดึงข้อมูลเวลาจากตาราง exams (สมมติว่าเราเก็บไว้ใน $exams)
$queryExam = $db->prepare("SELECT * FROM exams WHERE exam_id = :id");
$queryExam->execute([':id' => $exam_id]);
$exam_info = $queryExam->fetch(PDO::FETCH_ASSOC);

if (!$exam_info) die("ไม่พบข้อมูลข้อสอบ");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ทำข้อสอบ: <?= htmlspecialchars($exam_info['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <style>
        body { background-color: #f8f9fa; }
        .sticky-timer { position: sticky; top: 20px; z-index: 1000; }
        .question-card { background: white; border-radius: 15px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .option-item { cursor: pointer; border: 1px solid #dee2e6; border-radius: 10px; padding: 10px 15px; margin-bottom: 10px; transition: 0.2s; }
        .option-item:hover { background-color: #f0eaff; border-color: #764ba2; }
        .form-check-input:checked + .form-check-label { font-weight: bold; color: #764ba2; }
    </style>
</head>
<body>

<div class="container mt-5 mb-5 w-75">
    <div class="row">
        <div class="col-md-8">
            <div class="mb-4">
                <h2 class="fw-bold"><?= htmlspecialchars($exam_info['title']) ?></h2>
                <p class="text-muted">กรุณาเลือกคำตอบที่ถูกต้องที่สุดเพียงข้อเดียว</p>
            </div>

            <form id="examForm" action="../../controllers/SubmitExamController.php" method="POST">
                <input type="hidden" name="exam_id" value="<?= $exam_id ?>">
                <input type="hidden" name="student_id" value="<?= $student_id ?>">

                <?php foreach ($questions as $index => $q): ?>
                    <div class="question-card">
                        <h5 class="fw-bold mb-3"><?= ($index + 1) ?>. <?= htmlspecialchars($q['question_text']) ?></h5>
                        
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <label class="d-block option-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="answers[<?= $q['question_id'] ?>]" value="<?= $i ?>" required>
                                    <span class="form-check-label">
                                        <?= htmlspecialchars($q['option_' . $i]) ?>
                                    </span>
                                </div>
                            </label>
                        <?php endfor; ?>
                    </div>
                <?php endforeach; ?>

                <div class="text-end">
                    <button type="button" class="btn btn-primary btn-lg px-5" onclick="confirmSubmit()">ส่งข้อสอบ</button>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            <div class="sticky-timer">
                <div class="card border-0 shadow-sm text-center p-4">
                    <h6 class="text-muted mb-2">เวลาที่เหลือ</h6>
                    <h2 id="timer" class="fw-bold text-danger">00:00</h2>
                    <hr>
                    <small class="text-muted">เมื่อหมดเวลา ระบบจะส่งคำตอบให้อัตโนมัติ</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // ตั้งค่าเวลาจากฐานข้อมูล (นาที -> วินาที)
    let timeLeft = <?= $exam_info['duration_minutes'] * 60 ?>;
    const timerDisplay = document.getElementById('timer');

    const countdown = setInterval(() => {
        let minutes = Math.floor(timeLeft / 60);
        let seconds = timeLeft % 60;

        timerDisplay.innerHTML = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

        if (timeLeft <= 0) {
            clearInterval(countdown);
            alert('หมดเวลาแล้ว! ระบบจะส่งคำตอบอัตโนมัติ');
            document.getElementById('examForm').submit();
        }
        timeLeft--;
    }, 1000);

    function confirmSubmit() {
        Swal.fire({
            title: 'ยืนยันการส่งข้อสอบ?',
            text: "เมื่อส่งแล้วจะไม่สามารถกลับมาแก้ไขได้อีก",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'ใช่, ส่งเลย!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('examForm').submit();
            }
        })
    }
</script>
</body>
</html>