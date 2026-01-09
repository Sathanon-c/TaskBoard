<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include_once('../config/Database.php');
include_once('../models/AssignmentModel.php');

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$db = (new Database())->getConnection();
$assignmentModel = new AssignmentModel($db);

// รับค่าจากฟอร์ม
$course_id   = $_POST['course_id'] ?? '';
$title       = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$deadline    = $_POST['deadline'] ?? null;
$status      = 1;

// Validate
if (!$course_id || !$title) {
    $_SESSION['error'] = "Course and Title are required.";
    header("Location: ../views/teacher/CreateAssignment.php?course_id=" . $course_id);
    exit;
}

// เพิ่ม Assignment
$assignmentModel->createAssignment($course_id, $title, $description, $deadline, $status);

$_SESSION['success'] = "Assignment created successfully.";

// redirect กลับไปหน้า detail ของ course
header("Location: ../views/teacher/CourseDetail.php?course_id=" . $course_id);
exit;
?>
