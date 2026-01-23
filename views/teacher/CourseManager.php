<?php
session_start();
include_once('../../config/Database.php');
include_once('../../models/CourseModel.php');

$db = (new Database())->getConnection();
$courseModel = new CourseModel($db);

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) die("User not logged in.");

$selected_level = $_GET['level'] ?? '';
$search = trim($_GET['search'] ?? '');

// ดึง level ของ teacher นั้น
$levels = $courseModel->getLevelsByUserId($user_id);

// ดึง courses ของ teacher ตาม filter
$courses = $courseModel->getCoursesByUserId($user_id, $selected_level, $search);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Manager - TaskBoard</title>

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
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .form-select:focus,
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
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

        .card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .card-header-gradient {
            height: 6px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .badge-code {
            background: #e7f1ff;
            color: #0d6efd;
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .badge-level {
            background: #f2f2f2;
            color: #495057;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
            border: 1px solid #e0e0e0;
        }

        .stats-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: #f2f2f2;
            color: black;
            border-radius: 20px;
            font-weight: 600;
        }

        .empty-state {
            background: white;
            border-radius: 15px;
            padding: 4rem 2rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>
    <?php include('../../include/NavbarTeacher.php'); ?>

    <div class="container mt-4 mb-5 w-75">

        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1 fw-bold text-dark d-flex align-items-center">
                        <i class='bx bxs-book-content me-2'></i> จัดการรายวิชา
                    </h2>
                    <p class="text-muted mb-0">จัดการรายวิชาของคุณและติดตามงานนักศึกษา</p>
                </div>
                <div class="stats-badge py-3 px-4">
                    <small><span>ทั้งหมด <?= count($courses) ?> รายวิชา</span></small>
                </div>
            </div>
        </div>

        <!-- Filter & Search Section -->
        <div class="filter-section">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold mb-2">
                        <i class='bx bx-filter-alt me-1'></i><small>ค้นหาจากระดับชั้น</small>
                    </label>
                    <select name="level" class="form-select form-select-sm py-2">
                        <option value="">ทั้งหมด</option>
                        <?php foreach ($levels as $level): ?>
                            <option value="<?= htmlspecialchars($level) ?>" <?= ($selected_level == $level) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($level) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label fw-semibold mb-2">
                        <i class='bx bx-search me-1'></i><small>ค้นหา</small>
                    </label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                        class="form-control form-control-sm py-2" placeholder="ค้นหาจากชื่อรายวิชา">
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <small>ค้นหา</small>
                    </button>
                    <a href="CreateCourse.php" class="btn btn-success px-3">
                        <small>สร้างรายวิชา</small>
                    </a>
                </div>
            </form>
        </div>

        <!-- Course Cards -->
        <?php if (count($courses) > 0): ?>
            <div class="row g-3">
                <?php foreach ($courses as $course): ?>
                    <div class="col-md-4">
                        <a href="CourseDetail.php?course_id=<?= $course['course_id'] ?>" class="text-decoration-none">
                            <div class="card h-100">
                                <div class="card-header-gradient"></div>
                                <div class="card-body">
                                    <span class="badge-code mb-2 d-inline-block">
                                        <small><?= htmlspecialchars($course['course_code']) ?></small>
                                    </span>
                                    <h6 class="card-title fw-bold text-dark mb-3" style="min-height: 48px;">
                                        <small><?= htmlspecialchars($course['course_name']) ?></small>
                                    </h6>
                                    <span class="badge-level mb-3 d-inline-block">
                                        <small><?= htmlspecialchars($course['level']) ?></small>
                                    </span>
                                    <span class="badge-level mb-3 d-inline-block ms-1">
                                        <small><?= htmlspecialchars($course['class_id']) ?></small>
                                    </span>
                                    <span class="badge-level mb-3 d-inline-block ms-1">
                                        <small>นักศึกษา <?= $course['student_count'] ?? 0 ?> คน</small>
                                    </span>
                                    <div class="border-top pt-3 mt-3"></div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class='bx bx-book-open fs-1 text-muted'></i>
                <p class="text-muted mt-3 mb-0">ไม่พบรายวิชา</p>
                <p class="text-muted mb-0"><small>ลองค้นหาอีกครั้งหรือสรร้างนรายวิชาใหม่</small></p>
            </div>
        <?php endif; ?>

    </div>

    <?php include_once('../../include/alert.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>