<?php
$pageTitle = 'Customer Returns & Refunds';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();

$returns = $db->query("SELECT r.*, s.invoice_number, u.name as processed_by 
    FROM returns r 
    JOIN sales s ON r.sale_id = s.id 
    LEFT JOIN users u ON r.user_id = u.id 
    ORDER BY r.id DESC")->fetchAll();
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="background: #f8fafc; min-height: calc(100vh - 65px);">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Customer Returns & Stock Restitution</h3>
                <p class="text-muted small m-0">Process invoice item returns, issue cash refunds, and restore sale inventory.</p>
            </div>
            <a href="<?= BASE_URL ?>returns/create.php" class="btn btn-danger fw-bold px-3 py-2 shadow-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-rotate-left"></i> Process Return / Refund
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-muted">
                        <tr>
                            <th>Return #</th>
                            <th>Original Invoice</th>
                            <th>Date & Time</th>
                            <th>Refund Amount</th>
                            <th>Method</th>
                            <th>Reason</th>
                            <th>Processed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($returns)): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted">No returns recorded yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($returns as $ret): ?>
                                <tr>
                                    <td class="fw-bold font-monospace small text-danger"><?= htmlspecialchars($ret['return_number']) ?></td>
                                    <td class="fw-semibold font-monospace small"><?= htmlspecialchars($ret['invoice_number']) ?></td>
                                    <td class="small text-muted"><?= date('Y-m-d H:i', strtotime($ret['created_at'])) ?></td>
                                    <td class="fw-bold currency-font text-danger"><?= formatCurrency((float)$ret['refund_amount']) ?></td>
                                    <td><span class="badge bg-secondary-subtle text-secondary text-uppercase" style="font-size:0.7rem;"><?= htmlspecialchars($ret['refund_method']) ?></span></td>
                                    <td class="small text-muted"><?= htmlspecialchars($ret['reason']) ?></td>
                                    <td class="small text-muted"><?= htmlspecialchars($ret['processed_by'] ?? 'Staff') ?></td>
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
