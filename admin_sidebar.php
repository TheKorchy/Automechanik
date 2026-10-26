<?php require_once "config/auth.php"; requireAdmin(); ?>

<div class="offcanvas offcanvas-start bg-dark text-white"
     id="adminSidebar">

    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title">
            <i class="feather-settings"></i> Administrácia
        </h5>
        <button class="btn-close btn-close-white"
                data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body p-0">
        <ul class="list-group list-group-flush">

            <li class="list-group-item bg-dark">
                <a href="admin_dashboard.php" class="text-white">
                    <i class="fas fa-chart-line me-2"></i> Prehľad
                </a>
            </li>

            <li class="list-group-item bg-dark">
                <a href="admin_reports.php" class="text-white">
                    <i class="fas fa-file-alt me-2"></i> Reports
                </a>
            </li>

            <li class="list-group-item bg-dark">
                <a href="admin_payments.php" class="text-white">
                    <i class="fas fa-credit-card me-2"></i> Payments
                </a>
            </li>

            <li class="list-group-item bg-dark">
                <a href="admin_customers.php" class="text-white">
                    <i class="fas fa-users me-2"></i> Customers
                </a>
            </li>

            <li class="list-group-item bg-dark">
                <a href="admin_settings.php" class="text-white">
                    <i class="fas fa-cog me-2"></i> Settings
                </a>
            </li>

            <li class="list-group-item bg-dark">
                <a href="admin_auth.php" class="text-white">
                    <i class="fas fa-user-shield me-2"></i> Authentication
                </a>
            </li>

            <li class="list-group-item bg-dark">
                <a href="logout.php" class="text-danger">
                    <i class="fas fa-power-off me-2"></i> Odhlásiť
                </a>
            </li>

        </ul>
    </div>
</div>
