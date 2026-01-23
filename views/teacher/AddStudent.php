<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include_once('../../config/Database.php');
include_once('../../models/CourseModel.php');
include_once('../../models/StudentModel.php');

$db = (new Database())->getConnection();
$courseModel = new CourseModel($db);
$studentModel = new StudentModel($db);

$course_id = $_GET['course_id'] ?? null;
if (!$course_id) die("Course ID missing.");

// โหลดข้อมูลรายวิชา
$course = $courseModel->getCourseById($course_id);
if (!$course) die("Course not found.");

// โหลดรายชื่อนักศึกษาที่ "ยังไม่ถูกเพิ่มใน course นี้"
$students = $studentModel->getStudentsNotInCourse($course_id);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Students - <?= htmlspecialchars($course['course_name']) ?></title>

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
            padding: 1rem 1.5rem;
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

        .student-section {
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
            padding: 0.7rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .custom-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #0d6efd;
        }

        .student-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            background: #e7f1ff;
            color: #0d6efd;
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

        .select-all-bar {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .submit-bar {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.25rem;
            margin-top: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .selected-count {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-weight: 600;
            color: #495057;
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
                    <i class='bx bxs-user-plus fs-4 text-primary'></i>
                    <h4 class="mb-0 fw-bold">เพิ่มนักศึกษาเข้ารายวิชา</h4>
                </div>
                <a href="CourseStudentManager.php?course_id=<?= $course_id ?>" class="action-icon">
                    <i class='bxr  bx-reply-big fs-5'></i>
                </a>
            </div>
        </div>
        <!-- Student Selection Section -->
        <div class="student-section">
            <form method="POST" action="../../controllers/AddStudentToCourseController.php" id="addStudentForm">
                <input type="hidden" name="course_id" value="<?= $course_id ?>">

                <div class="section-header">
                    <div class="d-flex align-items-center gap-3">
                        <i class='bx bxs-group fs-3 text-primary'></i>
                        <div>
                            <h5 class="mb-0 fw-bold">นักศึกษาที่พร้อมเข้าร่วม</h5>
                            <small class="text-muted">เลือกนักศึกษาที่จะเพิ่มในหลักสูตรนี้</small>
                        </div>
                        <span class="badge bg-primary"><?= count($students) ?></span>
                    </div>
                </div>

                <?php if (!empty($students)): ?>
                    <!-- Select All Bar -->
                    <div class="select-all-bar">
                        <input type="checkbox" id="selectAll" class="custom-checkbox">
                        <label for="selectAll" class="mb-0 fw-semibold" style="cursor: pointer;">
                            <small>เลือกนักศึกษาทั้งหมด</small>
                        </label>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 50px;"><small>เลือก</small></th>
                                    <th><small>รหัสนักศึกษา</small></th>
                                    <th><small>ชื่อนักศึกษา</small></th>
                                    <th><small>สาขา</small></th>
                                    <th style="width: 100px;"><small>ระดับชั้น</small></th>
                                    <th style="width: 100px;"><small>ห้องเรียน</small></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $stu): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox"
                                                name="student_ids[]"
                                                value="<?= $stu['student_id'] ?>"
                                                class="custom-checkbox student-checkbox">
                                        </td>
                                        <td>
                                            <span class="student-badge">
                                                <small><?= htmlspecialchars($stu['student_code']) ?></small>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">
                                                <small><?= htmlspecialchars($stu['first_name']) ?> <?= htmlspecialchars($stu['last_name']) ?></small>
                                            </div>
                                        </td>
                                        <td class="text-muted">
                                            <small><?= htmlspecialchars($stu['major']) ?></small>
                                        </td>
                                        <td class="text-muted">
                                            <small><?= htmlspecialchars($stu['year']) ?></small>
                                        </td>
                                        <td class="text-muted">
                                            <small><?= htmlspecialchars($stu['class_id'] ?? '-') ?></small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Submit Bar -->
                    <div class="submit-bar">
                        <div class="selected-count">
                            <i class='bx bx-check-circle'></i>
                            <small>เลือกนักศึกษา <span id="selectedCount">0</span> คน</small>
                        </div>
                        <button type="submit" class="btn btn-success px-4" id="submitBtn">
                            <small><i class='bx bx-plus me-1'></i>เพิ่มนักศึกษา</small>
                        </button>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class='bx bx-user-x'></i>
                        <h5 class="mb-2 fw-bold text-dark">ไม่มีนักศึกษาที่เลือกได้ในตอนนี้</h5>
                        <p class="text-muted mb-0">นักศึกษาทุกคนอยู๋ในรายวิชาเรีบยร้อยแล้ว</p>
                    </div>
                <?php endif; ?>

            </form>
        </div>

    </div>
    <?php include_once('../../include/alert.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Select All functionality
        const selectAllCheckbox = document.getElementById('selectAll');
        const studentCheckboxes = document.querySelectorAll('.student-checkbox');
        const selectedCountSpan = document.getElementById('selectedCount');
        const submitBtn = document.getElementById('submitBtn');

        // Update count on page load
        updateSelectedCount();

        // Select All toggle
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                studentCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectedCount();
            });
        }

        // Individual checkbox change
        studentCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectedCount();

                // Update "Select All" checkbox state
                const allChecked = Array.from(studentCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(studentCheckboxes).some(cb => cb.checked);

                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = allChecked;
                    selectAllCheckbox.indeterminate = someChecked && !allChecked;
                }
            });
        });

        // Update selected count
        function updateSelectedCount() {
            const count = document.querySelectorAll('.student-checkbox:checked').length;
            selectedCountSpan.textContent = count;

            // Disable submit button if no students selected
            if (submitBtn) {
                submitBtn.disabled = count === 0;
            }
        }

        // Form validation
        document.getElementById('addStudentForm')?.addEventListener('submit', function(e) {
            const checked = document.querySelectorAll('.student-checkbox:checked').length;
            if (checked === 0) {
                e.preventDefault();
                alert('Please select at least one student to add.');
            }
        });
    </script>
</body>

</html>