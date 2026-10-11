<?php
$pageTitle = 'Suppliers & Vendors';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();
$suppliers = $db->query("SELECT * FROM suppliers WHERE status='active' ORDER BY company_name ASC")->fetchAll();
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="background: #f8fafc; min-height: calc(100vh - 65px);">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Suppliers & Distributors</h3>
                <p class="text-muted small m-0">Vendor directory, contact representatives, and outstanding payables ledger.</p>
            </div>
            <a href="<?= BASE_URL ?>suppliers/add.php" class="btn btn-success fw-bold px-3 py-2 shadow-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-plus"></i> Add New Supplier
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-muted">
                        <tr>
                            <th>Company Name</th>
                            <th>Contact Person</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Outstanding Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suppliers as $s): ?>
                            <tr>
                                <td class="fw-bold text-dark small"><?= htmlspecialchars($s['company_name']) ?></td>
                                <td class="small"><?= htmlspecialchars($s['contact_person']) ?></td>
                                <td class="small font-monospace"><?= htmlspecialchars($s['phone']) ?></td>
                                <td class="small text-muted"><?= htmlspecialchars($s['email'] ?? '-') ?></td>
                                <td class="small text-muted"><?= htmlspecialchars($s['address'] ?? '-') ?></td>
                                <td class="fw-bold currency-font text-danger small"><?= formatCurrency((float)$s['outstanding_balance']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
