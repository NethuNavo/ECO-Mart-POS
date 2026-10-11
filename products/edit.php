<?php
$pageTitle = 'Edit Product';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();
$error = '';
$success = '';
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: ' . BASE_URL . 'products/index.php');
    exit;
}

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
    $minStock = (int)($_POST['min_stock_level'] ?? 10);
    $reorderLevel = (int)($_POST['reorder_level'] ?? 20);
    $unit = trim($_POST['unit'] ?? 'pcs');
    $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $status = $_POST['status'] ?? 'active';

    if (empty($barcode) || empty($name) || $sellingPrice <= 0) {
        $error = 'Please fill in required fields (Barcode, Name, and Selling Price).';
    } else {
        try {
            $upd = $db->prepare("UPDATE products SET barcode=?, sku=?, name=?, category_id=?, brand_id=?, supplier_id=?, cost_price=?, selling_price=?, discount_price=?, min_stock_level=?, reorder_level=?, unit=?, expiry_date=?, status=? WHERE id=?");
            $upd->execute([
                $barcode, $sku, $name, $categoryId, $brandId, $supplierId,
                $costPrice, $sellingPrice, $discountPrice,
                $minStock, $reorderLevel, $unit, $expiryDate, $status, $id
            ]);

            logAudit('PRODUCT_UPDATED', "Updated product details for '{$name}' (ID: {$id}).", $_SESSION['user_id'] ?? 1);
            $success = "Product details successfully updated!";
            
            // Reload
            $stmt->execute([$id]);
            $product = $stmt->fetch();
        } catch (Exception $e) {
            $error = $e->getMessage();
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
                <h3 class="fw-bold text-dark mb-1">Edit Product: <?= htmlspecialchars($product['name']) ?></h3>
                <p class="text-muted small m-0">Update pricing, barcodes, and inventory thresholds.</p>
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
            <form method="POST" action="edit.php?id=<?= $id ?>" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Barcode (UPC/EAN-13) *</label>
                    <input type="text" name="barcode" class="form-control font-monospace" value="<?= htmlspecialchars($product['barcode']) ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small">SKU Code</label>
                    <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($product['sku']) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Unit of Measure</label>
                    <input type="text" name="unit" class="form-control" value="<?= htmlspecialchars($product['unit']) ?>" required>
                </div>

                <div class="col-md-8">
                    <label class="form-label fw-semibold small">Product Description / Title *</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Category *</label>
                    <select name="category_id" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Brand</label>
                    <select name="brand_id" class="form-select">
                        <option value="">Select Brand (Optional)</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= $product['brand_id'] == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Primary Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">Select Supplier (Optional)</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= $product['supplier_id'] == $s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['company_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Cost Price (LKR) *</label>
                    <div class="input-group">
                        <span class="input-group-text">Rs.</span>
                        <input type="number" step="0.01" name="cost_price" class="form-control" value="<?= $product['cost_price'] ?>" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Selling Price (LKR) *</label>
                    <div class="input-group">
                        <span class="input-group-text">Rs.</span>
                        <input type="number" step="0.01" name="selling_price" class="form-control" value="<?= $product['selling_price'] ?>" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Discounted Promo Price (Optional)</label>
                    <div class="input-group">
                        <span class="input-group-text">Rs.</span>
                        <input type="number" step="0.01" name="discount_price" class="form-control" value="<?= $product['discount_price'] ?? '' ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Min Stock Alert Level</label>
                    <input type="number" name="min_stock_level" class="form-control" value="<?= $product['min_stock_level'] ?>" min="1">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Reorder Threshold</label>
                    <input type="number" name="reorder_level" class="form-control" value="<?= $product['reorder_level'] ?>" min="1">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Batch Expiry Date</label>
                    <input type="date" name="expiry_date" class="form-control" value="<?= $product['expiry_date'] ?? '' ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : '' ?>>Inactive / Discontinued</option>
                    </select>
                </div>

                <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>products/index.php" class="btn btn-light px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary fw-bold px-4"><i class="fa-solid fa-save me-1"></i> Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
