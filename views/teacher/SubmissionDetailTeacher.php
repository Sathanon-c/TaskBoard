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

        .page-header {
            background: white;
            border-radius: 15px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .assignment-title-bar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
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

        .info-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        .card-header-custom {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .card-header-custom i {
            font-size: 1.5rem;
            color: #0d6efd;
        }

        .card-header-custom h5 {
            margin: 0;
            font-weight: 700;
            color: #2d3748;
        }

        .info-item {
            display: flex;
            align-items: start;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .info-item i {
            color: #64748b;
            font-size: 1.2rem;
            margin-top: 0.1rem;
        }

        .info-label {
            font-weight: 600;
            color: #495057;
            min-width: 120px;
        }

        .info-value {
            color: #64748b;
            flex: 1;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .status-submitted {
            background: #d8ffdeff;
            color: #198754;
        }

        .status-revision {
            background: #fff8e1;
            color: #f59e0b;
        }

        .status-graded {
            background: #e7f1ff;
            color: #0d6efd;
        }

        .download-btn {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
            color: white;
            padding: 1rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 172, 254, 0.4);
            color: white;
        }

        .download-btn i {
            font-size: 1.2rem;
        }

        .form-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label i {
            color: #94a3b8;
            font-size: 1rem;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }

        .form-control::placeholder {
            color: #cbd5e0;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 150px;
        }

        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 1rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, #e0e0e0, transparent);
            margin: 1.5rem 0;
        }

        .file-section {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid #f0f0f0;
        }

        .section-subtitle {
            font-size: 0.95rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-subtitle i {
            color: #0d6efd;
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
    </style>
</head>

<body>
    <?php include('../../include/NavbarTeacher.php'); ?>

    <div class="container mt-4 mb-5 w-75">

        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class='bx bxs-file-find fs-4 text-primary'></i>
                    <h4 class="mb-0 fw-bold">ตรวจงานที่ส่ง : <span class="fs-5 text-primary"><?= $assignment_title ?></span></h4>
                </div>
                <a href="AssignmentDetail.php?assignment_id=<?= htmlspecialchars($assignment_id) ?>"
                    class="action-icon">
                    <i class='bxr  bx-reply-big fs-5'></i>
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Student Work -->
            <div class="col-lg-6">
                <div class="info-card">
                    <div class="card-header-custom">
                        <i class='bx bxs-user-circle'></i>
                        <h5>งานของนักศึกษา</h5>
                    </div>

                    <div class="info-item">
                        <i class='bx bx-user'></i>
                        <div class="flex-grow-1">
                            <div class="info-label"><small>ชื่อนักศึกษา</small></div>
                            <div class="info-value fw-bold text-dark"><?= $student_name ?></div>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class='bx bx-calendar'></i>
                        <div class="flex-grow-1">
                            <div class="info-label"><small>ส่งเมื่อ</small></div>
                            <div class="info-value"><?= date('d M Y H:i', strtotime($submission['submitted_at'])) ?></div>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class='bx bx-check-shield'></i>
                        <div class="flex-grow-1">
                            <div class="info-label mb-2"><small>สถานะ</small></div>
                            <span class="status-badge <?= $submission['status'] === 'Needs Revision' ? 'status-revision' : ($submission['status'] === 'Graded' ? 'status-graded' : 'status-submitted') ?>">
                                <?= htmlspecialchars($submission['status']) ?>
                            </span>
                        </div>
                    </div>

                    <!-- File Download Section -->
                    <div class="file-section">
                        <div class="section-subtitle">
                            <i class='bx bxs-file'></i>
                            <span>ไฟล์งาน</span>
                        </div>
                        <a href="../../<?= htmlspecialchars($file_path) ?>"
                            target="_blank"
                            class="btn download-btn w-100">
                            <i class='bx bxs-download me-2'></i>
                            ดาวโหลดไฟล์งาน
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Column: Feedback Form -->
            <div class="col-lg-6">
                <div class="form-card">
                    <div class="card-header-custom">
                        <i class='bx bxs-edit'></i>
                        <h5>ตอบกลับ & อัพเดตงาน</h5>
                    </div>

                    <form action="../../controllers/TeacherSubmissionController.php" method="POST">
                        <input type="hidden" name="submission_id" value="<?= htmlspecialchars($submission_id) ?>">

                        <div class="mb-4">
                            <label for="new_status" class="form-label">
                                <i class='bx bx-check-circle'></i>
                                <small>อัพเดตงาน</small>
                            </label>
                            <select name="new_status" id="new_status" class="form-select" required>
                                <option value="Submitted" <?= $submission['status'] === 'Submitted' ? 'selected' : '' ?>>
                                    ส่งแล้ว
                                </option>
                                <option value="Needs Revision" <?= $submission['status'] === 'Needs Revision' ? 'selected' : '' ?>>
                                    ส่งกลับไปแก้ไข
                                </option>
                                <option value="Graded" <?= $submission['status'] === 'Graded' ? 'selected' : '' ?>>
                                    ตรวจแล้ว
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="teacher_feedback" class="form-label">
                                <i class='bx bx-message-square-detail'></i>
                                <small>ตอบกลับ</small>
                            </label>
                            <textarea name="teacher_feedback"
                                id="teacher_feedback"
                                rows="8"
                                class="form-control"
                                placeholder="Provide constructive feedback here..."><?= htmlspecialchars($submission['teacher_feedback'] ?? '') ?></textarea>
                            <small class="text-muted mt-2 d-block">
                                <i class='bx bx-info-circle me-1'></i>
                                Provide detailed feedback to help the student improve
                            </small>
                        </div>

                        <button type="submit" class="btn submit-btn w-100">
                            <i class='bx bx-save me-2'></i>
                            บันทึกการอัพเดต
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