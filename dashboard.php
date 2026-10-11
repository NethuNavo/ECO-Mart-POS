<?php
$pageTitle = 'Executive Dashboard';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$db = Database::getConnection();

// 1. Today's Sales & Transactions
$todaySalesStmt = $db->query("SELECT COALESCE(SUM(grand_total), 0) as total_sales, COUNT(*) as tx_count FROM sales WHERE DATE(created_at) = CURDATE() AND status = 'completed'");
$todayStats = $todaySalesStmt->fetch();
$todaySales = $todayStats['total_sales'];
$todayTxCount = $todayStats['tx_count'];

// 2. All-Time Total Revenue & Estimated Profit
$profitStmt = $db->query("SELECT 
    COALESCE(SUM(si.line_total), 0) as total_revenue,
    COALESCE(SUM((si.unit_price - si.cost_price) * si.quantity - si.discount_amount), 0) as estimated_profit
    FROM sale_items si 
    JOIN sales s ON si.sale_id = s.id 
    WHERE s.status = 'completed'");
$profitStats = $profitStmt->fetch();
$totalRevenue = $profitStats['total_revenue'];
$estimatedProfit = $profitStats['estimated_profit'];

// 3. Product & Inventory Counts
$prodCountStmt = $db->query("SELECT COUNT(*) as count FROM products WHERE status = 'active'");
$totalProducts = $prodCountStmt->fetch()['count'];

$lowStockStmt = $db->query("SELECT COUNT(*) as count FROM products WHERE quantity <= min_stock_level AND status = 'active'");
$lowStockCount = $lowStockStmt->fetch()['count'];

$expiringStmt = $db->query("SELECT COUNT(*) as count FROM products WHERE expiry_date IS NOT NULL AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND status = 'active'");
$expiringCount = $expiringStmt->fetch()['count'];

// 4. Customers & Suppliers
$custCount = $db->query("SELECT COUNT(*) as count FROM customers WHERE status = 'active'")->fetch()['count'];
$suppCount = $db->query("SELECT COUNT(*) as count FROM suppliers WHERE status = 'active'")->fetch()['count'];
$pendingPurchases = $db->query("SELECT COUNT(*) as count FROM purchases WHERE status = 'pending'")->fetch()['count'];

// 5. Recent Sales Table
$recentSales = $db->query("SELECT s.*, c.name as customer_name, u.name as cashier_name 
    FROM sales s 
    LEFT JOIN customers c ON s.customer_id = c.id 
    LEFT JOIN users u ON s.user_id = u.id 
    ORDER BY s.id DESC LIMIT 6")->fetchAll();

// 6. Low Stock Table
$lowStockItems = $db->query("SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.quantity <= p.min_stock_level 
    ORDER BY p.quantity ASC LIMIT 5")->fetchAll();

// 7. Last 7 Days Sales Trend for Chart.js
$salesTrend = $db->query("SELECT DATE(created_at) as sale_date, COALESCE(SUM(grand_total), 0) as daily_total 
    FROM sales 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND status = 'completed'
    GROUP BY DATE(created_at) 
    ORDER BY sale_date ASC")->fetchAll();

$chartLabels = [];
$chartData = [];
// Populate last 7 days including empty days
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $chartLabels[] = date('M d', strtotime($d));
    $found = 0;
    foreach ($salesTrend as $st) {
        if ($st['sale_date'] === $d) {
            $found = (float)$st['daily_total'];
            break;
        }
    }
    $chartData[] = $found;
}
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="background-color: #f8fafc; min-height: calc(100vh - 65px);">
        <!-- Dashboard Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Supermarket Operations Dashboard</h3>
                <p class="text-muted small m-0">Live financial metrics, inventory alerts, and POS terminal transactions.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASE_URL ?>pos/index.php" class="btn btn-success fw-bold px-3 py-2 shadow-sm d-flex align-items-center gap-2">
                    <i class="fa-solid fa-cash-register"></i> Open POS Terminal
                </a>
                <a href="<?= BASE_URL ?>reports/sales.php" class="btn btn-outline-secondary px-3 py-2">
                    <i class="fa-solid fa-file-excel me-1"></i> Reports
                </a>
            </div>
        </div>

        <!-- 4 Primary KPI Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card card-kpi p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase">Today's Sales</div>
                            <div class="fs-4 fw-bold currency-font text-dark mt-1"><?= formatCurrency((float)$todaySales) ?></div>
                            <div class="small text-success mt-1"><i class="fa-solid fa-arrow-trend-up me-1"></i> <?= $todayTxCount ?> Transactions Today</div>
                        </div>
                        <div class="bg-success-subtle text-success p-3 rounded-4">
                            <i class="fa-solid fa-wallet fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card card-kpi p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase">Estimated Net Profit</div>
                            <div class="fs-4 fw-bold currency-font text-emerald mt-1" style="color:#245C3A;"><?= formatCurrency((float)$estimatedProfit) ?></div>
                            <div class="small text-muted mt-1">Margin on completed bills</div>
                        </div>
                        <div class="bg-emerald-subtle p-3 rounded-4" style="background:#EAF0E5; color:#245C3A;">
                            <i class="fa-solid fa-sack-dollar fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card card-kpi p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase">Low Stock Warnings</div>
                            <div class="fs-4 fw-bold text-danger mt-1"><?= $lowStockCount ?> <span class="fs-6 fw-normal text-muted">SKUs</span></div>
                            <div class="small text-danger mt-1"><i class="fa-solid fa-circle-exclamation me-1"></i> <?= $expiringCount ?> Expiring Soon</div>
                        </div>
                        <div class="bg-danger-subtle text-danger p-3 rounded-4">
                            <i class="fa-solid fa-triangle-exclamation fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card card-kpi p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase">Total Active SKUs</div>
                            <div class="fs-4 fw-bold text-dark mt-1"><?= $totalProducts ?></div>
                            <div class="small text-muted mt-1"><?= $custCount ?> Registered Customers</div>
                        </div>
                        <div class="bg-primary-subtle text-primary p-3 rounded-4">
                            <i class="fa-solid fa-boxes-stacked fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold m-0"><i class="fa-solid fa-chart-line text-success me-2"></i> 7-Day Revenue Trend (LKR)</h6>
                        <span class="badge bg-light text-dark border">Daily Consolidated</span>
                    </div>
                    <div style="height: 250px;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold m-0"><i class="fa-solid fa-pie-chart text-info me-2"></i> Payment Split</h6>
                    </div>
                    <div class="d-flex justify-content-center align-items-center" style="height: 220px;">
                        <canvas id="paymentChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Row: Recent Transactions & Low Stock Alerts -->
        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold m-0"><i class="fa-solid fa-receipt text-primary me-2"></i> Recent POS Checkout Sales</h6>
                        <a href="<?= BASE_URL ?>sales/index.php" class="small text-decoration-none fw-semibold">View All &rarr;</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-muted">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Customer</th>
                                    <th>Cashier</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentSales)): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-4">No sales recorded yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recentSales as $sale): ?>
                                        <tr>
                                            <td class="fw-bold small"><?= htmlspecialchars($sale['invoice_number']) ?></td>
                                            <td class="small"><?= htmlspecialchars($sale['customer_name'] ?? 'Walk-in') ?></td>
                                            <td class="small text-muted"><?= htmlspecialchars($sale['cashier_name'] ?? 'Staff') ?></td>
                                            <td class="fw-bold currency-font text-success small"><?= formatCurrency((float)$sale['grand_total']) ?></td>
                                            <td>
                                                <span class="badge bg-secondary-subtle text-secondary text-uppercase" style="font-size:0.7rem;"><?= htmlspecialchars($sale['payment_method']) ?></span>
                                            </td>
                                            <td>
                                                <a href="<?= BASE_URL ?>sales/receipt.php?id=<?= $sale['id'] ?>" class="btn btn-sm btn-outline-primary py-0 px-2" title="Print Receipt">
                                                    <i class="fa-solid fa-print"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold m-0 text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i> Critical Low Stock SKUs</h6>
                        <a href="<?= BASE_URL ?>inventory/index.php" class="small text-decoration-none text-danger fw-semibold">Manage &rarr;</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-muted">
                                <tr>
                                    <th>Product</th>
                                    <th>In Stock</th>
                                    <th>Threshold</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($lowStockItems)): ?>
                                    <tr><td colspan="4" class="text-center text-success py-4"><i class="fa-solid fa-circle-check me-1"></i> All products are healthy!</td></tr>
                                <?php else: ?>
                                    <?php foreach ($lowStockItems as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold small"><?= htmlspecialchars($item['name']) ?></div>
                                                <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($item['barcode']) ?></div>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger fw-bold"><?= $item['quantity'] ?> <?= htmlspecialchars($item['unit']) ?></span>
                                            </td>
                                            <td class="small text-muted"><?= $item['min_stock_level'] ?></td>
                                            <td>
                                                <a href="<?= BASE_URL ?>purchases/create.php?product_id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger py-0 px-2 small">
                                                    Restock
                                                </a>
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

<script>
// Sales Trend Chart
const ctxSales = document.getElementById('salesChart').getContext('2d');
new Chart(ctxSales, {
    type: 'line',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            label: 'Daily Sales (Rs.)',
            data: <?= json_encode($chartData) ?>,
            borderColor: '#245C3A',
            backgroundColor: 'rgba(5, 150, 105, 0.1)',
            fill: true,
            tension: 0.35,
            borderWidth: 2.5,
            pointBackgroundColor: '#B7D94C'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#E4E0D4' } },
            x: { grid: { display: false } }
        }
    }
});

// Payment Split Doughnut
const ctxPayment = document.getElementById('paymentChart').getContext('2d');
new Chart(ctxPayment, {
    type: 'doughnut',
    data: {
        labels: ['Cash', 'Credit Card', 'Online QR'],
        datasets: [{
            data: [65, 25, 10],
            backgroundColor: ['#245C3A', '#B7D94C', '#A8B88A']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
