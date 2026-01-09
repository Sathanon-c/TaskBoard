<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// 1. ตรวจสอบสิทธิ์การเข้าถึง
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../views/auth/login.php");
    exit;
}

// 2. Include Models & Database
include_once("../config/Database.php");
include_once("../models/UserModel.php");
include_once("../models/AdminModel.php");
include_once("../models/TeacherModel.php");
include_once("../models/StudentModel.php"); 

$db = (new Database())->getConnection();
$userModel = new UserModel($db);
$teacherModel = new TeacherModel($db);
$studentModel = new StudentModel($db);
$adminModel = new AdminModel($db);

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// 3. ดึงข้อมูลจากฟอร์ม (POST Data)
$data = [
    'email' => htmlspecialchars(trim($_POST['email'] ?? '')), 
    'first_name' => htmlspecialchars(trim($_POST['first_name'] ?? '')),
    'last_name' => htmlspecialchars(trim($_POST['last_name'] ?? '')),
    'phone' => htmlspecialchars(trim($_POST['phone'] ?? '')),
    'gender' => strtolower(htmlspecialchars(trim($_POST['gender'] ?? ''))),
];

// 4. แยกข้อมูลเฉพาะ Role และเตรียมตัวแปรสถานะ
$userUpdate = false; // สำหรับ UserModel (อัปเดต Email)
$profileUpdate = false; // สำหรับ TeacherModel/StudentModel

try {
    // 4.1 อัปเดตข้อมูลในตาราง USER (Email)
    if (isset($data['email'])) {
        $userUpdate = $userModel->updateProfile($user_id, $data); 
    }

    // 4.2 อัปเดตข้อมูล Profile (First/Last Name, Phone, Gender, อื่นๆ)
    if ($role === 'teacher') {
        $data['department'] = htmlspecialchars(trim($_POST['department'] ?? ''));
        $profileUpdate = $teacherModel->updateTeacherProfile($user_id, $data);
        
    } elseif ($role === 'student') {
        $data['student_code'] = htmlspecialchars(trim($_POST['student_code'] ?? ''));
        $data['major'] = htmlspecialchars(trim($_POST['major'] ?? ''));
        $data['year'] = htmlspecialchars(trim($_POST['year'] ?? ''));
        $profileUpdate = $studentModel->updateStudentProfile($user_id, $data);
    } elseif ($role === 'admin'){
        $profileUpdate = $adminModel->updateAdminProfile($user_id, $data);
    }
    
    if ($userUpdate || $profileUpdate) {
        $_SESSION['success'] = "Profile updated successfully!";
        $_SESSION['first_name'] = $data['first_name'];
        $_SESSION['last_name'] = $data['last_name'];
    } else {
        $_SESSION['error'] = "Profile update failed or no changes detected."; 
    }

} catch (Exception $e) {
    $_SESSION['error'] = "An error occurred: " . $e->getMessage();
}

// 6. Redirect กลับไปที่หน้า Profile เดิมตาม Role
if ($role === 'teacher') {
    header("Location: ../views/teacher/Profile.php");
} elseif ($role === 'student') {
    header("Location: ../views/student/Profile.php");
}elseif ($role === 'admin'){
    header("Location: ../views/admin/Profile.php");
}else {
    header("Location: ../views/auth/login.php");
}
exit;