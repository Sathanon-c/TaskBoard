<?php
session_start();
include_once("../config/Database.php");
include_once("../models/LoginModel.php");

if (isset($_POST['signin'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: ../views/auth/login.php");
        exit;
    }

    // 2. สร้าง Database Connection
    $db = (new Database())->getConnection(); 
    
    // 3. สร้าง Model โดยส่ง 3 ค่า (DB, Email, Password)
    $login = new LoginModel($db, $email, $password); 
    
    // 4. เรียกใช้งาน Model
    $user = $login->verifyPassword();
    
    if (!$user) {
        $_SESSION['error'] = "Email or Password incorrect";
        header("Location: ../views/auth/login.php");
        exit;
    }

    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    
    session_write_close();

    switch (strtolower($user['role'])) {
        case 'admin':
            header("Location: ../views/admin/UserManager.php");
            break;
        case 'teacher':
            header("Location: ../views/teacher/CourseManager.php");
            break;
        default:
            header("Location: ../views/student/AllAssignmentStudent.php");
            break;
    }
    exit;
}
?>