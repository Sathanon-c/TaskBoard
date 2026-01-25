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

// คำนวณสถิติ
$total_exams = count($exams);
$published_count = count(array_filter($exams, fn($e) => $e['status'] === 'published'));
$draft_count = $total_exams - $published_count;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อสอบ - <?= htmlspecialchars($course['course_name']) ?></title>
    
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
            padding: 2rem;
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

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 2px solid #f0f0f0;
            text-align: center;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 0.75rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon-total {
            background: #e7f1ff;
            color: #0d6efd;
        }

        .stat-icon-published {
            background: #d8ffdeff;
            color: #198754;
        }

        .stat-icon-draft {
            background: #f2f2f2;
            color: #6c757d;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3748;
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .exam-section {
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

        .exam-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-left: 4px solid;
            transition: all 0.3s ease;
        }

        .exam-card:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .exam-card.published {
            border-left-color: #198754;
        }

        .exam-card.draft {
            border-left-color: #6c757d;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            min-width: 100px;
            display: inline-block;
            text-align: center;
        }

        .status-published {
            background: #d8ffdeff;
            color: #198754;
        }

        .status-draft {
            background: #f2f2f2;
            color: #6c757d;
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

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .empty-state i {
            font-size: 4rem;
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

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 0.75rem 1rem;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
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
                        <i class='bx bxs-receipt me-2'></i> จัดการข้อสอบ (Exams)
                    </h2>
                    <p class="text-muted mb-0">สร้างและจัดการข้อสอบสำหรับรายวิชา</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="CourseDetail.php?course_id=<?= $course_id ?>" class="btn btn-secondary">
                        <i class='bx bx-arrow-back me-1'></i>กลับ
                    </a>
                    <button class="btn btn-success px-4" data-bs-toggle="modal" data-bs-target="#createExamModal">
                        <i class='bx bx-plus-circle me-1'></i>สร้างข้อสอบใหม่
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-total">
                        <i class='bx bxs-receipt'></i>
                    </div>
                    <div class="stat-number"><?= $total_exams ?></div>
                    <div class="stat-label">ข้อสอบทั้งหมด</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-published">
                        <i class='bx bxs-show'></i>
                    </div>
                    <div class="stat-number"><?= $published_count ?></div>
                    <div class="stat-label">เปิดสอบแล้ว</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-draft">
                        <i class='bx bxs-hide'></i>
                    </div>
                    <div class="stat-number"><?= $draft_count ?></div>
                    <div class="stat-label">ปิดสอบ/แบบร่าง</div>
                </div>
            </div>
        </div>

        <!-- Exams Section -->
        <div class="exam-section">
            <div class="section-header">
                <div class="d-flex align-items-center gap-3">
                    <i class='bx bxs-file fs-3 text-primary'></i>
                    <div>
                        <h5 class="mb-0 fw-bold">รายการข้อสอบ</h5>
                        <small class="text-muted">จัดการและควบคุมข้อสอบทั้งหมด</small>
                    </div>
                    <span class="badge bg-primary"><?= $total_exams ?></span>
                </div>
            </div>

            <?php if (empty($exams)): ?>
                <div class="empty-state">
                    <i class='bx bx-receipt'></i>
                    <h5 class="mb-2 fw-bold text-dark">ยังไม่มีข้อสอบ</h5>
                    <p class="text-muted mb-3">ยังไม่มีการสร้างข้อสอบในวิชานี้</p>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createExamModal">
                        <i class='bx bx-plus-circle me-1'></i>สร้างข้อสอบแรก
                    </button>
                </div>
            <?php else: ?>
                <?php foreach ($exams as $exam): 
                    $is_published = ($exam['status'] === 'published');
                ?>
                    <div class="exam-card <?= $is_published ? 'published' : 'draft' ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center gap-3 flex-grow-1">
                                <div>
                                    <span class="status-badge <?= $is_published ? 'status-published' : 'status-draft' ?>">
                                        <i class='bx <?= $is_published ? 'bxs-show' : 'bxs-hide' ?> me-1'></i>
                                        <?= $is_published ? 'เปิดสอบ' : 'ปิดสอบ' ?>
                                    </span>
                                </div>

                                <div class="flex-grow-1">
                                    <h5 class="fw-bold mb-2"><?= htmlspecialchars($exam['title']) ?></h5>
                                    <div class="d-flex gap-3">
                                        <span class="exam-info">
                                            <i class='bx bx-time-five'></i>
                                            <small><?= $exam['duration_minutes'] ?> นาที</small>
                                        </span>
                                        <span class="exam-info">
                                            <i class='bx bx-calendar'></i>
                                            <small><?= date('d M Y', strtotime($exam['created_at'])) ?></small>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <a href="../../controllers/ToggleExamStatusController.php?exam_id=<?= $exam['exam_id'] ?>&course_id=<?= $course_id ?>&current_status=<?= $exam['status'] ?>" 
                                   class="btn btn-sm <?= $is_published ? 'btn-warning' : 'btn-success' ?>" 
                                   title="<?= $is_published ? 'ปิดการเข้าถึง' : 'เปิดให้นักศึกษาสอบ' ?>">
                                    <i class='bx <?= $is_published ? 'bxs-lock' : 'bxs-lock-open' ?> me-1'></i>
                                    <?= $is_published ? 'ปิดสอบ' : 'เปิดสอบ' ?>
                                </a>

                                <a href="AddQuestions.php?exam_id=<?= $exam['exam_id'] ?>&course_id=<?= $course_id ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class='bx bx-list-plus me-1'></i>โจทย์
                                </a>

                                <a href="ExamResults.php?exam_id=<?= $exam['exam_id'] ?>&course_id=<?= $course_id ?>"
                                    class="btn btn-sm btn-info">
                                    <i class='bx bx-bar-chart-alt-2 me-1'></i>คะแนน
                                </a>

                                <a href="../../controllers/DeleteExamController.php?exam_id=<?= $exam['exam_id'] ?>&course_id=<?= $course_id ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('คำเตือน: ข้อมูลโจทย์และผลการสอบทั้งหมดจะถูกลบถาวร ยืนยันการลบ?')">
                                    <i class='bx bx-trash'></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

    <!-- Create Exam Modal -->
    <div class="modal fade" id="createExamModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="../../controllers/CreateExamController.php" method="POST" class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold mb-1">สร้างข้อสอบใหม่</h5>
                        <small class="text-muted">กรอกข้อมูลพื้นฐานของข้อสอบ</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="course_id" value="<?= $course_id ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class='bx bx-text me-1'></i>ชื่อชุดข้อสอบ
                        </label>
                        <input type="text" 
                               name="title" 
                               class="form-control" 
                               placeholder="เช่น สอบกลางภาค ชุดที่ 1" 
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class='bx bx-time-five me-1'></i>ระยะเวลาทำข้อสอบ (นาที)
                        </label>
                        <input type="number" 
                               name="duration" 
                               class="form-control" 
                               value="60" 
                               min="1"
                               required>
                        <small class="text-muted">
                            <i class='bx bx-info-circle me-1'></i>
                            กำหนดเวลาที่นักเรียนมีในการทำข้อสอบ
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class='bx bx-x me-1'></i>ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class='bx bx-save me-1'></i>บันทึกและไปเพิ่มโจทย์
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include('../../include/alert.php'); ?>
    <?php include('../../include/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>