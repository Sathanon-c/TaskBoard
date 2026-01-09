<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | TaskBoard</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Boxicons -->
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@100;200;300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "IBM Plex Sans Thai", sans-serif;
            font-style: normal;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px 20px;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -100px;
            left: -100px;
            z-index: 1;
            animation: float 6s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: fixed;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            bottom: -50px;
            right: -50px;
            z-index: 1;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(20px);
            }
        }

        .container {
            position: relative;
            z-index: 10;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            backdrop-filter: blur(10px);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .card-body {
            padding: 45px 40px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-title {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .register-subtitle {
            font-size: 14px;
            color: #999;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            font-weight: 600;
            font-size: 13px;
            color: #333;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            color: #667eea;
            font-size: 18px;
            z-index: 2;
        }

        .input,
        .form-control,
        .form-select {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
            background: white;
        }

        .input:focus,
        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .input::placeholder,
        .form-control::placeholder {
            color: #ccc;
        }

        .form-select {
            padding-right: 40px;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23667eea' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px 12px;
            appearance: none;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            background: none;
            border: none;
            color: #667eea;
            cursor: pointer;
            font-size: 18px;
            z-index: 3;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #764ba2;
        }

        .button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }

        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .button:active {
            transform: translateY(0);
        }

        .button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .register-footer {
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        .register-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-footer a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        /* Alert Styles */
        .alert-container {
            margin-bottom: 20px;
        }

        .alert {
            border: none;
            border-radius: 10px;
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .alert-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-close {
            filter: brightness(0) invert(1);
        }

        .form-row-wrapper {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .form-row-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .form-row-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .card-body {
                padding: 30px 20px;
            }

            .register-title {
                font-size: 24px;
            }

            .form-row-2,
            .form-row-3 {
                grid-template-columns: 1fr;
            }

            .input,
            .form-control,
            .form-select {
                padding: 12px 15px 12px 45px;
                font-size: 16px;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 20px 15px;
            }

            .card {
                border-radius: 15px;
            }

            .card-body {
                padding: 25px 18px;
            }

            .register-title {
                font-size: 22px;
            }

            .form-label {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-1 col-xl-7">
                <div class="card shadow-lg">
                    <div class="card-body">

                        <!-- Header -->
                        <div class="register-header">
                            <h1 class="register-title">
                                สร้างบัญชี
                            </h1>
                            <p class="register-subtitle">เข้าสู่ระบบแล้วจัดการงานของคุณเอง</p>
                        </div>

                        <!-- Alert -->
                        <div class="alert-container">
                            <?php include('../../include/alert.php'); ?>
                        </div>

                        <!-- Form -->
                        <form action="../../controllers/RegisterController.php" method="POST">

                            <input type="hidden" name="role" value="student">

                            <!-- Email & Password Row -->
                            <div class="form-row-wrapper form-row-2 mb-3">
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        อีเมล
                                    </label>
                                    <div class="input-wrapper">
                                        <i class="input-icon bx bx-envelope"></i>
                                        <input type="email" class="input" id="email" name="email" placeholder="กรุณากรอกอีเมล" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="password" class="form-label">
                                        รหัสผ่าน
                                    </label>
                                    <div class="input-wrapper">
                                        <i class='input-icon bxr  bxs-lock-keyhole'></i>
                                        <input type="password" class="input" id="password" name="password" placeholder="กรุณากรอกรหัสผ่าน" required>
                                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- First & Last Name Row -->
                            <div class="form-row-wrapper form-row-2 mb-3">

                                <!-- ชื่อจริง -->
                                <div class="form-group">
                                    <label for="first_name" class="form-label">ชื่อจริง</label>
                                    <div class="input-wrapper">
                                        <i class="input-icon bx bx-user"></i>
                                        <input type="text" class="input" id="first_name" name="first_name" placeholder="ชื่อจริง" required>
                                    </div>
                                </div>

                                <!-- นามสกุล -->
                                <div class="form-group">
                                    <label for="last_name" class="form-label">นามสกุล</label>
                                    <div class="input-wrapper">
                                        <i class="input-icon bx bx-user"></i>
                                        <input type="text" class="input" id="last_name" name="last_name" placeholder="นามสกุล" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Student Code -->
                            <div class="form-group mb-3">
                                <label for="student_code" class="form-label">รหัสนักศึกษา</label>
                                <div class="input-wrapper">
                                    <i class='input-icon bxr  bxs-user-id-card'></i>
                                    <input type="text" class="input" id="student_code" name="student_code" placeholder="กรอกรหัสนักศึกษา" required>
                                </div>
                            </div>

                            <!-- Major, Year & Class Row -->
                            <div class="form-row-wrapper form-row-3 mb-3">
                                <div class="form-group">
                                    <label for="major" class="form-label">
                                        สาขา
                                    </label>
                                    <div class="input-wrapper">
                                        <i class='input-icon bxr  bxs-education'></i>
                                        <select name="major" class="form-select">
                                            <option value="">เลือกสาขา</option>

                                            <optgroup label="Industrial (ช่างอุตสาหกรรม)">
                                                <option value="เทคโนโลยีสารสนเทศ">Information Technology (เทคโนโลยีสารสนเทศ)</option>
                                                <option value="ช่างยนต์">Auto Mechanics (ช่างยนต์)</option>
                                                <option value="ช่างไฟฟ้า">Electrical Power (ช่างไฟฟ้า)</option>
                                                <option value="ช่างอิเล็กทอนิคส์">Electronics (ช่างอิเล็กทรอนิกส์)</option>
                                            </optgroup>

                                            <optgroup label="Business (บริหารธุรกิจ)">
                                                <option value="บัญชี">Accounting (บัญชี)</option>
                                                <option value="คอมพิวเตอร์ธุรกิจ">Digital Business Technology (คอมพิวเตอร์ธุรกิจ)</option>
                                                <option value="การตลาด">Marketing (การตลาด)</option>
                                            </optgroup>

                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="year" class="form-label">
                                        ระดับชั้น
                                    </label>
                                    <div class="input-wrapper">
                                        <i class="input-icon bx bx-calendar"></i>
                                        <select class="form-select" id="year" name="year" required>
                                            <option value="" disabled selected>เลือกระดับชั้น</option>
                                            <option value="Vocational 1">ปวช.1</option>
                                            <option value="Vocational 2">ปวช.2</option>
                                            <option value="Vocational 3">ปวช.3</option>
                                            <option value="Diploma 1">ปวส.1</option>
                                            <option value="Diploma 2">ปวส.2</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="class_id" class="form-label">
                                        ห้องเรียน
                                    </label>
                                    <div class="input-wrapper">
                                        <i class='input-icon bxr  bxs-briefcase-alt-2'></i>
                                        <select class="form-select" id="class_id" name="class_id" required>
                                            <option value="" disabled selected>เลือกห้องเรียน</option>
                                            <option value="สทส.67.1">สทส.67.1</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Gender & Phone Row -->
                            <div class="form-row-wrapper form-row-2 mb-4">
                                <div class="form-group">
                                    <label for="gender" class="form-label">
                                        เพศ
                                    </label>
                                    <div class="input-wrapper">
                                        <i class="input-icon bx bx-male"></i>
                                        <select class="form-select" id="gender" name="gender">
                                            <option value="" disabled selected>เลือกเพศ</option>
                                            <option value="Male">ชาย</option>
                                            <option value="Female">หญิง</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="phone" class="form-label">
                                        เบอร์โทรศัพท์
                                    </label>
                                    <div class="input-wrapper">
                                        <i class="input-icon bx bx-phone"></i>
                                        <input type="tel" class="input" id="phone" name="phone" placeholder="กรอกเบอร์โทรศัพท์">
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="button" name="signup">
                                สมัครสมาชิก
                            </button>

                            <!-- Footer -->
                            <p class="register-footer">
                                มีบัญชีเรียบร้อยแล้ว? <a href="login.php">เข้าสู่ระบบ</a>
                            </p>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleBtn = event.target.closest('.password-toggle');
            const icon = toggleBtn.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bx-hide');
                icon.classList.add('bx-show');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bx-show');
                icon.classList.add('bx-hide');
            }
        }

        // Auto dismiss alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    </script>

</body>

</html>