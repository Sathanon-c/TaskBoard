<?php
session_start();

// เปิด Error Reporting สำหรับการ Debug (ปิดเมื่อใช้งานจริง)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Log errors to file แทนการ display
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/upload_errors.log');

// ✅ เพิ่ม CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// ✅ ให้ OPTIONS request ผ่าน
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 1. INCLUDES & INITIALIZATION
include_once("../config/Database.php");
include_once("../models/UserModel.php");
include_once("../models/StudentModel.php");
include_once("../models/TeacherModel.php");
include_once("../models/AdminModel.php");


// ตรวจสอบการอัพโหลดไฟล์
error_log("FILES received: " . json_encode($_FILES));
error_log("FILES csv_file: " . json_encode($_FILES['csv_file'] ?? 'NOT FOUND'));

if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    error_log("Upload error - error code: " . ($_FILES['csv_file']['error'] ?? 'NO FILE'));
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ไม่สามารถอัพโหลดไฟล์', 'debug' => $_FILES['csv_file'] ?? 'NO FILE'], JSON_UNESCAPED_UNICODE);
    exit;
}

$file = $_FILES['csv_file'];

// ตรวจสอบประเภทไฟล์
if (!preg_match('/\.csv$/i', $file['name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'โปรดอัพโหลดไฟล์ CSV เท่านั้น'], JSON_UNESCAPED_UNICODE);
    exit;
}

// อ่านไฟล์ CSV
$csvData = file_get_contents($file['tmp_name']);
$lines = array_filter(array_map('trim', explode("\n", $csvData)), function($line) {
    return !empty($line);
});

if (count($lines) < 2) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ไฟล์ต้องมีอย่างน้อย 2 แถว (หัวแถว + ข้อมูล)'], JSON_UNESCAPED_UNICODE);
    exit;
}

// แยกหัวแถว
$header = str_getcsv(array_shift($lines));
$header = array_map('trim', $header);

// เชื่อมต่อฐานข้อมูล
try {
    $db = (new Database())->getConnection();
    $userModel = new UserModel($db);
    $studentModel = new StudentModel($db);
    $teacherModel = new TeacherModel($db);
    $adminModel = new AdminModel($db);
} catch (Exception $e) {
    http_response_code(500);
    error_log("Database Connection Error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูล',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$createdCount = 0;
$skippedCount = 0;
$errors = [];

// ประมวลผลแต่ละแถว
foreach ($lines as $index => $line) {
    try {
        $values = str_getcsv($line);
        $values = array_map('trim', $values);

        // สร้าง associative array
        $userData = [];
        foreach ($header as $key => $columnName) {
            $columnName = trim($columnName);
            if (!empty($columnName)) {
                $userData[$columnName] = $values[$key] ?? '';
            }
        }

        // ตรวจสอบฟิลด์ที่จำเป็น
        error_log("Row " . ($index + 2) . " Data: " . json_encode($userData));
        
        if (empty($userData['role']) || empty($userData['email']) || empty($userData['password']) || 
            empty($userData['first_name']) || empty($userData['last_name']) || empty($userData['gender'])) {
            $skippedCount++;
            $errors[] = "แถว " . ($index + 2) . ": ข้อมูลไม่ครบถ้วน (role=" . ($userData['role'] ?? 'empty') . 
                        ", email=" . ($userData['email'] ?? 'empty') . 
                        ", password=" . ($userData['password'] ?? 'empty') . 
                        ", first_name=" . ($userData['first_name'] ?? 'empty') . 
                        ", last_name=" . ($userData['last_name'] ?? 'empty') . 
                        ", gender=" . ($userData['gender'] ?? 'empty') . ")";
            continue;
        }

        // ตรวจสอบบทบาท
        if (!in_array($userData['role'], ['student', 'teacher', 'admin'])) {
            $skippedCount++;
            $errors[] = "แถว " . ($index + 2) . ": บทบาทไม่ถูกต้อง";
            continue;
        }

        // ตรวจสอบอีเมล
        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            $skippedCount++;
            $errors[] = "แถว " . ($index + 2) . ": อีเมลไม่ถูกต้อง";
            continue;
        }

        // ตรวจสอบว่าอีเมลมีอยู่แล้วหรือไม่ (ต้องเพิ่ม method นี้ใน UserModel ถ้ายังไม่มี)
        // สำหรับตอนนี้ ใช้ getUserByEmail ถ้ามี หรือประมาณนี้:
        // $existingUser = $userModel->getUserByEmail($userData['email']);
        // ถ้าไม่มี method นี้ ให้เขียน raw SQL ตรวจสอบ
        $stmt = $db->prepare("SELECT user_id FROM user WHERE email = :email");
        $stmt->bindParam(':email', $userData['email']);
        $stmt->execute();
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingUser) {
            $skippedCount++;
            $errors[] = "แถว " . ($index + 2) . ": อีเมลนี้มีอยู่ในระบบแล้ว";
            continue;
        }

        // ตรวจสอบ student_code ถ้าเป็นนักศึกษา
        if ($userData['role'] === 'student' && !empty($userData['student_code'])) {
            $stmt = $db->prepare("SELECT student_id FROM student WHERE student_code = :student_code");
            $stmt->bindParam(':student_code', $userData['student_code']);
            $stmt->execute();
            $existingStudent = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingStudent) {
                $skippedCount++;
                $errors[] = "แถว " . ($index + 2) . ": รหัสนักศึกษาซ้ำกับในระบบ";
                continue;
            }
        }

        // เริ่ม Transaction
        $db->beginTransaction();

        try {
            // Sanitize ข้อมูล
            $email = htmlspecialchars(trim($userData['email']));
            $password = htmlspecialchars(trim($userData['password']));
            $firstName = htmlspecialchars(trim($userData['first_name']));
            $lastName = htmlspecialchars(trim($userData['last_name']));
            $gender = htmlspecialchars(trim($userData['gender']));
            $phone = htmlspecialchars(trim($userData['phone'] ?? ''));
            $role = htmlspecialchars(trim($userData['role']));

            // 1. สร้างบัญชี users (ใช้ individual parameters)
            $userId = $userModel->createUser($email, $password, $role);

            if (!$userId) {
                throw new Exception("ไม่สามารถสร้างบัญชี users");
            }

            // 2. สร้างข้อมูลตามบทบาท
            if ($role === 'student') {
                $studentCode = htmlspecialchars(trim($userData['student_code'] ?? ''));
                $major = htmlspecialchars(trim($userData['major'] ?? ''));
                $year = htmlspecialchars(trim($userData['year'] ?? ''));
                $classId = htmlspecialchars(trim($userData['class_id'] ?? ''));

                // ใช้ individual parameters ตาม signature ของ createStudent
                if (!$studentModel->createStudent(
                    $userId,
                    $firstName,
                    $lastName,
                    $studentCode ?: null,
                    $major ?: null,
                    $year ?: null,
                    $phone ?: null,
                    $gender,
                    $classId ?: null
                )) {
                    throw new Exception("ไม่สามารถสร้างข้อมูลนักศึกษา");
                }

            } elseif ($role === 'teacher') {
                $department = htmlspecialchars(trim($userData['department'] ?? ''));

                // ใช้ individual parameters ตาม signature ของ createTeacher
                if (!$teacherModel->createTeacher(
                    $userId,
                    $firstName,
                    $lastName,
                    $phone ?: null,
                    $gender,
                    $department ?: null
                )) {
                    throw new Exception("ไม่สามารถสร้างข้อมูลอาจารย์");
                }

            } elseif ($role === 'admin') {
                // ใช้ individual parameters ตาม signature ของ createAdmin
                if (!$adminModel->createAdmin(
                    $userId,
                    $firstName,
                    $lastName,
                    $phone ?: null,
                    $gender
                )) {
                    throw new Exception("ไม่สามารถสร้างข้อมูลผู้ดูแลระบบ");
                }
            }

            // Commit Transaction
            $db->commit();
            $createdCount++;

        } catch (Exception $e) {
            // Rollback Transaction
            $db->rollBack();
            $skippedCount++;
            $errors[] = "แถว " . ($index + 2) . ": " . $e->getMessage();
            continue;
        }

    } catch (Exception $e) {
        $skippedCount++;
        $errors[] = "แถว " . ($index + 2) . ": " . $e->getMessage();
        continue;
    }
}

// ส่งผลลัพธ์
header('Content-Type: application/json; charset=utf-8');
if ($createdCount > 0) {
    echo json_encode([
        'success' => true,
        'message' => 'อัพโหลดสำเร็จ',
        'created_count' => $createdCount,
        'skipped_count' => $skippedCount,
        'errors' => $errors
    ], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ไม่สามารถสร้างบัญชีใด ๆ เลย',
        'errors' => $errors
    ], JSON_UNESCAPED_UNICODE);
}
?>