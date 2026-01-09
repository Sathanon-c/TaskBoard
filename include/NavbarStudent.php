<style>
    .navbar-brand {
        transition: all 0.25s ease;
    }

    .navbar-brand:hover {
        color: #6c63ff !important;
    }

    .nav-link {
        transition: all 0.25s ease;
        border-radius: 5px;
    }

    .nav-link:hover {
        background-color: #f0f0ff;
        color: #6c63ff !important;
        transform: translateY(-1px);
    }

    .dropdown-item {
        transition: all 0.25s ease;
    }

    .dropdown-item:hover {
        background-color: #f0f0ff;
        color: #6c63ff !important;
    }

    .navbar-text a {
        transition: all 0.3s ease;
        color: #495057;
        border-radius: 20px;
        background-color: #f2f2f2ff;
    }

    .navbar-text a:hover {
        background-color: #d3d5d8ff;
        color: #6c63ff;
        transform: scale(1.06);
    }
</style>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light shadow-sm py-2 bg-light">
    <div class="container-fluid px-5">

        <!-- Taskboard Logo -->
        <a class="navbar-brand fw-bold text-dark d-flex align-items-center" href="AllAssignmentStudent.php">
            <i class='bxr  bxs-clipboard-code fs-3 me-1'></i> TaskBoard
        </a>

        <ul class="navbar-nav me-auto">

            <!-- My Course -->
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center px-3" href="MyCourse.php"><small>รายวิชา</small></a>
            </li>

            <!-- Assignment -->
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center px-3" href="AllAssignmentStudent.php"><small>จัดการงาน</small></a>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <small>เพิ่มเติม</small> <i class='bx bx-chevron-down ms-1'></i>
                </a>

                <ul class="dropdown-menu dropdown-menu-end py-2">
                    <li>
                        <a class="dropdown-item py-2" href="Profile.php?user_id=<?= $_SESSION['user_id'] ?>">
                            <small>โปรไฟล์</small>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="EditProfile.php?user_id=<?= $_SESSION['user_id'] ?>">
                            <small>แก้ไขโปรไฟล์</small>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="../../controllers/LogoutController.php">
                            <small>ออกจากระบบ</small>
                        </a>
                    </li>
                </ul>
            </li>

        </ul>

        <!-- User Name -->
        <span class="navbar-text d-flex align-items-center ms-auto">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="Profile.php?user_id=<?= $_SESSION['user_id'] ?>" class="text-decoration-none px-4 py-2">
                    <small class=" fw-bold"><?= $_SESSION['first_name'] ?></small>
                </a>
            <?php else: ?>
                <a href="#" class="me-2">Guest</a>
            <?php endif; ?>
        </span>


    </div>
    </div>
</nav>