<?php
session_start();

// 1. ตรวจสอบสิทธิ์และรับ assignment_id
if ($_SESSION['role'] !== 'teacher' || !isset($_GET['assignment_id'])) {
    header("Location: ../views/auth/login.php");
    exit;
}

// 2. INCLUDE MODELS & DATABASE โดยตรง
include_once('../../config/Database.php');
include_once('../../models/AssignmentModel.php');
include_once('../../models/CourseModel.php');
include_once('../../models/TeacherModel.php');
include_once('../../models/SubmissionModel.php');

$db = (new Database())->getConnection();
$assignmentModel = new AssignmentModel($db);
$courseModel = new CourseModel($db);
$teacherModel = new TeacherModel($db);
$submissionModel = new SubmissionModel($db);

$assignment_id = (int)$_GET['assignment_id'];
$user_id = $_SESSION['user_id'];

// 3. ดึง Teacher ID ที่ถูกต้องจาก User ID
$current_teacher_id = $teacherModel->getTeacherIdByUserId($user_id);

if (!$current_teacher_id) {
    $_SESSION['error'] = "Teacher profile not found. Please log in again.";
    header("Location: CourseManager.php");
    exit;
}

// 4. ดึงข้อมูล Assignment
$assignment = $assignmentModel->getAssignmentById($assignment_id);

if (!$assignment) {
    $_SESSION['error'] = "Assignment not found.";
    header("Location: CourseManager.php");
    exit;
}

// 5. ดึงข้อมูล Course
$course_id = $assignment['course_id'];
$course = $courseModel->getCourseById($course_id);

// 6. Security Check
if (!$course || $course['teacher_id'] !== $current_teacher_id) {
    $_SESSION['error'] = "You do not have permission to view this assignment.";
    header("Location: CourseManager.php");
    exit;
}

$student_submissions = $submissionModel->getStudentsAndSubmissionStatus($assignment_id, $course_id);

// คำนวณสถิติ
$total_students = count($student_submissions);
$submitted_count = count(array_filter($student_submissions, fn($s) => !empty($s['submission_id'])));
$pending_count = $total_students - $submitted_count;
$submission_rate = $total_students > 0 ? round(($submitted_count / $total_students) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($assignment['title']) ?> - TaskBoard</title>

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

        .assignment-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .assignment-header {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .assignment-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2d3748;
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
            min-width: 120px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-label i {
            color: #94a3b8;
        }

        .info-value {
            color: #64748b;
            flex: 1;
        }

        .course-link {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .course-link:hover {
            color: #0a58ca;
            text-decoration: underline;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 2px solid #f0f0f0;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
        }

        .stat-icon-total {
            background: #e7f1ff;
            color: #0d6efd;
        }

        .stat-icon-submitted {
            background: #d8ffdeff;
            color: #198754;
        }

        .stat-icon-pending {
            background: #fff8e1;
            color: #ffc107;
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

        .submission-section {
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

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
            padding: 1rem;
            white-space: nowrap;
            background: #f8f9fa;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
            transform: scale(1.002);
        }

        .table tbody td {
            vertical-align: middle;
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
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

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .delete-form {
            display: inline;
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
    </style>
</head>

<body>
    <?php include('../../include/NavbarTeacher.php'); ?>

    <div class="container mt-4 mb-5 w-75">

        <!-- Assignment Info Card -->
        <div class="assignment-card">
            <div class="assignment-header">
                <div class="d-flex justify-content-between align-items-start">
                    <h2 class="assignment-title"><?= htmlspecialchars($assignment['title']) ?></h2>
                    <div class="action-buttons">
                        <a href="CourseDetail.php?course_id=<?= htmlspecialchars($course['course_id']) ?>" class="action-icon">
                            <i class='bxr  bx-reply-big fs-5'></i>
                        </a>
                        <a href="EditAssignment.php?assignment_id=<?= htmlspecialchars($assignment['assignment_id']) ?>"
                            class="action-icon">
                            <i class='bx bx-cog fs-5'></i>
                        </a>
                        <form action="../../controllers/DeleteAssignmentController.php" method="POST" class="delete-form"
                            onsubmit="return confirm('Are you sure you want to delete this assignment?');">
                            <input type="hidden" name="assignment_id" value="<?= htmlspecialchars($assignment['assignment_id']) ?>">
                            <input type="hidden" name="course_id" value="<?= htmlspecialchars($course['course_id']) ?>">
                            <button type="submit" class="border-0 action-icon action-icon-danger">
                                <i class='bx bx-trash fs-5'></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="info-row">
                <span class="info-label">
                    <small>รายวิชา:</small>
                </span>
                <span class="info-value">
                    <a href="CourseDetail.php?course_id=<?= htmlspecialchars($course['course_id']) ?>" class="course-link">
                        <small><?= htmlspecialchars($course['course_code']) ?> - <?= htmlspecialchars($course['course_name']) ?></small>
                    </a>
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">
                    <small>รายละเอียดเพิ่มเติม:</small>
                </span>
                <span class="info-value">
                    <small><?= nl2br(htmlspecialchars($assignment['description'])) ?: '-' ?></small>
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">
                    <small>กำหนดส่ง:</small>
                </span>
                <span class="info-value">
                    <small><?= $assignment['deadline'] ? date('d M Y', strtotime($assignment['deadline'])) : 'No deadline set' ?></small>
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">
                    <small>สร้างโดย:</small>
                </span>
                <span class="info-value">
                    <small>-</small>
                </span>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-total">
                        <i class='bx bxs-group'></i>
                    </div>
                    <div class="stat-number"><?= $total_students ?></div>
                    <div class="stat-label">นักศึกษาทั้งหมด</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-submitted">
                        <i class='bx bxs-check-circle'></i>
                    </div>
                    <div class="stat-number"><?= $submitted_count ?></div>
                    <div class="stat-label">ส่งแล้ว (<?= $submission_rate ?>%)</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-pending">
                        <i class='bxr  bx-stopwatch'></i> 
                    </div>
                    <div class="stat-number"><?= $pending_count ?></div>
                    <div class="stat-label">รอการส่ง</div>
                </div>
            </div>
        </div>

        <!-- Submissions Section -->
        <div class="submission-section">
            <div class="section-header">
                <div class="d-flex align-items-center gap-3">
                    <i class='bx bxs-file fs-3 text-primary'></i>
                    <div>
                        <h5 class="mb-0 fw-bold">งานของนักศึกษา</h5>
                        <small class="text-muted">ติดตามและจัดการงานของนักศึกษา</small>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><small>ชื่อนักศึกษา</small></th>
                            <th><small>ส่งเมื่อ</small></th>
                            <th style="width: 150px;"><small>สถานะ</small></th>
                            <th style="width: 150px;" class="text-center"><small>จัดการ</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($student_submissions)): ?>
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <i class='bx bx-user-x'></i>
                                        <p class="text-muted mb-0 fw-semibold">ไม่มีนักศ฿กษาในรายวิชา</p>
                                        <small class="text-muted">ยังไม่มีนักศึกษาในรายวิชาตอนนี้</small>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($student_submissions as $student):
                                $status = $student['status'] ?? 'Pending';
                                $status_class = match ($status) {
                                    'Submitted' => 'bg-success',
                                    'Needs Revision' => 'bg-warning text-dark',
                                    'Graded' => 'bg-primary',
                                    default => 'bg-danger',
                                };
                            ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">
                                            <small><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></small>
                                        </div>
                                    </td>
                                    <td class="text-muted">
                                        <small>
                                            <?php if ($student['submitted_at']): ?>
                                                <i class='bx bx-calendar me-1'></i>
                                                <?= date('d M Y H:i', strtotime($student['submitted_at'])) ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge <?= $status_class ?>">
                                            <small><?= htmlspecialchars($status) ?></small>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($student['submission_id']): ?>
                                            <a href="SubmissionDetailTeacher.php?submission_id=<?= htmlspecialchars($student['submission_id']) ?>"
                                                class="btn btn-sm btn-primary">
                                                <small>เพิ่มเติม</small>
                                            </a>
                                        <?php else: ?>
                                            <small class="text-muted">N/A</small>
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
                    <?php include_once('../../include/alert.php');?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>