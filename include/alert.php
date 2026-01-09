<?php
// ตรวจสอบว่า session ถูกเริ่มแล้ว
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<style>
    .alert-floating-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1050;
        width: 380px;
        max-width: calc(100% - 40px);
    }

    /* Success Alert */
    .alert-success-custom {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        color: white;
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
        animation: slideInRight 0.4s ease-out;
    }

    .alert-success-custom .alert-icon {
        font-size: 1.5rem;
        margin-right: 12px;
        flex-shrink: 0;
    }

    /* Error Alert */
    .alert-error-custom {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        border: none;
        color: white;
        box-shadow: 0 8px 24px rgba(239, 68, 68, 0.3);
        animation: slideInRight 0.4s ease-out;
    }

    .alert-error-custom .alert-icon {
        font-size: 1.5rem;
        margin-right: 12px;
        flex-shrink: 0;
    }

    /* Warning Alert */
    .alert-warning-custom {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border: none;
        color: white;
        box-shadow: 0 8px 24px rgba(245, 158, 11, 0.3);
        animation: slideInRight 0.4s ease-out;
    }

    .alert-warning-custom .alert-icon {
        font-size: 1.5rem;
        margin-right: 12px;
        flex-shrink: 0;
    }

    /* Info Alert */
    .alert-info-custom {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        color: white;
        box-shadow: 0 8px 24px rgba(59, 130, 246, 0.3);
        animation: slideInRight 0.4s ease-out;
    }

    .alert-info-custom .alert-icon {
        font-size: 1.5rem;
        margin-right: 12px;
        flex-shrink: 0;
    }

    /* Close button */
    .btn-close-custom {
        border: none;
        background: transparent;
        color: inherit;
        opacity: 0.8;
        font-size: 1.25rem;
        padding: 0;
        margin-left: auto;
        flex-shrink: 0;
        transition: opacity 0.3s ease, transform 0.3s ease;
        cursor: pointer;
    }

    .btn-close-custom:hover {
        opacity: 1;
        transform: scale(1.1);
    }

    .btn-close-custom:active {
        transform: scale(0.95);
    }

    /* Alert content */
    .alert-message-text {
        font-size: 0.95rem;
        line-height: 1.4;
        font-weight: 500;
    }

    .alert-message-desc {
        font-size: 0.85rem;
        opacity: 0.9;
        margin-top: 4px;
        line-height: 1.3;
    }

    /* Animation */
    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }

    .alert-custom.hide {
        animation: slideOutRight 0.3s ease-out forwards;
    }

    /* Responsive */
    @media (max-width: 576px) {
        .alert-floating-container {
            width: calc(100% - 30px);
            bottom: 15px;
            right: 15px;
        }

        .alert-message-text {
            font-size: 0.9rem;
        }
    }
</style>

<div class="alert-floating-container" id="alertContainer">
    <?php
    // ฟังก์ชันแสดง Alert
    $alerts = [];

    // Success Alert
    if (isset($_SESSION['success'])) {
        $alerts[] = [
            'type' => 'success',
            'icon' => 'bx-check-circle',
            'message' => $_SESSION['success']
        ];
        unset($_SESSION['success']);
    }

    // Error Alert
    if (isset($_SESSION['error'])) {
        $alerts[] = [
            'type' => 'error',
            'icon' => 'bx-x-circle',
            'message' => $_SESSION['error']
        ];
        unset($_SESSION['error']);
    }

    // Warning Alert
    if (isset($_SESSION['warning'])) {
        $alerts[] = [
            'type' => 'warning',
            'icon' => 'bx-alert-circle',
            'message' => $_SESSION['warning']
        ];
        unset($_SESSION['warning']);
    }

    // Info Alert
    if (isset($_SESSION['info'])) {
        $alerts[] = [
            'type' => 'info',
            'icon' => 'bx-info-circle',
            'message' => $_SESSION['info']
        ];
        unset($_SESSION['info']);
    }

    // แสดง Alerts
    foreach ($alerts as $index => $alert) {
        $typeClass = 'alert-' . $alert['type'] . '-custom';
        echo '
        <div class="alert alert-custom ' . $typeClass . ' alert-dismissible fade show d-flex align-items-center" role="alert" data-alert-id="' . $index . '">
            
            <div class="alert-icon">
                <i class="bx ' . htmlspecialchars($alert['icon']) . '"></i>
            </div>
            
            <div class="alert-content flex-grow-1">
                <div class="alert-message-text">' . htmlspecialchars($alert['message']) . '</div>
            </div>
            
            <button type="button" class="btn-close-custom" data-bs-dismiss="alert" aria-label="Close">
                <i class="bx bx-x"></i>
            </button>

        </div>';
    }
    ?>
</div>

<script>
    // Auto-dismiss alerts after 5 seconds
    document.querySelectorAll('.alert-custom').forEach((alert, index) => {
        setTimeout(() => {
            alert.classList.add('hide');
            setTimeout(() => alert.remove(), 300);
        }, 5000 + (index * 200)); // Stagger if multiple alerts
    });

    // Handle manual close
    document.querySelectorAll('[data-bs-dismiss="alert"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const alert = this.closest('.alert-custom');
            alert.classList.add('hide');
            setTimeout(() => alert.remove(), 300);
        });
    });
</script>