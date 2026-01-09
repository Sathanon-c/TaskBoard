<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include_once('../config/Database.php');
include_once('../models/CourseModel.php');

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$db = (new Database())->getConnection();
$courseModel = new CourseModel($db);

$course_code = $_POST['course_code'] ?? '';
$course_name = $_POST['course_name'] ?? '';
$level = $_POST['level'] ?? '';
$class_id = $_POST['class_id'] ?? '';
$course_detail = $_POST['course_detail'] ?? '';
$status = 1;

$user_id = $_SESSION['user_id']; // user_id ของครูที่ login

// ดึง teacher_id ของ user_id นั้น
$stmt = $db->prepare("SELECT teacher_id FROM teacher WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);
$teacher_id = $teacher['teacher_id'] ?? null; // ใช้ null ในกรณีที่ไม่พบ

// 1. ตรวจสอบข้อมูลเบื้องต้น
if (!$course_code || !$course_name) {
    $_SESSION['error'] = "Course code and name are required.";
    header("Location: ../views/teacher/CreateCourse.php");
    exit;
}

// 2. ตรวจสอบ Teacher ID
if (!$teacher_id) {
    $_SESSION['error'] = "Teacher ID not found. Cannot create course.";
    header("Location: ../views/teacher/CreateCourse.php");
    exit;
}

// 3. สร้างวิชา และจัดการ Session Success
try {
    $courseModel->createCourse($course_code, $course_name, $level,$class_id, $course_detail, $teacher_id, $status);
    
    // 📌 เพิ่ม Session Success Message
    $_SESSION['success'] = "Course created successfully!";
    
    header("Location: ../views/teacher/CourseManager.php");
    exit;

} catch (Exception $e) {
    // 📌 จัดการ Error Message หากมีปัญหาในการสร้าง Course
    // ในกรณีที่เมธอด createCourse ไม่ได้โยน Exception แต่คืนค่า false
    if (isset($db->errorCode) && $db->errorCode() != '00000') {
        $_SESSION['error'] = "Error creating course: " . $db->errorInfo()[2];
    } else {
         $_SESSION['error'] = "An unexpected error occurred while creating the course. (Error: " . $e->getMessage() . ")";
    }

    header("Location: ../views/teacher/CreateCourse.php");
    exit;
}
?>