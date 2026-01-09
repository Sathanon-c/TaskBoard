<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include_once("../config/Database.php");
include_once("../models/RegisterModel.php");

$db = (new Database())->getConnection();
$student = new CreateStudent($db);

if(isset($_POST['signup'])){

    // 1. SETTING DATA (เพิ่ม class_id และลบ confirm_password)
    
    $student->setFirstname($_POST['first_name']);
    $student->setLastname($_POST['last_name']);
    $student->setStudentCode($_POST['student_code']);
    $student->setEmail($_POST['email']);
    $student->setMajor($_POST['major']);
    $student->setPhone($_POST['phone']);
    $student->setGender($_POST['gender']);
    $student->setYear($_POST['year']);
    $student->setPassword($_POST['password']);
    
    // 📌 เพิ่มฟิลด์ Class ID
    $student->setClassId($_POST['class_id']);
    
    // ❌ ลบ: $student->setConfirmPassword($_POST['confirm_password']);
    
    // 2. VALIDATION (ลบการตรวจสอบ confirm password)

    // ❌ ลบ Logic นี้ออก:
    /* if (!$student->validatePassword()) {
        $_SESSION['error'] = "Sorry, Passwords do not match. Please try again.";
        header("Location: ../views/auth/register.php");
        exit;
    }
    */

    if (!$student->checkPasswordLength()) {
        $_SESSION['error'] = "Sorry, Password must be at least 6 characters long. Please try again.";
        header("Location: ../views/auth/register.php");
        exit;
    }

    // Check email/student code duplicate
    if ($student->checkEmail()) {
        $_SESSION['error'] = "Sorry, This email already exists. Try another.";
        header("Location: ../views/auth/register.php");
        exit;
    }

    if ($student->checkStudentCodeExists()) {
        $_SESSION['error'] = "Sorry, This student code already exists.";
        header("Location: ../views/auth/register.php");
        exit;
    }

    // 3. Create student (Model ต้องรองรับ class_id แล้ว)
    if($student->create()){
        $_SESSION['success'] = "Registration successful! Please log in.";
        header("Location: ../views/auth/login.php");
        exit;
    } else {
        $_SESSION['error'] = "Registration failed. Please try again later.";
        header("Location: ../views/auth/register.php");
        exit;
    }
}