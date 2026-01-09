<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once("../config/Database.php");
include_once("../models/AssignmentModel.php");
include_once("../models/CourseModel.php");

// 1. ตรวจสอบสิทธิ์
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: ../auth/login.php");
    exit;
}

// 2. รับค่า POST
$assignment_id = $_POST['assignment_id'] ?? null;
$course_id = $_POST['course_id'] ?? null;
$title = $_POST['title'] ?? '';
$deadline = $_POST['deadline'] ?? '';
$description = $_POST['description'] ?? '';

$user_id = $_SESSION['user_id'];

// 3. ตรวจสอบข้อมูล
if (!$assignment_id || !$course_id || !$title || !$deadline) {
    $_SESSION['error'] = "Missing required fields for update.";
    header("Location: ../views/teacher/UpdateAssignment.php?assignment_id=" . htmlspecialchars($assignment_id));
    exit;
}

$db = (new Database())->getConnection();
$assignmentModel = new AssignmentModel($db);
$courseModel = new CourseModel($db);

// 4. ตรวจสอบความเป็นเจ้าของอีกครั้ง (ป้องกันการส่งค่า POST โดยตรง)
if (!$courseModel->isCourseOwner($course_id, $user_id)) {
    $_SESSION['error'] = "Authorization failed. You cannot edit this assignment.";
    header("Location: ../views/teacher/CourseManager.php"); // Redirect ไปที่หน้า Course Manager หลัก
    exit;
}

// 5. ดำเนินการอัปเดต
try {
    // 📌 คุณต้องสร้างเมธอด updateAssignment ใน AssignmentModel.php
    $result = $assignmentModel->updateAssignment($assignment_id, $course_id, $title, $deadline, $description);

    if ($result) {
        $_SESSION['success'] = "Assignment has been successfully updated.";
    } else {
        $_SESSION['error'] = "Update failed or no changes were made.";
    }

    header("Location: ../views/teacher/CourseDetail.php?course_id=" . htmlspecialchars($course_id));
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = "An error occurred during update: " . $e->getMessage();
    header("Location: ../views/teacher/UpdateAssignment.php?assignment_id=" . htmlspecialchars($assignment_id));
    exit;
}
?>