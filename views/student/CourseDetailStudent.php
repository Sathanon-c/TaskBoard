<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. INCLUDE MODELS & DATABASE
include_once('../../config/Database.php');
include_once('../../models/StudentModel.php');
include_once('../../models/EnrollmentModel.php');
include_once('../../models/CourseModel.php');
include_once('../../models/AssignmentModel.php');
include_once('../../models/SubmissionModel.php');

$db = (new Database())->getConnection();
$studentModel = new StudentModel($db);
$enrollmentModel = new EnrollmentModel($db);
$courseModel = new CourseModel($db);
$assignmentModel = new AssignmentModel($db);
$submissionModel = new SubmissionModel($db);

$user_id = $_SESSION['user_id'];
$course_id = (int)$_GET['id'];

// 3. ดึง Student ID
$student_profile = $studentModel->getStudentProfileByUserId($user_id);

if (!$student_profile) {
    $_SESSION['error'] = "Student profile data not found.";
    header("Location: MyCourse.php");
    exit;
}
$student_id = $student_profile['student_id'];

// 5. ดึงข้อมูลคอร์สหลัก
$course = $courseModel->getCourseById($course_id);

if (!$course) {
    $_SESSION['error'] = "Course not found.";
    header("Location: MyCourse.php");
    exit;
}

// 6. ดึงรายการงาน (Assignments)
$assignments = $assignmentModel->getAssignmentsByCourse($course_id);

// 7. Loop เพื่อดึงสถานะการส่งงานและผนวกเข้ากับรายการ Assignments
foreach ($assignments as $key => $assignment) {
    $submission = $submissionModel->getSubmissionStatus($assignment['assignment_id'], $student_id);
    $assignments[$key]['submission_status'] = $submission['status'] ?? 'Pending';
    $assignments[$key]['feedback'] = $submission['teacher_feedback'] ?? null;
}

// คำนวณสถิติ
$total_assignments = count($assignments);
$submitted_count = count(array_filter($assignments, fn($a) => $a['submission_status'] === 'Submitted'));
$pending_count = count(array_filter($assignments, fn($a) => $a['submission_status'] === 'Pending'));
$overdue_count = count(array_filter($assignments, fn($a) => $a['submission_status'] === 'Overdue'));
$graded_count = count(array_filter($assignments, fn($a) => $a['submission_status'] === 'Graded'));

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

        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 0;
        }

        .breadcrumb-item a {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb-item a:hover {
            text-decoration: underline;
        }

        .breadcrumb-item.active {
            color: #6c757d;
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

        .course-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .course-header {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .course-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .course-code {
            display: inline-block;
            background: #e7f1ff;
            color: #0d6efd;
            padding: 0.35rem 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            margin-left: 0.5rem;
        }

        .teacher-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
            font-size: 0.95rem;
        }

        .teacher-info i {
            color: #94a3b8;
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

        .stat-icon-submitted {
            background: #d1f2ff;
            color: #0dcaf0;
        }

        .stat-icon-pending {
            background: #fff8e1;
            color: #ffc107;
        }

        .stat-icon-overdue {
            background: #ffe5e5;
            color: #dc3545;
        }

        .stat-icon-graded {
            background: #d8ffdeff;
            color: #198754;
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

        .assignment-section {
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

        .feedback-icon {
            color: #ffc107;
            font-size: 1.2rem;
            margin-left: 0.5rem;
            vertical-align: middle;
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
    </style>
</head>

<body>
    <?php include('../../include/NavbarStudent.php'); ?>

    <div class="container mt-4 mb-5 w-75">

        <!-- Course Info Card -->
        <div class="course-card">
            <div class="course-header">
                <h1 class="course-title">
                    <?= htmlspecialchars($course['course_name']) ?>
                    <span class="course-code"><?= htmlspecialchars($course['course_code']) ?></span>
                </h1>
                <div class="teacher-info">
                    <i class='bx bx-user'></i>
                    <span>สร้างโดย : <strong><?= htmlspecialchars($course['teacher_first']) ?> <?= htmlspecialchars($course['teacher_last']) ?></strong></span>
                </div>
            </div>
            <p class="text-muted mb-0">
                <?= nl2br(htmlspecialchars($course['course_detail'])) ?>
            </p>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-total">
                        <i class='bxr  bxs-file-detail'></i>
                    </div>
                    <div class="stat-number"><?= $total_assignments ?></div>
                    <div class="stat-label">ทั้งหมด</div>
                </div>
            </div>

            <div class="col">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-submitted">
                        <i class='bxr  bxs-folder-up-arrow'></i>
                    </div>
                    <div class="stat-number"><?= $submitted_count ?></div>
                    <div class="stat-label">ส่งแล้ว</div>
                </div>
            </div>

            <div class="col">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-pending">
                        <i class='bxr  bxs-stopwatch'></i>
                    </div>
                    <div class="stat-number"><?= $pending_count ?></div>
                    <div class="stat-label">ยังไม่ได้ส่ง</div>
                </div>
            </div>

            <div class="col">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-overdue">
                        <i class='bxr  bxs-file-x'></i>
                    </div>
                    <div class="stat-number"><?= $overdue_count ?></div>
                    <div class="stat-label">เกินกำหนด</div>
                </div>
            </div>

            <div class="col">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-graded">
                        <i class='bx bxs-check-circle'></i>
                    </div>
                    <div class="stat-number"><?= $graded_count ?></div>
                    <div class="stat-label">ตรวจแล้ว</div>
                </div>
            </div>
        </div>

        <!-- Assignments Section -->
        <div class="assignment-section">
            <div class="section-header">
                <div class="d-flex align-items-center gap-3">
                    <i class='bx bxs-file-archive fs-3 text-primary'></i>
                    <div>
                        <h5 class="mb-0 fw-bold">งานทั้งหมด</h5>
                        <small class="text-muted">จัดการและส่งงานของคุณ</small>
                    </div>
                    <span class="badge bg-primary"><?= $total_assignments ?></span>
                </div>
            </div>

            <?php if (empty($assignments)): ?>
                <div class="empty-state">
                    <i class='bx bx-file'></i>
                    <p class="text-muted mb-0 fw-semibold">ยังไม่มีงานตอนนี้</p>
                    <small class="text-muted">ไม่มีงานในรายวิชานี้</small>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><small>ชื่องาน</small></th>
                                <th style="width: 150px;"><small>กำหนดส่ง</small></th>
                                <th style="width: 150px;"><small>สถานะ</small></th>
                                <th style="width: 150px;" class="text-center"><small>จัดการ</small></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $assignment):
                                $status = $assignment['submission_status'];
                                $status_class = match ($status) {
                                    'Submitted' => 'bg-success text-light',
                                    'Needs Revision' => 'bg-warning text-light',
                                    'Graded' => 'bg-success text-light',
                                    default => 'bg-danger',
                                };
                            ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">
                                            <small>
                                                <?= htmlspecialchars($assignment['title']) ?>
                                                <?php if (!empty($assignment['feedback'])): ?>
                                                    <i class='bx bxs-message-dots feedback-icon' title="Teacher Feedback Available"></i>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td class="text-muted">
                                        <small>
                                            <i class='bx bx-calendar me-1'></i>
                                            <?= date('d M Y', strtotime($assignment['deadline'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge <?= $status_class ?>">
                                            <small><?= htmlspecialchars($status) ?></small>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="AssignmentDetailStudent.php?assignment_id=<?= htmlspecialchars($assignment['assignment_id']) ?>"
                                            class="btn btn-sm btn-primary">
                                            <small><i class='bx bx-show me-1'></i>View</small>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include('../../include/alert.php'); ?>
    <?php include('../../include/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html