<?php
session_start();
// เปิด Error Reporting สำหรับการ Debug (ปิดเมื่อใช้งานจริง)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. INCLUDES & INITIALIZATION
include_once("../config/Database.php");
include_once("../models/UserModel.php");
include_once("../models/StudentModel.php");
include_once("../models/TeacherModel.php");
include_once("../models/AdminModel.php");

$db = (new Database())->getConnection();
$userModel = new UserModel($db);
$studentModel = new StudentModel($db);
$teacherModel = new TeacherModel($db);
$adminModel = new AdminModel($db);

$user_id = $_POST['user_id'] ?? 0;
$current_role = $_POST['role'] ?? '';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/auth/login.php");
    exit;
}

if ($_SESSION['role'] !== 'admin' && (int)$user_id !== (int)$_SESSION['user_id']) {
    $_SESSION['error'] = "You do not have permission to update this user.";
    header("Location: ../views/UserManager.php"); 
    exit;
}

$email = htmlspecialchars(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$active = $_POST['active'] ?? 1;

$db->beginTransaction();

try {
    $userData = ['email' => $email, 'active' => $active];
    if (!empty($password)) {
        $userData['password'] = $password;
    }
    $userModel->updateUser($user_id, $userData);

    $first_name = htmlspecialchars(trim($_POST['first_name'] ?? ''));
    $last_name = htmlspecialchars(trim($_POST['last_name'] ?? ''));
    $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
    $gender = htmlspecialchars(trim($_POST['gender'] ?? ''));
    $class_id = htmlspecialchars(trim($_POST['class_id'] ?? ''));
    
    // Static Update Logic: ต้องส่งข้อมูลครบตามที่ Model ต้องการ
    if ($current_role == 'student') {
        $studentData = [
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'student_code' => htmlspecialchars(trim($_POST['student_code'] ?? '')),
            'major'        => htmlspecialchars(trim($_POST['major'] ?? '')),
            'year'         => htmlspecialchars(trim($_POST['year'] ?? '')),
            'phone'        => $phone,
            'gender'       => $gender,
            'class_id'     => $class_id
        ];
        $studentModel->updateStudent($user_id, $studentData);
    } 
    
    elseif ($current_role == 'teacher') {
        $teacherData = [
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'department' => htmlspecialchars(trim($_POST['department'] ?? '')),
            'phone'      => $phone,
            'gender'     => $gender
        ];
        $teacherModel->updateTeacher($user_id, $teacherData);
    } 
    
    elseif ($current_role == 'admin') {
        $adminData = [
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'phone'      => $phone,
            'gender'     => $gender
        ];
        $adminModel->updateAdmin($user_id, $adminData);
    }

    // 4.3. COMMIT (ยืนยันการเปลี่ยนแปลง)
    $db->commit();
    $_SESSION['success'] = "User data updated successfully.";

} catch (Exception $e) {
    // 4.4. ROLLBACK (ยกเลิกทั้งหมด)
    $db->rollBack();
    $_SESSION['error'] = "Update failed: " . $e->getMessage();
}

// 5. REDIRECT
if ($_SESSION['role'] == 'admin') {
    header("Location: ../views/admin/UserManager.php");
} else {
    // ผู้ใช้ทั่วไปถูกส่งกลับไปหน้า Profile ของตัวเอง
    header("Location: ../views/" . $_SESSION['role'] . "/Profile.php"); 
}
exit;
?>