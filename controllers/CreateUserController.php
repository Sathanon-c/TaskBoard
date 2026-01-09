<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "You do not have permission to create users.";
    header("Location: ../views/auth/login.php");
    exit;
}

include_once(__DIR__ . "/../config/Database.php");
include_once(__DIR__ . "/../models/UserModel.php");
include_once(__DIR__ . "/../models/StudentModel.php");
include_once(__DIR__ . "/../models/TeacherModel.php");
include_once(__DIR__ . "/../models/AdminModel.php");

$db = (new Database())->getConnection();
$userModel = new UserModel($db);
$studentModel = new StudentModel($db);
$teacherModel = new TeacherModel($db);
$adminModel = new AdminModel($db);

$role = $_POST['role'] ?? '';
$email = htmlspecialchars(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$first_name = htmlspecialchars(trim($_POST['first_name'] ?? ''));
$last_name = htmlspecialchars(trim($_POST['last_name'] ?? ''));
$phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
$gender = htmlspecialchars(trim($_POST['gender'] ?? ''));
$student_code = htmlspecialchars(trim($_POST['student_code'] ?? ''));
$major = htmlspecialchars(trim($_POST['major'] ?? ''));
$year = htmlspecialchars(trim($_POST['year'] ?? ''));
$department = htmlspecialchars(trim($_POST['department'] ?? ''));
$class_id = htmlspecialchars(trim($_POST['class_id'] ?? ''));


if (empty($role) || empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
    $_SESSION['error'] = "Missing required common fields.";
    header("Location: ../views/admin/CreateUser.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format.";
    header("Location: ../views/admin/CreateUser.php");
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['error'] = "Password must be at least 6 characters.";
    header("Location: ../views/admin/CreateUser.php");
    exit;
}


// 5. PROCESS CREATION (TRANSACTION)
try {
    // 5.1 เริ่ม Transaction: เพื่อให้แน่ใจว่าทั้ง User และ Profile ถูกสร้างพร้อมกัน
    $db->beginTransaction();

    // 5.2 สร้าง User ในตารางหลัก
    $user_id = $userModel->createUser($email, $password, $role);
    if (!$user_id) {
        throw new Exception("Failed to insert user into 'user' table.");
    }

    // 5.3 สร้าง Profile ตาม Role
    $profileCreated = false;

    switch ($role) {
        case 'student':
            // เพิ่มการตรวจสอบ student_codeExists() ที่นี่
            if (empty($student_code)) throw new Exception("Student Code is required.");

            $profileCreated = $studentModel->createStudent($user_id, $first_name, $last_name, $student_code, $major, $year, $phone, $gender, $class_id);
            break;

        case 'teacher':
            $profileCreated = $teacherModel->createTeacher($user_id, $first_name, $last_name, $department, $phone, $gender);
            break;

        case 'admin':
            // การสร้าง Admin ควรมีความเข้มงวดเป็นพิเศษ แต่ใช้เมธอดสร้าง Profile ได้
            $profileCreated = $adminModel->createAdmin($user_id, $first_name, $last_name, $phone, $gender);
            break;

        default:
            throw new Exception("Invalid role specified.");
    }

    if (!$profileCreated) {
        throw new Exception("Failed to create profile for role: " . $role);
    }

    // 5.4 สำเร็จ: ยืนยันการเปลี่ยนแปลง
    $db->commit();
    $_SESSION['success'] = ucfirst($role) . " created successfully.";
} catch (Exception $e) {
    // 5.5 ล้มเหลว: ยกเลิกการเปลี่ยนแปลงทั้งหมด (รวมถึง User ที่อาจสร้างไปแล้ว)
    $db->rollBack(); // 
    $_SESSION['error'] = "Error creating user: " . $e->getMessage();
}

// 6. REDIRECTION
header("Location: ../views/admin/UserManager.php");
exit;
