<?php
session_start();
include_once(__DIR__ . "/../../config/Database.php");
include_once(__DIR__ . "/../../models/UserModel.php");

$db = (new Database())->getConnection();
$userModel = new UserModel($db);

$role = $_GET['role'] ?? "";
$search = $_GET['search'] ?? "";

$users = $userModel->getUsers($role, $search);

$totalUsers = $userModel->countAllUsers();
$activeUsers = $userModel->countActiveUsers();
$inactiveUsers = $userModel->countInactiveUsers();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Manager - TaskBoard</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Boxicons -->
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>

    <style>
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

        .table-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
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

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
            padding: 0rem 1rem 1rem 1rem;
            white-space: nowrap;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {

            transform: scale(1.01);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .table tbody td {
            vertical-align: middle;
            padding: 1rem;
        }

        .badge-role {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-student {
            background: #d8ffdeff;
            color: #199b2cff;
        }

        .badge-teacher {
            background: #def9fcff;
            color: #2b8ec0ff;
        }

        .badge-admin {
            background: #fcdadaff;
            color: #f37781ff;
        }

        .status-active {
            color: #198754;
            font-weight: 600;
        }

        .status-inactive {
            color: #dc3545;
            font-weight: 600;
        }

        .action-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            transition: all 0.3s ease;
            color: black;
        }

        .action-icon:hover {
            background: #f8f9fa;
            color: #0d6efd;
            transform: scale(1.1);
        }

        .action-icon.delete:hover {
            background: #fee;
            color: #dc3545;
        }

        .stats-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: #f2f2f2ff;
            color: black;
            border-radius: 20px;
            font-weight: 600;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.75rem;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -35px;
            right: -30px;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            opacity: 0.1;
        }

        .stat-card-details::before {
            background: #0d6efd;
        }

        .stat-card-active::before {
            background: #198754;
        }

        .stat-card-inactive::before {
            background: #ff0707ff;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.7rem;
            margin-bottom: 1rem;
        }

        .stat-icon-details {
            color: #0d6efd;
        }

        .stat-icon-active {
            color: #198754;
        }

        .stat-icon-inactive {
            color: #ff0707ff;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.95rem;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .stat-description {
            color: #94a3b8;
            font-size: 0.8rem;
        }
    </style>
</head>

<body>
    <?php include('../../include/NavbarAdmin.php'); ?>

    <div class="container mt-4 mb-5 w-75">

        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1 fw-bold text-dark d-flex align-items-center justify-content-start">
                        <i class='bxr  bxs-user me-2'></i> จัดการบัญชี
                    </h2>
                    <p class="text-muted mb-0">จัดการบัญชีทั้งหมด, บทบาท, และ สิทธ์การเข้าถึง</p>
                </div>
                <div class="stats-badge py-3 px-4">
                    <i class='bx bxs-group'></i>
                    <small><span><?= count($users) ?> บัญชี</span></small>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">

            <!-- Total Users Card -->
            <div class="col-md-4">
                <div class="stat-card stat-card-details d-flex justify-content-between">
                    <div class=" d-flex flex-column">
                        <div class="stat-label">บัญชีทั้งหมด</div>
                        <div class="stat-number"><?= $totalUsers ?></div>
                        <div class="stat-description">รวมทุกบทบาท</div>
                    </div>
                    <div class="stat-icon stat-icon-details">
                        <i class='bx bxs-group'></i>
                    </div>
                </div>
            </div>

            <!-- Active Users Card -->
            <div class="col-md-4">
                <div class="stat-card stat-card-active d-flex justify-content-between">
                    <div class=" d-flex flex-column">
                        <div class="stat-label">บัญชื่อที่เปิดการใช้งาน</div>
                        <div class="stat-number"><?= $activeUsers ?></div>
                        <div class="stat-description">เปิดใช้งานอยู่ในปัจจุบัน</div>
                    </div>
                    <div class="stat-icon stat-icon-active">
                        <i class='bxr  bxs-user-check'></i>
                    </div>
                </div>
            </div>

            <!-- Inactive Users Card -->
            <div class="col-md-4">
                <div class="stat-card stat-card-inactive d-flex justify-content-between">
                    <div class=" d-flex flex-column">
                        <div class="stat-label">บัญชีที่ปิดการใช้งาน</div>
                        <div class="stat-number"><?= $inactiveUsers ?></div>
                        <div class="stat-description">ต้องการแก้ไขสถานะ</div>
                    </div>
                    <div class="stat-icon stat-icon-inactive">
                        <i class='bxr  bxs-user-x'></i>
                    </div>
                </div>
            </div>

        </div>

        <!-- Filter & Search Section -->
        <div class="filter-section">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold mb-2">
                        <i class='bx bx-filter-alt me-1'></i><small>ค้นหาจากบทบาท</small>
                    </label>
                    <select name="role" class="form-select form-select-sm py-2">
                        <option value="">ทั้งหมด</option>
                        <option value="student" <?= $role == 'student' ? 'selected' : '' ?>>นักศึกษา</option>
                        <option value="teacher" <?= $role == 'teacher' ? 'selected' : '' ?>>อาจารย์</option>
                        <option value="admin" <?= $role == 'admin' ? 'selected' : '' ?>>ผู้ดูแลระบบ</option>
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label fw-semibold mb-2">
                        <i class='bx bx-search me-1'></i><small>ค้นหา</small>
                    </label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                        class="form-control form-control-sm py-2" placeholder="ห้นหาจาก ชื่อ หรือ อีเมล">
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <small>ค้นหา</small>
                    </button>
                    <a href="CreateUser.php" class="btn btn-success px-3">
                        <small>สร้างบัญชี</small>
                    </a>
                </div>
            </form>
        </div>

        <!-- User Table -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th><small>ชื่อ</small></th>
                            <th><small>อีเมล</small></th>
                            <th style="width: 120px;"><small>บทบาท</small></th>
                            <th style="width: 100px;"><small>สถานะ</small></th>
                            <th style="width: 150px;"><small>วันที่สร้าง</small></th>
                            <th style="width: 120px;" class="text-center"><small>การจัดการ</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $i => $user): ?>
                                <?php
                                $isActive = $user['active'];
                                $newStatus = $isActive ? 0 : 1;

                                // กำหนดสี badge ตาม role
                                $badgeClass = 'badge-student';
                                if ($user['role'] == 'teacher') $badgeClass = 'badge-teacher';
                                if ($user['role'] == 'admin') $badgeClass = 'badge-admin';
                                ?>

                                <tr>
                                    <td class="text-muted fw-semibold"><small><?= $i + 1 ?></small></td>
                                    <td>
                                        <div class="fw-bold text-dark">
                                            <small><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></small>
                                        </div>
                                    </td>
                                    <td class="text-muted">
                                        <small><?= htmlspecialchars($user['email']) ?></small>
                                    </td>
                                    <td>
                                        <small>
                                            <span class="badge-role <?= $badgeClass ?> fw-bold">
                                                <?= ucfirst($user['role']) ?>
                                            </span>
                                        </small>
                                    </td>
                                    <td>
                                        <small>
                                            <span class="<?= $isActive ? 'status-active' : 'status-inactive' ?>">
                                                <?= $isActive ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </small>
                                    </td>
                                    <td class="text-muted">
                                        <small>
                                            <?= date('d M Y', strtotime($user['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small>
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="Profile.php?user_id=<?= $user['user_id'] ?>"
                                                    class="action-icon text-decoration-none" title="View Profile">
                                                    <i class='bxr  bx-file-detail fs-5'></i>
                                                </a>
                                                <a href="EditUser.php?user_id=<?= $user['user_id'] ?>"
                                                    class="action-icon text-decoration-none" title="Edit">
                                                    <i class='bx bx-edit fs-5'></i>
                                                </a>
                                                <a href="../../controllers/UserStatusController.php?user_id=<?= $user['user_id'] ?>&status=<?= $newStatus ?>"
                                                    class="action-icon delete text-decoration-none"
                                                    title="<?= $isActive ? 'Deactivate' : 'Activate' ?>">
                                                    <i class='bx <?= $isActive ? 'bx-user-x' : 'bx-user-check' ?> fs-5'></i>
                                                </a>
                                            </div>
                                        </small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class='bx bx-user-x fs-1 text-muted'></i>
                                    <p class="text-muted mt-2 mb-0">No users found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php include('../../include/alert.php'); ?>
    <?php include('../../include/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>

</html>