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

$submission_id = $_POST['submission_id'] ?? null;
$feedback = $_POST['teacher_feedback'] ?? null;
$new_status = $_POST['new_status'] ?? 'Submitted'; // ค่า default

if (!$submission_id || $feedback === null) {
    $_SESSION['error'] = "Invalid submission data.";
    header("Location: " . $_SERVER['HTTP_REFERER']); // กลับไปหน้าเดิม
    exit;
}

if ($submissionModel->updateSubmissionFeedback($submission_id, $feedback, $new_status)) {
    $_SESSION['success'] = "Feedback and status updated successfully!";
} else {
    $_SESSION['error'] = "Database error: Failed to update submission feedback.";
}

// Redirect กลับไปหน้าเดิม
header("Location: ../views/teacher/SubmissionDetailTeacher.php?submission_id=" . $submission_id);
exit;