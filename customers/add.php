<?php
$pageTitle = 'Register Customer';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name) || empty($phone)) {
        $error = 'Customer name and phone number are required.';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO customers (name, phone, email, address, loyalty_points, total_spend, status, created_at) VALUES (?, ?, ?, ?, 0, 0, 'active', NOW())");
            $stmt->execute([$name, $phone, $email ?: null, $address ?: null]);

            logAudit('CUSTOMER_CREATED', "Registered customer {$name} ({$phone}).", $_SESSION['user_id'] ?? 1);

            header('Location: ' . BASE_URL . 'customers/index.php?msg=added');
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
                <h3 class="fw-bold text-dark mb-1">Register New Customer</h3>
                <p class="text-muted small m-0">Add shopper profile for checkout loyalty point accruals and promotional messaging.</p>
            </div>
            <a href="<?= BASE_URL ?>customers/index.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Customers
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white" style="max-width: 600px;">
            <form method="POST" action="add.php" class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold small">Full Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Kasun Jayawardena" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Phone Number (Lookup Key) *</label>
                    <input type="text" name="phone" class="form-control font-monospace" placeholder="+94 77 123 4567" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="kasun@example.com">
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold small">Residential Address</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="e.g. 45/2 Peradeniya Road, Kandy"></textarea>
                </div>

                <div class="col-12 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>customers/index.php" class="btn btn-light px-4">Cancel</a>
                    <button type="submit" class="btn btn-success fw-bold px-4"><i class="fa-solid fa-check me-1"></i> Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
