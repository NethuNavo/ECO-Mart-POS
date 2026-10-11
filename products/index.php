<?php
$pageTitle = 'Products Catalog';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();
$search = trim($_GET['search'] ?? '');
$categoryFilter = (int)($_GET['category_id'] ?? 0);

$query = "SELECT p.*, c.name as category_name, b.name as brand_name, s.company_name as supplier_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN brands b ON p.brand_id = b.id 
    LEFT JOIN suppliers s ON p.supplier_id = s.id 
    WHERE 1=1";
$params = [];

if ($search !== '') {
    $query .= " AND (p.name LIKE ? OR p.barcode LIKE ? OR p.sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categoryFilter > 0) {
    $query .= " AND p.category_id = ?";
    $params[] = $categoryFilter;
}

$query .= " ORDER BY p.id DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="background: #f8fafc; min-height: calc(100vh - 65px);">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Product Catalog & Inventory Items</h3>
                <p class="text-muted small m-0">Manage supermarket SKUs, barcode mappings, cost & retail prices, and reorder levels.</p>
            </div>
            <a href="<?= BASE_URL ?>products/add.php" class="btn btn-success fw-bold px-3 py-2 shadow-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-plus"></i> Add New Product
            </a>
        </div>

        <!-- Filter & Search Bar -->
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
            <form method="GET" class="row g-2">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by name, barcode, or SKU..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="category_id" class="form-select">
                        <option value="0">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                    <?php if ($search !== '' || $categoryFilter > 0): ?>
                        <a href="<?= BASE_URL ?>products/index.php" class="btn btn-outline-secondary"><i class="fa-solid fa-xmark"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Products Table -->
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-muted">
                        <tr>
                            <th>Barcode / SKU</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Cost Price</th>
                            <th>Selling Price</th>
                            <th>Stock Qty</th>
                            <th>Expiry</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr><td colspan="9" class="text-center py-5 text-muted">No products found matching the criteria.</td></tr>
                        <?php else: ?>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold font-monospace small"><i class="fa-solid fa-barcode text-success me-1"></i> <?= htmlspecialchars($p['barcode']) ?></div>
                                        <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($p['sku']) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($p['name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($p['brand_name'] ?? 'Generic') ?></small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?></span></td>
                                    <td class="currency-font small text-muted"><?= formatCurrency((float)$p['cost_price']) ?></td>
                                    <td class="currency-font fw-bold text-success">
                                        <?= formatCurrency((float)($p['discount_price'] ?? $p['selling_price'])) ?>
                                        <?php if ($p['discount_price']): ?>
                                            <div class="text-decoration-line-through text-muted small" style="font-size:0.7rem;"><?= formatCurrency((float)$p['selling_price']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $p['quantity'] > $p['min_stock_level'] ? 'bg-success-subtle text-success' : ($p['quantity'] > 0 ? 'bg-warning-subtle text-warning' : 'bg-danger') ?> fw-bold">
                                            <?= $p['quantity'] ?> <?= htmlspecialchars($p['unit']) ?>
                                        </span>
                                    </td>
                                    <td class="small text-muted"><?= $p['expiry_date'] ?? 'N/A' ?></td>
                                    <td>
                                        <span class="badge <?= $p['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>"><?= ucfirst($p['status']) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>products/edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary py-0 px-2" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                        <a href="<?= BASE_URL ?>products/delete.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="return confirm('Are you sure you want to deactivate this product?')" title="Delete"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
