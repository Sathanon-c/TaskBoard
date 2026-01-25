<?php
session_start();
include_once('../config/Database.php');

if ($_SESSION['role'] !== 'teacher') die("Unauthorized");

$db = (new Database())->getConnection();
$exam_id = $_GET['exam_id'];
$course_id = $_GET['course_id'];
$current_status = $_GET['current_status'];

// สลับสถานะ
$new_status = ($current_status === 'published') ? 'hidden' : 'published';

$query = "UPDATE exams SET status = :status WHERE exam_id = :id";
$stmt = $db->prepare($query);
$stmt->execute([':status' => $new_status, ':id' => $exam_id]);

header("Location: ../views/teacher/ExamManager.php?course_id=$course_id");