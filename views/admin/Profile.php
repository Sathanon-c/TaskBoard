<?php
session_start();
include_once('../../config/Database.php');
include_once('../../models/UserModel.php');

// 1. ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$db = (new Database())->getConnection();
$userModel = new UserModel($db);

// --- ส่วนที่แก้ไขใหม่ ---
// เช็คว่ามี id ส่งมาทาง URL ไหม เช่น Profile.php?user_id=5
if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
    $target_user_id = $_GET['user_id'];
} else {
    // ถ้าไม่มี id ส่งมา ให้แสดงโปรไฟล์ตัวเอง
    $target_user_id = $_SESSION['user_id'];
}

// ดึงข้อมูลตาม ID ที่กำหนดไว้ด้านบน
$user = $userModel->getUserById($target_user_id);
// -----------------------

if (!$user) {
    // ถ้าหา user ไม่เจอ (เช่น ใส่ id มั่วใน URL) ให้กลับไปหน้าจัดการผู้ใช้
    header("Location: UserManager.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - TaskBoard</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Boxicons -->
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>

    <style>
        body {
            background: linear-gradient(135deg, #f5f0ff 0%, #e8dcff 50%, #ddc3ff 100%);
            min-height: 100vh;
        }

        .profile-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .profile-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .profile-header {
            padding: 3rem 2rem 2rem;
            text-align: center;
            position: relative;
        }

        .profile-header-primary::before {
            background-color: white;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            opacity: 0.1;
            transform: translate(40%, -60%);
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            position: relative;
            z-index: 1;
        }

        .profile-avatar i {
            font-size: 70px;
        }

        .profile-name {
            color: white;
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.5rem;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 25px;
            color: white;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        .profile-body {
            padding: 2rem;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            color: #495057;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .section-title i {
            font-size: 1.5rem;
            color: #667eea;
        }

        .info-item {
            margin-bottom: 1.5rem;
        }

        .info-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6c757d;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .info-value {
            color: #212529;
            font-weight: 600;
            font-size: 1rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .status-active {
            background: linear-gradient(135deg, #d4fc79 0%, #96e6a1 100%);
            color: #155724;
        }

        .status-inactive {
            background: linear-gradient(135deg, #ffeaa7 0%, #fd79a8 100%);
            color: #721c24;
        }

        .btn-custom {
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }

        .btn-back {
            background: #f8f9fa;
            color: #495057;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        @media (max-width: 768px) {
            .profile-header {
                padding: 2rem 1rem 1.5rem;
            }

            .profile-body {
                padding: 1.5rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include('../../include/NavbarAdmin.php'); ?>

    <div class="container mt-4 mb-5">
        <div class="profile-container w-50">
            <div class="profile-card">

                <!-- Profile Header -->
                <div class="profile-header bg-primary profile-header-primary">
                    <div class="profile-avatar">
                        <i class='bx bxs-user-circle text-primary'></i>
                    </div>
                    <h2 class="profile-name"><?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?></h2>
                    <div class="role-badge">
                        <i class='bx bxs-badge-check'></i>
                        <?= ucfirst($user['role']) ?>
                    </div>
                </div>

                <!-- Profile Body -->
                <div class="profile-body">

                    <!-- Account Information -->
                    <h5 class="section-title">
                        <i class='bx bxs-info-circle'></i>
                        ข้อมูลบัญชีผู้ใช้
                    </h5>

                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">
                                อีเมล
                            </div>
                            <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">
                                วันที่สร้าง
                            </div>
                            <div class="info-value"><?= date('d M Y, H:i', strtotime($user['created_at'])) ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">
                                สถานะ
                            </div>
                            <div>
                                <?php if ($user['active'] == 1): ?>
                                    <span class="status-badge status-active">
                                        เปิดใช้งาน
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge status-inactive">
                                        ปิดใช้งาน
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Details -->
                    <h5 class="section-title mt-4">
                        <i class='bxr  bx-user'></i> ข้อมูลส่วนตัว
                    </h5>

                    <?php if ($user['role'] == 'student'): ?>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">
                                   รหัสนักศึกษา
                                </div>
                                <div class="info-value"><?= htmlspecialchars($user['student_code']) ?></div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">
                                    เบอร์โทรศัพท์
                                </div>
                                <div class="info-value"><?= htmlspecialchars($user['s_phone']) ?></div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">
                                    สาขา
                                </div>
                                <div class="info-value"><?= htmlspecialchars($user['major']) ?></div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">
                                    Year
                                </div>
                                <div class="info-value"><?= htmlspecialchars($user['year']) ?></div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">
                                    เพศ
                                </div>
                                <div class="info-value"><?= htmlspecialchars($user['s_gender']) ?></div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">
                                    ห้องเรียน
                                </div>
                                <div class="info-value"><?= htmlspecialchars($user['class_id']) ?></div>
                            </div>
                        </div>

                    <?php elseif ($user['role'] == 'teacher'): ?>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">
                                    แผนก
                                </div>
                                <div class="info-value"><?= htmlspecialchars($user['department']) ?></div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">
                                    Phone
                                </div>
                                <div class="info-value"><?= htmlspecialchars($user['t_phone']) ?></div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">
                                    เพศ
                                </div>
                                <div class="info-value"><?= htmlspecialchars($user['t_gender']) ?></div>
                            </div>
                        </div>

                    <?php elseif ($user['role'] == 'admin'): ?>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">
                                   เบอร์โทรศัพท์
                                </div>
                                <div class="info-value"><?= htmlspecialchars($user['a_phone']) ?></div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">
                                    เพศ
                                </div>
                                <div class="info-value"><?= htmlspecialchars($user['a_gender']) ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="action-buttons d-flex align-items-center justify-content-end gap-2">
                        <?php
                        if ($user['user_id'] == $_SESSION['user_id']):
                        ?>
                            <a href="EditProfile.php" class="btn-custom mt-1 text-decoration-none btn-primary py-2 px-3">
                                <small>แก้ไขข้อมูล</small>
                            </a>
                        <?php else: ?>
                            <a href="EditUser.php?user_id=<?= $user['user_id'] ?>" class="btn-custom mt-1 text-decoration-none btn-warning py-2 px-3">
                                <small>แก้ไขข้อมูล</small>
                            </a>
                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>
    </div>

    <?php include_once('../../include/alert.php') ?>
    <?php include_once('../../include/footer.php'); ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>