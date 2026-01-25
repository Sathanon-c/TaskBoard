<?php
session_start();
include_once('../config/Database.php');

// ตรวจสอบสิทธิ์เบื้องต้น
if ($_SESSION['role'] !== 'teacher') {
    die("Unauthorized access.");
}

$db = (new Database())->getConnection();

// รับค่าจาก URL (GET)
$question_id = $_GET['question_id'] ?? null;
$exam_id = $_GET['exam_id'] ?? null;
$course_id = $_GET['course_id'] ?? null;

if ($question_id && $exam_id) {
    try {
        // คำสั่งลบโจทย์
        $query = "DELETE FROM questions WHERE question_id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $question_id);

        if ($stmt->execute()) {
            // ลบสำเร็จ ส่งกลับไปหน้าจัดการโจทย์พร้อมแจ้งเตือน (ถ้ามีระบบ alert)
            header("Location: ../views/teacher/AddQuestions.php?exam_id=$exam_id&course_id=$course_id&msg=deleted");
            exit;
        } else {
            echo "ไม่สามารถลบข้อมูลได้";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "ข้อมูลไม่ครบถ้วน";
}