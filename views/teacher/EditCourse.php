<?php
session_start();
include_once('../../config/Database.php');
include_once('../../models/CourseModel.php');

$db = (new Database())->getConnection();
$courseModel = new CourseModel($db);

$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;
if ($role !== 'teacher') die("Access denied.");

$course_id = $_GET['course_id'] ?? null;
if (!$course_id) die("Course not found.");

$course = $courseModel->getCourseById($course_id);
if (!$course) die("Course not found.");

$levels = $courseModel->getLevelsByUserId($user_id);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - <?= htmlspecialchars($course['course_name']) ?></title>

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

        .course-info-bar {
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
            padding-top: 2rem;
        }



        .info-card i {
            color: #ffc107;
            font-size: 1.2rem;
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
                        <h4 class="mb-0 fw-bold">แก้ไขรายวิชา</h4>
                        <small class="text-muted">แก้ไขข้อมูลของรยวิชา</small>
                    </div>
                </div>
                <a href="CourseDetail.php?course_id=<?= htmlspecialchars($course_id) ?>"
                    class="action-icon">
                    <i class='bxr  bx-reply-big fs-5'></i>
                </a>
            </div>
        </div>



        <!-- Form Card -->
        <div class="form-card">
            <form method="POST" action="../../controllers/UpdateCourseController.php" id="editCourseForm">
                <input type="hidden" name="course_id" value="<?= htmlspecialchars($course_id) ?>">

                <!-- Section 1: Basic Information -->
                <div class="form-section">
                    <div class="section-title">
                        <i class='bx bxs-book-content'></i>
                        <h5 class="mb-0 fw-bold">ข้อมูลทั่วไป</h5>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                <small>รหัสรายวิชา</small>
                            </label>
                            <input type="text"
                                class="form-control"
                                value="<?= htmlspecialchars($course['course_code']) ?>"
                                disabled>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label class="form-label">
                                <small>ชื่อรายวิชา</small>
                            </label>
                            <input type="text"
                                name="course_name"
                                class="form-control"
                                value="<?= htmlspecialchars($course['course_name']) ?>"
                                required
                                maxlength="255">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                <small>Level</small>
                            </label>
                            <select name="level" class="form-select" required>
                                <option value="">-- Select Level --</option>
                                <?php foreach ($levels as $lvl): ?>
                                    <option value="<?= htmlspecialchars($lvl) ?>" <?= ($course['level'] == $lvl) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($lvl) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                        </div>
                    </div>
                </div>

                <!-- Section 2: Course Details -->
                <div class="form-section">
                    <div class="section-title">
                        <h5 class="mb-0 fw-bold">รายละเอียดของรายวิชา</h5>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <small>รายละเอียดเพิ่มเติม</small>
                        </label>
                        <textarea name="course_detail"
                            class="form-control"
                            rows="6"
                            placeholder="Enter a detailed description of the course objectives, content, and learning outcomes..."><?= htmlspecialchars($course['course_detail']) ?></textarea>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class='bx bx-save'></i>
                        <small>อัปเดตรายวิชา</small>
                    </button>
                </div>

            </form>
        </div> 

    </div>

    <?php include_once('../../include/alert.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation and enhancement
        document.getElementById('editCourseForm').addEventListener('submit', function(e) {
            const courseName = document.querySelector('input[name="course_name"]').value.trim();
            const level = document.querySelector('select[name="level"]').value;

            if (!courseName || !level) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }

            // Confirm before submit
            if (!confirm('Are you sure you want to update this course?')) {
                e.preventDefault();
                return false;
            }
        });

        // Auto-resize textarea
        const textarea = document.querySelector('textarea[name="course_detail"]');
        if (textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        }
    </script>
</body>

</html>