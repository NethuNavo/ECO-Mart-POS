<?php
$pageTitle = 'Open Cashier Shift';
require_once __DIR__ . '/../config/database.php';
checkAuth();

// Process redirects and form submissions before the shared header outputs HTML.
// This keeps Location headers available for already-open shifts and successful submissions.

$db = Database::getConnection();
$error = '';
$userId = $_SESSION['user_id'] ?? 1;

// Check if shift is already open
$existing = $db->query("SELECT id FROM cashier_shifts WHERE user_id = {$userId} AND status = 'open' LIMIT 1")->fetch();
if ($existing) {
    header('Location: ' . BASE_URL . 'pos/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $float = (float)($_POST['opening_float'] ?? 5000);
    $notes = trim($_POST['notes'] ?? 'Morning terminal open');

    try {
        $stmt = $db->prepare("INSERT INTO cashier_shifts (user_id, opening_float, cash_sales, card_sales, online_sales, refunds_total, expected_cash, opened_at, status, notes) VALUES (?, ?, 0, 0, 0, 0, ?, NOW(), 'open', ?)");
        $stmt->execute([$userId, $float, $float, $notes]);

        logAudit('SHIFT_OPEN', "Cashier {$_SESSION['user_name']} opened shift with float Rs. " . number_format($float, 2), $userId);

        header('Location: ' . BASE_URL . 'pos/index.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="background: #f8fafc; min-height: calc(100vh - 65px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Open Cashier Terminal Shift</h3>
                <p class="text-muted small m-0">Count and record the starting cash float inside your till drawer.</p>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white" style="max-width: 600px;">
            <form method="POST" action="open.php" class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold small">Cashier Name</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['user_name'] ?? 'Cashier') ?>" disabled>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold small">Starting Till Cash Float (LKR) *</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text fw-bold">Rs.</span>
                        <input type="number" step="100" name="opening_float" class="form-control fw-bold fs-4" value="5000" required>
                    </div>
                    <small class="text-muted">Standard supermarket till float is Rs. 5,000.00 in mixed notes and coins.</small>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold small">Opening Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Lane 01 - Cash drawer verified"></textarea>
                </div>

                <div class="col-12 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>shifts/index.php" class="btn btn-light px-4">Cancel</a>
                    <button type="submit" class="btn btn-success fw-bold px-4"><i class="fa-solid fa-play me-1"></i> Open Shift & Launch POS</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
