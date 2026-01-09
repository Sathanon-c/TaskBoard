<?php
session_start();

include_once('../../config/Database.php');
include_once('../../models/CourseModel.php');
include_once('../../models/EnrollmentModel.php');
include_once('../../models/StudentModel.php');

$db = (new Database())->getConnection();
$courseModel = new CourseModel($db);

$course_id = $_GET['course_id'] ?? null;
if (!$course_id) die("Course not found.");

$course = $courseModel->getCourseById($course_id);
if (!$course) die("Course not found.");

$enrollmentModel = new EnrollmentModel($db);

$students = $enrollmentModel->getStudentsByCourse($course_id);

$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($course['course_name']) ?> - Student Manager</title>

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

        .btn {
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 2px solid #f0f0f0;
            text-align: center;
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 1rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            background: #e7f1ff;
            color: #0d6efd;
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .student-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .section-header {

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

        .student-code-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            background: #e7f1ff;
            color: #0d6efd;
        }

        .level-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            background: #f2f2f2;
            color: #495057;
            border: 1px solid #e0e0e0;
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

        .delete-form {
            display: inline;
        }

        .course-link {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.5s ease;
            font-size: 1.2rem;
        }

        .course-link:hover {
            color: #0a58ca;
            text-decoration: underline;
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

                <div class="d-flex flex-column">

                    <h2 class="mb-1 fw-bold text-dark d-flex align-items-center">
                        Student Manager
                    </h2>

                </div>

                <a href="CourseDetail.php?course_id=<?= $course_id ?>" class="action-icon">
                    <i class='bxr  bx-reply-big fs-5'></i>
                </a>

            </div>
        </div>


        <!-- Student List Section -->
        <div class="student-section">
            <div class="section-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <i class='bx bxs-group fs-3 text-primary'></i>
                        <div class="">
                            <div class="d-flex gap-4 align-items-center">
                                <h5 class="mb-0 fw-bold">Enrolled Students</h5>
                            </div>
                            <small class="text-muted">Manage students enrolled in this course</small>
                        </div>
                        <span class="badge bg-primary ms-3"><?= count($students) ?></span>
                    </div>
                    <a href="AddStudent.php?course_id=<?= $course_id ?>" class="btn btn-primary px-4">
                        <small>Add Student</small>
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><small>Student Code</small></th>
                            <th><small>Name</small></th>
                            <th><small>Email</small></th>
                            <th style="width: 120px;"><small>Level</small></th>
                            <th style="width: 120px;"><small>Class</small></th>
                            <th style="width: 120px;" class="text-center"><small>Action</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class='bx bx-user-x'></i>
                                        <p class="text-muted mb-2 fw-semibold">No students enrolled</p>
                                        <small class="text-muted">Click "Add Student" to enroll students in this course</small>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $st): ?>
                                <tr>
                                    <td>
                                        <span class="student-code-badge">
                                            <small><?= htmlspecialchars($st['student_code'] ?? 0) ?></small>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">
                                            <small><?= htmlspecialchars($st['first_name']) ?> <?= htmlspecialchars($st['last_name']) ?></small>
                                        </div>
                                    </td>
                                    <td class="text-muted">
                                        <small>
                                            <i class='bx bx-envelope me-1'></i>
                                            <?= htmlspecialchars($st['email']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            <small><?= htmlspecialchars($st['year']) ?></small>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            <small><?= htmlspecialchars($st['class_id']) ?></small>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <form action="../../controllers/RemoveStudentController.php"
                                            method="POST"
                                            class="delete-form"
                                            onsubmit="return confirm('Are you sure you want to remove <?= htmlspecialchars($st['first_name']) ?> from this course?');">
                                            <input type="hidden" name="course_id" value="<?= htmlspecialchars($course_id) ?>">
                                            <input type="hidden" name="student_id" value="<?= htmlspecialchars($st['student_id']) ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <small><i class='bx bx-trash me-1'></i>Remove</small>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <?php include_once('../../include/alert.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>