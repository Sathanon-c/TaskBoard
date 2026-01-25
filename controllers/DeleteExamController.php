<?php
session_start();
include_once('../config/Database.php');

if ($_SESSION['role'] !== 'teacher') {
    die("Unauthorized access.");
}

$db = (new Database())->getConnection();

$exam_id = $_GET['exam_id'] ?? null;
$course_id = $_GET['course_id'] ?? null;

if ($exam_id && $course_id) {
    try {
        // เริ่ม Transaction เพื่อความปลอดภัย เพราะต้องลบหลายตารางที่เกี่ยวข้องกัน
        $db->beginTransaction();

        // 1. ลบคะแนนสอบของนักศึกษา (ถ้ามี)
        $q1 = "DELETE FROM exam_results WHERE exam_id = :id";
        $stmt1 = $db->prepare($q1);
        $stmt1->execute([':id' => $exam_id]);

        // 2. ลบโจทย์ในชุดข้อสอบ (ถ้ามี)
        $q2 = "DELETE FROM questions WHERE exam_id = :id";
        $stmt2 = $db->prepare($q2);
        $stmt2->execute([':id' => $exam_id]);

        // 3. ลบชุดข้อสอบ
        $q3 = "DELETE FROM exams WHERE exam_id = :id";
        $stmt3 = $db->prepare($q3);
        $stmt3->execute([':id' => $exam_id]);

        $db->commit();
        header("Location: ../views/teacher/ExamManager.php?course_id=$course_id&msg=exam_deleted");
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Missing required parameters.";
}