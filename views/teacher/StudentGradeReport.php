<?php
session_start();

// 1. ตรวจสอบสิทธิ์
if ($_SESSION['role'] !== 'teacher' || !isset($_GET['course_id']) || !isset($_GET['student_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

include_once('../../config/Database.php');
include_once('../../models/CourseModel.php');
include_once('../../models/StudentModel.php');
include_once('../../models/SubmissionModel.php');
include_once('../../models/AssignmentModel.php');
include_once('../../models/ExamModel.php'); // เพิ่ม ExamModel

$db = (new Database())->getConnection();
$courseModel = new CourseModel($db);
$studentModel = new StudentModel($db);
$submissionModel = new SubmissionModel($db);
$examModel = new ExamModel($db); // สร้าง Instance

$course_id = (int)$_GET['course_id'];
$student_id = (int)$_GET['student_id'];

// ดึงข้อมูลพื้นฐาน
$course = $courseModel->getCourseById($course_id);
$student = $studentModel->getStudentById($student_id);

if (!$course || !$student) {
    die("Data not found.");
}

// --- ดึงข้อมูลคะแนนจาก 2 แหล่ง ---

// 1. ดึงข้อมูลคะแนนงาน (Assignments)
$assignment_grades = $submissionModel->getStudentGradesByCourse($course_id, $student_id);

// 2. ดึงข้อมูลคะแนนข้อสอบ (Exams)
$all_exams = $examModel->getExamsByCourse($course_id);
$exam_grades = [];

foreach ($all_exams as $ex) {
    $result = $examModel->getStudentResult($ex['exam_id'], $student_id);
    
    // แปลงรูปแบบ Exam ให้เหมือนกับ Assignment เพื่อใช้ในตารางเดียวกัน
    $exam_grades[] = [
        'assignment_title' => "[EXAM] " . $ex['title'],
        'student_score' => $result ? $result['score_obtained'] : null,
        'max_score' => $examModel->getTotalExamScore($ex['exam_id']), // ฟังก์ชันนี้ต้องมีใน ExamModel
        'status' => $result ? 'Graded' : 'Pending',
        'submitted_at' => $result ? $result['completed_at'] : null,
        'is_exam' => true // มาร์คไว้แยกประเภทใน HTML
    ];
}

// 3. รวมรายการทั้งหมดเข้าด้วยกัน
$grades = array_merge($assignment_grades, $exam_grades);

// --- คำนวณภาพรวมใหม่ (Dashboard Stats) ---
$total_max_score = 0;
$total_earned_score = 0;
$completed_tasks = 0;
$total_tasks = count($grades);

foreach ($grades as $g) {
    $total_max_score += (float)$g['max_score'];
    $total_earned_score += (float)($g['student_score'] ?? 0);
    
    // นับงาน/สอบที่ทำเสร็จแล้ว
    if ($g['status'] === 'Graded' || $g['status'] === 'Submitted') {
        $completed_tasks++;
    }
}

$progress_percent = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Report - <?= htmlspecialchars($student['first_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .info-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .stat-box {
            text-align: center;
            padding: 1.5rem;
            border-radius: 12px;
            background: #f8f9fa;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #764ba2;
        }

        .table-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .action-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #f8f9fa;
            color: #495057;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .badge-status {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
        }

        .action-icon:hover {
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
    </style>
</head>

<body>
    <?php include('../../include/NavbarTeacher.php'); ?>

    <div class="container mt-4 mb-5 w-75">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1 fw-bold">รายงานคะแนนรายบุคคล</h4>
                <p class="text-muted mb-0"><?= htmlspecialchars($course['course_code']) ?>  <?= htmlspecialchars($course['course_name']) ?></p>
            </div>
            <a href="CourseStudentManager.php?course_id=<?= $course_id ?>" class="action-icon">
                <i class='bxr  bx-reply-big fs-5'></i>
            </a>

        </div>

        <div class="info-card">
            <div class="row align-items-center">
                <div class="col-md-6 border-end">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.5rem;">
                            <i class='bx bxs-user'></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></h5>
                            <small class="text-muted">รหัสนักศึกษา: <?= htmlspecialchars($student['student_code']) ?></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 px-4">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-number"><?= $completed_tasks ?>/<?= $total_tasks ?></div>
                            <small class="text-muted">งานที่ส่ง</small>
                        </div>
                        <div class="col-4 border-start border-end">
                            <div class="stat-number text-success"><?= (float)$total_earned_score ?></div>
                            <small class="text-muted">คะแนนรวม</small>
                        </div>
                        <div class="col-4">
                            <div class="stat-number text-primary"><?= $progress_percent ?>%</div>
                            <small class="text-muted">ความคืบหน้า</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-section">
            <h5 class="fw-bold mb-4"><i class='bx bx-list-ul me-2 text-primary'></i>รายละเอียดคะแนนรายชิ้น</h5>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><small>ชื่องาน</small></th>
                            <th style="width: 250px;" class="text-center"><small>คะแนนที่ได้</small></th>
                            <th style="width: 200px;" class="text-center"><small>สถานะ</small></th>
                            <th style="width: 200px;" class="text-center"><small>วันที่ส่ง</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($grades)): ?>
                        <?php else: ?>
                            <?php foreach ($grades as $g):
                            $is_exam = isset($g['is_exam']);
                                $status = $g['status'] ?? 'Pending';
                                $badge_class = match ($status) {
                                    'Graded' => 'bg-success',
                                    'Submitted' => 'bg-info',
                                    'Needs Revision' => 'bg-warning text-dark',
                                    default => 'bg-secondary'
                                };
                            ?>
                                <tr>
<td>
        <div class="fw-bold <?= $is_exam ? 'text-primary' : 'text-dark' ?>">
            <small>
                <?php if($is_exam): ?>
                    <i class='bx bx-edit-alt me-1'></i>
                <?php endif; ?>
                <?= htmlspecialchars($g['assignment_title']) ?>
            </small>
        </div>
    </td>

                                    <td class="text-center">
                                        <span class="student-code-badge">
                                            <small><?= $g['student_score'] !== null ? (float)$g['student_score'] : '-' ?></small>
                                        </span>
                                        <small class="text-muted">/ <?= $g['max_score'] ?></small>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge badge-status <?= $badge_class ?>">
                                            <small><?= $status ?></small>
                                        </span>
                                    </td>

                                    <td class="text-center text-muted">
                                        <small>
                                            <i class='bx bx-calendar me-1'></i>
                                            <?= $g['submitted_at'] ? date('d M Y', strtotime($g['submitted_at'])) : '-' ?>
                                        </small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>