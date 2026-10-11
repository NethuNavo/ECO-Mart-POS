<?php
$pageTitle = 'Add New Product';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barcode = trim($_POST['barcode'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 1);
    $brandId = !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;
    $supplierId = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
    $costPrice = (float)($_POST['cost_price'] ?? 0);
    $sellingPrice = (float)($_POST['selling_price'] ?? 0);
    $discountPrice = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : null;
    $quantity = (int)($_POST['quantity'] ?? 0);
    $minStock = (int)($_POST['min_stock_level'] ?? 10);
    $reorderLevel = (int)($_POST['reorder_level'] ?? 20);
    $unit = trim($_POST['unit'] ?? 'pcs');
    $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;

    if (empty($barcode) || empty($name) || $sellingPrice <= 0) {
        $error = 'Please fill in required fields (Barcode, Name, and Selling Price).';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO products (barcode, sku, name, category_id, brand_id, supplier_id, cost_price, selling_price, discount_price, quantity, min_stock_level, reorder_level, unit, expiry_date, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())");
            $stmt->execute([
                $barcode,
                $sku ?: 'SM-' . strtoupper(substr(uniqid(), -6)),
                $name,
                $categoryId,
                $brandId,
                $supplierId,
                $costPrice,
                $sellingPrice,
                $discountPrice,
                $quantity,
                $minStock,
                $reorderLevel,
                $unit,
                $expiryDate
            ]);
            $newId = $db->lastInsertId();

            if ($quantity > 0) {
                $mv = $db->prepare("INSERT INTO stock_movements (product_id, type, quantity, balance_after, reference_id, user_id, notes, created_at) VALUES (?, 'purchase', ?, ?, 'INITIAL-STOCK', ?, 'Initial product registration', NOW())");
                $mv->execute([$newId, $quantity, $quantity, $_SESSION['user_id'] ?? 1]);
            }

            logAudit('PRODUCT_CREATED', "Added new product '{$name}' (Barcode: {$barcode}) with initial stock {$quantity}.", $_SESSION['user_id'] ?? 1);
            $success = "Product '{$name}' successfully added!";
        } catch (Exception $e) {
            $error = 'Error saving product: ' . $e->getMessage();
        }
    }
}

$categories = $db->query("SELECT * FROM categories WHERE status='active' ORDER BY name ASC")->fetchAll();
$brands = $db->query("SELECT * FROM brands WHERE status='active' ORDER BY name ASC")->fetchAll();
$suppliers = $db->query("SELECT * FROM suppliers WHERE status='active' ORDER BY company_name ASC")->fetchAll();
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="background: #f8fafc; min-height: calc(100vh - 65px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Add Supermarket Product</h3>
                <p class="text-muted small m-0">Register a new barcode item with supplier and inventory parameters.</p>
            </div>
            <a href="<?= BASE_URL ?>products/index.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Catalog
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <form method="POST" action="add.php" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Barcode (UPC/EAN-13) *</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-barcode"></i></span>
                        <input type="text" name="barcode" id="barcodeField" class="form-control" placeholder="Scan or enter 13-digit barcode" required>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="genBarcode()">Gen</button>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small">SKU Code</label>
                    <input type="text" name="sku" id="skuField" class="form-control" placeholder="e.g. SM-GRO-105">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Unit of Measure</label>
                    <select name="unit" class="form-select">
                        <option value="pcs">Pieces (pcs)</option>
                        <option value="pkt">Packet (pkt)</option>
                        <option value="kg">Kilogram (kg)</option>
                        <option value="g">Gram (g)</option>
                        <option value="L">Litre (L)</option>
                        <option value="ml">Millilitre (ml)</option>
                        <option value="btl">Bottle (btl)</option>
                        <option value="box">Box</option>
                        <option value="can">Can</option>
                        <option value="bag">Bag</option>
                    </select>
                </div>

                <div class="col-md-8">
                    <label class="form-label fw-semibold small">Product Description / Title *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Dilmah Ceylon Green Tea 50g" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Category *</label>
                    <select name="category_id" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Brand</label>
                    <select name="brand_id" class="form-select">
                        <option value="">Select Brand (Optional)</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Primary Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">Select Supplier (Optional)</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['company_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Cost / Purchase Price (LKR) *</label>
                    <div class="input-group">
                        <span class="input-group-text">Rs.</span>
                        <input type="number" step="0.01" name="cost_price" class="form-control" placeholder="0.00" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Selling / Retail Price (LKR) *</label>
                    <div class="input-group">
                        <span class="input-group-text">Rs.</span>
                        <input type="number" step="0.01" name="selling_price" class="form-control" placeholder="0.00" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Discounted Promo Price (Optional)</label>
                    <div class="input-group">
                        <span class="input-group-text">Rs.</span>
                        <input type="number" step="0.01" name="discount_price" class="form-control" placeholder="Leave blank if none">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Initial Stock Qty</label>
                    <input type="number" name="quantity" class="form-control" value="50" min="0">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Min Stock Alert Level</label>
                    <input type="number" name="min_stock_level" class="form-control" value="10" min="1">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Reorder Threshold</label>
                    <input type="number" name="reorder_level" class="form-control" value="20" min="1">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Batch Expiry Date</label>
                    <input type="date" name="expiry_date" class="form-control">
                </div>

                <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>products/index.php" class="btn btn-light px-4">Cancel</a>
                    <button type="submit" class="btn btn-success fw-bold px-4"><i class="fa-solid fa-check me-1"></i> Save Product to Database</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function genBarcode() {
    const randomCode = '893' + Math.floor(1000000000 + Math.random() * 9000000000);
    document.getElementById('barcodeField').value = randomCode;
    if (!document.getElementById('skuField').value) {
        document.getElementById('skuField').value = 'SM-SKU-' + Math.floor(1000 + Math.random() * 9000);
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
