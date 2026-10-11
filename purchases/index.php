<?php
$pageTitle = 'Purchase Orders & Stock In';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();

$purchases = $db->query("SELECT p.*, s.company_name as supplier_name, u.name as created_by 
    FROM purchases p 
    JOIN suppliers s ON p.supplier_id = s.id 
    LEFT JOIN users u ON p.user_id = u.id 
    ORDER BY p.id DESC")->fetchAll();
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="background: #f8fafc; min-height: calc(100vh - 65px);">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Purchase Orders & Supplier Inward Stock</h3>
                <p class="text-muted small m-0">Procurement workflow: PO Creation &rarr; Goods Receiving &rarr; Automatic Stock Increment.</p>
            </div>
            <a href="<?= BASE_URL ?>purchases/create.php" class="btn btn-success fw-bold px-3 py-2 shadow-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-plus"></i> Create Purchase Order
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-muted">
                        <tr>
                            <th>PO Number</th>
                            <th>Supplier</th>
                            <th>Created Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Received Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($purchases)): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted">No purchase orders found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($purchases as $po): ?>
                                <tr>
                                    <td class="fw-bold font-monospace small"><?= htmlspecialchars($po['po_number']) ?></td>
                                    <td class="fw-semibold small"><?= htmlspecialchars($po['supplier_name']) ?></td>
                                    <td class="small text-muted"><?= date('Y-m-d', strtotime($po['created_at'])) ?></td>
                                    <td class="fw-bold currency-font text-primary"><?= formatCurrency((float)$po['total_amount']) ?></td>
                                    <td>
                                        <span class="badge <?= $po['status'] === 'received' ? 'bg-success' : ($po['status'] === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                            <?= strtoupper($po['status']) ?>
                                        </span>
                                    </td>
                                    <td class="small text-muted"><?= $po['received_at'] ? date('Y-m-d H:i', strtotime($po['received_at'])) : 'Pending Inward' ?></td>
                                    <td class="text-end">
                                        <?php if ($po['status'] === 'pending'): ?>
                                            <a href="<?= BASE_URL ?>purchases/receive.php?id=<?= $po['id'] ?>" class="btn btn-sm btn-success py-0 px-2 fw-semibold" onclick="return confirm('Confirm receiving this delivery? Inventory will be automatically increased.')">
                                                <i class="fa-solid fa-box-open me-1"></i> Receive Stock
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border"><i class="fa-solid fa-check me-1"></i> In Stock</span>
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
