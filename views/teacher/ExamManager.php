<?php
session_start();
include_once('../../config/Database.php');
include_once('../../models/CourseModel.php');
include_once('../../models/ExamModel.php');

$db = (new Database())->getConnection();
$courseModel = new CourseModel($db);
$examModel = new ExamModel($db);

$course_id = $_GET['course_id'] ?? null;
if (!$course_id) die("Course ID is required.");

$course = $courseModel->getCourseById($course_id);
$exams = $examModel->getExamsByCourse($course_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>จัดการข้อสอบ - <?= htmlspecialchars($course['course_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <style>
        body { background: linear-gradient(135deg, #f5f0ff 0%, #e8dcff 50%, #ddc3ff 100%); min-height: 100vh; }
        .exam-card { background: white; border-radius: 15px; padding: 1.5rem; margin-bottom: 1rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .btn-create { background: #764ba2; color: white; border-radius: 10px; transition: 0.3s; }
        .btn-create:hover { background: #667eea; color: white; transform: translateY(-2px); }
    </style>
</head>
<body>
    <?php include('../../include/NavbarTeacher.php'); ?>

    <div class="container mt-5 w-75">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark">จัดการข้อสอบ (Exams)</h2>
                <p class="text-muted small">วิชา: <?= htmlspecialchars($course['course_code']) ?> <?= htmlspecialchars($course['course_name']) ?></p>
            </div>
            <div class="d-flex gap-2">
                <a href="CourseDetail.php?course_id=<?= $course_id ?>" class="btn btn-light"><i class='bx bx-arrow-back'></i> กลับ</a>
                <button class="btn btn-create px-4" data-bs-toggle="modal" data-bs-target="#createExamModal">
                    <i class='bx bx-plus-circle'></i> สร้างข้อสอบใหม่
                </button>
            </div>
        </div>

        <?php if (empty($exams)): ?>
            <div class="exam-card text-center py-5">
                <i class='bx bx-receipt fs-1 text-muted'></i>
                <p class="mt-3 text-muted">ยังไม่มีการสร้างข้อสอบในวิชานี้</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($exams as $exam): ?>
                    <div class="col-12">
                        <div class="exam-card d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold mb-1"><?= htmlspecialchars($exam['title']) ?></h5>
                                <small class="text-muted">
                                    <i class='bx bx-time-five'></i> เวลาทำ: <?= $exam['duration_minutes'] ?> นาที | 
                                    <i class='bx bx-calendar'></i> สร้างเมื่อ: <?= date('d M Y', strtotime($exam['created_at'])) ?>
                                </small>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="AddQuestions.php?exam_id=<?= $exam['exam_id'] ?>&course_id=<?= $course_id ?>" class="btn btn-sm btn-outline-primary">
                                    <i class='bx bx-list-plus'></i> เพิ่มโจทย์
                                </a>
<a href="ExamResults.php?exam_id=<?= $exam['exam_id'] ?>&course_id=<?= $course_id ?>" 
   class="btn btn-sm btn-outline-success">
    <i class='bx bx-bar-chart-alt-2'></i> ดูคะแนน
</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="modal fade" id="createExamModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="../../controllers/CreateExamController.php" method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">สร้างข้อสอบใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="course_id" value="<?= $course_id ?>">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">ชื่อชุดข้อสอบ</label>
                        <input type="text" name="title" class="form-control" placeholder="เช่น สอบกลางภาค ชุดที่ 1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">ระยะเวลาทำข้อสอบ (นาที)</label>
                        <input type="number" name="duration" class="form-control" value="60" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary px-4">บันทึกและไปเพิ่มโจทย์</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>