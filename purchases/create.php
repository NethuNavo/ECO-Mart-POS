<?php
$pageTitle = 'Create Purchase Order';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplierId = (int)($_POST['supplier_id'] ?? 0);
    $productId = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);
    $unitCost = (float)($_POST['unit_cost'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    if ($supplierId <= 0 || $productId <= 0 || $quantity <= 0 || $unitCost <= 0) {
        $error = 'Please fill all required purchase details correctly.';
    } else {
        try {
            $db->beginTransaction();
            $poNumber = 'PO-' . date('Y') . '-' . sprintf("%04d", rand(100, 9999));
            $totalAmount = $quantity * $unitCost;

            $stmt = $db->prepare("INSERT INTO purchases (po_number, supplier_id, user_id, total_amount, status, notes, created_at) VALUES (?, ?, ?, ?, 'pending', ?, NOW())");
            $stmt->execute([$poNumber, $supplierId, $_SESSION['user_id'] ?? 1, $totalAmount, $notes]);
            $poId = $db->lastInsertId();

            $pItem = $db->prepare("INSERT INTO purchase_items (purchase_id, product_id, quantity, unit_cost, total_cost) VALUES (?, ?, ?, ?, ?)");
            $pItem->execute([$poId, $productId, $quantity, $unitCost, $totalAmount]);

            logAudit('PO_CREATED', "Created purchase order {$poNumber} for supplier ID {$supplierId} (Rs. " . number_format($totalAmount, 2) . ").", $_SESSION['user_id'] ?? 1);

            $db->commit();
            header('Location: ' . BASE_URL . 'purchases/index.php?msg=created');
            exit;
        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            $error = $e->getMessage();
        }
    }
}

$suppliers = $db->query("SELECT * FROM suppliers WHERE status='active' ORDER BY company_name ASC")->fetchAll();
$products = $db->query("SELECT id, name, barcode, cost_price FROM products WHERE status='active' ORDER BY name ASC")->fetchAll();
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="background: #f8fafc; min-height: calc(100vh - 65px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Create Supplier Purchase Order (PO)</h3>
                <p class="text-muted small m-0">Generate an inward order for restocking supermarket inventory.</p>
            </div>
            <a href="<?= BASE_URL ?>purchases/index.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Orders
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white" style="max-width: 700px;">
            <form method="POST" action="create.php" class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold small">Select Supplier *</label>
                    <select name="supplier_id" class="form-select" required>
                        <option value="">-- Choose Supplier --</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['company_name']) ?> (<?= htmlspecialchars($s['contact_person']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold small">Product Item *</label>
                    <select name="product_id" id="prodSelect" class="form-select" onchange="onProdChange()" required>
                        <option value="">-- Select Product to Order --</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>" data-cost="<?= $p['cost_price'] ?>">
                                <?= htmlspecialchars($p['name']) ?> [<?= htmlspecialchars($p['barcode']) ?>]
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Order Quantity *</label>
                    <input type="number" name="quantity" id="qtyInput" class="form-control fw-bold" value="50" min="1" oninput="calcPOTotal()" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Unit Cost Price (LKR) *</label>
                    <div class="input-group">
                        <span class="input-group-text">Rs.</span>
                        <input type="number" step="0.01" name="unit_cost" id="costInput" class="form-control fw-bold" placeholder="0.00" oninput="calcPOTotal()" required>
                    </div>
                </div>

                <div class="col-12 bg-light p-3 rounded-3 border d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Estimated Order Total:</span>
                    <span class="fs-4 fw-bold text-primary currency-font" id="poTotalText">Rs. 0.00</span>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold small">Procurement Notes / Batch Details</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Delivery scheduled via Ceylon Cold Stores truck on Thursday morning."></textarea>
                </div>

                <div class="col-12 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>purchases/index.php" class="btn btn-light px-4">Cancel</a>
                    <button type="submit" class="btn btn-success fw-bold px-4"><i class="fa-solid fa-check me-1"></i> Submit Purchase Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function onProdChange() {
    const sel = document.getElementById('prodSelect');
    const cost = sel.options[sel.selectedIndex].dataset.cost;
    if (cost) {
        document.getElementById('costInput').value = parseFloat(cost).toFixed(2);
        calcPOTotal();
    }
}

function calcPOTotal() {
    const qty = parseInt(document.getElementById('qtyInput').value) || 0;
    const cost = parseFloat(document.getElementById('costInput').value) || 0;
    const total = qty * cost;
    document.getElementById('poTotalText').innerText = 'Rs. ' + total.toFixed(2);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
