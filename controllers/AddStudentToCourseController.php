<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('../config/Database.php');
include_once('../models/EnrollmentModel.php');

$db = (new Database())->getConnection();
$enrollmentModel = new EnrollmentModel($db);

// รับค่า POST
$course_id   = $_POST['course_id'] ?? null;
$student_ids = $_POST['student_ids'] ?? [];

// Validate เบื้องต้น
if (!$course_id || empty($student_ids) || !is_array($student_ids)) {
    header("Location: ../views/Teacher/AddStudent.php?course_id=$course_id&error=empty");
    exit;
}

try {
    // เริ่ม transaction (สำคัญมาก)
    $db->beginTransaction();

    foreach ($student_ids as $sid) {
        // กันค่าผิดประเภท
        $sid = (int) $sid;

        // เพิ่มนักเรียนเข้า enrollment
        $enrollmentModel->addStudent($course_id, $sid);
    }

    // commit จริงลง DB
    $db->commit();

    $_SESSION['success'] = "Add student to course successfully.";

    header("Location: ../views/Teacher/CourseStudentManager.php?course_id=$course_id&added=1");
    exit;

} catch (PDOException $e) {

    // rollback ถ้ามี error
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    // debug ตอนพัฒนา (โปรดลบออกตอนขึ้น production)
    die("Database error: " . $e->getMessage());
}
