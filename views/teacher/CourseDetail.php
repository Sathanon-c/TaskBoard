<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('../../config/Database.php');
include_once('../../models/CourseModel.php');
include_once('../../models/EnrollmentModel.php');
include_once('../../models/AssignmentModel.php');
include_once('../../models/ExamModel.php'); // เพิ่ม ExamModel

$db = (new Database())->getConnection();
$courseModel = new CourseModel($db);
$assignmentModel = new AssignmentModel($db);
$examModel = new ExamModel($db); // สร้าง Instance

$course_id = $_GET['course_id'] ?? null;
if (!$course_id) die("Course not found.");

$course = $courseModel->getCourseById($course_id);
if (!$course) die("Course not found.");

// --- รวมข้อมูล งาน และ ข้อสอบ ---
$combined_list = [];

// 1. ดึงรายการงาน (Assignments)
$raw_assignments = $assignmentModel->getAssignmentsByCourse($course_id);
foreach ($raw_assignments as $as) {
    $as['type'] = 'assignment';
    // เก็บชื่อตัวแปรให้ตรงกันเพื่อใช้ในตาราง
    $as['display_title'] = $as['title']; 
    $as['display_deadline'] = $as['deadline'];
    $combined_list[] = $as;
}

// 2. ดึงรายการข้อสอบ (Exams)
$raw_exams = $examModel->getExamsByCourse($course_id);
foreach ($raw_exams as $ex) {
    $ex['type'] = 'exam';
    $ex['display_title'] = "[EXAM] " . $ex['title'];
    // ข้อสอบอาจจะใช้วันที่สร้าง หรือคุณจะเพิ่มฟิลด์ deadline ในอนาคตก็ได้
    $ex['display_deadline'] = $ex['created_at']; 
    $combined_list[] = $ex;
}

// 3. นำข้อมูลที่รวมแล้วไปใช้งานแทนตัวแปรเดิม
$assignments = $combined_list;

$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($course['course_name']) ?> - TaskBoard</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Boxicons -->
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <!-- Summer note -->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>

    <style>
        body {
            background: linear-gradient(135deg, #f5f0ff 0%, #e8dcff 50%, #ddc3ff 100%);
            min-height: 100vh;
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

        .course-info-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .action-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: #f8f9fa;
            color: #495057;
            text-decoration: none;
        }

        .action-icon:hover {
            background: #e7f1ff;
            color: #0d6efd;
            transform: scale(1.1);
        }

        .action-icon-danger:hover {
            background: #ffe7e7ff;
            color: #fd0d0dff;
        }

        .info-row {
            margin-bottom: 1rem;
            display: flex;
            align-items: start;
            gap: 0.5rem;
        }

        .info-label {
            font-weight: 600;
            color: #495057;
            min-width: 100px;
        }

        .info-value {
            color: #64748b;
            flex: 1;
        }

        .level-badge {
            display: inline-block;
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            background: #f2f2f2;
            color: #495057;
            border: 1px solid #e0e0e0;
        }

        .assignment-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .section-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
            padding: 0.75rem 1rem;
            white-space: nowrap;
            background: #f8f9fa;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
            transform: scale(1.005);
        }

        .table tbody td {
            vertical-align: middle;
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .table-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            transition: all 0.3s ease;
            color: #495057;
            text-decoration: none;
        }

        .table-icon:hover {
            background: #e7f1ff;
            color: #0d6efd;
            transform: scale(1.1);
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

        .submission-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.3rem 0.7rem;
            background: #f2f2f2;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 0.8rem;
            color: #64748b;
        }
    </style>
</head>

<body>
    <?php include('../../include/NavbarTeacher.php'); ?>

    <div class="container mt-4 mb-5 w-75">

        <!-- Course Info Card -->
        <div class="course-info-card">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="flex-grow-1">
                    <div class="mb-3 d-flex align-items-end justify-content-start">
                        <h2 class="text-primary "><?= htmlspecialchars($course['course_code']) ?></h2>
                        <h2 class="course-title ms-3 fw-bold"><?= htmlspecialchars($course['course_name']) ?></h2>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="CourseManager.php" class="action-icon">
                        <i class='bxr  bx-reply-big fs-5'></i>
                    </a>
                    <a href="CourseStudentManager.php?course_id=<?= $course['course_id'] ?>"
                        class="action-icon" title="Manage Students">
                        <i class='bx bxs-group fs-5'></i>
                    </a>
                    <a href="EditCourse.php?course_id=<?= htmlspecialchars($course_id) ?>"
                        class="action-icon" title="Edit Course">
                        <i class='bx bx-cog fs-5'></i>
                    </a>
                    <a href="../../controllers/DeleteCourseController.php?action=delete&course_id=<?= htmlspecialchars($course_id) ?>"
                        class="action-icon action-icon-danger"
                        title="Delete Course"
                        onclick="return confirm('Are you sure you want to delete this course? This action cannot be undone.');">
                        <i class='bx bxs-trash fs-5'></i>
                    </a>
                </div>
            </div>

            <div class="info-row">
                <span class="info-label">ปีการศึกษา:</span>
                <span class="info-value"><small><?= htmlspecialchars($course['level']) ?></small></span>
            </div>

            <div class="info-row">
                <span class="info-label">ห้องเรียน:</span>
                <span class="info-value"><small><?= htmlspecialchars($course['class_id']) ?></small></span>
            </div>

            <div class="info-row">
                <span class="info-label">รายละเอียดเพิ่มเติม:</span>
                <span class="info-value"><small><?= ($course['course_detail']) ?></small></span>
            </div>

            <div class="info-row">
                <span class="info-label">สร้างโดย:</span>
                <span class="info-value"><small><?= htmlspecialchars($course['teacher_first']) ?> <?= htmlspecialchars($course['teacher_last']) ?></small></span>
            </div>
        </div>

        <!-- Assignments Section -->
        <div class="assignment-section">
            <div class="section-header">
                <div class="d-flex align-items-center gap-3 flex-grow-1">
                    <i class='bxr  bx-clipboard fs-3 text-primary'></i>
                    <div>
                        <h5 class="mb-0 fw-bold">จัดการงาน</h5>
                        <small class="text-muted">จัดการงานและการส่งงาน</small>
                    </div>
                    <span class="badge bg-primary ms-2"><?= count($assignments) ?></span>
                </div>

                <div class="d-flex align-items-end justify-content-end gap-3">


                    <a href="ExamManager.php?course_id=<?= htmlspecialchars($course_id) ?>"
                        class="btn btn-primary px-4">
                        <small>จัดการข้อสอบ</small>
                    </a>

                    <a href="CreateAssignment.php?course_id=<?= htmlspecialchars($course_id) ?>"
                        class="btn btn-success px-4">
                        <small>เพิ่มงาน</small>
                    </a>
                    
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><small>ชื่องาน</small></th>
                            <th><small>ชื่อรายวิชา</small></th>
                            <th><small>กำหนดส่ง</small></th>
                            <th style="width: 100px;" class="text-center"><small>จัดการ</small></th>
                        </tr>
                    </thead>
                    <tbody>
    <?php if (empty($assignments)): ?>
        <tr>
            <td colspan="4">
                <div class="empty-state">
                    <i class='bx bx-file'></i>
                    <p class="text-muted mb-0 fw-semibold">ไม่พบงานหรือข้อสอบ</p>
                    <small class="text-muted">สร้างงานหรือข้อสอบเพื่อเริ่มจัดการรายวิชา</small>
                </div>
            </td>
        </tr>
    <?php else: ?>
        <?php foreach ($assignments as $item): 
            $is_exam = ($item['type'] === 'exam');
        ?>
            <tr>
                <td>
                    <div class="fw-bold text-dark d-flex align-items-center">
                        <?php if($is_exam): ?>
                            <small class="text-primary">[EXAM]</small>
                        <?php else: ?>
                            <i class='bx bx-file me-2 text-secondary' title="งาน"></i>
                        <?php endif; ?>
                        
                        <small class="ms-1"><?= htmlspecialchars($item['title']) ?></small>
                    </div>
                </td>
                <td>
                    <span class="badge bg-primary bg-opacity-10 text-light border">
                        <small><?= htmlspecialchars($course['course_code']) ?></small>
                    </span>
                </td>
                <td class="text-muted">
                    <small>
                        <i class='bx bx-calendar me-1'></i>
                        <?= htmlspecialchars($item['deadline'] ?? '-') ?>
                    </small>
                </td>
                <td class="text-center">
                    <?php if($is_exam): ?>
                        <a href="ExamResults.php?exam_id=<?= $item['exam_id'] ?>&course_id=<?= $course_id ?>"
                            class="btn btn-sm btn-primary">
                            <small>ผลสอบ</small>
                        </a>
                    <?php else: ?>
                        <a href="AssignmentDetail.php?assignment_id=<?= htmlspecialchars($item['assignment_id']) ?>"
                            class="btn btn-sm btn-primary">
                            <small>เพิ่มเติม</small>
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</tbody>
                </table>
            </div>
        </div>

    </div>

    <?php include_once('../../include/alert.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Summer note -->
    <script>
        $('#summernote').summernote({
            placeholder: 'Hello stand alone ui',
            tabsize: 2,
            height: 120,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    </script>
</body>

</html>