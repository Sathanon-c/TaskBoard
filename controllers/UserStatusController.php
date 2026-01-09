<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. ตรวจสอบสิทธิ์ (Authorization Check) - ต้องเป็น Admin เท่านั้น
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Access denied.";
    header("Location: ../views/auth/login.php"); 
    exit;
}

include_once("../config/Database.php");
include_once("../models/UserModel.php");

$db = (new Database())->getConnection();
$userModel = new UserModel($db);

// 2. รับค่า user_id และสถานะใหม่ (status: 0 หรือ 1)
$user_id = $_GET['user_id'] ?? null;
$new_status = $_GET['status'] ?? null; // 0 = Inactive, 1 = Active

// ตรวจสอบความถูกต้องของข้อมูล
if ($user_id && ($new_status === '0' || $new_status === '1')) {
    
    // 3. เรียกใช้เมธอด updateUser ใน UserModel เพื่อเปลี่ยนค่า active
    $is_active = (int)$new_status;
    $success = $userModel->updateUser($user_id, ['active' => $is_active]);

    if ($success) {
        $message = $is_active ? "User has been activated." : "User has been deactivated.";
        $_SESSION['success'] = $message;
    } else {
        $_SESSION['error'] = "Failed to update user status.";
    }
} else {
    $_SESSION['error'] = "Invalid request parameters.";
}

// 4. Redirect กลับไปหน้าเดิม
header("Location: ../views/admin/UserManager.php");
exit;
?>