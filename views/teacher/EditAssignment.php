<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. ตรวจสอบสิทธิ์
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit;
}

// 2. INCLUDES & INITIALIZATION
include_once('../../config/Database.php');
include_once('../../models/AssignmentModel.php');
include_once('../../models/CourseModel.php');

$db = (new Database())->getConnection();
$assignmentModel = new AssignmentModel($db);
$courseModel = new CourseModel($db);

$user_id = $_SESSION['user_id'];
$assignment_id = $_GET['assignment_id'] ?? null;

// 3. ตรวจสอบ Assignment ID และดึงข้อมูล
if (!$assignment_id) {
    die("Assignment ID is missing.");
}

$assignment = $assignmentModel->getAssignmentById($assignment_id);

if (!$assignment) {
    die("Assignment not found.");
}

$course_id = $assignment['course_id'];

// 4. ตรวจสอบความเป็นเจ้าของ (Security Check)
if (!$courseModel->isCourseOwner($course_id, $user_id)) {
    $_SESSION['error'] = "Authorization failed. You do not own this assignment.";
    header("Location: CourseDetails.php?course_id=" . htmlspecialchars($course_id));
    exit;
}

// 5. ดึงรายการ Course ทั้งหมดของ Teacher
$teacher_courses = $courseModel->getCoursesByUserId($user_id);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Assignment - <?= htmlspecialchars($assignment['title']) ?></title>

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

        .assignment-info-bar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .form-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .form-section:last-of-type {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            color: #2d3748;
        }

        .section-title i {
            font-size: 1.5rem;
            color: #0d6efd;
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

        .required-mark {
            color: #dc3545;
            margin-left: 0.25rem;
        }

        .btn {
            border-radius: 10px;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 0rem;
            padding-top: 0rem;
        }

        .form-helper {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .form-helper i {
            font-size: 0.85rem;
        }

        .info-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-card i {
            color: #ffc107;
            font-size: 1.2rem;
        }

        .date-input-wrapper {
            position: relative;
        }

        .date-input-wrapper .form-control {
            padding-left: 2.5rem;
        }

        .date-input-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
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
                    <div>
                        <h4 class="mb-0 fw-bold">แก้ไขงาน</h4>
                        <small class="text-muted">แก้ไขรายละเอียดของงาน</small>
                    </div>
                </div>
                <a href="AssignmentDetail.php?assignment_id=<?= htmlspecialchars($assignment_id) ?>"class="action-icon">
                    <i class='bxr  bx-reply-big fs-5'></i>
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="form-card">
            <form action="../../controllers/UpdateAssignmentController.php" method="POST" id="editAssignmentForm">

                <input type="hidden" name="assignment_id" value="<?= htmlspecialchars($assignment_id) ?>">

                <!-- Section 1: Course Assignment -->
                <div class="form-section">
                    <div class="section-title">
                        <i class='bx bxs-book-content'></i>
                        <h5 class="mb-0 fw-bold">Course Assignment</h5>
                    </div>

                    <div class="mb-3">
                        <label for="course_id" class="form-label">

                            <small>Assigned Course</small>
                        </label>
                        <select class="form-select" id="course_id" name="course_id" required>
                            <?php foreach ($teacher_courses as $course): ?>
                                <option value="<?= htmlspecialchars($course['course_id']) ?>"
                                    <?= ($course['course_id'] == $course_id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($course['course_code']) ?> <?= htmlspecialchars($course['course_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-helper">
                            <i class='bx bx-info-circle'></i>
                            <span>สามารถย้าย assignment ไปยังคอร์สอื่นได้</span>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Assignment Details -->
                <div class="form-section">
                    <div class="section-title">
                        <i class='bxr  bx-file-detail'></i>
                        <h5 class="mb-0 fw-bold">รายละเอียดของงาน</h5>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">

                            <small>ชื่องาน</small>
                        </label>
                        <input type="text"
                            class="form-control" id="title" name="title" required maxlength="255" value="<?= htmlspecialchars($assignment['title']) ?>" placeholder="e.g., Homework #1: Variables and Data Types">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">

                            <small>รายละเอียดเพิ่มเติม</small>
                        </label>
                        <textarea class="form-control"
                            id="summernote"
                            name="description"
                            rows="6"
                            placeholder="Enter detailed instructions, requirements, and objectives for this assignment..."><?= htmlspecialchars($assignment['description']) ?></textarea>
                    </div>
                </div>

                <!-- Section 3: Deadline -->
                <div class="form-section">
                    <div class="section-title">
                        <i class='bx bxs-calendar'></i>
                        <h5 class="mb-0 fw-bold">กำหนดส่ง</h5>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="deadline" class="form-label">

                                <small>กำหนดส่ง<span class="required-mark">*</span></small>
                            </label>
                            <div class="date-input-wrapper">
                                <i class='bx bx-calendar'></i>
                                <input type="date" class="form-control" id="deadline" name="deadline" required value="<?= htmlspecialchars($assignment['deadline']) ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class='bx bx-save'></i>
                        <small>อัปเดตงาน</small>
                    </button>
                </div>

            </form>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation and enhancement
        document.getElementById('editAssignmentForm').addEventListener('submit', function(e) {
            const title = document.querySelector('input[name="title"]').value.trim();
            const courseId = document.querySelector('select[name="course_id"]').value;
            const deadline = document.querySelector('input[name="deadline"]').value;

            if (!title || !courseId || !deadline) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }

            // Confirm before submit
            if (!confirm('Are you sure you want to update this assignment?')) {
                e.preventDefault();
                return false;
            }
        });

        // Auto-resize textarea
        const textarea = document.querySelector('textarea[name="description"]');
        if (textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        }
    </script>

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