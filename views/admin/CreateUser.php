<?php
session_start();

// ตรวจสอบว่าเป็น Admin เท่านั้น (ตามมาตรฐานของหน้าอื่นๆ ในระบบ)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New User | TaskBoard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>

    <style>
        .page-header {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: none;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            /* ปรับขอบมนให้เข้ากับสไตล์ */
            font-size: 0.85rem;
            padding: 0.6rem 1rem;
        }

        .form-select:focus,
        .form-control:focus {
            border-color: #6c63ff;
            /* ใช้สีหลักของระบบ */
            box-shadow: 0 0 0 0.2rem rgba(108, 99, 255, 0.25);
        }

        .btn-primary {
            background-color: #6c63ff;
            border-color: #6c63ff;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #5a52d3;
            border-color: #5a52d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .role-fields {
            display: none;
        }

        /* สไตล์สำหรับหัวข้อฟิลด์เฉพาะ Role */
        .role-fields h5 {
            color: #6c63ff;
            font-weight: 700;
            border-left: 5px solid #6c63ff;
            padding-left: 10px;
        }
    </style>
</head>

<body>
    <?php include('../../include/NavbarAdmin.php'); ?>

    <div class="container mt-4 mb-5 w-75">

        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1 fw-bold text-dark">
                        สร้างบัญชี
                    </h2>
                    <p class="text-muted mb-0">กรอกข้อมูลเพื่อสร้างบัญชีใหม่</p>
                </div>

                <!-- ปุ่มโยนไฟล์ -->
                <div>
                    <a href="upload_files.php" class="btn btn-primary">
                        เพิ่มไฟล์
                    </a>
                </div>
            </div>
        </div>


        <div class="card p-5">
            <form action="../../controllers/CreateUserController.php" method="POST">

                <div class="mb-4">
                    <label class="form-label fw-bold"><i class='bx bxs-group me-1'></i><small>เลือกบทบาท</small></label>
                    <select name="role" id="roleSelector" class="form-select" required>
                        <option value="">-- เลือกบทบาท --</option>
                        <option value="student">นักศึกษา</option>
                        <option value="teacher">อาจารย์</option>
                        <option value="admin">ผู้ดูแลระบบ</option>
                    </select>
                </div>

                <h5 class="my-3 text-dark fw-bold">ข้อมูลบัญชีผู้ใช้</h5>
                <hr class="mt-0">

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold"><small>อีเมล</small></label>
                        <input type="email" name="email" class="form-control" placeholder="กรุณากรอกอีเมล" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold"><small>รหัสผ่าน</small></label>
                        <input type="password" name="password" class="form-control" placeholder="กรุณากรอกรหัสผ่าน" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold"><small>เบอร์โทรศัพท์</small></label>
                        <input type="text" name="phone" class="form-control" placeholder="กรอกเบอร์โทรศัพท์">
                    </div>
                </div>

                <h5 class="my-3 text-dark fw-bold">ข้อมูลทั่วไป</h5>
                <hr class="mt-0">

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold"><small>ชื่อจริง</small></label>
                        <input type="text" name="first_name" class="form-control" placeholder="ชื่อจริง" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold"><small>นามสกุล</small></label>
                        <input type="text" name="last_name" class="form-control" placeholder="นามสกุล" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold"><small>เพศ</small></label>
                        <select name="gender" class="form-select" required>
                            <option value="">-- เลือกเพศ --</option>
                            <option value="Male">ชาย</option>
                            <option value="Female">หญิง</option>
                        </select>
                    </div>
                </div>

                <div id="studentFields" class="role-fields row">
                    <h5 class="my-3">ข้อมูลนักศึกษา</h5>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold"><small>รหัสนักศึกษา</small></label>
                        <input type="text" name="student_code" class="form-control" placeholder="กรอกรหัสนักศึกษา">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold"><small>สาขา</small></label>
                        <select name="major" class="form-select">
                            <option value="">-- เลือกสาขา --</option>

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

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold"><small>ปีการศึกษา</small></label>
                        <select name="year" class="form-select">
                            <option value="">-- Select Year --</option>
                            <option value="ปวช. 1">ปวช. 1</option>
                            <option value="ปวช. 2">ปวช. 2</option>
                            <option value="ปวช. 3">ปวช. 3</option>
                            <option value="ปวส. 1">ปวส. 1</option>
                            <option value="ปวส. 2">ปวส. 2</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold"><small>ห้องเรียน</small></label>
                        <select name="class_id" class="form-select">
                            <option value="">-- เลือกห้องเรียน --</option>
                            <option value="ปวช. 1">สทส.67.1</option>
                            <option value="ปวช. 1">สบพ.67.1</option>
                        </select>
                    </div>
                </div>

                <div id="teacherFields" class="role-fields row">
                    <h5 class="my-3">ข้อมูลอาจารย์</h5>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold"><small>แผนก</small></label>
                        <select name="department" class="form-select">
                            <option value="">-- เลือกแผนก --</option>

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

                <div id="adminFields" class="role-fields row">
                    <h5 class="my-3">ข้อมูลผู้ดูแลระบบ</h5>
                    <div class="col-12 text-muted">
                        <p>ไม่มีข้อมูลเพิ่มเติมสำหรับผู้ดูแลระบบ</p>
                    </div>
                </div>

                <div class="text-end mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class='bx bx-user-plus me-1'></i>สร้างบัญชี
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('roleSelector').addEventListener('change', function() {
            var role = this.value;
            var allFields = document.querySelectorAll('.role-fields');

            allFields.forEach(function(el) {
                el.style.display = 'none';
                // ลบ required ออกจากฟิลด์ที่ซ่อน
                el.querySelectorAll('input, select').forEach(function(input) {
                    input.removeAttribute('required');
                });
            });

            if (role) {
                var fieldsDiv = document.getElementById(role + 'Fields');
                if (fieldsDiv) {
                    fieldsDiv.style.display = 'flex';
                    // เพิ่ม required เข้าไปในฟิลด์ที่แสดง (ยกเว้น adminFields ที่อาจไม่มีฟิลด์เพิ่ม)
                    if (role === 'student' || role === 'teacher') {
                        fieldsDiv.querySelectorAll('input, select').forEach(function(input) {
                            // ใช้ input.name เพื่อระบุฟิลด์ที่ต้องการให้เป็น required ในแต่ละ role
                            input.setAttribute('required', 'required');
                        });
                    }
                }
            }
        });

        // 💡 เรียกใช้ครั้งแรกเพื่อซ่อนฟิลด์ทั้งหมดเมื่อโหลดหน้า
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('roleSelector').dispatchEvent(new Event('change'));
        });
    </script>
    <?php include('../../include/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>