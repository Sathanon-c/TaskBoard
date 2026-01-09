<?php
session_start();

// 1. ตรวจสอบสิทธิ์
if ($_SESSION['role'] !== 'student' || !isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// 2. INCLUDE MODELS & DATABASE
include_once('../../config/Database.php');
include_once('../../models/StudentModel.php');
include_once('../../models/AssignmentModel.php');
include_once('../../models/SubmissionModel.php');

$db = (new Database())->getConnection();
$studentModel = new StudentModel($db);
$assignmentModel = new AssignmentModel($db);
$submissionModel = new SubmissionModel($db);

$user_id = $_SESSION['user_id'];

// รับค่า Filter และ Search จาก URL
$search_query = $_GET['search'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';

// 3. ดึง Student ID
$student_id = $studentModel->getStudentIdByUserId($user_id);

if (!$student_id) {
    $_SESSION['error'] = "Student profile not found.";
    header("Location: ../auth/login.php");
    exit;
}

// 4. ดึง Assignments ทั้งหมด
$assignments = $assignmentModel->getAllAssignmentsByStudentId($student_id, $search_query);

// 5. Loop เพื่อดึงสถานะและกรอง
$filtered_assignments = [];

foreach ($assignments as $assignment) {
    $submission = $submissionModel->getSubmissionStatus($assignment['assignment_id'], $student_id);
    $status = $submission['status'] ?? 'Pending';
    $submitted_at = $submission['submitted_at'] ?? null;
    $deadline_passed = (strtotime($assignment['deadline']) < time() && $status !== 'Graded');

    $final_status = $status;
    if ($deadline_passed && $status === 'Pending') {
        $final_status = 'Overdue';
    }

    $match_status = (empty($status_filter) || $status_filter === $final_status);

    if ($match_status) {
        $assignment['submission_status'] = $final_status;
        $assignment['submitted_at'] = $submitted_at;
        $filtered_assignments[] = $assignment;
    }
}

$assignments = $filtered_assignments;

// คำนวณสถิติ
$total_assignments = count($assignments);
$submitted_count = count(array_filter($assignments, fn($a) => $a['submission_status'] === 'Submitted'));
$pending_count = count(array_filter($assignments, fn($a) => $a['submission_status'] === 'Pending'));
$overdue_count = count(array_filter($assignments, fn($a) => $a['submission_status'] === 'Overdue'));
$graded_count = count(array_filter($assignments, fn($a) => $a['submission_status'] === 'Graded'));

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assignments - TaskBoard</title>

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

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 2px solid #f0f0f0;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 0.75rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon-total {
            background: #e7f1ff;
            color: #0d6efd;
        }

        .stat-icon-submitted {
            background: #d1f2ff;
            color: #0dcaf0;
        }

        .stat-icon-pending {
            background: #fff8e1;
            color: #ffc107;
        }

        .stat-icon-overdue {
            background: #ffe5e5;
            color: #dc3545;
        }

        .stat-icon-graded {
            background: #d8ffdeff;
            color: #198754;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3748;
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .assignment-section {
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
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
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

        .stats-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: #f2f2f2;
            color: black;
            border-radius: 20px;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <?php include('../../include/NavbarStudent.php'); ?>

    <div class="container mt-4 mb-5 w-75">

        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1 fw-bold text-dark d-flex align-items-center">จัดการงานที่ได้รับมอบหมาย</h2>
                    <p class="text-muted mb-0">ติดตามและจัดการงานทั้งหมดของคุณ</p>
                </div>
                <div class="stats-badge py-3 px-4">
                    <small><span>ทั้งหมด <?= $total_assignments ?> งาน</span></small>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-total">
                        <i class='bxr  bxs-file-detail'></i>
                    </div>
                    <div class="stat-number"><?= $total_assignments ?></div>
                    <div class="stat-label">ทั้งหมด</div>
                </div>
            </div>

            <div class="col">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-submitted">
                        <i class='bxr  bxs-folder-up-arrow'></i>
                    </div>
                    <div class="stat-number"><?= $submitted_count ?></div>
                    <div class="stat-label">ส่งแล้ว</div>
                </div>
            </div>

            <div class="col">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-pending">
                        <i class='bxr  bxs-stopwatch'></i>
                    </div>
                    <div class="stat-number"><?= $pending_count ?></div>
                    <div class="stat-label">ยังไม่ไดส่ง</div>
                </div>
            </div>

            <div class="col">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-overdue">
                        <i class='bxr  bxs-file-x'></i>
                    </div>
                    <div class="stat-number"><?= $overdue_count ?></div>
                    <div class="stat-label">เกินกำหนด</div>
                </div>
            </div>

            <div class="col">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-graded">
                        <i class='bx bxs-check-circle'></i>
                    </div>
                    <div class="stat-number"><?= $graded_count ?></div>
                    <div class="stat-label">ตรวจแล้ว</div>
                </div>
            </div>
        </div>

        <!-- ฟีเจอร์ค้นหา -->
        <div class="filter-section">
            <form action="AllAssignmentStudent.php" method="GET" class="row g-3 align-items-end">

                <!-- ค้นหาจากสถานะ -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold mb-2"><small>ค้นหาจากสถานะ</small></label>
                    <select name="status_filter" class="form-select form-select-sm py-2">
                        <option value="">ทั้งหมด</option>
                        <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>ยังไม่ได้ส่ง</option>
                        <option value="Submitted" <?= $status_filter === 'Submitted' ? 'selected' : '' ?>>ส่งแล้ว</option>
                        <option value="Overdue" <?= $status_filter === 'Overdue' ? 'selected' : '' ?>>เกินกำหนด</option>
                        <option value="Graded" <?= $status_filter === 'Graded' ? 'selected' : '' ?>>ส่งแล้ว</option>
                        <option value="Needs Revision" <?= $status_filter === 'Needs Revision' ? 'selected' : '' ?>>แก้ไข</option>
                    </select>
                </div>

                <!-- กรอกชื่อค้นหา -->
                <div class="col-md-5">
                    <label class="form-label fw-semibold mb-2"><i class='bx bx-search me-1'></i><small>ค้นหา</small></label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" class="form-control form-control-sm py-2" placeholder="ค้นหาจากชื่อ งาน หรือ รายวิชา">
                </div>

                <!-- ปุ่มค้นหา -->
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4"><small>ค้นหา</small></button>
                </div>

            </form>
        </div>

        <!-- ตารางงาน -->
        <div class="assignment-section">
            <div class="section-header">
                <div class="d-flex align-items-center gap-3">
                    <i class='bx bxs-file fs-3 text-primary'></i>
                    <div>
                        <h5 class="mb-0 fw-bold">งานทั้งหมด</h5>
                        <small class="text-muted">ภาพรวมงานทั้งหมดของคุณ</small>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><small>ชื่องาน</small></th>
                            <th><small>รายวิชา</small></th>
                            <th style="width: 160px;"><small>กำหนดส่ง</small></th>
                            <th style="width: 120px;"><small>สถานะ</small></th>
                            <th style="width: 100px;" class="text-center"><small>จัดการ</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($assignments)): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class='bx bx-file'></i>
                                        <p class="text-muted mb-0 fw-semibold">ไม่พบงาน</p>
                                        <small class="text-muted"><?= $search_query || $status_filter ? 'ลองค้นหาอีกครั้ง' : 'คุณไม่มีงานตอนนี้' ?></small>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($assignments as $as):
                                $status = $as['submission_status'];
                                $status_class = match ($status) {
                                    'Submitted' => 'bg-success',
                                    'Graded' => 'bg-success',
                                    'Pending' => 'bg-warning',
                                    'Overdue' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                            ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">
                                            <small><?= htmlspecialchars($as['title']) ?></small>
                                        </div>
                                    </td>
                                    <td class="text-muted">
                                        <small>
                                            <?= htmlspecialchars($as['course_code']) ?> - <?= htmlspecialchars($as['course_name']) ?>
                                        </small>
                                    </td>
                                    <td class="text-muted">
                                        <small>
                                            <i class='bx bx-calendar'></i>
                                            <?= date('d M Y', strtotime($as['deadline'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge <?= $status_class ?>">
                                            <small><?= htmlspecialchars($status) ?></small>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="AssignmentDetailStudent.php?assignment_id=<?= htmlspecialchars($as['assignment_id']) ?>"
                                            class="btn btn-sm btn-primary">
                                            <small><i class='bx bx-show me-1'></i>View</small>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <?php include('../../include/alert.php'); ?>
    <?php include('../../include/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>