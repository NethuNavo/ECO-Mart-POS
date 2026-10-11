<?php
$pageTitle = 'Store Configuration';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();
$error = '';
$success = '';
$configuredStoreName = getSetting('store_name', 'ECO Mart');
$configuredReceiptHeader = getSetting('receipt_header', 'WELCOME TO ECO MART');
if (stripos($configuredStoreName, 'SmartMart') !== false) {
    $configuredStoreName = str_ireplace('SmartMart', 'ECO Mart', $configuredStoreName);
}
if (stripos($configuredReceiptHeader, 'SMARTMART') !== false) {
    $configuredReceiptHeader = str_ireplace('SMARTMART', 'ECO MART', $configuredReceiptHeader);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settingsToUpdate = [
        'store_name' => trim($_POST['store_name'] ?? ''),
        'store_address' => trim($_POST['store_address'] ?? ''),
        'store_phone' => trim($_POST['store_phone'] ?? ''),
        'store_email' => trim($_POST['store_email'] ?? ''),
        'currency_symbol' => trim($_POST['currency_symbol'] ?? 'Rs. '),
        'tax_percentage' => (float)($_POST['tax_percentage'] ?? 0),
        'loyalty_points_per_rupee' => (float)($_POST['loyalty_points_per_rupee'] ?? 0.01),
        'receipt_header' => trim($_POST['receipt_header'] ?? ''),
        'receipt_footer' => trim($_POST['receipt_footer'] ?? '')
    ];

    try {
        $upd = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($settingsToUpdate as $k => $v) {
            $upd->execute([$k, (string)$v]);
        }

        logAudit('SETTINGS_UPDATED', "Updated supermarket store and receipt configurations.", $_SESSION['user_id'] ?? 1);
        $success = "Store settings successfully saved!";
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
                <h3 class="fw-bold text-dark mb-1">Supermarket Store Settings & Tax Rules</h3>
                <p class="text-muted small m-0">Store branding, tax rates, thermal printer bill templates, and loyalty calculation ratios.</p>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white" style="max-width: 800px;">
            <form method="POST" action="index.php" class="row g-3">
                <h6 class="fw-bold text-success border-bottom pb-2 mb-2"><i class="fa-solid fa-store me-2"></i> Store Identity & Contact</h6>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Supermarket Trading Name *</label>
                    <input type="text" name="store_name" class="form-control" value="<?= htmlspecialchars($configuredStoreName) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Store Telephone / Hotline *</label>
                    <input type="text" name="store_phone" class="form-control" value="<?= htmlspecialchars(getSetting('store_phone', '+94 11 258 9900')) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Physical Address (For Receipts) *</label>
                    <input type="text" name="store_address" class="form-control" value="<?= htmlspecialchars(getSetting('store_address', 'No. 245 Galle Road, Colombo 04')) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Store Email</label>
                    <input type="email" name="store_email" class="form-control" value="<?= htmlspecialchars(getSetting('store_email', 'info@smartmart.lk')) ?>">
                </div>

                <h6 class="fw-bold text-success border-bottom pb-2 mt-4 mb-2"><i class="fa-solid fa-calculator me-2"></i> Financial, Currency & Loyalty</h6>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Currency Symbol</label>
                    <input type="text" name="currency_symbol" class="form-control font-monospace" value="<?= htmlspecialchars(getSetting('currency_symbol', 'Rs. ')) ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Default VAT / Tax Rate (%)</label>
                    <div class="input-group">
                        <input type="number" step="0.1" name="tax_percentage" class="form-control" value="<?= htmlspecialchars(getSetting('tax_percentage', '0')) ?>">
                        <span class="input-group-text">%</span>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Loyalty Ratio (Points per LKR 100)</label>
                    <input type="number" step="0.001" name="loyalty_points_per_rupee" class="form-control" value="<?= htmlspecialchars(getSetting('loyalty_points_per_rupee', '0.01')) ?>">
                    <small class="text-muted" style="font-size:0.7rem;">0.01 = 1 point per Rs. 100 spend</small>
                </div>

                <h6 class="fw-bold text-success border-bottom pb-2 mt-4 mb-2"><i class="fa-solid fa-receipt me-2"></i> Thermal Printer Bill Header & Footer</h6>

                <div class="col-12">
                    <label class="form-label fw-semibold small">Receipt Header Message</label>
                    <input type="text" name="receipt_header" class="form-control" value="<?= htmlspecialchars($configuredReceiptHeader) ?>">
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold small">Receipt Footer Return & Exchange Policy</label>
                    <textarea name="receipt_footer" class="form-control" rows="2"><?= htmlspecialchars(getSetting('receipt_footer', 'Thank you for shopping with us! Exchange possible within 3 days with bill.')) ?></textarea>
                </div>

                <div class="col-12 pt-3 border-top d-flex justify-content-end">
                    <button type="submit" class="btn btn-success fw-bold px-4"><i class="fa-solid fa-floppy-disk me-1"></i> Save Store Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
