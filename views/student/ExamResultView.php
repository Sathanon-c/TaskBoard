<?php
session_start();
$score = $_SESSION['last_score'] ?? 0;
$max = $_SESSION['max_score'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>สรุปผลคะแนน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <style>
        body { background: #f0f2f5; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .result-card { background: white; border-radius: 20px; padding: 3rem; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; max-width: 450px; }
        .score-circle { width: 150px; height: 150px; border-radius: 50%; background: #e8dcff; color: #764ba2; display: flex; flex-direction: column; align-items: center; justify-content: center; margin: 2rem auto; border: 5px solid #764ba2; }
    </style>
</head>
<body>
    <div class="result-card">
        <i class='bx bxs-badge-check text-success' style="font-size: 5rem;"></i>
        <h2 class="fw-bold mt-3">ส่งข้อสอบเรียบร้อย!</h2>
        <p class="text-muted">คุณได้ทำการส่งข้อสอบเสร็จสิ้นแล้ว นี่คือคะแนนของคุณ</p>
        
        <div class="score-circle">
            <span class="fs-1 fw-bold"><?= $score ?></span>
            <small class="text-muted">เต็ม <?= $max ?></small>
        </div>

        <a href="AllAssignmentStudent.php" class="btn btn-primary w-100 py-3 mt-3 shadow-sm">
            กลับหน้าจัดการงาน
        </a>
    </div>
</body>
</html>