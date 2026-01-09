    <style>
        .footer {
            background-color: white;
            border-top: 2px solid #dee2e6;
            padding: 1.5rem 0;
            margin-top: auto;
        }

        .footer-text {
            color: #6c757d;
            font-size: 0.875rem;
            text-align: center;
        }

        .footer-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .footer-divider {
            color: #dee2e6;
        }

        .version-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.85rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #dee2e6;
            border-radius: 20px;
            font-size: 0.75rem;
            color: #495057;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
        }

        .school-name {
            font-weight: 600;
            color: #495057;
        }

        .creator-name {
            color: #6c757d;
        }

        .creator-name i {
            color: #dc3545;
        }
    </style>
    <!-- Footer -->
    <footer class="footer mt-auto">
        <div class="container">
            <div class="footer-text">
                <!-- Row 1: Copyright & Version -->
                <div class="footer-row mb-3">
                    <span>
                        <i class='bx bx-copyright'></i>
                        <?= date('Y') ?> TaskBoard LMS. All rights reserved.
                    </span>
                    <span class="footer-divider">|</span>
                    <span class="version-badge">
                        <i class='bx bx-code-alt'></i>
                        Version 1.0.1 (Build 20251210)
                    </span>
                </div>

                <!-- Row 2: School & Creator -->
                <div class="footer-row">
                    <span class="school-name">
                        <i class='bx bxs-school me-1'></i>
                        วิทยาลัยเทคนิคกำแพงเพชร
                    </span>
                    <span class="footer-divider">|</span>
                    <span class="creator-name">
                        <i class='bx bxs-heart'></i>
                        Created by <span class="school-name">Sathanon Chotjantuk</span>
                    </span>
                </div>
            </div>
        </div>
    </footer>