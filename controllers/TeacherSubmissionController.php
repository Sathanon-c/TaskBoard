<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if ($_SESSION['role'] !== 'teacher' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/auth/login.php");
    exit;
}

include_once("../config/Database.php");
include_once("../models/SubmissionModel.php");

$db = (new Database())->getConnection();
$submissionModel = new SubmissionModel($db);

// ... โค้ดส่วนบนเดิม ...

$submission_id = $_POST['submission_id'] ?? null;
$feedback = $_POST['teacher_feedback'] ?? null;
$new_status = $_POST['new_status'] ?? 'Submitted';
$score = $_POST['score'] ?? 0; // รับค่าคะแนนเพิ่มจากฟอร์ม

if (!$submission_id) {
    $_SESSION['error'] = "Invalid submission data.";
    header("Location: ../views/teacher/CourseManager.php");
    exit;
}

// ปรับการเรียกฟังก์ชันให้ส่ง $score ไปด้วย
if ($submissionModel->updateSubmissionGrade($submission_id, $feedback, $new_status, $score)) {
    $_SESSION['success'] = "บันทึกการให้คะแนนเรียบร้อยแล้ว!";
} else {
    $_SESSION['error'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
}

header("Location: ../views/teacher/SubmissionDetailTeacher.php?submission_id=" . $submission_id);
exit;