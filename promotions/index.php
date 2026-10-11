<?php
$pageTitle = 'Promotions & Discounts';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $type = $_POST['type'] ?? 'percentage';
    $value = (float)($_POST['value'] ?? 0);
    $startDate = $_POST['start_date'] ?? date('Y-m-d');
    $endDate = $_POST['end_date'] ?? date('Y-m-d', strtotime('+30 days'));

    if (empty($title) || $value <= 0) {
        $error = 'Promotion title and discount value are required.';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO promotions (title, type, value, min_spend, start_date, end_date, status) VALUES (?, ?, ?, 0.00, ?, ?, 'active')");
            $stmt->execute([$title, $type, $value, $startDate, $endDate]);

            logAudit('PROMO_CREATED', "Created campaign '{$title}'.", $_SESSION['user_id'] ?? 1);
            $success = "Promotion campaign activated!";
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$promos = $db->query("SELECT * FROM promotions ORDER BY id DESC")->fetchAll();
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="background: #f8fafc; min-height: calc(100vh - 65px);">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Promotional Pricing & Campaigns</h3>
                <p class="text-muted small m-0">Define supermarket discounts, flash sales, category deals and seasonal offers.</p>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h6 class="fw-bold mb-3"><i class="fa-solid fa-tag text-warning me-2"></i> Launch Promotion Campaign</h6>
                    <form method="POST" action="index.php" class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Campaign Name *</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Avurudu Season 10% Off Bakery" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Discount Type</label>
                            <select name="type" class="form-select">
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (LKR)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Discount Value *</label>
                            <input type="number" step="0.5" name="value" class="form-control fw-bold" placeholder="10" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-warning w-100 fw-bold py-2"><i class="fa-solid fa-bullhorn me-1"></i> Activate Campaign</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                    <div class="p-3 border-bottom">
                        <h6 class="fw-bold m-0"><i class="fa-solid fa-tags text-secondary me-2"></i> Active & Past Promotional Deals</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-muted">
                                <tr>
                                    <th>Campaign</th>
                                    <th>Benefit</th>
                                    <th>Valid Period</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($promos as $pr): ?>
                                    <tr>
                                        <td class="fw-bold small text-dark"><?= htmlspecialchars($pr['title']) ?></td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success fw-bold">
                                                <?= $pr['type'] === 'percentage' ? $pr['value'] . '% OFF' : 'Rs. ' . number_format($pr['value'], 2) . ' OFF' ?>
                                            </span>
                                        </td>
                                        <td class="small text-muted"><?= htmlspecialchars($pr['start_date']) ?> to <?= htmlspecialchars($pr['end_date']) ?></td>
                                        <td><span class="badge bg-success"><?= strtoupper($pr['status']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
