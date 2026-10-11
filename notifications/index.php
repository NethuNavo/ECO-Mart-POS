<?php
$pageTitle = 'Inventory Notifications & Stock Alerts';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();

// Low stock items
$lowStock = $db->query("SELECT p.*, c.name as category_name, s.company_name as supplier_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN suppliers s ON p.supplier_id = s.id 
    WHERE p.quantity <= p.min_stock_level AND p.status = 'active' 
    ORDER BY p.quantity ASC")->fetchAll();

// Expiring items within 30 days
$expiring = $db->query("SELECT p.*, c.name as category_name, s.company_name as supplier_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN suppliers s ON p.supplier_id = s.id 
    WHERE p.expiry_date IS NOT NULL AND p.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND p.status = 'active'
    ORDER BY p.expiry_date ASC")->fetchAll();
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="background: #f8fafc; min-height: calc(100vh - 65px);">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Inventory Alerts & Batch Expiry Warnings</h3>
                <p class="text-muted small m-0">Critical supermarket replenishment notices, threshold breaches and perishable clearance items.</p>
            </div>
            <a href="<?= BASE_URL ?>purchases/create.php" class="btn btn-primary fw-semibold px-3 py-2 shadow-sm">
                <i class="fa-solid fa-truck-ramp-box me-1"></i> Issue Restock PO
            </a>
        </div>

        <div class="row g-4">
            <!-- Low Stock Alerts -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden h-100">
                    <div class="p-3 border-bottom bg-warning-subtle d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark m-0"><i class="fa-solid fa-triangle-exclamation text-warning me-2"></i> Low & Depleted Stock Items (<?= count($lowStock) ?>)</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-muted">
                                <tr>
                                    <th>Product</th>
                                    <th>Current Stock</th>
                                    <th>Min Alert</th>
                                    <th>Supplier</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($lowStock)): ?>
                                    <tr><td colspan="5" class="text-center py-4 text-muted">All inventory items are currently above threshold!</td></tr>
                                <?php else: ?>
                                    <?php foreach ($lowStock as $ls): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold small text-dark"><?= htmlspecialchars($ls['name']) ?></div>
                                                <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($ls['barcode']) ?></div>
                                            </td>
                                            <td>
                                                <span class="badge <?= $ls['quantity'] <= 0 ? 'bg-danger' : 'bg-warning text-dark' ?> fw-bold">
                                                    <?= $ls['quantity'] ?> <?= htmlspecialchars($ls['unit']) ?>
                                                </span>
                                            </td>
                                            <td class="small text-muted"><?= $ls['min_stock_level'] ?></td>
                                            <td class="small text-muted"><?= htmlspecialchars($ls['supplier_name'] ?? 'N/A') ?></td>
                                            <td class="text-end">
                                                <a href="<?= BASE_URL ?>purchases/create.php" class="btn btn-sm btn-outline-primary py-0 px-2 small">Reorder</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Expiry Date Warnings -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden h-100">
                    <div class="p-3 border-bottom bg-danger-subtle d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-danger m-0"><i class="fa-solid fa-calendar-xmark me-2"></i> Perishable Expiry Warnings (< 30 Days) (<?= count($expiring) ?>)</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-muted">
                                <tr>
                                    <th>Product</th>
                                    <th>Expiry Date</th>
                                    <th>Qty on Hand</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($expiring)): ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted">No perishable goods expiring soon.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($expiring as $ex): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold small text-dark"><?= htmlspecialchars($ex['name']) ?></div>
                                                <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($ex['category_name']) ?></div>
                                            </td>
                                            <td class="small fw-bold text-danger font-monospace"><?= htmlspecialchars($ex['expiry_date']) ?></td>
                                            <td class="small fw-semibold"><?= $ex['quantity'] ?> <?= htmlspecialchars($ex['unit']) ?></td>
                                            <td class="text-end">
                                                <a href="<?= BASE_URL ?>promotions/index.php" class="btn btn-sm btn-outline-warning py-0 px-2 small" title="Create Flash Clearance Promo">Promo</a>
                                                <a href="<?= BASE_URL ?>inventory/adjust.php?product_id=<?= $ex['id'] ?>" class="btn btn-sm btn-outline-danger py-0 px-2 small" title="Write off expired batch">Write-off</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
