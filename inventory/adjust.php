<?php
$pageTitle = 'Stock Adjustment';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $type = $_POST['type'] ?? 'adjustment';
    $qtyChange = (int)($_POST['qty_change'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    if ($productId <= 0 || $qtyChange === 0) {
        $error = 'Please select a product and specify non-zero quantity change.';
    } else {
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("SELECT quantity, name FROM products WHERE id = ? FOR UPDATE");
            $stmt->execute([$productId]);
            $prod = $stmt->fetch();

            if (!$prod) {
                throw new Exception('Product not found.');
            }

            $newBalance = $prod['quantity'] + $qtyChange;
            if ($newBalance < 0) {
                throw new Exception("Cannot reduce stock below zero. Current stock is {$prod['quantity']}.");
            }

            // Update product stock
            $upd = $db->prepare("UPDATE products SET quantity = ? WHERE id = ?");
            $upd->execute([$newBalance, $productId]);

            // Insert stock movement
            $mv = $db->prepare("INSERT INTO stock_movements (product_id, type, quantity, balance_after, reference_id, user_id, notes, created_at) VALUES (?, ?, ?, ?, 'MANUAL-ADJ', ?, ?, NOW())");
            $mv->execute([$productId, $type, $qtyChange, $newBalance, $_SESSION['user_id'] ?? 1, $notes]);

            logAudit('STOCK_ADJUSTED', "Adjusted stock for '{$prod['name']}' by {$qtyChange} ({$type}). New balance: {$newBalance}. Notes: {$notes}", $_SESSION['user_id'] ?? 1);

            $db->commit();
            $success = "Stock for '{$prod['name']}' updated from {$prod['quantity']} to {$newBalance} successfully!";
        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            $error = $e->getMessage();
        }
    }
}

$preSelectedProd = (int)($_GET['product_id'] ?? 0);
$products = $db->query("SELECT id, name, barcode, quantity, unit FROM products WHERE status='active' ORDER BY name ASC")->fetchAll();
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="background: #f8fafc; min-height: calc(100vh - 65px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Manual Stock Adjustment</h3>
                <p class="text-muted small m-0">Correct shelf counts, write off damaged or expired goods, or register audit reconciliations.</p>
            </div>
            <a href="<?= BASE_URL ?>inventory/index.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Inventory
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white" style="max-width: 700px;">
            <form method="POST" action="adjust.php" class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold small">Select Product *</label>
                    <select name="product_id" class="form-select" required>
                        <option value="">-- Choose Product --</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $preSelectedProd === $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['name']) ?> (Current: <?= $p['quantity'] ?> <?= htmlspecialchars($p['unit']) ?>) - [<?= htmlspecialchars($p['barcode']) ?>]
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Adjustment Reason / Type *</label>
                    <select name="type" class="form-select" required>
                        <option value="adjustment">Stock Count Correction / Audit</option>
                        <option value="damaged">Damaged / Broken Shelf Loss</option>
                        <option value="expired">Expired Date Clearance</option>
                        <option value="purchase">Unrecorded Batch Addition</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Quantity Change (+ to Add, - to Subtract) *</label>
                    <input type="number" name="qty_change" class="form-control fw-bold" placeholder="e.g. +10 or -3" required>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold small">Audit Notes / Explanation</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Describe the reason for this inventory count modification..."></textarea>
                </div>

                <div class="col-12 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>inventory/index.php" class="btn btn-light px-4">Cancel</a>
                    <button type="submit" class="btn btn-warning fw-bold px-4"><i class="fa-solid fa-check me-1"></i> Apply Stock Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
