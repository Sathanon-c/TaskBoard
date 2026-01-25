<?php
session_start();
include_once('../config/Database.php');
include_once('../models/ExamModel.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = (new Database())->getConnection();
    $examModel = new ExamModel($db);

    $exam_id = $_POST['exam_id'];
    $student_id = $_POST['student_id'];
    $answers = $_POST['answers'] ?? []; // เป็น array [question_id => selected_option]

    // 1. ดึงโจทย์และเฉลยทั้งหมดของข้อสอบชุดนี้มา
    $questions = $examModel->getQuestionsByExam($exam_id);
    
    $total_earned_score = 0;
    $total_max_score = 0;

    // 2. เริ่มการตรวจคะแนน
    foreach ($questions as $q) {
        $q_id = $q['question_id'];
        $correct_opt = (int)$q['correct_option'];
        $points = (int)$q['points'];
        
        $total_max_score += $points;

        // เช็คว่านักศึกษาตอบข้อนี้ไหม และตอบถูกหรือไม่
        if (isset($answers[$q_id])) {
            $student_ans = (int)$answers[$q_id];
            if ($student_ans === $correct_opt) {
                $total_earned_score += $points;
            }
        }
    }

    // 3. บันทึกผลสอบลงตาราง exam_results
    $save_status = $examModel->saveResult($exam_id, $student_id, $total_earned_score);

    if ($save_status) {
        // เก็บไว้ใน Session เพื่อไปโชว์ในหน้าถัดไป
        $_SESSION['last_score'] = $total_earned_score;
        $_SESSION['max_score'] = $total_max_score;
        
        // ส่งไปหน้าสรุปคะแนน
        header("Location: ../views/student/ExamResultView.php?exam_id=$exam_id");
        exit;
    } else {
        echo "เกิดข้อผิดพลาดในการบันทึกคะแนน";
    }
}