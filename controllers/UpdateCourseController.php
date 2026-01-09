<?php
session_start();
include_once('../config/Database.php');
include_once('../models/CourseModel.php');

$db = (new Database())->getConnection();
$courseModel = new CourseModel($db);

$course_id = $_POST['course_id'];
$data = [
    'course_name' => $_POST['course_name'],
    'level' => $_POST['level'],
    'course_detail' => $_POST['course_detail']
];

$courseModel->updateCourse($course_id, $data);
$_SESSION['success'] = "Course detail updated successfully!";

header("Location: ../views/Teacher/CourseDetail.php?course_id=" . $course_id);
exit;
