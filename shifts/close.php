<?php
$pageTitle = 'Close Cashier Shift';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();
$error = '';
$shiftId = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT cs.*, u.name as cashier_name FROM cashier_shifts cs JOIN users u ON cs.user_id = u.id WHERE cs.id = ?");
$stmt->execute([$shiftId]);
$shift = $stmt->fetch();

if (!$shift || $shift['status'] === 'closed') {
    header('Location: ' . BASE_URL . 'shifts/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $closingCash = (float)($_POST['closing_cash'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    $discrepancy = $closingCash - (float)$shift['expected_cash'];

    try {
        $upd = $db->prepare("UPDATE cashier_shifts SET closing_cash = ?, discrepancy = ?, closed_at = NOW(), status = 'closed', notes = CONCAT(IFNULL(notes,''), '\nClose notes: ', ?) WHERE id = ?");
        $upd->execute([$closingCash, $discrepancy, $notes, $shiftId]);

        logAudit('SHIFT_CLOSED', "Closed shift #{$shiftId}. Expected: Rs. " . number_format($shift['expected_cash'], 2) . ", Counted: Rs. " . number_format($closingCash, 2) . ", Discrepancy: Rs. " . number_format($discrepancy, 2), $_SESSION['user_id'] ?? 1);

        header('Location: ' . BASE_URL . 'shifts/index.php?msg=closed');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="background: #f8fafc; min-height: calc(100vh - 65px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Close Cashier Shift & Cash Audit</h3>
                <p class="text-muted small m-0">Count physical currency in the cash drawer to reconcile against POS sales totals.</p>
            </div>
            <a href="<?= BASE_URL ?>shifts/index.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Shifts
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white" style="max-width: 650px;">
            <!-- Shift Live Performance Metrics -->
            <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                <div class="col-6">
                    <div class="small text-muted">Opening Float:</div>
                    <div class="fw-bold currency-font"><?= formatCurrency((float)$shift['opening_float']) ?></div>
                </div>
                <div class="col-6">
                    <div class="small text-muted">Cash Sales:</div>
                    <div class="fw-bold text-success currency-font">+ <?= formatCurrency((float)$shift['cash_sales']) ?></div>
                </div>
                <div class="col-6">
                    <div class="small text-muted">Card / QR Sales:</div>
                    <div class="fw-bold text-primary currency-font"><?= formatCurrency((float)$shift['card_sales'] + (float)$shift['online_sales']) ?></div>
                </div>
                <div class="col-6">
                    <div class="small text-muted">Total Refunds:</div>
                    <div class="fw-bold text-danger currency-font">- <?= formatCurrency((float)$shift['refunds_total']) ?></div>
                </div>
                <div class="col-12 border-top pt-2 mt-2 d-flex justify-content-between align-items-baseline">
                    <span class="fw-bold">Expected Physical Cash in Drawer:</span>
                    <span class="fs-4 fw-bold text-dark currency-font" id="expectedCashVal"><?= formatCurrency((float)$shift['expected_cash']) ?></span>
                </div>
            </div>

            <form method="POST" action="close.php?id=<?= $shiftId ?>" class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold small">Counted Physical Cash in Drawer (LKR) *</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text fw-bold">Rs.</span>
                        <input type="number" step="0.01" name="closing_cash" id="countedCashInput" class="form-control fw-bold fs-4" placeholder="0.00" oninput="calcDiscrepancy()" required>
                    </div>
                </div>

                <div class="col-12">
                    <div class="p-3 rounded-3 border text-center" id="discBox" style="background:#f8fafc;">
                        <div class="small text-muted text-uppercase fw-semibold">Discrepancy</div>
                        <div class="fs-4 fw-bold currency-font text-muted" id="discText">Rs. 0.00</div>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold small">End of Shift Notes / Remarks</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Verified by Head Cashier"></textarea>
                </div>

                <div class="col-12 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>shifts/index.php" class="btn btn-light px-4">Cancel</a>
                    <button type="submit" class="btn btn-warning fw-bold px-4"><i class="fa-solid fa-lock me-1"></i> Finalize & Close Till</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const expectedCash = <?= (float)$shift['expected_cash'] ?>;
function calcDiscrepancy() {
    const counted = parseFloat(document.getElementById('countedCashInput').value) || 0;
    const diff = counted - expectedCash;
    const txt = document.getElementById('discText');
    const box = document.getElementById('discBox');
    
    if (Math.abs(diff) < 0.01) {
        txt.innerText = 'Rs. 0.00 (Balanced)';
        txt.className = 'fs-4 fw-bold currency-font text-success';
        box.style.background = '#ecfdf5';
    } else if (diff > 0) {
        txt.innerText = '+ Rs. ' + diff.toFixed(2) + ' (Excess)';
        txt.className = 'fs-4 fw-bold currency-font text-info';
        box.style.background = '#eff6ff';
    } else {
        txt.innerText = '- Rs. ' + Math.abs(diff).toFixed(2) + ' (Shortage)';
        txt.className = 'fs-4 fw-bold currency-font text-danger';
        box.style.background = '#fef2f2';
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
