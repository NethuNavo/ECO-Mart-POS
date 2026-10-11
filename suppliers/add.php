<?php
$pageTitle = 'Add Supplier';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company = trim($_POST['company_name'] ?? '');
    $person = trim($_POST['contact_person'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($company) || empty($phone)) {
        $error = 'Company name and phone number are required.';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO suppliers (company_name, contact_person, phone, email, address, outstanding_balance, status, created_at) VALUES (?, ?, ?, ?, ?, 0.00, 'active', NOW())");
            $stmt->execute([$company, $person, $phone, $email ?: null, $address ?: null]);

            logAudit('SUPPLIER_CREATED', "Added supplier {$company} ({$phone}).", $_SESSION['user_id'] ?? 1);

            header('Location: ' . BASE_URL . 'suppliers/index.php?msg=added');
            exit;
        } catch (Exception $e) {
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
                <h3 class="fw-bold text-dark mb-1">Add Supplier / Distributor</h3>
                <p class="text-muted small m-0">Register a new product manufacturer or procurement partner.</p>
            </div>
            <a href="<?= BASE_URL ?>suppliers/index.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Suppliers
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white" style="max-width: 600px;">
            <form method="POST" action="add.php" class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold small">Company / Distributor Name *</label>
                    <input type="text" name="company_name" class="form-control" placeholder="e.g. Fonterra Brands Lanka" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Contact Representative</label>
                    <input type="text" name="contact_person" class="form-control" placeholder="e.g. Kamal Perera">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Telephone / Hotline *</label>
                    <input type="text" name="phone" class="form-control font-monospace" placeholder="+94 11 200 0000" required>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold small">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="orders@fonterra.lk">
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold small">Warehouse / Office Address</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="e.g. Biyagama Free Trade Zone, Malwana"></textarea>
                </div>

                <div class="col-12 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>suppliers/index.php" class="btn btn-light px-4">Cancel</a>
                    <button type="submit" class="btn btn-success fw-bold px-4"><i class="fa-solid fa-check me-1"></i> Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
