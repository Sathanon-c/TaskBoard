<?php
session_start();
include_once('../config/Database.php');
include_once('../models/ExamModel.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = (new Database())->getConnection();
    $examModel = new ExamModel($db);

    $exam_id = $_POST['exam_id'];
    $course_id = $_POST['course_id'];
    $text = $_POST['question_text'];
    $opt1 = $_POST['option_1'];
    $opt2 = $_POST['option_2'];
    $opt3 = $_POST['option_3'];
    $opt4 = $_POST['option_4'];
    $correct = $_POST['correct_option'];
    $points = 1; // ตั้งค่าเริ่มต้นที่ 1 คะแนนต่อข้อ

    if ($examModel->addQuestion($exam_id, $text, $opt1, $opt2, $opt3, $opt4, $correct, $points)) {
        header("Location: ../views/teacher/AddQuestions.php?exam_id=$exam_id&course_id=$course_id");
    } else {
        echo "เกิดข้อผิดพลาดในการบันทึกโจทย์";
    }
}