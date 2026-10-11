<?php
$pageTitle = 'Tax Invoice Receipt';
require_once __DIR__ . '/../config/database.php';
checkAuth();

$id = (int)($_GET['id'] ?? 0);
$format = $_GET['format'] ?? 'thermal'; // 'thermal' or 'a4'

$db = Database::getConnection();
$stmt = $db->prepare("SELECT s.*, c.name as customer_name, c.phone as customer_phone, c.address as customer_address, u.name as cashier_name 
    FROM sales s 
    LEFT JOIN customers c ON s.customer_id = c.id 
    LEFT JOIN users u ON s.user_id = u.id 
    WHERE s.id = ?");
$stmt->execute([$id]);
$sale = $stmt->fetch();

if (!$sale) {
    die("Invoice record not found.");
}

$itemsStmt = $db->prepare("SELECT si.*, p.name as product_name, p.barcode, p.unit 
    FROM sale_items si 
    JOIN products p ON si.product_id = p.id 
    WHERE si.sale_id = ?");
$itemsStmt->execute([$id]);
$items = $itemsStmt->fetchAll();

$storeName = getSetting('store_name', 'ECO Mart');
if (stripos($storeName, 'SmartMart') !== false) {
    $storeName = str_ireplace('SmartMart', 'ECO Mart', $storeName);
}
$storeAddress = getSetting('store_address', 'No. 245 Galle Road, Colombo 04');
$storePhone = getSetting('store_phone', '+94 11 258 9900');
$receiptHeader = getSetting('receipt_header', 'WELCOME TO ECO MART');
if (stripos($receiptHeader, 'SMARTMART') !== false) {
    $receiptHeader = str_ireplace('SMARTMART', 'ECO MART', $receiptHeader);
}
$receiptFooter = getSetting('receipt_footer', 'Thank you for shopping! Exchange possible within 3 days with bill.');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($sale['invoice_number']) ?> — Receipt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #F8F5E9;
            font-family: 'Plus Jakarta Sans', sans-serif;
            padding: 20px 0;
        }
        .thermal-paper {
            background: #ffffff;
            width: 320px;
            margin: 0 auto;
            padding: 16px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            line-height: 1.4;
        }
        .a4-paper {
            background: #ffffff;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        }
        .dashed-line {
            border-top: 1px dashed #333;
            margin: 8px 0;
        }
        @media print {
            body { background: transparent; padding: 0; }
            .no-print { display: none !important; }
            .thermal-paper, .a4-paper { box-shadow: none; padding: 0; width: 100%; max-width: 100%; border-radius: 0; }
        }
    </style>
</head>
<body>

<div class="container text-center no-print mb-3">
    <div class="d-inline-flex gap-2">
        <a href="<?= BASE_URL ?>sales/index.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Back to Sales</a>
        <a href="<?= BASE_URL ?>pos/index.php" class="btn btn-outline-success btn-sm"><i class="fa-solid fa-cash-register me-1"></i> POS Fast Lane</a>
        <button onclick="window.print()" class="btn btn-success btn-sm fw-bold"><i class="fa-solid fa-print me-1"></i> Print Invoice</button>
        <?php if ($format === 'thermal'): ?>
            <a href="?id=<?= $sale['id'] ?>&format=a4" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-file-lines me-1"></i> Switch to A4 Invoice</a>
        <?php else: ?>
            <a href="?id=<?= $sale['id'] ?>&format=thermal" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-receipt me-1"></i> Switch to Thermal 80mm</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($format === 'thermal'): ?>
    <!-- 80mm / 58mm Thermal Receipt Format -->
    <div class="thermal-paper">
        <div class="text-center">
            <h6 class="fw-bold mb-0 text-uppercase"><?= htmlspecialchars($storeName) ?></h6>
            <div style="font-size:10px;"><?= htmlspecialchars($storeAddress) ?></div>
            <div style="font-size:10px;">Tel: <?= htmlspecialchars($storePhone) ?></div>
            <div class="fw-bold mt-1 text-uppercase" style="font-size:12px;">*** <?= htmlspecialchars($receiptHeader) ?> ***</div>
            <div class="small fw-semibold mt-1">OFFICIAL TAX RECEIPT</div>
        </div>

        <div class="dashed-line"></div>

        <div>
            <div>Inv #: <strong><?= htmlspecialchars($sale['invoice_number']) ?></strong></div>
            <div>Date: <?= date('d/m/Y H:i:s', strtotime($sale['created_at'])) ?></div>
            <div>Cashier: <?= htmlspecialchars($sale['cashier_name'] ?? 'Staff') ?></div>
            <div>Customer: <?= htmlspecialchars($sale['customer_name'] ?? 'Walk-in') ?></div>
        </div>

        <div class="dashed-line"></div>

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 1px dashed #333;">
                    <th style="text-align:left;">Item</th>
                    <th style="text-align:center;">Qty</th>
                    <th style="text-align:right;">Price</th>
                    <th style="text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td style="padding: 2px 0;">
                            <?= htmlspecialchars($it['product_name']) ?>
                            <?php if ($it['discount_amount'] > 0): ?>
                                <br><small style="font-size: 9px; color:#666;">(Disc: -Rs. <?= number_format($it['discount_amount'], 2) ?>)</small>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center;"><?= $it['quantity'] ?></td>
                        <td style="text-align:right;"><?= number_format($it['unit_price'], 2) ?></td>
                        <td style="text-align:right; font-weight:bold;"><?= number_format($it['line_total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="dashed-line"></div>

        <div style="line-height: 1.6;">
            <div class="d-flex justify-content-between">
                <span>Subtotal:</span>
                <span>Rs. <?= number_format($sale['subtotal'], 2) ?></span>
            </div>
            <?php if ($sale['discount_total'] > 0): ?>
                <div class="d-flex justify-content-between">
                    <span>Discount:</span>
                    <span>- Rs. <?= number_format($sale['discount_total'], 2) ?></span>
                </div>
            <?php endif; ?>
            <?php if ($sale['tax_total'] > 0): ?>
                <div class="d-flex justify-content-between">
                    <span>Tax:</span>
                    <span>Rs. <?= number_format($sale['tax_total'], 2) ?></span>
                </div>
            <?php endif; ?>
            <div class="d-flex justify-content-between fw-bold" style="font-size: 13px; border-top: 1px solid #000; padding-top: 2px;">
                <span>GRAND TOTAL:</span>
                <span>Rs. <?= number_format($sale['grand_total'], 2) ?></span>
            </div>
            <div class="d-flex justify-content-between mt-1">
                <span>Paid (<?= strtoupper($sale['payment_method']) ?>):</span>
                <span>Rs. <?= number_format($sale['paid_amount'], 2) ?></span>
            </div>
            <div class="d-flex justify-content-between">
                <span>Change Returned:</span>
                <span>Rs. <?= number_format($sale['change_amount'], 2) ?></span>
            </div>
        </div>

        <?php if ($sale['loyalty_points_earned'] > 0): ?>
            <div class="dashed-line"></div>
            <div class="text-center">
                <strong>Loyalty Points Earned: +<?= $sale['loyalty_points_earned'] ?> Pts</strong>
            </div>
        <?php endif; ?>

        <div class="dashed-line"></div>

        <div class="text-center" style="font-size: 10px;">
            <div><?= htmlspecialchars($receiptFooter) ?></div>
            <div class="mt-2 font-monospace" style="letter-spacing: 2px;">*<?= $sale['invoice_number'] ?>*</div>
            <div class="text-muted" style="font-size: 8px;">ECO Mart POS v2.5</div>
        </div>
    </div>

<?php else: ?>
    <!-- Corporate A4 Tax Invoice Format -->
    <div class="a4-paper">
        <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
            <div>
                <h2 class="fw-bold text-success m-0"><?= htmlspecialchars($storeName) ?></h2>
                <div class="text-muted"><?= htmlspecialchars($storeAddress) ?></div>
                <div class="text-muted">Tel: <?= htmlspecialchars($storePhone) ?></div>
            </div>
            <div class="text-end">
                <h3 class="fw-bold text-dark mb-1">TAX INVOICE</h3>
                <div class="fs-5 font-monospace fw-bold text-primary"><?= htmlspecialchars($sale['invoice_number']) ?></div>
                <div class="text-muted small">Date: <?= date('F d, Y h:i A', strtotime($sale['created_at'])) ?></div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <div class="text-muted small fw-semibold text-uppercase">Billed To:</div>
                <div class="fw-bold fs-6"><?= htmlspecialchars($sale['customer_name'] ?? 'Walk-in Customer') ?></div>
                <?php if (!empty($sale['customer_phone'])): ?>
                    <div class="small text-muted">Phone: <?= htmlspecialchars($sale['customer_phone']) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-6 text-end">
                <div class="text-muted small fw-semibold text-uppercase">Processed By:</div>
                <div class="fw-bold"><?= htmlspecialchars($sale['cashier_name'] ?? 'Counter Staff') ?></div>
                <div class="small text-muted">Payment: <?= strtoupper($sale['payment_method']) ?> (<?= ucfirst($sale['payment_status']) ?>)</div>
            </div>
        </div>

        <table class="table table-bordered align-middle mb-4">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Barcode</th>
                    <th>Product Description</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Total (LKR)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $idx => $it): ?>
                    <tr>
                        <td><?= $idx + 1 ?></td>
                        <td class="font-monospace small"><?= htmlspecialchars($it['barcode']) ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($it['product_name']) ?></td>
                        <td class="text-center"><?= $it['quantity'] ?> <?= htmlspecialchars($it['unit']) ?></td>
                        <td class="text-end font-monospace">Rs. <?= number_format($it['unit_price'], 2) ?></td>
                        <td class="text-end font-monospace fw-bold">Rs. <?= number_format($it['line_total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end fw-bold">Subtotal:</td>
                    <td class="text-end font-monospace fw-bold">Rs. <?= number_format($sale['subtotal'], 2) ?></td>
                </tr>
                <?php if ($sale['discount_total'] > 0): ?>
                    <tr>
                        <td colspan="5" class="text-end fw-bold text-danger">Discount:</td>
                        <td class="text-end font-monospace fw-bold text-danger">- Rs. <?= number_format($sale['discount_total'], 2) ?></td>
                    </tr>
                <?php endif; ?>
                <tr class="table-light">
                    <td colspan="5" class="text-end fs-5 fw-bold text-dark">GRAND TOTAL:</td>
                    <td class="text-end font-monospace fs-5 fw-bold text-success">Rs. <?= number_format($sale['grand_total'], 2) ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="border-top pt-3 d-flex justify-content-between text-muted small">
            <div><?= htmlspecialchars($receiptFooter) ?></div>
            <div class="fw-bold">ECO Mart</div>
        </div>
    </div>
<?php endif; ?>

</body>
</html>
