<?php
$currentUser = $_SESSION['user_name'] ?? 'Staff Member';
$currentRole = $_SESSION['user_role'] ?? 'Cashier';
?>
<!-- Top Navigation Bar -->
<nav class="navbar navbar-expand-lg border-bottom sticky-top bg-body">
    <div class="container-fluid px-3">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>dashboard.php">
            <img src="<?= BASE_URL ?>asset/images/eco-mart-logo.svg" alt="ECO Mart" width="38" height="38">
            <span>ECO Mart <span class="badge bg-success-subtle text-success fs-6 py-1 px-2 border border-success-subtle">POS</span></span>
        </a>

        <div class="d-flex align-items-center gap-2 ms-auto">
            <!-- Quick POS Checkout Launch Button -->
            <a href="<?= BASE_URL ?>pos/index.php" class="btn btn-success fw-bold d-flex align-items-center gap-2 px-3 shadow-sm">
                <i class="fa-solid fa-cash-register"></i>
                <span class="d-none d-md-inline">POS Terminal</span>
            </a>

            <!-- Notifications -->
            <a href="<?= BASE_URL ?>notifications/index.php" class="btn btn-outline-secondary position-relative rounded-circle p-2" style="width:40px;height:40px;">
                <i class="fa-regular fa-bell"></i>
                <?php if ($unreadNotifs > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= $unreadNotifs ?>
                    </span>
                <?php endif; ?>
            </a>

            <!-- User Menu -->
            <div class="dropdown">
                <button class="btn btn-light border dropdown-toggle d-flex align-items-center gap-2 py-1 px-2 rounded-3" type="button" data-bs-toggle="dropdown">
                    <div class="bg-emerald-subtle rounded-circle text-success fw-bold d-flex align-items-center justify-content-center" style="width:32px;height:32px;background:#ecfdf5;">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <div class="text-start d-none d-lg-block">
                        <div class="fw-semibold small leading-none"><?= htmlspecialchars($currentUser) ?></div>
                        <div class="text-muted small" style="font-size: 0.75rem;"><?= htmlspecialchars($currentRole) ?></div>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                    <li class="px-3 py-2 border-bottom">
                        <div class="fw-bold"><?= htmlspecialchars($currentUser) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></div>
                        <span class="badge bg-primary-subtle text-primary mt-1"><?= htmlspecialchars($currentRole) ?></span>
                    </li>
                    <?php if ($currentRole === 'Administrator'): ?>
                        <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>settings/index.php"><i class="fa-solid fa-gear me-2 text-secondary"></i> Store Settings</a></li>
                        <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>users/index.php"><i class="fa-solid fa-users-gear me-2 text-secondary"></i> User Management</a></li>
                        <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>audit/index.php"><i class="fa-solid fa-clock-rotate-left me-2 text-secondary"></i> Security Audit Log</a></li>
                        <li><hr class="dropdown-divider"></li>
                    <?php endif; ?>
                    <li><a class="dropdown-item py-2 text-danger" href="<?= BASE_URL ?>logout.php"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Log Out</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
