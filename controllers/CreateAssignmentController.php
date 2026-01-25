<?php
// ในโปรดักชั่นควรปิด display_errors นะครับ แต่ตอนแก้บั๊กเปิดไว้แบบนี้ดีแล้ว
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include_once('../config/Database.php');
include_once('../models/AssignmentModel.php');

// 1. เช็ค Login และบทบาท (ถ้ามี)
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$db = (new Database())->getConnection();
$assignmentModel = new AssignmentModel($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. รับค่าและทำความสะอาด (Sanitize)
    $course_id   = $_POST['course_id'] ?? '';
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $deadline    = $_POST['deadline'] ?? null;
    $max_score   = $_POST['max_score'] ?? 0; // รับค่าคะแนนที่ขาดไป
    $status      = 1;

    // 4. Validate ข้อมูลที่จำเป็น
    if (empty($course_id) || empty($title) || empty($max_score)) {
        $_SESSION['error'] = "กรุณากรอกข้อมูลที่สำคัญ (ชื่อและคะแนนเต็ม) ให้ครบถ้วน";
        header("Location: ../views/teacher/CreateAssignment.php?course_id=" . htmlspecialchars($course_id));
        exit;
    }

    // 5. พยายามบันทึกข้อมูล
    try {
        // อย่าลืมไปเพิ่ม parameter $max_score ใน AssignmentModel.php ด้วยนะครับ
        $result = $assignmentModel->createAssignment($course_id, $title, $description, $deadline, $max_score, $status);
        
        if ($result) {
            $_SESSION['success'] = "สร้างงานใหม่เรียบร้อยแล้ว";
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    // Redirect กลับ
    header("Location: ../views/teacher/CourseDetail.php?course_id=" . urlencode($course_id));
    exit;
} else {
    // ถ้าไม่ได้มาด้วย POST ให้ดีดกลับ
    header("Location: ../views/teacher/Dashboard.php");
    exit;
}