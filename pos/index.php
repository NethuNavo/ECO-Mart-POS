<?php
$pageTitle = 'ECO Mart Fast Checkout POS Terminal';
require_once __DIR__ . '/../includes/header.php';

$db = Database::getConnection();

// Fetch Active Products with Category and Brand
$productsStmt = $db->query("SELECT p.*, c.name as category_name, c.slug as category_slug, b.name as brand_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN brands b ON p.brand_id = b.id 
    WHERE p.status = 'active' 
    ORDER BY p.name ASC");
$products = $productsStmt->fetchAll();

// Resolve legacy database filenames to the product images shipped with the app.
$productImageAliases = [
    'rice.jpg' => 'samba.png',
    'sugar.jpg' => 'sugar.png',
    'dhal.jpg' => 'dhal.png',
    'anchor.jpg' => 'anchor.png',
    'tea.jpg' => 'dilmha.png',
    'coffee.jpg' => 'nescafe.png',
    'egb.jpg' => 'gingerbeer.png',
    'cracker.jpg' => 'creamcaker.png',
    'lemonpuff.jpg' => 'lemonpuff.png',
    'milk.jpg' => 'milk.png',
    'cheese.jpg' => 'cheese.png',
    'astra.jpg' => 'astra.png',
    'eggs.jpg' => 'eggs.png',
    'carrots.jpg' => 'carrorts.png',
    'onions.jpg' => 'onion.png',
    'bananas.jpg' => 'bananas.png',
    'chicken.jpg' => 'chicken.png',
    'sunlight.jpg' => 'soap.png',
    'surfexcel.jpg' => 'surfexcel.png',
    'vim.jpg' => 'vim.png',
    'signal.jpg' => 'signal.png',
    'lifebuoy.jpg' => 'lifeboy.png',
    'icecream.jpg' => 'icecream.png',
];
$productImageDirectory = __DIR__ . '/../asset/images/products/';
$fallbackProductImage = 'default_product.svg';
foreach ($products as &$product) {
    $storedImage = basename(str_replace('\\', '/', trim((string)($product['image'] ?? ''))));
    $imageFile = $productImageAliases[strtolower($storedImage)] ?? $storedImage;
    if ($imageFile === '' || !is_file($productImageDirectory . $imageFile)) {
        $imageFile = $fallbackProductImage;
    }
    $product['image_url'] = BASE_URL . 'asset/images/products/' . rawurlencode($imageFile);
}
unset($product);

// Fetch Categories for Quick Filter Bar
$categories = $db->query("SELECT * FROM categories WHERE status = 'active' ORDER BY id ASC")->fetchAll();

// Fetch Customers
$customers = $db->query("SELECT * FROM customers WHERE status = 'active' ORDER BY name ASC")->fetchAll();

// Check if current user has an open shift; if not, suggest opening one
$userId = $_SESSION['user_id'] ?? 1;
$shiftStmt = $db->prepare("SELECT * FROM cashier_shifts WHERE user_id = ? AND status = 'open' ORDER BY id DESC LIMIT 1");
$shiftStmt->execute([$userId]);
$activeShift = $shiftStmt->fetch();
?>

<div class="d-flex flex-column" style="height: 100vh; overflow: hidden; background: #F8F5E9;">
    <!-- POS Top Header Bar -->
    <header class="d-flex align-items-center justify-content-between px-3 py-2 bg-white border-bottom shadow-sm">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= BASE_URL ?>dashboard.php" class="btn btn-outline-secondary btn-sm rounded-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
            </a>
            <div class="d-flex align-items-center gap-2">
                <img src="<?= BASE_URL ?>asset/images/eco-mart-logo.svg" alt="ECO Mart" width="40" height="36">
                <span class="fw-bold fs-5 text-success">ECO Mart POS</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle d-none d-md-inline">Lane #01</span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <!-- Active Shift Status -->
            <?php if ($activeShift): ?>
                <div class="badge bg-success-subtle text-success px-3 py-2 rounded-3 border border-success-subtle small">
                    <i class="fa-solid fa-clock me-1"></i> Shift #<?= $activeShift['id'] ?> Active (Float: <?= formatCurrency((float)$activeShift['opening_float']) ?>)
                </div>
            <?php else: ?>
                <a href="<?= BASE_URL ?>shifts/open.php" class="badge bg-warning-subtle text-warning px-3 py-2 rounded-3 border border-warning-subtle text-decoration-none small">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> No Active Shift — Click to Open
                </a>
            <?php endif; ?>

            <div class="fw-semibold text-muted small d-none d-lg-block">
                <i class="fa-regular fa-user me-1 text-success"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Cashier') ?>
            </div>

            <button class="btn btn-outline-danger btn-sm" onclick="clearCartConfirm()" title="Clear Current Cart">
                <i class="fa-solid fa-trash-can me-1"></i> Clear Cart
            </button>
        </div>
    </header>

    <!-- Main Terminal Body: 2 Columns (Catalog Left, Cart Right) -->
    <div class="row g-0 flex-grow-1" style="overflow: hidden;">
        
        <!-- Left Product Search & Selection Area -->
        <div class="col-lg-7 col-xl-8 d-flex flex-column h-100 p-3 border-end" style="background: #F8F5E9; overflow-y: auto;">
            
            <!-- Barcode Scanner & Search Bar -->
            <div class="row g-2 mb-3">
                <div class="col-md-7">
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-barcode text-success fs-5"></i></span>
                        <input type="text" id="barcodeInput" class="form-control form-control-lg border-start-0 ps-0" placeholder="Scan Barcode or enter SKU (Press Enter)..." autofocus autocomplete="off">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" id="searchInput" class="form-control form-control-lg border-start-0 ps-0" placeholder="Search product name..." onkeyup="filterProducts()">
                    </div>
                </div>
            </div>

            <!-- Categories Horizontal Filter Strip -->
            <div class="d-flex gap-2 overflow-x-auto pb-2 mb-3" style="white-space: nowrap;">
                <button class="btn btn-dark btn-sm rounded-pill px-3 py-1 cat-filter active" onclick="filterCategory('all', this)">
                    All Products
                </button>
                <?php foreach ($categories as $cat): ?>
                    <button class="btn btn-white bg-white border btn-sm rounded-pill px-3 py-1 cat-filter" onclick="filterCategory('<?= htmlspecialchars($cat['slug']) ?>', this)">
                        <i class="fa-solid <?= htmlspecialchars($cat['icon']) ?> text-success me-1"></i> <?= htmlspecialchars($cat['name']) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Products Card Grid -->
            <div class="row g-2" id="productsGrid">
                <?php foreach ($products as $p): ?>
                    <div class="col-6 col-sm-4 col-md-4 col-xl-3 product-item" 
                         data-category="<?= htmlspecialchars($p['category_slug'] ?? 'all') ?>" 
                         data-name="<?= strtolower(htmlspecialchars($p['name'])) ?>"
                         data-barcode="<?= htmlspecialchars($p['barcode']) ?>"
                         data-sku="<?= htmlspecialchars($p['sku']) ?>"
                         onclick="addToCart(<?= htmlspecialchars(json_encode($p)) ?>)">
                        <div class="card h-100 border-0 shadow-sm rounded-3 p-2 bg-white cursor-pointer position-relative prod-card <?= $p['quantity'] <= 0 ? 'opacity-50' : '' ?>">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="badge bg-light text-muted border" style="font-size:0.7rem;"><?= htmlspecialchars($p['unit']) ?></span>
                                <span class="badge <?= $p['quantity'] > $p['min_stock_level'] ? 'bg-success-subtle text-success' : ($p['quantity'] > 0 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger') ?>" style="font-size:0.7rem;">
                                    <?= $p['quantity'] > 0 ? $p['quantity'] . ' left' : 'Out of Stock' ?>
                                </span>
                            </div>
                            <div class="mt-2 mb-1 d-flex justify-content-center align-items-center" style="height: 88px;">
                                <img src="<?= htmlspecialchars($p['image_url'], ENT_QUOTES, 'UTF-8') ?>"
                                     alt="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>"
                                     class="img-fluid" style="max-height: 88px; max-width: 100%; object-fit: contain;"
                                     loading="lazy"
                                     onerror="this.onerror=null;this.src='<?= BASE_URL ?>asset/images/products/default_product.svg';">
                            </div>
                            <div class="fw-bold text-dark text-truncate mt-1" style="font-size: 0.9rem;" title="<?= htmlspecialchars($p['name']) ?>">
                                <?= htmlspecialchars($p['name']) ?>
                            </div>
                            <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($p['barcode']) ?></div>
                            
                            <div class="mt-2 d-flex align-items-baseline justify-content-between">
                                <div class="fw-bold text-success currency-font fs-6">
                                    <?= formatCurrency((float)($p['discount_price'] ?? $p['selling_price'])) ?>
                                </div>
                                <?php if (!empty($p['discount_price'])): ?>
                                    <small class="text-decoration-line-through text-muted" style="font-size: 0.7rem;">
                                        <?= formatCurrency((float)$p['selling_price']) ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right Cart & Billing Checkout Column -->
        <div class="col-lg-5 col-xl-4 d-flex flex-column h-100 bg-white p-3 shadow-lg justify-content-between" style="overflow-y: auto;">
            
            <!-- Customer Selection Strip -->
            <div>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <label class="form-label fw-bold small text-muted text-uppercase m-0">Customer</label>
                    <span id="custPointsBadge" class="badge bg-primary-subtle text-primary d-none">0 Loyalty Pts</span>
                </div>
                <div class="input-group input-group-sm mb-3">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-user text-muted"></i></span>
                    <select id="customerSelect" class="form-select" onchange="onCustomerChange()">
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['id'] ?>" data-phone="<?= htmlspecialchars($c['phone']) ?>" data-points="<?= $c['loyalty_points'] ?>">
                                <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['phone']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Cart Items Table -->
                <div class="table-responsive border rounded-3 mb-2" style="max-height: 38vh; overflow-y: auto;">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light small text-muted sticky-top">
                            <tr>
                                <th>Item</th>
                                <th class="text-center" style="width: 100px;">Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Total</th>
                                <th style="width: 30px;"></th>
                            </tr>
                        </thead>
                        <tbody id="cartTableBody">
                            <!-- Populated dynamically via JavaScript -->
                            <tr id="emptyCartRow">
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-cart-shopping fs-3 mb-2 text-black-50"></i>
                                    <div class="fw-semibold">Cart is currently empty</div>
                                    <small>Scan a barcode or click a product to add</small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Totals Calculation Panel -->
            <div class="bg-light p-3 rounded-3 border">
                <div class="d-flex justify-content-between text-muted small mb-1">
                    <span>Subtotal:</span>
                    <span id="txtSubtotal" class="fw-semibold currency-font">Rs. 0.00</span>
                </div>
                <div class="d-flex justify-content-between text-muted small mb-1">
                    <span>Discount:</span>
                    <span id="txtDiscount" class="text-danger fw-semibold currency-font">- Rs. 0.00</span>
                </div>
                <div class="d-flex justify-content-between text-muted small mb-1">
                    <span>Tax (0%):</span>
                    <span id="txtTax" class="currency-font">Rs. 0.00</span>
                </div>
                
                <hr class="my-2">
                
                <div class="d-flex justify-content-between align-items-baseline mb-3">
                    <span class="fs-5 fw-bold text-dark">Grand Total:</span>
                    <span id="txtGrandTotal" class="fs-3 fw-bold text-success currency-font">Rs. 0.00</span>
                </div>

                <!-- Action & Payment Buttons -->
                <div class="row g-2">
                    <div class="col-4">
                        <button class="btn btn-outline-secondary w-100 py-2 fw-semibold btn-sm" onclick="holdSale()">
                            <i class="fa-solid fa-pause me-1"></i> Hold
                        </button>
                    </div>
                    <div class="col-4">
                        <button class="btn btn-outline-primary w-100 py-2 fw-semibold btn-sm" onclick="showCardPaymentModal()">
                            <i class="fa-solid fa-credit-card me-1"></i> Card
                        </button>
                    </div>
                    <div class="col-4">
                        <button class="btn btn-outline-warning w-100 py-2 fw-semibold btn-sm" onclick="showOnlinePaymentModal()">
                            <i class="fa-solid fa-qrcode me-1"></i> LankaQR
                        </button>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-success w-100 py-3 fw-bold fs-5 shadow" id="btnPayCash" onclick="showCashModal()">
                            <i class="fa-solid fa-money-bill-wave me-2"></i> CASH CHECKOUT [F1]
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Cash Payment & Change Calculator -->
<div class="modal fade" id="cashModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-success text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-cash-register me-2"></i> Cash Checkout & Change</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="text-muted small text-uppercase fw-semibold">Payable Amount</div>
                    <div class="fs-2 fw-bold text-success currency-font" id="modalPayableAmount">Rs. 0.00</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Tendered Cash (Amount Received)</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text fw-bold">Rs.</span>
                        <input type="number" step="10" id="tenderedInput" class="form-control fw-bold fs-4 text-dark" placeholder="0.00" oninput="calcChange()" autofocus>
                    </div>
                </div>

                <!-- Quick Tender Bills in LKR -->
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="quickTender(500)">+ Rs. 500</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="quickTender(1000)">+ Rs. 1,000</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="quickTender(2000)">+ Rs. 2,000</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="quickTender(5000)">+ Rs. 5,000</button>
                    <button type="button" class="btn btn-outline-success btn-sm fw-bold" onclick="exactCash()">Exact Cash</button>
                </div>

                <!-- Balance / Change Display -->
                <div class="bg-light p-3 rounded-3 text-center border">
                    <div class="text-muted small fw-semibold text-uppercase">Change to Return</div>
                    <div class="fs-3 fw-bold text-primary currency-font" id="modalChangeAmount">Rs. 0.00</div>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 bg-light rounded-bottom-4">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success fw-bold px-4 py-2" id="btnCompleteCashSale" onclick="processSale('cash')">
                    <i class="fa-solid fa-check me-1"></i> Complete & Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Thermal Receipt View & Print -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold text-success"><i class="fa-solid fa-check-circle me-1"></i> Sale Completed!</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3" id="thermalReceiptContent" style="font-family: 'JetBrains Mono', monospace; font-size: 12px;">
                <!-- Thermal receipt content rendered dynamically -->
            </div>
            <div class="modal-footer border-0 pt-0 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">New Sale</button>
                <button type="button" class="btn btn-success btn-sm fw-bold" onclick="printThermalReceipt()">
                    <i class="fa-solid fa-print me-1"></i> Print (80mm / 58mm)
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// POS State Management
let cart = [];
const allProducts = <?= json_encode($products) ?>;
const BASE_URL = '<?= BASE_URL ?>';

// Barcode input auto-listener
document.getElementById('barcodeInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const code = this.value.trim();
        if (!code) return;
        
        const found = allProducts.find(p => p.barcode === code || p.sku.toLowerCase() === code.toLowerCase());
        if (found) {
            addToCart(found);
            this.value = '';
        } else {
            alert('Product with barcode "' + code + '" not found in catalog.');
            this.select();
        }
    }
});

// Add to Cart
function addToCart(product) {
    if (product.quantity <= 0) {
        alert('Item "' + product.name + '" is currently OUT OF STOCK.');
        return;
    }
    
    const existing = cart.find(item => item.id === product.id);
    if (existing) {
        if (existing.qty + 1 > product.quantity) {
            alert('Cannot add more. Available stock limit reached (' + product.quantity + ').');
            return;
        }
        existing.qty += 1;
    } else {
        const unitPrice = parseFloat(product.discount_price || product.selling_price);
        const originalPrice = parseFloat(product.selling_price);
        const discount = originalPrice - unitPrice;
        
        cart.push({
            id: product.id,
            name: product.name,
            barcode: product.barcode,
            unitPrice: unitPrice,
            costPrice: parseFloat(product.cost_price),
            taxPercent: parseFloat(product.tax_percent || 0),
            discount: discount > 0 ? discount : 0,
            stock: parseInt(product.quantity),
            qty: 1
        });
    }
    renderCart();
}

function updateQty(id, change) {
    const item = cart.find(i => i.id === id);
    if (!item) return;
    
    if (item.qty + change > item.stock) {
        alert('Available stock limit reached (' + item.stock + ').');
        return;
    }
    
    item.qty += change;
    if (item.qty <= 0) {
        cart = cart.filter(i => i.id !== id);
    }
    renderCart();
}

function removeFromCart(id) {
    cart = cart.filter(i => i.id !== id);
    renderCart();
}

function renderCart() {
    const tbody = document.getElementById('cartTableBody');
    if (cart.length === 0) {
        tbody.innerHTML = `<tr id="emptyCartRow"><td colspan="5" class="text-center py-5 text-muted"><i class="fa-solid fa-cart-shopping fs-3 mb-2 text-black-50"></i><div class="fw-semibold">Cart is currently empty</div><small>Scan a barcode or click a product to add</small></td></tr>`;
        updateTotals(0, 0, 0);
        return;
    }

    let html = '';
    let subtotal = 0;
    let totalDiscount = 0;
    let totalTax = 0;

    cart.forEach(item => {
        const lineTotal = item.unitPrice * item.qty;
        subtotal += lineTotal;
        totalDiscount += (item.discount * item.qty);
        totalTax += (lineTotal * (item.taxPercent / 100));

        html += `
        <tr>
            <td>
                <div class="fw-bold small text-truncate" style="max-width: 140px;" title="${item.name}">${item.name}</div>
                <div class="text-muted" style="font-size:0.7rem;">${item.barcode}</div>
            </td>
            <td class="text-center">
                <div class="input-group input-group-sm d-inline-flex justify-content-center" style="width: 85px;">
                    <button class="btn btn-outline-secondary btn-sm p-0 px-1" onclick="updateQty(${item.id}, -1)">-</button>
                    <span class="form-control text-center p-0 fw-bold">${item.qty}</span>
                    <button class="btn btn-outline-secondary btn-sm p-0 px-1" onclick="updateQty(${item.id}, 1)">+</button>
                </div>
            </td>
            <td class="text-end small currency-font">Rs. ${item.unitPrice.toFixed(2)}</td>
            <td class="text-end fw-bold small text-success currency-font">Rs. ${lineTotal.toFixed(2)}</td>
            <td class="text-end">
                <button class="btn btn-link text-danger p-0" onclick="removeFromCart(${item.id})"><i class="fa-solid fa-xmark"></i></button>
            </td>
        </tr>`;
    });

    tbody.innerHTML = html;
    updateTotals(subtotal, totalDiscount, totalTax);
}

function updateTotals(subtotal, discount, tax) {
    const grandTotal = subtotal + tax;
    document.getElementById('txtSubtotal').innerText = 'Rs. ' + subtotal.toFixed(2);
    document.getElementById('txtDiscount').innerText = '- Rs. ' + discount.toFixed(2);
    document.getElementById('txtTax').innerText = 'Rs. ' + tax.toFixed(2);
    document.getElementById('txtGrandTotal').innerText = 'Rs. ' + grandTotal.toFixed(2);
    window.currentGrandTotal = grandTotal;
}

function clearCartConfirm() {
    if (cart.length > 0 && confirm('Are you sure you want to clear the entire cart?')) {
        cart = [];
        renderCart();
    }
}

// Filter product grid by Category
function filterCategory(catSlug, btn) {
    document.querySelectorAll('.cat-filter').forEach(b => b.classList.remove('active', 'btn-dark'));
    btn.classList.add('active', 'btn-dark');

    const items = document.querySelectorAll('.product-item');
    items.forEach(item => {
        if (catSlug === 'all' || item.dataset.category === catSlug) {
            item.classList.remove('d-none');
        } else {
            item.classList.add('d-none');
        }
    });
}

// Search products by name or barcode
function filterProducts() {
    const query = document.getElementById('searchInput').value.toLowerCase().trim();
    const items = document.querySelectorAll('.product-item');
    items.forEach(item => {
        const name = item.dataset.name;
        const code = item.dataset.barcode;
        const sku = item.dataset.sku.toLowerCase();
        if (name.includes(query) || code.includes(query) || sku.includes(query)) {
            item.classList.remove('d-none');
        } else {
            item.classList.add('d-none');
        }
    });
}

// Modal and checkout logic
let cashModalInstance = null;
let receiptModalInstance = null;

function showCashModal() {
    if (cart.length === 0) {
        alert('Cart is empty. Please add products before checking out.');
        return;
    }
    const payable = window.currentGrandTotal || 0;
    document.getElementById('modalPayableAmount').innerText = 'Rs. ' + payable.toFixed(2);
    document.getElementById('tenderedInput').value = payable.toFixed(2);
    calcChange();

    cashModalInstance = new bootstrap.Modal(document.getElementById('cashModal'));
    cashModalInstance.show();
}

function quickTender(amount) {
    const current = parseFloat(document.getElementById('tenderedInput').value) || 0;
    document.getElementById('tenderedInput').value = (current + amount).toFixed(2);
    calcChange();
}

function exactCash() {
    const payable = window.currentGrandTotal || 0;
    document.getElementById('tenderedInput').value = payable.toFixed(2);
    calcChange();
}

function calcChange() {
    const payable = window.currentGrandTotal || 0;
    const tendered = parseFloat(document.getElementById('tenderedInput').value) || 0;
    const change = tendered - payable;
    const changeElem = document.getElementById('modalChangeAmount');
    
    if (change >= 0) {
        changeElem.innerText = 'Rs. ' + change.toFixed(2);
        changeElem.className = 'fs-3 fw-bold text-success currency-font';
    } else {
        changeElem.innerText = 'Short: Rs. ' + Math.abs(change).toFixed(2);
        changeElem.className = 'fs-3 fw-bold text-danger currency-font';
    }
}

// Process Sale (AJAX call to pos/process_sale.php)
function processSale(paymentMethod) {
    const payable = window.currentGrandTotal || 0;
    const tendered = paymentMethod === 'cash' 
        ? (parseFloat(document.getElementById('tenderedInput').value) || payable) 
        : payable;

    if (paymentMethod === 'cash' && tendered < payable) {
        alert('Received cash is less than payable grand total.');
        return;
    }

    const customerId = document.getElementById('customerSelect').value;

    const payload = {
        customerId: customerId,
        paymentMethod: paymentMethod,
        items: cart,
        grandTotal: payable,
        paidAmount: tendered,
        changeAmount: Math.max(0, tendered - payable)
    };

    fetch(BASE_URL + 'pos/process_sale.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            if (cashModalInstance) cashModalInstance.hide();
            renderThermalReceipt(res.sale);
            cart = [];
            renderCart();
        } else {
            alert('Failed to complete sale: ' + res.error);
        }
    })
    .catch(err => {
        alert('Server communication error: ' + err.message);
    });
}

function renderThermalReceipt(sale) {
    const custSelect = document.getElementById('customerSelect');
    const custName = custSelect.options[custSelect.selectedIndex].text;

    let itemsHtml = '';
    sale.items.forEach(i => {
        itemsHtml += `
        <div style="display:flex; justify-content:space-between; margin-bottom:3px;">
            <span>${i.name} x ${i.qty}</span>
            <span>Rs. ${(i.unitPrice * i.qty).toFixed(2)}</span>
        </div>`;
    });

    const receiptHtml = `
    <div style="text-align:center; border-bottom:1px dashed #000; padding-bottom:8px; margin-bottom:8px;">
        <h5 style="margin:0; font-weight:bold;">ECO MART</h5>
        <div>No. 245 Galle Road, Colombo 04</div>
        <div>Tel: +94 11 258 9900</div>
        <div style="margin-top:4px;"><strong>TAX INVOICE</strong></div>
    </div>
    <div style="font-size:11px; margin-bottom:8px; border-bottom:1px dashed #000; padding-bottom:6px;">
        <div>Inv #: <strong>${sale.invoice_number}</strong></div>
        <div>Date: ${new Date().toLocaleString()}</div>
        <div>Cashier: <?= htmlspecialchars($_SESSION['user_name'] ?? 'Staff') ?></div>
        <div>Customer: ${custName}</div>
    </div>
    <div style="border-bottom:1px dashed #000; padding-bottom:8px; margin-bottom:8px;">
        ${itemsHtml}
    </div>
    <div style="font-size:12px; margin-bottom:8px; border-bottom:1px dashed #000; padding-bottom:6px;">
        <div style="display:flex; justify-content:space-between; font-weight:bold; font-size:14px;">
            <span>GRAND TOTAL:</span>
            <span>Rs. ${sale.grandTotal.toFixed(2)}</span>
        </div>
        <div style="display:flex; justify-content:space-between; margin-top:4px;">
            <span>Paid (${sale.paymentMethod.toUpperCase()}):</span>
            <span>Rs. ${sale.paidAmount.toFixed(2)}</span>
        </div>
        <div style="display:flex; justify-content:space-between;">
            <span>Change:</span>
            <span>Rs. ${sale.changeAmount.toFixed(2)}</span>
        </div>
    </div>
    <div style="text-align:center; font-size:10px;">
        <div>*** THANK YOU FOR SHOPPING! ***</div>
        <div>Goods exchange within 3 days with bill.</div>
    </div>
    `;

    document.getElementById('thermalReceiptContent').innerHTML = receiptHtml;
    receiptModalInstance = new bootstrap.Modal(document.getElementById('receiptModal'));
    receiptModalInstance.show();
}

function printThermalReceipt() {
    const printWindow = window.open('', '', 'width=320,height=600');
    printWindow.document.write('<html><head><title>Receipt</title><style>body{font-family:monospace;font-size:12px;padding:10px;}</style></head><body>');
    printWindow.document.write(document.getElementById('thermalReceiptContent').innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

function showCardPaymentModal() {
    if (cart.length === 0) return alert('Cart is empty.');
    if (confirm('Process Credit/Debit Card payment of Rs. ' + (window.currentGrandTotal || 0).toFixed(2) + ' via POS Card Terminal?')) {
        processSale('card');
    }
}

function showOnlinePaymentModal() {
    if (cart.length === 0) return alert('Cart is empty.');
    if (confirm('Generate LankaQR online payment of Rs. ' + (window.currentGrandTotal || 0).toFixed(2) + '?')) {
        processSale('online');
    }
}

function holdSale() {
    if (cart.length === 0) return alert('Cart is empty.');
    localStorage.setItem('smartmart_held_cart', JSON.stringify(cart));
    alert('Current cart has been saved to Held Sales buffer.');
    cart = [];
    renderCart();
}

function onCustomerChange() {
    const select = document.getElementById('customerSelect');
    const points = select.options[select.selectedIndex].dataset.points;
    const badge = document.getElementById('custPointsBadge');
    if (points && parseInt(points) > 0) {
        badge.innerText = points + ' Loyalty Pts';
        badge.classList.remove('d-none');
    } else {
        badge.classList.add('d-none');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
