<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. ตรวจสอบสิทธิ์
if ($_SESSION['role'] !== 'student' || !isset($_GET['assignment_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

include_once('../../config/Database.php');
include_once('../../models/StudentModel.php');
include_once('../../models/EnrollmentModel.php');
include_once('../../models/AssignmentModel.php');
include_once('../../models/SubmissionModel.php');

$db = (new Database())->getConnection();
$studentModel = new StudentModel($db);
$enrollmentModel = new EnrollmentModel($db);
$assignmentModel = new AssignmentModel($db);
$submissionModel = new SubmissionModel($db);

$user_id = $_SESSION['user_id'];
$assignment_id = (int)$_GET['assignment_id'];

// 3. ดึง Student ID
$student_profile = $studentModel->getStudentProfileByUserId($user_id);

if (!$student_profile) {
    $_SESSION['error'] = "Student profile data not found.";
    header("Location: Dashboard.php");
    exit;
}
$student_id = $student_profile['student_id'];

// 4. ดึงรายละเอียด Assignment
$assignment = $assignmentModel->getAssignmentById($assignment_id);

if (!$assignment) {
    $_SESSION['error'] = "Assignment not found.";
    header("Location: MyCourse.php");
    exit;
}
$course_id = $assignment['course_id'];

// 5. ดึงสถานะการส่งงานล่าสุด
$submission = $submissionModel->getSubmissionStatus($assignment_id, $student_id);

// 6. กำหนดตัวแปรสถานะ
$is_submitted = (bool)$submission;
$submission_status = $submission['status'] ?? 'Pending';
$submission_file_path = $submission['file_path'] ?? null;
$submitted_time = $submission['submitted_at'] ?? null;
$teacher_feedback = $submission['teacher_feedback'] ?? null;

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
        <!-- Summer note -->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>

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
            margin-bottom: 0.5rem;
        }

        .meta-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .meta-info i {
            color: #94a3b8;
        }

        .description-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 1rem;
        }

        .description-title {
            font-weight: 600;
            color: #495057;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .description-title i {
            color: #0d6efd;
        }

        .feedback-card {
            background: white;
            border: 2px solid #ffc107;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(255, 193, 7, 0.1);
        }

        .feedback-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #f59e0b;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .feedback-header i {
            font-size: 1.5rem;
        }

        .feedback-content {
            background: #fff8e1;
            border-radius: 10px;
            padding: 1.25rem;
            color: #2d3748;
        }

        .sidebar-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 20px;
        }

        .card-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .card-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .section-title {
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title .bxs-info-circle{
            color: #0d6efd;
            font-size: 1.3rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-label {
            font-weight: 500;
            color: #64748b;
            font-size: 0.9rem;
        }

        .info-value {
            font-weight: 600;
            color: #2d3748;
            font-size: 0.9rem;
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

        .status-pending {
            background: #ffe5e5;
            color: #dc3545;
        }

        .status-revision {
            background: #fff8e1;
            color: #f59e0b;
        }

        .submission-info {
            background: #e7f1ff;
            border: 1px solid #b3d7ff;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #0d6efd;
            font-size: 0.85rem;
        }

        .submission-info i {
            font-size: 1.1rem;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }

        .deadline-note {
            text-align: center;
            color: #64748b;
            font-size: 0.8rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e9ecef;
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
    <?php include('../../include/NavbarStudent.php'); ?>

    <div class="container mt-4 mb-5">

        <div class="row">
            <!-- Left Column: Assignment Details -->
            <div class="col-lg-8">
                <!-- Assignment Info Card -->
                <div class="assignment-card">
                    <div class="assignment-header">
                        <h1 class="assignment-title"><?= htmlspecialchars($assignment['title']) ?></h1>
                        <div class="meta-info">
                            <i class='bx bx-book'></i>
                            <span><?= htmlspecialchars($assignment['course_name']) ?></span>
                        </div>
                        <div class="meta-info">
                            <i class='bx bx-user'></i>
                            <span>สร้างโดย : <strong><?= htmlspecialchars($assignment['teacher_first']) ?> <?= htmlspecialchars($assignment['teacher_last']) ?></strong></span>
                        </div>
                    </div>

                    <div class="description-title">
                        <i class='bx bx-detail'></i>
                        <span>รายละเอียดงานเพิ่มเติม</span>
                    </div>
                    <div class="description-box">
                        <?= nl2br(($assignment['description'])) ?>
                    </div>
                </div>

                <!-- Teacher Feedback Card -->
                <?php if (!empty($teacher_feedback)): ?>
                    <div class="feedback-card">
                        <div class="feedback-header">
                            <i class='bx bxs-message-dots'></i>
                            <span>ความคิดเห็น</span>
                        </div>
                        <div class="feedback-content">
                            <?= nl2br(htmlspecialchars($teacher_feedback)) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Submission Sidebar -->
            <div class="col-lg-4">
                <div class="sidebar-card">

                    <!-- Assignment Info Section -->
                    <div class="card-section">
                        <div class="section-title d-flex justify-content-between align-items-center">
                            <i class='bx bxs-info-circle'></i>
                            <span>ข้อมูลงาน</span>
                            <a href="AllAssignmentStudent.php" class="action-icon">
                                <i class='bxr  bx-reply-big fs-5'></i>
                            </a>
                        </div>

                        <div class="info-item">
                            <span class="info-label">กำหนดส่ง</span>
                            <span class="info-value"><?= date('d M Y', strtotime($assignment['deadline'])) ?></span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">สถานะ</span>
                            <span class="status-badge <?= $submission_status === 'Submitted' ? 'status-submitted' : ($submission_status === 'Needs Revision' ? 'status-revision' : 'status-pending') ?>">
                                <?= htmlspecialchars($submission_status) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Submission Section -->
                    <div class="card-section">
                        <div class="section-title">
                            <i class='bx bxs-cloud-upload'></i>
                            <span><?= $is_submitted ? 'แก้ไขการส่งงาน' : 'ส่งงาน' ?></span>
                        </div>

                        <?php if ($is_submitted): ?>
                            <div class="submission-info">
                                <i class='bx bxs-check-circle'></i>
                                <span>ส่งเมื่อวันที่ : <?= date('d M Y H:i', strtotime($submitted_time)) ?></span>
                            </div>

                            <a href="../../<?= htmlspecialchars($submission_file_path) ?>"
                                target="_blank"
                                class="btn btn-outline-primary w-100 mb-3">
                                <i class='bx bxs-download me-1'></i>
                                ดาวโหลดไฟล์
                            </a>
                        <?php endif; ?>

                        <form action="../../controllers/SubmitAssignmentController.php"
                            method="POST"
                            enctype="multipart/form-data"
                            id="submissionForm">
                            <input type="hidden" name="assignment_id" value="<?= htmlspecialchars($assignment_id) ?>">
                            <input type="hidden" name="student_id" value="<?= htmlspecialchars($student_id) ?>">

                            <div class="mb-3">
                                <label for="file_upload" class="form-label">
                                    <?= $is_submitted ? 'เลือกไฟล์ใหม่' : 'เลือกไฟล์เพื่อส่งงาน' ?>
                                </label>
                                <input class="form-control"
                                    type="file"
                                    id="file_upload"
                                    name="submission_file"
                                    required
                                    accept=".pdf,.doc,.docx,.zip,.rar">
                                <small class="text-muted d-block mt-1">
                                    <i class='bx bx-info-circle me-1'></i>
                                    ชนิดไฟล์ที่รองรับ : PDF, DOC, DOCX, ZIP, RAR
                                </small>
                            </div>

                            <button type="submit" class="btn btn-<?= $is_submitted ? 'warning' : 'success' ?> w-100">
                                <i class='bx <?= $is_submitted ? 'bx-refresh' : 'bx-upload' ?> me-1'></i>
                                <?= $is_submitted ? 'ส่งงานใหม่อีกครั้ง' : 'ส่งงาน' ?>
                            </button>
                        </form>

                        <div class="deadline-note">
                            <i class='bx bx-time-five me-1'></i>
                            ครบกำหนดวันที่ : <?= date('l, d M Y', strtotime($assignment['deadline'])) ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <?php include('../../include/alert.php'); ?>
    <?php include('../../include/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.getElementById('submissionForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('file_upload');

            if (!fileInput.files.length) {
                e.preventDefault();
                alert('Please select a file to upload.');
                return false;
            }

            // File size check (max 10MB)
            const maxSize = 10 * 1024 * 1024; // 10MB in bytes
            if (fileInput.files[0].size > maxSize) {
                e.preventDefault();
                alert('File size must be less than 10MB.');
                return false;
            }

            // Confirm submission
            const isUpdate = <?= $is_submitted ? 'true' : 'false' ?>;
            const message = isUpdate ?
                'Are you sure you want to update your submission? This will replace your previous file.' :
                'Are you sure you want to submit this assignment?';

            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    </script>
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