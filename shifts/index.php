<?php
$pageTitle = 'Cashier Shifts & Till Balancing';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();

$shifts = $db->query("SELECT cs.*, u.name as cashier_name 
    FROM cashier_shifts cs 
    JOIN users u ON cs.user_id = u.id 
    ORDER BY cs.id DESC")->fetchAll();

$userId = $_SESSION['user_id'] ?? 1;
$activeShift = $db->query("SELECT * FROM cashier_shifts WHERE user_id = {$userId} AND status = 'open' ORDER BY id DESC LIMIT 1")->fetch();
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="background: #f8fafc; min-height: calc(100vh - 65px);">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Cashier Shift & Drawer Balancing</h3>
                <p class="text-muted small m-0">Open shifts with float, monitor live sales tender, and reconcile end-of-shift drawer discrepancies.</p>
            </div>
            <div class="d-flex gap-2">
                <?php if ($activeShift): ?>
                    <a href="<?= BASE_URL ?>shifts/close.php?id=<?= $activeShift['id'] ?>" class="btn btn-warning fw-bold px-3 py-2 shadow-sm">
                        <i class="fa-solid fa-lock me-1"></i> Close Active Shift #<?= $activeShift['id'] ?>
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>shifts/open.php" class="btn btn-success fw-bold px-3 py-2 shadow-sm">
                        <i class="fa-solid fa-clock me-1"></i> Open New Cashier Shift
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-muted">
                        <tr>
                            <th>Shift #</th>
                            <th>Cashier</th>
                            <th>Opened Time</th>
                            <th>Opening Float</th>
                            <th>Cash Sales</th>
                            <th>Card Sales</th>
                            <th>Expected Drawer</th>
                            <th>Actual Closing</th>
                            <th>Discrepancy</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($shifts)): ?>
                            <tr><td colspan="11" class="text-center py-5 text-muted">No cashier shifts registered.</td></tr>
                        <?php else: ?>
                            <?php foreach ($shifts as $sh): ?>
                                <tr>
                                    <td class="fw-bold font-monospace small">#<?= $sh['id'] ?></td>
                                    <td class="fw-semibold small"><?= htmlspecialchars($sh['cashier_name']) ?></td>
                                    <td class="small text-muted"><?= date('Y-m-d H:i', strtotime($sh['opened_at'])) ?></td>
                                    <td class="small currency-font"><?= formatCurrency((float)$sh['opening_float']) ?></td>
                                    <td class="small currency-font text-success fw-semibold"><?= formatCurrency((float)$sh['cash_sales']) ?></td>
                                    <td class="small currency-font text-primary"><?= formatCurrency((float)$sh['card_sales']) ?></td>
                                    <td class="small currency-font fw-bold"><?= formatCurrency((float)$sh['expected_cash']) ?></td>
                                    <td class="small currency-font"><?= $sh['closing_cash'] !== null ? formatCurrency((float)$sh['closing_cash']) : '<span class="text-muted">In Progress</span>' ?></td>
                                    <td>
                                        <?php if ($sh['status'] === 'closed'): ?>
                                            <?php if ((float)$sh['discrepancy'] == 0): ?>
                                                <span class="badge bg-success-subtle text-success">Balanced (Rs. 0.00)</span>
                                            <?php elseif ((float)$sh['discrepancy'] > 0): ?>
                                                <span class="badge bg-info-subtle text-info">+Rs. <?= number_format((float)$sh['discrepancy'], 2) ?> Excess</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger">-Rs. <?= number_format(abs((float)$sh['discrepancy']), 2) ?> Short</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Live</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $sh['status'] === 'open' ? 'bg-success' : 'bg-secondary' ?>"><?= strtoupper($sh['status']) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($sh['status'] === 'open'): ?>
                                            <a href="<?= BASE_URL ?>shifts/close.php?id=<?= $sh['id'] ?>" class="btn btn-sm btn-outline-warning py-0 px-2 small">Close</a>
                                        <?php endif; ?>
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
