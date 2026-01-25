<?php
session_start();
include_once('../../config/Database.php');
include_once('../../models/ExamModel.php');

$db = (new Database())->getConnection();
$examModel = new ExamModel($db);

$exam_id = $_GET['exam_id'] ?? null;
$course_id = $_GET['course_id'] ?? null;

// ดึงข้อมูลหัวข้อข้อสอบ
$queryExam = $db->prepare("SELECT title FROM exams WHERE exam_id = :id");
$queryExam->execute([':id' => $exam_id]);
$exam = $queryExam->fetch(PDO::FETCH_ASSOC);

// ดึงรายชื่อนักศึกษาและคะแนนที่ทำได้
$queryResults = $db->prepare("
    SELECT s.student_code, s.first_name, s.last_name, er.score_obtained, er.completed_at 
    FROM exam_results er
    JOIN student s ON er.student_id = s.student_id
    WHERE er.exam_id = :exam_id
    ORDER BY s.student_code ASC
");
$queryResults->execute([':exam_id' => $exam_id]);
$results = $queryResults->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ผลการสอบ: <?= htmlspecialchars($exam['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <style>
        body { background-color: #f4f7f6; }
        .results-card { background: white; border-radius: 15px; padding: 2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <?php include('../../include/NavbarTeacher.php'); ?>

    <div class="container mt-5 w-75">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold">ผลการสอบ: <?= htmlspecialchars($exam['title']) ?></h3>
                <p class="text-muted">รายชื่อนักศึกษาที่ทำการส่งข้อสอบแล้ว</p>
            </div>
            <a href="ExamManager.php?course_id=<?= $course_id ?>" class="btn btn-secondary">กลับ</a>
        </div>

        <div class="results-card">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>รหัสนักศึกษา</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th class="text-center">คะแนนที่ได้</th>
                        <th>วันที่/เวลาที่ส่ง</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)): ?>
                        <tr><td colspan="4" class="text-center py-4">ยังไม่มีนักศึกษาเข้าสอบ</td></tr>
                    <?php else: ?>
                        <?php foreach ($results as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['student_code']) ?></td>
                            <td><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                            <td class="text-center fw-bold text-primary"><?= $r['score_obtained'] ?></td>
                            <td class="small text-muted"><?= date('d M Y, H:i', strtotime($r['completed_at'])) ?> น.</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>