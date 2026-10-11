<?php
$pageTitle = 'Inventory & Stock Management';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();

// Inventory Valuation Summary
$valStmt = $db->query("SELECT 
    SUM(quantity) as total_units,
    SUM(quantity * cost_price) as total_cost_value,
    SUM(quantity * selling_price) as total_retail_value 
    FROM products WHERE status='active'");
$val = $valStmt->fetch();

// Stock Items
$items = $db->query("SELECT p.*, c.name as category_name, b.name as brand_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN brands b ON p.brand_id = b.id 
    WHERE p.status = 'active' 
    ORDER BY p.quantity ASC")->fetchAll();

// Recent Stock Movements
$movements = $db->query("SELECT sm.*, p.name as product_name, p.barcode, u.name as user_name 
    FROM stock_movements sm 
    JOIN products p ON sm.product_id = p.id 
    LEFT JOIN users u ON sm.user_id = u.id 
    ORDER BY sm.id DESC LIMIT 10")->fetchAll();
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="background: #f8fafc; min-height: calc(100vh - 65px);">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Live Stock & Inventory Valuation</h3>
                <p class="text-muted small m-0">Real-time shelf counts, automatic decrement tracking, and stock adjustments.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>inventory/adjust.php" class="btn btn-warning fw-semibold px-3 py-2 shadow-sm">
                    <i class="fa-solid fa-sliders me-1"></i> Stock Adjustment
                </a>
                <a href="<?= BASE_URL ?>purchases/create.php" class="btn btn-success fw-semibold px-3 py-2 shadow-sm">
                    <i class="fa-solid fa-truck-ramp-box me-1"></i> Receive PO Stock
                </a>
            </div>
        </div>

        <!-- Inventory Valuation KPIs -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <div class="text-muted small text-uppercase fw-semibold">Total Stock on Hand</div>
                    <div class="fs-3 fw-bold text-dark mt-1"><?= number_format((float)($val['total_units'] ?? 0)) ?> <span class="fs-6 text-muted fw-normal">Units</span></div>
                    <div class="small text-muted mt-1">Across all categorized supermarket aisles</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <div class="text-muted small text-uppercase fw-semibold">Inventory Valuation (Cost Basis)</div>
                    <div class="fs-3 fw-bold text-primary currency-font mt-1"><?= formatCurrency((float)($val['total_cost_value'] ?? 0)) ?></div>
                    <div class="small text-muted mt-1">Purchase asset investment</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <div class="text-muted small text-uppercase fw-semibold">Expected Retail Value</div>
                    <div class="fs-3 fw-bold text-success currency-font mt-1"><?= formatCurrency((float)($val['total_retail_value'] ?? 0)) ?></div>
                    <div class="small text-success mt-1"><i class="fa-solid fa-arrow-trend-up me-1"></i> Potential revenue at current MRP</div>
                </div>
            </div>
        </div>

        <!-- Stock Levels Table -->
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold m-0"><i class="fa-solid fa-boxes-stacked text-secondary me-2"></i> Current SKU Quantities & Health Status</h6>
                <span class="badge bg-light text-dark border">Sorted by lowest stock</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-muted">
                        <tr>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>In Stock</th>
                            <th>Min Alert</th>
                            <th>Unit Cost</th>
                            <th>Total Cost Value</th>
                            <th>Stock Status</th>
                            <th class="text-end">Quick Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <?php 
                                $isOut = $item['quantity'] <= 0;
                                $isLow = $item['quantity'] <= $item['min_stock_level'];
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-bold small text-dark"><?= htmlspecialchars($item['name']) ?></div>
                                    <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($item['barcode']) ?></div>
                                </td>
                                <td><span class="badge bg-light text-dark border small"><?= htmlspecialchars($item['category_name'] ?? '') ?></span></td>
                                <td class="fw-bold <?= $isOut ? 'text-danger' : ($isLow ? 'text-warning' : 'text-dark') ?>">
                                    <?= $item['quantity'] ?> <?= htmlspecialchars($item['unit']) ?>
                                </td>
                                <td class="small text-muted"><?= $item['min_stock_level'] ?></td>
                                <td class="small currency-font"><?= formatCurrency((float)$item['cost_price']) ?></td>
                                <td class="small fw-semibold currency-font"><?= formatCurrency((float)($item['quantity'] * $item['cost_price'])) ?></td>
                                <td>
                                    <?php if ($isOut): ?>
                                        <span class="badge bg-danger">OUT OF STOCK</span>
                                    <?php elseif ($isLow): ?>
                                        <span class="badge bg-warning text-dark">LOW STOCK</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">HEALTHY</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>inventory/adjust.php?product_id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-secondary py-0 px-2 small">
                                        Adjust
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Stock Audit Movements -->
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="p-3 border-bottom">
                <h6 class="fw-bold m-0"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Stock Movement Audit Ledger (Recent 10 Events)</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light small text-muted">
                        <tr>
                            <th>Date/Time</th>
                            <th>Product</th>
                            <th>Event Type</th>
                            <th>Qty Change</th>
                            <th>Balance After</th>
                            <th>Reference</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movements as $m): ?>
                            <tr>
                                <td class="small text-muted"><?= date('Y-m-d H:i', strtotime($m['created_at'])) ?></td>
                                <td class="small fw-semibold"><?= htmlspecialchars($m['product_name']) ?></td>
                                <td>
                                    <span class="badge <?= $m['type'] === 'sale' ? 'bg-danger-subtle text-danger' : ($m['type'] === 'purchase' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary') ?> text-uppercase" style="font-size: 0.7rem;">
                                        <?= htmlspecialchars($m['type']) ?>
                                    </span>
                                </td>
                                <td class="small fw-bold <?= $m['quantity'] > 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= $m['quantity'] > 0 ? '+' . $m['quantity'] : $m['quantity'] ?>
                                </td>
                                <td class="small fw-bold"><?= $m['balance_after'] ?></td>
                                <td class="small font-monospace text-muted"><?= htmlspecialchars($m['reference_id'] ?? '-') ?></td>
                                <td class="small text-muted"><?= htmlspecialchars($m['user_name'] ?? 'System') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
