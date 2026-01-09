<?php
session_start();

// 1. ตรวจสอบสิทธิ์ (ต้องเป็น Teacher) และข้อมูลที่จำเป็น
if ($_SESSION['role'] !== 'teacher' || !isset($_POST['course_id']) || !isset($_POST['student_id'])) {
    $_SESSION['error'] = "Invalid request or insufficient permissions.";
    header("Location: ../views/teacher/CourseManager.php"); // Redirect ไปหน้าเริ่มต้น
    exit;
}

include_once("../config/Database.php");
include_once("../models/EnrollmentModel.php");

$db = (new Database())->getConnection();
$enrollmentModel = new EnrollmentModel($db);

$course_id = $_POST['course_id'];
$student_id = $_POST['student_id'];

try {
    // 2. เรียกใช้ Model เพื่อลบนักเรียนออกจากคอร์ส
    if ($enrollmentModel->removeStudent($course_id, $student_id)) {
        $_SESSION['success'] = "Student has been successfully removed from the course.";
    } else {
        $_SESSION['error'] = "Failed to remove student. Student may not be enrolled.";
    }

} catch (Exception $e) {
    $_SESSION['error'] = "An error occurred: " . $e->getMessage();
}

// 3. Redirect กลับไปที่หน้ารายชื่อนักเรียนเดิม
header("Location: ../views/teacher/CourseStudentManager.php?course_id=" . $course_id);
exit;