<?php
session_start();
include_once('../config/Database.php');
include_once('../models/ExamModel.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'teacher') {
    $db = (new Database())->getConnection();
    $examModel = new ExamModel($db);

    $course_id = $_POST['course_id'];
    $title = $_POST['title'];
    $duration = $_POST['duration'];

    // สร้างหัวข้อข้อสอบ
    if ($examModel->createExam($course_id, $title, $duration)) {
        // ดึง exam_id ล่าสุดที่เพิ่งสร้างเพื่อพาไปเพิ่มโจทย์
        $exam_id = $db->lastInsertId();
        header("Location: ../views/teacher/AddQuestions.php?exam_id=$exam_id&course_id=$course_id");
        exit;
    } else {
        $_SESSION['error'] = "ไม่สามารถสร้างข้อสอบได้";
        header("Location: ../views/teacher/ExamManager.php?course_id=$course_id");
        exit;
    }
}