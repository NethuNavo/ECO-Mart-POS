<?php
$pageTitle = 'Customers & Loyalty';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();
$customers = $db->query("SELECT * FROM customers WHERE status='active' ORDER BY loyalty_points DESC")->fetchAll();
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="background: #f8fafc; min-height: calc(100vh - 65px);">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Customer Accounts & Loyalty Points</h3>
                <p class="text-muted small m-0">Customer directory, telephone lookup, loyalty balance and lifetime supermarket spend.</p>
            </div>
            <a href="<?= BASE_URL ?>customers/add.php" class="btn btn-success fw-bold px-3 py-2 shadow-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-user-plus"></i> Register Customer
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-muted">
                        <tr>
                            <th>Customer Name</th>
                            <th>Phone Number</th>
                            <th>Email</th>
                            <th>Loyalty Balance</th>
                            <th>Lifetime Spend</th>
                            <th>Joined Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $c): ?>
                            <tr>
                                <td class="fw-bold small text-dark"><?= htmlspecialchars($c['name']) ?></td>
                                <td class="small font-monospace"><?= htmlspecialchars($c['phone']) ?></td>
                                <td class="small text-muted"><?= htmlspecialchars($c['email'] ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1">
                                        <i class="fa-solid fa-star text-warning me-1"></i> <?= $c['loyalty_points'] ?> Pts
                                    </span>
                                </td>
                                <td class="fw-bold currency-font text-success small"><?= formatCurrency((float)$c['total_spend']) ?></td>
                                <td class="small text-muted"><?= date('Y-m-d', strtotime($c['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
