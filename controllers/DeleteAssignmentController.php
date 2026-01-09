<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once("../config/Database.php");
include_once("../models/AssignmentModel.php");
include_once("../models/CourseModel.php");

// 1. การตรวจสอบสิทธิ์
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: ../auth/login.php");
    exit;
}

// 2. การรับค่า
$assignment_id = $_POST['assignment_id'] ?? null;
$course_id = $_POST['course_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$assignment_id || !$course_id) {
    $_SESSION['error'] = "Invalid request or missing IDs.";
    // Redirect กลับไปหน้า Course Detail
    header("Location: ../views/teacher/CourseDetail.php?course_id=" . htmlspecialchars($course_id));
    exit;
}

$db = (new Database())->getConnection();
$assignmentModel = new AssignmentModel($db);
$courseModel = new CourseModel($db);

// 3. ตรวจสอบความเป็นเจ้าของ (Security Check)
// ตรวจสอบว่า Teacher ที่กำลังลบเป็นเจ้าของ Course ที่ Assignment นี้อยู่หรือไม่
// เมธอด isCourseOwner ควรมีอยู่ใน CourseModel
if (!$courseModel->isCourseOwner($course_id, $user_id)) {
    $_SESSION['error'] = "Authorization failed. You do not own this course or assignment.";
    header("Location: ../views/teacher/CourseManager.php");
    exit;
}

// 4. ดำเนินการลบ
try {
    // ดึงชื่อ Assignment สำหรับข้อความแจ้งเตือน
    $assignment = $assignmentModel->getAssignmentById($assignment_id);
    $assignment_title = $assignment['title'] ?? 'Unknown Assignment';

    // ลบ Assignment (รวมถึง Submissions ที่เกี่ยวข้อง)
    $assignmentModel->deleteAssignment($assignment_id); 

    // ตั้งค่า Session Success
    $_SESSION['success'] = "Assignment has been successfully deleted.";
    
    // Redirect กลับไปหน้า Course Detail
    header("Location: ../views/teacher/CourseDetail.php?course_id=" . htmlspecialchars($course_id));
    exit;

} catch (Exception $e) {
    // จัดการข้อผิดพลาด
    $_SESSION['error'] = "Failed to delete assignment. " . $e->getMessage();
    
    // Redirect กลับไปหน้า Course Detail
    header("Location: ../views/teacher/CourseDetail.php?course_id=" . htmlspecialchars($course_id));
    exit;
}
?>