<?php
$currentPath = $_SERVER['REQUEST_URI'];
$role = $_SESSION['user_role'] ?? 'Cashier';
function isNavActive($needle, $currentPath) {
    return strpos($currentPath, $needle) !== false ? 'active' : '';
}
?>
<div class="sidebar p-3 d-flex flex-column justify-content-between">
    <div>
        <div class="text-uppercase text-muted fw-bold px-3 mb-2" style="font-size: 0.7rem; letter-spacing: 0.05em;">Main Navigation</div>
        
        <a href="<?= BASE_URL ?>dashboard.php" class="nav-link-custom <?= isNavActive('dashboard.php', $currentPath) ?>">
            <i class="fa-solid fa-chart-pie text-success"></i> Dashboard
        </a>
        
        <a href="<?= BASE_URL ?>pos/index.php" class="nav-link-custom <?= isNavActive('/pos/', $currentPath) ?>">
            <i class="fa-solid fa-cash-register text-emerald"></i> <strong>POS Terminal</strong>
        </a>

        <a href="<?= BASE_URL ?>sales/index.php" class="nav-link-custom <?= isNavActive('/sales/', $currentPath) ?>">
            <i class="fa-solid fa-receipt text-primary"></i> Sales & Invoices
        </a>

        <a href="<?= BASE_URL ?>shifts/index.php" class="nav-link-custom <?= isNavActive('/shifts/', $currentPath) ?>">
            <i class="fa-solid fa-clock text-warning"></i> Cashier Shifts
        </a>

        <a href="<?= BASE_URL ?>returns/index.php" class="nav-link-custom <?= isNavActive('/returns/', $currentPath) ?>">
            <i class="fa-solid fa-rotate-left text-danger"></i> Returns & Refunds
        </a>

        <div class="text-uppercase text-muted fw-bold px-3 mt-3 mb-2" style="font-size: 0.7rem; letter-spacing: 0.05em;">Inventory & Supply</div>
        
        <a href="<?= BASE_URL ?>products/index.php" class="nav-link-custom <?= isNavActive('/products/', $currentPath) ?>">
            <i class="fa-solid fa-boxes-stacked text-info"></i> Products Catalog
        </a>

        <a href="<?= BASE_URL ?>inventory/index.php" class="nav-link-custom <?= isNavActive('/inventory/', $currentPath) ?>">
            <i class="fa-solid fa-warehouse text-secondary"></i> Stock & Alerts
        </a>

        <?php if ($role === 'Administrator' || $role === 'Manager'): ?>
            <a href="<?= BASE_URL ?>purchases/index.php" class="nav-link-custom <?= isNavActive('/purchases/', $currentPath) ?>">
                <i class="fa-solid fa-truck-ramp-box text-dark"></i> Purchases & PO
            </a>

            <a href="<?= BASE_URL ?>suppliers/index.php" class="nav-link-custom <?= isNavActive('/suppliers/', $currentPath) ?>">
                <i class="fa-solid fa-handshake text-primary"></i> Suppliers
            </a>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>customers/index.php" class="nav-link-custom <?= isNavActive('/customers/', $currentPath) ?>">
            <i class="fa-solid fa-users text-success"></i> Customers & Loyalty
        </a>

        <?php if ($role === 'Administrator' || $role === 'Manager'): ?>
            <div class="text-uppercase text-muted fw-bold px-3 mt-3 mb-2" style="font-size: 0.7rem; letter-spacing: 0.05em;">Management</div>
            
            <a href="<?= BASE_URL ?>promotions/index.php" class="nav-link-custom <?= isNavActive('/promotions/', $currentPath) ?>">
                <i class="fa-solid fa-tags text-warning"></i> Promotions & Deals
            </a>

            <a href="<?= BASE_URL ?>expenses/index.php" class="nav-link-custom <?= isNavActive('/expenses/', $currentPath) ?>">
                <i class="fa-solid fa-wallet text-danger"></i> Store Expenses
            </a>

            <a href="<?= BASE_URL ?>reports/sales.php" class="nav-link-custom <?= isNavActive('/reports/', $currentPath) ?>">
                <i class="fa-solid fa-chart-line text-indigo"></i> Reports & Analytics
            </a>
        <?php endif; ?>

        <?php if ($role === 'Administrator'): ?>
            <div class="text-uppercase text-muted fw-bold px-3 mt-3 mb-2" style="font-size: 0.7rem; letter-spacing: 0.05em;">Administration</div>
            
            <a href="<?= BASE_URL ?>users/index.php" class="nav-link-custom <?= isNavActive('/users/', $currentPath) ?>">
                <i class="fa-solid fa-user-gear text-info"></i> User Accounts
            </a>

            <a href="<?= BASE_URL ?>audit/index.php" class="nav-link-custom <?= isNavActive('/audit/', $currentPath) ?>">
                <i class="fa-solid fa-shield-halved text-secondary"></i> Audit Trail
            </a>

            <a href="<?= BASE_URL ?>settings/index.php" class="nav-link-custom <?= isNavActive('/settings/', $currentPath) ?>">
                <i class="fa-solid fa-sliders text-dark"></i> System Settings
            </a>
        <?php endif; ?>
    </div>

    <div class="pt-3 border-top mt-3">
        <div class="d-flex align-items-center justify-content-between px-2 text-muted small">
            <span><i class="fa-solid fa-circle text-success me-1" style="font-size: 8px;"></i> XAMPP Local</span>
            <span class="badge bg-secondary-subtle text-secondary">v2.5</span>
        </div>
    </div>
</div>
