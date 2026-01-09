<?php
session_start();
// เปิดใช้งานการรายงานข้อผิดพลาด
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ... (ส่วนตรวจสอบสิทธิ์และการรับค่าเดิม) ...

// 2. INCLUDE MODELS & DATABASE
include_once("../config/Database.php");
include_once("../models/SubmissionModel.php"); // 💡 ต้อง Include เพื่อดึงสถานะเก่า

$db = (new Database())->getConnection();
$submissionModel = new SubmissionModel($db); // 💡 สร้าง Object

// รับค่าจากฟอร์ม
$assignment_id = $_POST['assignment_id'] ?? null;
$student_id = $_POST['student_id'] ?? null;
$file = $_FILES['submission_file'] ?? null;

// ... (ส่วน 3. ตรวจสอบข้อมูลพื้นฐานและความผิดพลาดในการอัปโหลด เดิม) ...

// 4. ตั้งค่าการจัดการไฟล์ (เดิม)
$target_dir = "../uploads/submissions/"; 
$base_path_for_db = "uploads/submissions/";

// ... (ส่วน 4.1 ตรวจสอบและสร้าง Folder ถ้าไม่มี เดิม) ...

// 4.2 สร้างชื่อไฟล์ที่ไม่ซ้ำกัน (เดิม)
$file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$new_file_name = $assignment_id . "_" . $student_id . "_" . time() . "." . $file_ext;
$target_file = $target_dir . $new_file_name;
$file_path_db = $base_path_for_db . $new_file_name;

// 4.3 ตรวจสอบขนาดไฟล์ (เดิม)
// ... (Logic ตรวจสอบขนาดไฟล์เดิม) ...


// 📌 ส่วนที่เพิ่มเข้ามา: ตรวจสอบและลบไฟล์เก่าก่อนอัปโหลดไฟล์ใหม่
$existing_submission = $submissionModel->getSubmissionStatus($assignment_id, $student_id);

if ($existing_submission && $existing_submission['file_path']) {
    // 💡 สร้างเส้นทางจริงของไฟล์เก่าบน Server
    $old_file_path = "../" . $existing_submission['file_path']; 
    
    // 💡 ตรวจสอบว่าไฟล์เก่ามีอยู่จริงหรือไม่ และทำการลบ
    if (file_exists($old_file_path)) {
        if (unlink($old_file_path)) {
            // Optional: บันทึก Log ว่าลบสำเร็จ
            // error_log("Successfully deleted old submission file: " . $old_file_path);
        } else {
            // Optional: บันทึก Log ว่าลบไม่สำเร็จ
            // error_log("Failed to delete old submission file: " . $old_file_path);
        }
    }
}
// 📌 สิ้นสุดส่วนลบไฟล์เก่า

// 5. ย้ายไฟล์ที่อัปโหลด (Move Uploaded File) (เดิม)
if (move_uploaded_file($file['tmp_name'], $target_file)) {
    
    // 6. บันทึกข้อมูลลงฐานข้อมูล (เดิม)
    if ($submissionModel->createSubmission($assignment_id, $student_id, $file_path_db)) {
        $_SESSION['success'] = "Assignment successfully submitted or updated! The previous file (if any) has been removed.";
    } else {
        $_SESSION['error'] = "Database error: Failed to record submission.";
        // ถ้าบันทึก DB ไม่สำเร็จ ควรลบไฟล์ที่อัปโหลดใหม่ด้วย
        unlink($target_file); 
    }

} else {
    // ... (Error handling เดิม) ...
}

// 7. Redirect กลับไปหน้าเดิม (เดิม)
header("Location: ../views/student/AssignmentDetailStudent.php?assignment_id=" . $assignment_id);
exit;