<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// 1. ตรวจสอบสิทธิ์
if ($_SESSION['role'] !== 'teacher' || !isset($_GET['submission_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// 2. INCLUDE MODELS & DATABASE
include_once('../../config/Database.php');
include_once('../../models/SubmissionModel.php');
include_once('../../models/TeacherModel.php');

$db = (new Database())->getConnection();
$submissionModel = new SubmissionModel($db);
$teacherModel = new TeacherModel($db);

$submission_id = (int)$_GET['submission_id'];
$user_id = $_SESSION['user_id'];

// 3. ดึง Teacher ID
$current_teacher_id = $teacherModel->getTeacherIdByUserId($user_id);

// 4. ดึงข้อมูล Submission Detail
$submission = $submissionModel->getSubmissionDetailById($submission_id);

if (!$submission) {
    $_SESSION['error'] = "Submission not found.";
    header("Location: CourseManager.php");
    exit;
}

$assignment_id = $submission['assignment_id'];
$student_name = htmlspecialchars($submission['student_first'] . ' ' . $submission['student_last']);
$assignment_title = htmlspecialchars($submission['assignment_title']);
$file_path = $submission['file_path'];
$file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Submission - <?= $assignment_title ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Boxicons -->
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>

    <style>
        body {
            background: linear-gradient(135deg, #f5f0ff 0%, #e8dcff 50%, #ddc3ff 100%);
            min-height: 100vh;
        }
        .page-header, .info-card, .form-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .assignment-title-bar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
        }
        .card-header-custom {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 1rem;
        }
        .info-item {
            display: flex;
            gap: .75rem;
            padding: .75rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .status-badge {
            padding: .4rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: .85rem;
        }
        .status-submitted { background: #d8ffdeff; color: #198754; }
        .status-revision { background: #fff8e1; color: #f59e0b; }
        .status-graded { background: #e7f1ff; color: #0d6efd; }
        .download-btn {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
            color: white;
            padding: 1rem;
            border-radius: 10px;
            font-weight: 600;
        }
        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 1rem;
            border-radius: 10px;
            font-weight: 600;
        }
    </style>
</head>

<body>
<?php include('../../include/NavbarTeacher.php'); ?>

<div class="container mt-4 mb-5 w-75">

    <div class="page-header mb-4 d-flex justify-content-between align-items-center">
        <h4 class="fw-bold mb-0">
            <i class='bx bxs-file-find text-primary'></i>
            ตรวจงานที่ส่ง : <?= $assignment_title ?>
        </h4>
        <a href="AssignmentDetail.php?assignment_id=<?= htmlspecialchars($assignment_id) ?>" class="btn btn-light">
            <i class='bx bx-arrow-back'></i>
        </a>
    </div>

    <div class="row g-4">
        <!-- LEFT -->
        <div class="col-lg-6">
            <div class="info-card">
                <div class="card-header-custom">
                    <i class='bx bxs-user-circle fs-4 text-primary'></i>
                    <h5 class="mb-0">งานของนักศึกษา</h5>
                </div>

                <div class="info-item">
                    <strong>ชื่อ:</strong> <?= $student_name ?>
                </div>

                <div class="info-item">
                    <strong>ส่งเมื่อ:</strong>
                    <?= date('d M Y H:i', strtotime($submission['submitted_at'])) ?>
                </div>

                <div class="info-item">
                    <strong>สถานะ:</strong>
                    <span class="status-badge
                        <?= $submission['status'] === 'Needs Revision' ? 'status-revision' :
                        ($submission['status'] === 'Graded' ? 'status-graded' : 'status-submitted') ?>">
                        <?= htmlspecialchars($submission['status']) ?>
                    </span>
                </div>

                <hr>

                <!-- FILE SECTION -->
                <?php if ($file_ext === 'pdf'): ?>
                    <h6 class="fw-bold mb-2">ไฟล์งาน (Preview)</h6>
                    <div class="ratio ratio-4x3 mb-3">
                        <iframe src="../../<?= htmlspecialchars($file_path) ?>"
                                title="PDF Preview">
                        </iframe>
                    </div>
                <?php endif; ?>

                <a href="../../<?= htmlspecialchars($file_path) ?>"
                   target="_blank"
                   class="btn download-btn w-100">
                    <i class='bx bxs-download me-2'></i>
                    ดาวน์โหลดไฟล์งาน
                </a>
            </div>
        </div>

        <!-- RIGHT -->
        <div class="col-lg-6">
            <div class="form-card">
                <div class="card-header-custom">
                    <i class='bx bxs-edit fs-4 text-primary'></i>
                    <h5 class="mb-0">ตอบกลับ & อัพเดตงาน</h5>
                </div>

                <form action="../../controllers/TeacherSubmissionController.php" method="POST">
                    <input type="hidden" name="submission_id" value="<?= htmlspecialchars($submission_id) ?>">

                    <div class="mb-3">
                        <label class="form-label">สถานะ</label>
                        <select name="new_status" class="form-select" required>
                            <option value="Submitted" <?= $submission['status'] === 'Submitted' ? 'selected' : '' ?>>ส่งแล้ว</option>
                            <option value="Needs Revision" <?= $submission['status'] === 'Needs Revision' ? 'selected' : '' ?>>ให้แก้ไข</option>
                            <option value="Graded" <?= $submission['status'] === 'Graded' ? 'selected' : '' ?>>ตรวจแล้ว</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">คะแนน (เต็ม <?= $submission['max_score'] ?? 0 ?>)</label>
                        <input type="number" name="score" class="form-control"
                               step="0.01" min="0"
                               max="<?= $submission['max_score'] ?? 0 ?>"
                               value="<?= $submission['score'] ?? '' ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">ความคิดเห็น</label>
                        <textarea name="teacher_feedback" class="form-control" rows="6"><?= htmlspecialchars($submission['teacher_feedback'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn submit-btn w-100">
                        <i class='bx bx-save me-2'></i>บันทึกการอัพเดต
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<?php include_once('../../include/alert.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
