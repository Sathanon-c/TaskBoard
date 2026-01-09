<?php
session_start();
// 1. การตั้งค่าและ Includes
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once("../config/Database.php");
include_once("../models/CourseModel.php");

// 2. การตรวจสอบสิทธิ์
// ตรวจสอบว่าเข้าสู่ระบบแล้วและเป็น Teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: ../auth/login.php");
    exit;
}

// 3. การรับค่า
$course_id = $_GET['course_id'] ?? null;
$action = $_GET['action'] ?? null;
$user_id = $_SESSION['user_id'];

if ($action !== 'delete' || !$course_id) {
    $_SESSION['error'] = "Invalid request or missing course ID.";
    header("Location: ../views/teacher/CourseManager.php");
    exit;
}

$db = (new Database())->getConnection();
$courseModel = new CourseModel($db);

// 4. ตรวจสอบความเป็นเจ้าของ (Security Check)
// นี่เป็นขั้นตอนสำคัญที่สุด: ต้องมั่นใจว่า Teacher ที่กำลังจะลบคอร์ส เป็นเจ้าของคอร์สเรียนนั้นจริง
if (!$courseModel->isCourseOwner($course_id, $user_id)) {
    $_SESSION['error'] = "Authorization failed. You do not own this course.";
    header("Location: ../views/teacher/CourseManager.php");
    exit;
}

// 5. ดำเนินการลบ
try {
    // ดึงชื่อคอร์สเพื่อใช้ในข้อความแจ้งเตือน
    $course = $courseModel->getCourseById($course_id);
    $course_name = $course['course_name'] ?? 'Unknown Course';

    // ลบคอร์ส (เมธอดนี้ควรจะลบ Assignments, Submissions, และ Enrolled Students ที่เกี่ยวข้องทั้งหมดด้วย)
    $courseModel->deleteCourse($course_id); 

    // ตั้งค่า Session Success
    $_SESSION['success'] = "Course  has been successfully deleted.";
    header("Location: ../views/teacher/CourseManager.php");
    exit;

} catch (Exception $e) {
    // จัดการข้อผิดพลาด
    $_SESSION['error'] = "Failed to delete course. Please try again. Error: " . $e->getMessage();
    header("Location: ../views/teacher/CourseManager.php");
    exit;
}
?>