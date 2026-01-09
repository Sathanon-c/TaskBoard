<?php
session_start();

// ล้าง session ทั้งหมด
session_unset();
session_destroy();

// กลับไปหน้า Login
header("Location: ../views/auth/login.php");
exit();
?>
