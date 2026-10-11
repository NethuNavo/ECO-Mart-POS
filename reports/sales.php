<?php
$pageTitle = 'Sales & Profit Analytics';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// 1. Sales Totals & Profit
$summaryStmt = $db->prepare("SELECT 
    COUNT(s.id) as total_invoices,
    COALESCE(SUM(s.grand_total), 0) as total_revenue,
    COALESCE(SUM(s.tax_total), 0) as total_tax,
    COALESCE(SUM(s.discount_total), 0) as total_discount,
    COALESCE(SUM(si.quantity * si.unit_price), 0) - COALESCE(SUM(si.quantity * si.cost_price), 0) as estimated_gross_profit
    FROM sales s
    LEFT JOIN sale_items si ON s.id = si.sale_id
    WHERE DATE(s.created_at) BETWEEN ? AND ? AND s.status = 'completed'");
$summaryStmt->execute([$startDate, $endDate]);
$summary = $summaryStmt->fetch();

// 2. Sales by Category
$catStmt = $db->prepare("SELECT c.name as category_name, SUM(si.line_total) as category_revenue, SUM(si.quantity) as units_sold
    FROM sale_items si
    JOIN sales s ON si.sale_id = s.id
    JOIN products p ON si.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    WHERE DATE(s.created_at) BETWEEN ? AND ? AND s.status = 'completed'
    GROUP BY c.id ORDER BY category_revenue DESC");
$catStmt->execute([$startDate, $endDate]);
$categoriesData = $catStmt->fetchAll();

// 3. Top 10 Best-Selling Products
$topStmt = $db->prepare("SELECT p.name, p.barcode, SUM(si.quantity) as total_qty, SUM(si.line_total) as total_revenue
    FROM sale_items si
    JOIN sales s ON si.sale_id = s.id
    JOIN products p ON si.product_id = p.id
    WHERE DATE(s.created_at) BETWEEN ? AND ? AND s.status = 'completed'
    GROUP BY p.id ORDER BY total_qty DESC LIMIT 10");
$topStmt->execute([$startDate, $endDate]);
$topProducts = $topStmt->fetchAll();

// 4. Payment Methods Distribution
$payStmt = $db->prepare("SELECT payment_method, COUNT(id) as tx_count, SUM(grand_total) as total_amount
    FROM sales
    WHERE DATE(created_at) BETWEEN ? AND ? AND status = 'completed'
    GROUP BY payment_method");
$payStmt->execute([$startDate, $endDate]);
$payMethods = $payStmt->fetchAll();
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="background: #f8fafc; min-height: calc(100vh - 65px);">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Supermarket Sales & Profit Analytics</h3>
                <p class="text-muted small m-0">In-depth revenue intelligence, Gross Margin (COGS deduction), category breakdown & top performers.</p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-outline-secondary fw-semibold"><i class="fa-solid fa-print me-1"></i> Print Report</button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4 no-print">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted m-0">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted m-0">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2"><i class="fa-solid fa-filter me-1"></i> Generate Analytic Metrics</button>
                </div>
            </form>
        </div>

        <!-- Summary KPIs -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <div class="text-muted small fw-semibold text-uppercase">Gross Revenue</div>
                    <div class="fs-4 fw-bold text-success currency-font mt-1"><?= formatCurrency((float)$summary['total_revenue']) ?></div>
                    <div class="small text-muted mt-1"><?= $summary['total_invoices'] ?> Total Invoices Billed</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <div class="text-muted small fw-semibold text-uppercase">Gross Margin (Profit)</div>
                    <div class="fs-4 fw-bold text-primary currency-font mt-1"><?= formatCurrency((float)$summary['estimated_gross_profit']) ?></div>
                    <div class="small text-success mt-1">Revenue minus wholesale COGS</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <div class="text-muted small fw-semibold text-uppercase">Average Basket Size</div>
                    <div class="fs-4 fw-bold text-dark currency-font mt-1">
                        <?= $summary['total_invoices'] > 0 ? formatCurrency((float)$summary['total_revenue'] / $summary['total_invoices']) : 'Rs. 0.00' ?>
                    </div>
                    <div class="small text-muted mt-1">Average transaction value</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <div class="text-muted small fw-semibold text-uppercase">Total Discounts Given</div>
                    <div class="fs-4 fw-bold text-warning currency-font mt-1"><?= formatCurrency((float)$summary['total_discount']) ?></div>
                    <div class="small text-muted mt-1">Promotional savings to shoppers</div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Top Selling Products -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden h-100">
                    <div class="p-3 border-bottom">
                        <h6 class="fw-bold m-0"><i class="fa-solid fa-trophy text-warning me-2"></i> Top 10 Best-Selling Supermarket Products</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-muted">
                                <tr>
                                    <th>#</th>
                                    <th>Product Name</th>
                                    <th class="text-center">Units Sold</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($topProducts)): ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted">No sales in this date range.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($topProducts as $idx => $tp): ?>
                                        <tr>
                                            <td class="text-muted small"><?= $idx + 1 ?></td>
                                            <td>
                                                <div class="fw-semibold small text-dark"><?= htmlspecialchars($tp['name']) ?></div>
                                                <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($tp['barcode']) ?></div>
                                            </td>
                                            <td class="text-center fw-bold small"><?= number_format($tp['total_qty']) ?></td>
                                            <td class="text-end fw-bold currency-font text-success small"><?= formatCurrency((float)$tp['total_revenue']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Category & Tender Distribution -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                    <div class="p-3 border-bottom">
                        <h6 class="fw-bold m-0"><i class="fa-solid fa-layer-group text-primary me-2"></i> Department / Category Share</h6>
                    </div>
                    <div class="p-3">
                        <?php foreach ($categoriesData as $cd): ?>
                            <div class="d-flex justify-content-between small fw-semibold mb-1">
                                <span><?= htmlspecialchars($cd['category_name']) ?></span>
                                <span class="currency-font text-dark"><?= formatCurrency((float)$cd['category_revenue']) ?></span>
                            </div>
                            <div class="progress mb-3" style="height: 6px;">
                                <?php $pct = $summary['total_revenue'] > 0 ? ($cd['category_revenue'] / $summary['total_revenue']) * 100 : 0; ?>
                                <div class="progress-bar bg-primary" style="width: <?= $pct ?>%"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                    <div class="p-3 border-bottom">
                        <h6 class="fw-bold m-0"><i class="fa-solid fa-credit-card text-success me-2"></i> Payment Mode Breakdown</h6>
                    </div>
                    <div class="p-3">
                        <?php foreach ($payMethods as $pm): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <div>
                                    <div class="fw-bold text-uppercase small"><?= htmlspecialchars($pm['payment_method']) ?></div>
                                    <div class="text-muted" style="font-size:0.75rem;"><?= $pm['tx_count'] ?> Transactions</div>
                                </div>
                                <div class="fw-bold currency-font text-success"><?= formatCurrency((float)$pm['total_amount']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
