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
$queryExam = $db->prepare("SELECT * FROM exams WHERE exam_id = :id");
$queryExam->execute([':id' => $exam_id]);
$exam_info = $queryExam->fetch(PDO::FETCH_ASSOC);

if (!$exam_info) die("ไม่พบข้อมูลข้อสอบ");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ทำข้อสอบ: <?= htmlspecialchars($exam_info['title']) ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Boxicons -->
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>

    <style>
        body {
            background: linear-gradient(135deg, #f5f0ff 0%, #e8dcff 50%, #ddc3ff 100%);
            min-height: 100vh;
        }

        .exam-header {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .exam-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .exam-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
            font-size: 0.9rem;
        }

        .exam-info i {
            color: #94a3b8;
        }

        .question-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #0d6efd;
        }

        .question-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: #0d6efd;
            color: white;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            margin-right: 0.75rem;
        }

        .question-text {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 1.5rem;
        }

        .option-item {
            cursor: pointer;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .option-item:hover {
            background: #e7f1ff;
            border-color: #0d6efd;
            transform: translateX(4px);
        }

        .option-item input:checked ~ .option-content {
            background: #e7f1ff;
            border-color: #0d6efd;
        }

        .form-check-input {
            width: 20px;
            height: 20px;
            margin-top: 0.15rem;
            cursor: pointer;
            accent-color: #0d6efd;
        }

        .form-check-label {
            font-size: 0.95rem;
            color: #495057;
            cursor: pointer;
            margin-left: 0.5rem;
        }

        .option-item:has(input:checked) {
            background: #e7f1ff;
            border-color: #0d6efd;
        }

        .option-item:has(input:checked) .form-check-label {
            font-weight: 600;
            color: #0d6efd;
        }

        .sticky-timer {
            position: sticky;
            top: 20px;
            z-index: 1000;
        }

        .timer-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            border: 2px solid #f0f0f0;
        }

        .timer-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
        }

        .timer-label {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .timer-display {
            font-size: 3rem;
            font-weight: 700;
            color: #dc3545;
            font-family: 'Courier New', monospace;
            margin-bottom: 1rem;
        }

        .timer-warning {
            background: #fff8e1;
            border: 1px solid #ffe082;
            border-radius: 8px;
            padding: 0.75rem;
            margin-top: 1rem;
        }

        .timer-warning i {
            color: #f59e0b;
        }

        .progress-indicator {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .progress-text {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .submit-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            text-align: center;
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

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 1rem 3rem;
            font-size: 1.1rem;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #5568d3 0%, #6a4091 100%);
            color: white;
        }
    </style>
</head>

<body>
    <?php include('../../include/NavbarStudent.php'); ?>

    <div class="container mt-4 mb-5">
        <div class="row">
            <!-- Left: Questions -->
            <div class="col-lg-8">
                <!-- Exam Header -->
                <div class="exam-header">
                    <h1 class="exam-title">
                        <i class='bx bxs-file-doc me-2'></i>
                        <?= htmlspecialchars($exam_info['title']) ?>
                    </h1>
                    <div class="exam-info">
                        <i class='bx bx-info-circle'></i>
                        <span>กรุณาเลือกคำตอบที่ถูกต้องที่สุดเพียงข้อเดียว</span>
                    </div>
                    <div class="exam-info mt-2">
                        <i class='bx bx-time-five'></i>
                        <span>เวลาในการทำข้อสอบ: <?= $exam_info['duration_minutes'] ?> นาที</span>
                    </div>
                </div>

                <!-- Progress Indicator -->
                <div class="progress-indicator">
                    <div class="progress-text">
                        <i class='bx bx-list-check me-1'></i>
                        <strong>ความคืบหน้า:</strong> <span id="answered">0</span> / <?= count($questions) ?> ข้อ
                    </div>
                    <div class="progress">
                        <div id="progressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Questions Form -->
                <form id="examForm" action="../../controllers/SubmitExamController.php" method="POST">
                    <input type="hidden" name="exam_id" value="<?= $exam_id ?>">
                    <input type="hidden" name="student_id" value="<?= $student_id ?>">

                    <?php foreach ($questions as $index => $q): ?>
                        <div class="question-card">
                            <div class="d-flex align-items-start mb-3">
                                <span class="question-number"><?= $index + 1 ?></span>
                                <h5 class="question-text mb-0 flex-grow-1">
                                    <?= htmlspecialchars($q['question_text']) ?>
                                </h5>
                            </div>
                            
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                <label class="option-item">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="answers[<?= $q['question_id'] ?>]" 
                                               value="<?= $i ?>" 
                                               required
                                               onchange="updateProgress()">
                                        <span class="form-check-label">
                                            <?= htmlspecialchars($q['option_' . $i]) ?>
                                        </span>
                                    </div>
                                </label>
                            <?php endfor; ?>
                        </div>
                    <?php endforeach; ?>

                    <!-- Submit Section -->
                    <div class="submit-section">
                        <i class='bx bx-check-circle fs-1 text-success mb-3'></i>
                        <h5 class="fw-bold mb-3">พร้อมส่งข้อสอบแล้วหรือไม่?</h5>
                        <p class="text-muted mb-4">ตรวจสอบคำตอบของคุณให้ครบถ้วนก่อนส่ง</p>
                        <button type="button" class="btn btn-submit" onclick="confirmSubmit()">
                            <i class='bx bx-send me-2'></i>ส่งข้อสอบ
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right: Timer -->
            <div class="col-lg-4">
                <div class="sticky-timer">
                    <div class="timer-card">
                        <div class="timer-icon">
                            <i class='bx bx-timer'></i>
                        </div>
                        <div class="timer-label">เวลาที่เหลือ</div>
                        <div id="timer" class="timer-display">00:00</div>
                        <div class="timer-warning">
                            <i class='bx bx-info-circle me-1'></i>
                            <small>เมื่อหมดเวลา ระบบจะส่งคำตอบให้อัตโนมัติ</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const totalQuestions = <?= count($questions) ?>;
        
        // Timer
        let timeLeft = <?= $exam_info['duration_minutes'] * 60 ?>;
        const timerDisplay = document.getElementById('timer');

        const countdown = setInterval(() => {
            let minutes = Math.floor(timeLeft / 60);
            let seconds = timeLeft % 60;

            timerDisplay.innerHTML = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

            // Change color when time is running out
            if (timeLeft <= 60) {
                timerDisplay.style.color = '#dc3545';
            } else if (timeLeft <= 300) {
                timerDisplay.style.color = '#ffc107';
            }

            if (timeLeft <= 0) {
                clearInterval(countdown);
                Swal.fire({
                    title: 'หมดเวลา!',
                    text: 'ระบบจะส่งคำตอบอัตโนมัติ',
                    icon: 'warning',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    document.getElementById('examForm').submit();
                });
            }
            timeLeft--;
        }, 1000);

        // Progress tracking
        function updateProgress() {
            const answered = document.querySelectorAll('input[type="radio"]:checked').length;
            const percentage = (answered / totalQuestions) * 100;
            
            document.getElementById('answered').textContent = answered;
            document.getElementById('progressBar').style.width = percentage + '%';
        }

        // Confirm submit
        function confirmSubmit() {
            const answered = document.querySelectorAll('input[type="radio"]:checked').length;
            
            if (answered < totalQuestions) {
                Swal.fire({
                    title: 'คำเตือน!',
                    text: `คุณยังตอบไม่ครบ (${answered}/${totalQuestions} ข้อ) ต้องการส่งข้อสอบหรือไม่?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'ใช่, ส่งเลย!',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitExam();
                    }
                });
            } else {
                Swal.fire({
                    title: 'ยืนยันการส่งข้อสอบ?',
                    text: "เมื่อส่งแล้วจะไม่สามารถกลับมาแก้ไขได้อีก",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'ใช่, ส่งเลย!',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitExam();
                    }
                });
            }
        }

        function submitExam() {
            Swal.fire({
                title: 'กำลังส่งข้อสอบ...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            document.getElementById('examForm').submit();
        }

        // Prevent accidental page refresh
        window.addEventListener('beforeunload', function (e) {
            e.preventDefault();
            e.returnValue = '';
        });
    </script>
</body>

</html>