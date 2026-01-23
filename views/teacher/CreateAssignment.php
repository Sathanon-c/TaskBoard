<?php
session_start();

$course_id = $_GET['course_id'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Assignment - TaskBoard</title>

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

        .form-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .form-section {
            margin-bottom: 1rem;
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
            color: #0d6efd;
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
                        <h4 class="mb-0 fw-bold">สร้างงานt</h4>
                        <small class="text-muted">สร้างงานใหม่ในรายวิชา</small>
                    </div>
                </div>
                <a href="CourseDetail.php?course_id=<?= htmlspecialchars($course_id) ?>" class="action-icon">
                    <i class='bxr  bx-reply-big fs-5'></i>
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="form-card">
            <form action="../../controllers/CreateAssignmentController.php" method="POST" id="createAssignmentForm">

                <input type="hidden" name="course_id" value="<?= htmlspecialchars($course_id) ?>">

                <!-- Section 1: Basic Information -->
                <div class="form-section">
                    <div class="section-title">
                        <i class='bx bxs-book-content'></i>
                        <h5 class="mb-0 fw-bold">ข้อมูลงาน</h5>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <small>ชื่องาน</small>
                        </label>
                        <input type="text"
                            name="title"
                            class="form-control"
                            placeholder="e.g., Homework #1: Variables and Data Types"
                            required
                            maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <small>รายละเอียดเพิ่มเติม</small>
                        </label>
                        <textarea name="description"
                            class="form-control"
                            rows="6"
                            placeholder="Enter detailed instructions, requirements, and objectives for this assignment..."></textarea>
                    </div>
                </div>

                <!-- Section 2: Deadline -->
                <div class="form-section">
                    <div class="section-title">
                        <i class='bx bxs-calendar'></i>
                        <h5 class="mb-0 fw-bold">กำหนดส่ง</h5>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <small>กำหนดส่ง</small>
                            </label>
                            <div class="date-input-wrapper">
                                <i class='bx bx-calendar'></i>
                                <input type="date"
                                    name="deadline"
                                    class="form-control"
                                    min="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="submit" class="btn btn-success px-4">
                        <i class='bx bx-check'></i>
                        <small>สร้างงาน</small>
                    </button>
                </div>

            </form>
        </div>

    </div>
                    <?php include_once('../../include/alert.php');?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation and enhancement
        document.getElementById('createAssignmentForm').addEventListener('submit', function(e) {
            const title = document.querySelector('input[name="title"]').value.trim();

            if (!title) {
                e.preventDefault();
                alert('Please enter an assignment title.');
                return false;
            }

            // Confirm before submit
            if (!confirm('Are you sure you want to create this assignment?')) {
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

        // Set minimum date to today for deadline
        const dateInput = document.querySelector('input[name="deadline"]');
        if (dateInput) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.setAttribute('min', today);
        }
    </script>
</body>

</html>