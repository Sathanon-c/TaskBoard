<?php
session_start();
include_once('../config/Database.php');
$db = (new Database())->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_id = $_POST['exam_id'] ?? null;
    $course_id = $_POST['course_id'] ?? null;

    // ตรวจสอบว่ามี exam_id ไหม
    if (!$exam_id) {
        $_SESSION['error'] = 'ไม่ได้ระบุ exam_id';
        header("Location: ../views/teacher/AddQuestions.php?exam_id=$exam_id&course_id=$course_id");
        exit;
    }

    // ตรวจสอบไฟล์
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = 'ไม่สามารถอัพโหลดไฟล์: ' . $_FILES['csv_file']['error'];
        header("Location: ../views/teacher/AddQuestions.php?exam_id=$exam_id&course_id=$course_id");
        exit;
    }

    $file = $_FILES['csv_file'];

    // ตรวจสอบประเภทไฟล์
    if (!preg_match('/\.csv$/i', $file['name'])) {
        $_SESSION['error'] = 'โปรดอัพโหลดไฟล์ CSV เท่านั้น';
        header("Location: ../views/teacher/AddQuestions.php?exam_id=$exam_id&course_id=$course_id");
        exit;
    }

    // อ่านไฟล์ CSV
    $csvData = file_get_contents($file['tmp_name']);
    $lines = array_filter(array_map('trim', explode("\n", $csvData)), function($line) {
        return !empty($line);
    });

    if (count($lines) < 2) {
        $_SESSION['error'] = 'ไฟล์ต้องมีอย่างน้อย 2 แถว';
        header("Location: ../views/teacher/AddQuestions.php?exam_id=$exam_id&course_id=$course_id");
        exit;
    }

    // แยกหัวแถว
    $header = str_getcsv(array_shift($lines));
    $header = array_map('trim', $header);

    $createdCount = 0;
    $errorCount = 0;

    // ประมวลผลแต่ละแถว
    foreach ($lines as $index => $line) {
        $values = str_getcsv($line);
        $values = array_map('trim', $values);

        // สร้าง associative array
        $data = [];
        foreach ($header as $key => $columnName) {
            $columnName = trim($columnName);
            if (!empty($columnName)) {
                $data[$columnName] = $values[$key] ?? '';
            }
        }

        // ตรวจสอบฟิลด์ที่จำเป็น
        if (empty($data['question_text']) || empty($data['option_1']) || empty($data['option_2']) || 
            empty($data['option_3']) || empty($data['option_4']) || empty($data['correct_option'])) {
            $errorCount++;
            continue;
        }

        // ตรวจสอบ correct_option
        $correctOption = (int)$data['correct_option'];
        if ($correctOption < 1 || $correctOption > 4) {
            $errorCount++;
            continue;
        }

        // เตรียมข้อมูล
        $questionText = htmlspecialchars(trim($data['question_text']));
        $option1 = htmlspecialchars(trim($data['option_1']));
        $option2 = htmlspecialchars(trim($data['option_2']));
        $option3 = htmlspecialchars(trim($data['option_3']));
        $option4 = htmlspecialchars(trim($data['option_4']));
        $points = 1;

        // บันทึกโจทย์
        $query = "INSERT INTO questions (exam_id, question_text, option_1, option_2, option_3, option_4, correct_option, points) 
                  VALUES (:exam_id, :txt, :o1, :o2, :o3, :o4, :correct, :points)";
        
        $stmt = $db->prepare($query);
        $result = $stmt->execute([
            ':exam_id' => $exam_id,
            ':txt' => $questionText,
            ':o1' => $option1,
            ':o2' => $option2,
            ':o3' => $option3,
            ':o4' => $option4,
            ':correct' => $correctOption,
            ':points' => $points
        ]);

        if (!$result) {
            $error = $stmt->errorInfo();
            $_SESSION['error'] = "Database Error: " . $error[2];
            error_log("Insert Error Row " . ($index + 2) . ": " . $error[2]);
            $errorCount++;
            continue;
        }

        $createdCount++;
    }

    if ($createdCount > 0) {
        $_SESSION['success'] = "เพิ่มโจทย์สำเร็จ $createdCount ข้อ";
    }
    if ($errorCount > 0) {
        $_SESSION['error'] = "ข้ามไป $errorCount แถวเนื่องจากข้อมูลไม่ถูกต้อง";
    }

    header("Location: ../views/teacher/AddQuestions.php?exam_id=$exam_id&course_id=$course_id");
    exit;
}
?>