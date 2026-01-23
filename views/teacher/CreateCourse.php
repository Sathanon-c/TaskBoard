<?php
session_start();
// ตรวจสอบว่าเป็น Teacher เท่านั้น
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Course - TaskBoard</title>

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
            font-size: 0.8rem;
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
            margin-top: 2rem;
            padding-top: 2rem;
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
                        <h4 class="mb-0 fw-bold">สร้างรายวิชาใหม่</h4>
                        <small class="text-muted">สร้างรายวิชาของคุณและจัดการ</small>
                    </div>
                </div>
                <a href="CourseManager.php" class="btn btn-secondary">
                    <i class='bx bx-arrow-back'></i>ย้อนกลับ
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="form-card">
            <form action="../../controllers/CreateCourseController.php" method="POST" id="createCourseForm">

                <input type="hidden" name="action" value="create">
                <!-- Section 1: Basic Information -->
                <div class="form-section">
                    <div class="section-title">
                        <i class='bx bxs-book-content'></i>
                        <h5 class="mb-0 fw-bold">ข้อมูลทั่วไป</h5>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                <small>รหัสวิขา</small>
                            </label>
                            <input type="text"
                                name="course_code"
                                class="form-control"
                                placeholder="กรอกรหัสวิชา"
                                required
                                maxlength="20">
                        </div>

                        <div class="col-md-8 mb-3">
                            <label class="form-label">
                                <small>ชื่อรายวิชา</small>
                            </label>
                            <input type="text"
                                name="course_name"
                                class="form-control"
                                placeholder="กรอกชื่อรายวิชา"
                                required
                                maxlength="255">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                <small>ระดับชั้น</small>
                            </label>
                            <select name="level" class="form-select" required>
                                <option value="">-- เลือกระดับชั้น --</option>
                                <option value="ปวช.1">ปวช.1</option>
                                <option value="ปวช.2">ปวช.2</option>
                                <option value="ปวช.3">ปวช.3</option>
                                <option value="ปวส.1">ปวส.1</option>
                                <option value="ปวส.2">ปวส.2</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                <small>ห้องเรียน</small>
                            </label>
                            <select name="class_id" class="form-select" required>
                                <option value="">-- เลือกห้องเรียน --</option>
                                <option value="สทส.67.1">สทส.67.1</option>
                                <option value="สบพ.67.1">สบพ.67.1</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Course Details -->
                <div class="form-section">
                    <div class="section-title">
                        <i class='bxr  bx-file-detail'></i>
                        <h5 class="mb-0 fw-bold">รายละเอียดเพิ่มเติม</h5>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <small>รายละเอียดเพิ่มเติม</small>
                        </label>
                        <textarea name="course_detail"
                            class="form-control"
                            rows="6"
                            placeholder="กรอกรายละเอียดเพิ่มเพิ่มหรือจุดประสงค์ของรายวิชา"
                            id="summernote"
                            required></textarea>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="submit" class="btn btn-success px-4">
                        <small>สร้างรายวิชา</small>
                    </button>
                </div>

            </form>
        </div>

    </div>
    <?php include_once('../../include/alert.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation and enhancement
        document.getElementById('createCourseForm').addEventListener('submit', function(e) {
            const courseCode = document.querySelector('input[name="course_code"]').value.trim();
            const courseName = document.querySelector('input[name="course_name"]').value.trim();
            const level = document.querySelector('select[name="level"]').value;
            const courseDetail = document.querySelector('textarea[name="course_detail"]').value.trim();

            if (!courseCode || !courseName || !level || !courseDetail) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }

            // Confirm before submit
            if (!confirm('Are you sure you want to create this course?')) {
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