<?php
$pageTitle = 'Sales & Invoices Ledger';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();
$search = trim($_GET['search'] ?? '');
$method = trim($_GET['method'] ?? '');

$query = "SELECT s.*, c.name as customer_name, c.phone as customer_phone, u.name as cashier_name 
    FROM sales s 
    LEFT JOIN customers c ON s.customer_id = c.id 
    LEFT JOIN users u ON s.user_id = u.id 
    WHERE 1=1";
$params = [];

if ($search !== '') {
    $query .= " AND (s.invoice_number LIKE ? OR c.name LIKE ? OR c.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($method !== '') {
    $query .= " AND s.payment_method = ?";
    $params[] = $method;
}

$query .= " ORDER BY s.id DESC LIMIT 50";
$stmt = $db->prepare($query);
$stmt->execute($params);
$sales = $stmt->fetchAll();
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="background: #f8fafc; min-height: calc(100vh - 65px);">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Sales & Checkout Invoices</h3>
                <p class="text-muted small m-0">Permanent log of all POS transactions, tax invoices, and payment tender modes.</p>
            </div>
            <a href="<?= BASE_URL ?>pos/index.php" class="btn btn-success fw-bold px-3 py-2 shadow-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-cash-register"></i> New Sale Terminal
            </a>
        </div>

        <!-- Filter Bar -->
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
            <form method="GET" class="row g-2">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by invoice #, customer name or phone..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="method" class="form-select">
                        <option value="">All Payment Methods</option>
                        <option value="cash" <?= $method === 'cash' ? 'selected' : '' ?>>Cash</option>
                        <option value="card" <?= $method === 'card' ? 'selected' : '' ?>>Credit/Debit Card</option>
                        <option value="online" <?= $method === 'online' ? 'selected' : '' ?>>Online / LankaQR</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                    <?php if ($search !== '' || $method !== ''): ?>
                        <a href="<?= BASE_URL ?>sales/index.php" class="btn btn-outline-secondary"><i class="fa-solid fa-xmark"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Sales Table -->
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-muted">
                        <tr>
                            <th>Invoice Number</th>
                            <th>Date & Time</th>
                            <th>Customer</th>
                            <th>Cashier</th>
                            <th>Payment</th>
                            <th>Grand Total</th>
                            <th>Points</th>
                            <th>Status</th>
                            <th class="text-end">Receipt / Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sales)): ?>
                            <tr><td colspan="9" class="text-center py-5 text-muted">No sales transactions found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($sales as $s): ?>
                                <tr>
                                    <td>
                                        <a href="<?= BASE_URL ?>sales/receipt.php?id=<?= $s['id'] ?>" class="fw-bold text-decoration-none font-monospace small">
                                            <?= htmlspecialchars($s['invoice_number']) ?>
                                        </a>
                                    </td>
                                    <td class="small text-muted"><?= date('Y-m-d H:i', strtotime($s['created_at'])) ?></td>
                                    <td>
                                        <div class="fw-semibold small"><?= htmlspecialchars($s['customer_name'] ?? 'Walk-in Customer') ?></div>
                                        <?php if (!empty($s['customer_phone']) && $s['customer_phone'] !== '0000000000'): ?>
                                            <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($s['customer_phone']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small text-muted"><?= htmlspecialchars($s['cashier_name'] ?? 'Staff') ?></td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary text-uppercase" style="font-size:0.7rem;"><?= htmlspecialchars($s['payment_method']) ?></span>
                                    </td>
                                    <td class="fw-bold currency-font text-success">
                                        <?= formatCurrency((float)$s['grand_total']) ?>
                                    </td>
                                    <td>
                                        <?php if ($s['loyalty_points_earned'] > 0): ?>
                                            <span class="badge bg-primary-subtle text-primary">+<?= $s['loyalty_points_earned'] ?> pts</span>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $s['status'] === 'completed' ? 'bg-success' : 'bg-warning' ?>"><?= ucfirst($s['status']) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>sales/receipt.php?id=<?= $s['id'] ?>&format=thermal" class="btn btn-sm btn-outline-dark py-0 px-2" title="Thermal Receipt (80mm)">
                                            <i class="fa-solid fa-receipt"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>sales/receipt.php?id=<?= $s['id'] ?>&format=a4" class="btn btn-sm btn-outline-primary py-0 px-2" title="Full A4 Invoice">
                                            <i class="fa-solid fa-file-invoice"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>returns/create.php?invoice=<?= urlencode($s['invoice_number']) ?>" class="btn btn-sm btn-outline-danger py-0 px-2" title="Process Return/Refund">
                                            <i class="fa-solid fa-rotate-left"></i>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
