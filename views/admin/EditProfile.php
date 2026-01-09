<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

include_once("../../config/Database.php");
include_once("../../models/UserModel.php");

$db = (new Database())->getConnection();
$userModel = new UserModel($db);

$user_id = $_SESSION['user_id']; // ดึง ID ของ Admin ที่ล็อกอินอยู่
$user = $userModel->getUserById($user_id);

if (!$user) {
    // ควร Redirect ไปหน้า Error หรือ Logout
    session_destroy();
    header("Location: ../auth/login.php");
    exit;
}

// 💡 Data Helpers: ดึงข้อมูล Profile ทั่วไปของ Admin
$profileData = [
    'phone' => $user['a_phone'] ?? '',
    'gender' => strtolower($user['a_gender'] ?? '')
];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Profile | TaskBoard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .card {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: none;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            font-size: 0.85rem;
            padding: 0.6rem 1rem;
        }

        .form-select:focus,
        .form-control:focus {
            border-color: #6c63ff;
            box-shadow: 0 0 0 0.2rem rgba(108, 99, 255, 0.25);
        }

        .btn-primary {
            background-color: #6c63ff;
            border-color: #6c63ff;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #5a52d3;
            border-color: #5a52d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-secondary {
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
        }

        .section-header {
            color: #6c63ff;
            font-weight: 700;
            border-left: 5px solid #6c63ff;
            padding-left: 10px;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <?php include('../../include/NavbarAdmin.php'); ?>

    <div class="container mt-4 mb-5 w-75">

        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1 fw-bold text-dark">
                        <i class='bx bxs-user-detail me-2'></i>แก้ไขข้อมูลบัญชี
                    </h2>
                </div>
            </div>
        </div>

        <div class="card p-5">
    <form action="../../controllers/UpdateProfileController.php" method="POST">
        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
        <input type="hidden" name="role" value="<?= $user['role'] ?>">

        <h5 class="section-header">ข้อมูลบัญชีผู้ใช้</h5>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold"><small>อีเมล</small></label>
                <input type="email" class="form-control" name="email"
                    value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold"><small>เบอร์โทรศัพท์</small></label>
                <input type="text" name="phone" class="form-control"
                    value="<?= htmlspecialchars($profileData['phone']) ?>">
            </div>
        </div>

        <h5 class="section-header">ข้อมูลพื้นฐาน</h5>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold"><small>ชื่อ</small></label>
                <input type="text" name="first_name" class="form-control"
                    value="<?= htmlspecialchars($user['first_name']) ?>" required>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold"><small>นามสกุล</small></label>
                <input type="text" name="last_name" class="form-control"
                    value="<?= htmlspecialchars($user['last_name']) ?>" required>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold"><small>เพศ</small></label>
                <select name="gender" class="form-select">
                    <option value="">-- เลือกเพศ --</option>
                    <option value="male" <?= ($profileData['gender'] === 'male') ? 'selected' : '' ?>>ชาย</option>
                    <option value="female" <?= ($profileData['gender'] === 'female') ? 'selected' : '' ?>>หญิง</option>
                </select>
            </div>
        </div>

        <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
            <a href="Profile.php" class="btn btn-secondary px-4">
                <i class='bx bx-x me-1'></i> ยกเลิก
            </a>
            <button type="submit" class="btn btn-primary px-4">
                <i class='bx bx-save me-1'></i> บันทึกการเปลี่ยนแปลง
            </button>
        </div>
    </form>
</div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>

</html>