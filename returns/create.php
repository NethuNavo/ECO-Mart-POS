<?php
$pageTitle = 'Process Customer Return';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();
$error = '';
$success = '';

$invoiceQuery = trim($_GET['invoice'] ?? '');
$saleData = null;
$saleItems = [];

if ($invoiceQuery !== '') {
    $sStmt = $db->prepare("SELECT * FROM sales WHERE invoice_number = ? LIMIT 1");
    $sStmt->execute([$invoiceQuery]);
    $saleData = $sStmt->fetch();

    if ($saleData) {
        $itStmt = $db->prepare("SELECT si.*, p.name as product_name, p.barcode FROM sale_items si JOIN products p ON si.product_id = p.id WHERE si.sale_id = ?");
        $itStmt->execute([$saleData['id']]);
        $saleItems = $itStmt->fetchAll();
    } else {
        $error = "Invoice '{$invoiceQuery}' not found in database.";
    }
}

// Process POST Return
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_return') {
    $saleId = (int)$_POST['sale_id'];
    $productId = (int)$_POST['product_id'];
    $returnQty = (int)$_POST['return_qty'];
    $unitPrice = (float)$_POST['unit_price'];
    $refundMethod = $_POST['refund_method'] ?? 'cash';
    $reason = trim($_POST['reason'] ?? 'Customer request');

    if ($returnQty <= 0 || $productId <= 0) {
        $error = 'Please specify a valid item and return quantity.';
    } else {
        try {
            $db->beginTransaction();

            $totalRefund = $returnQty * $unitPrice;
            $retNumber = 'RET-' . date('Ymd') . '-' . sprintf("%04d", rand(100, 9999));

            // 1. Insert into returns table
            $insRet = $db->prepare("INSERT INTO returns (return_number, sale_id, user_id, refund_amount, refund_method, reason, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $insRet->execute([$retNumber, $saleId, $_SESSION['user_id'] ?? 1, $totalRefund, $refundMethod, $reason]);
            $retId = $db->lastInsertId();

            // 2. Insert into return_items
            $insRetItem = $db->prepare("INSERT INTO return_items (return_id, product_id, quantity, unit_price, total_refund) VALUES (?, ?, ?, ?, ?)");
            $insRetItem->execute([$retId, $productId, $returnQty, $unitPrice, $totalRefund]);

            // 3. RESTORE PRODUCT INVENTORY STOCK
            $updStock = $db->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
            $updStock->execute([$returnQty, $productId]);

            // 4. Record stock movement
            $curStock = $db->query("SELECT quantity, name FROM products WHERE id = {$productId}")->fetch();
            $mv = $db->prepare("INSERT INTO stock_movements (product_id, type, quantity, balance_after, reference_id, user_id, notes, created_at) VALUES (?, 'return', ?, ?, ?, ?, ?, NOW())");
            $mv->execute([$productId, $returnQty, $curStock['quantity'], $retNumber, $_SESSION['user_id'] ?? 1, "Returned from {$invoiceQuery}: {$reason}"]);

            // 5. Update shift refund totals if active shift exists
            $shiftStmt = $db->prepare("SELECT id FROM cashier_shifts WHERE user_id = ? AND status = 'open' LIMIT 1");
            $shiftStmt->execute([$_SESSION['user_id'] ?? 1]);
            $shift = $shiftStmt->fetch();
            if ($shift) {
                $db->prepare("UPDATE cashier_shifts SET refunds_total = refunds_total + ?, expected_cash = expected_cash - ? WHERE id = ?")
                   ->execute([$totalRefund, $totalRefund, $shift['id']]);
            }

            logAudit('RETURN_PROCESSED', "Processed return {$retNumber} for Rs. " . number_format($totalRefund, 2) . " on {$invoiceQuery}.", $_SESSION['user_id'] ?? 1);

            $db->commit();
            header('Location: ' . BASE_URL . 'returns/index.php?msg=success');
            exit;

        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            $error = $e->getMessage();
        }
    }
}
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="background: #f8fafc; min-height: calc(100vh - 65px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Process Customer Return & Restock</h3>
                <p class="text-muted small m-0">Look up sales invoice, select item to return, issue refund and restore inventory.</p>
            </div>
            <a href="<?= BASE_URL ?>returns/index.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Returns
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Step 1: Invoice Lookup Card -->
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4" style="max-width: 600px;">
            <form method="GET" class="row g-2">
                <label class="form-label fw-bold small">Enter or Scan Sales Invoice Number</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-receipt"></i></span>
                    <input type="text" name="invoice" class="form-control fw-bold font-monospace" placeholder="e.g. INV-20260819-0001" value="<?= htmlspecialchars($invoiceQuery) ?>" required>
                    <button type="submit" class="btn btn-primary fw-semibold"><i class="fa-solid fa-magnifying-glass me-1"></i> Fetch Bill</button>
                </div>
            </form>
        </div>

        <?php if ($saleData && !empty($saleItems)): ?>
            <!-- Step 2: Return Items Selector -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white" style="max-width: 700px;">
                <div class="d-flex justify-content-between border-bottom pb-3 mb-3">
                    <div>
                        <div class="small text-muted">Invoice: <strong class="font-monospace text-dark"><?= htmlspecialchars($saleData['invoice_number']) ?></strong></div>
                        <div class="small text-muted">Bill Total: <strong><?= formatCurrency((float)$saleData['grand_total']) ?></strong></div>
                    </div>
                    <div>
                        <div class="small text-muted">Date: <?= date('Y-m-d H:i', strtotime($saleData['created_at'])) ?></div>
                    </div>
                </div>

                <form method="POST" action="create.php?invoice=<?= urlencode($invoiceQuery) ?>">
                    <input type="hidden" name="action" value="submit_return">
                    <input type="hidden" name="sale_id" value="<?= $saleData['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Select Purchased Item to Return *</label>
                        <select name="product_id" id="returnItemSelect" class="form-select" onchange="onReturnItemChange()" required>
                            <option value="">-- Choose Item from Invoice --</option>
                            <?php foreach ($saleItems as $it): ?>
                                <option value="<?= $it['product_id'] ?>" data-price="<?= $it['unit_price'] ?>" data-maxqty="<?= $it['quantity'] ?>">
                                    <?= htmlspecialchars($it['product_name']) ?> (Billed Qty: <?= $it['quantity'] ?>, Unit Price: Rs. <?= number_format($it['unit_price'], 2) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <input type="hidden" name="unit_price" id="returnUnitPrice" value="0">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Return Quantity *</label>
                            <input type="number" name="return_qty" id="returnQtyInput" class="form-control fw-bold" value="1" min="1" oninput="calcRefundTotal()" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Refund Method</label>
                            <select name="refund_method" class="form-select">
                                <option value="cash">Cash Refund</option>
                                <option value="card">Credit Card Reversal</option>
                                <option value="credit_note">Store Credit Note</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Reason for Return *</label>
                        <select name="reason" class="form-select" required>
                            <option value="Customer changed mind / unused">Customer changed mind / unused sealed</option>
                            <option value="Defective or damaged packaging">Defective or damaged packaging</option>
                            <option value="Wrong product billed by error">Wrong product billed by error</option>
                            <option value="Expired date claim">Expired date claim</option>
                        </select>
                    </div>

                    <div class="bg-danger-subtle p-3 rounded-3 border border-danger-subtle d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold text-danger">Total Refund Payable:</span>
                        <span class="fs-4 fw-bold text-danger currency-font" id="refundTotalText">Rs. 0.00</span>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <a href="<?= BASE_URL ?>returns/index.php" class="btn btn-light px-4">Cancel</a>
                        <button type="submit" class="btn btn-danger fw-bold px-4"><i class="fa-solid fa-rotate-left me-1"></i> Approve Refund & Restock Item</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function onReturnItemChange() {
    const sel = document.getElementById('returnItemSelect');
    const price = sel.options[sel.selectedIndex].dataset.price;
    const maxQty = sel.options[sel.selectedIndex].dataset.maxqty;
    if (price) {
        document.getElementById('returnUnitPrice').value = price;
        document.getElementById('returnQtyInput').max = maxQty;
        calcRefundTotal();
    }
}

function calcRefundTotal() {
    const price = parseFloat(document.getElementById('returnUnitPrice').value) || 0;
    const qty = parseInt(document.getElementById('returnQtyInput').value) || 0;
    const total = price * qty;
    document.getElementById('refundTotalText').innerText = 'Rs. ' + total.toFixed(2);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
