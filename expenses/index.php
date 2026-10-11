<?php
$pageTitle = 'Supermarket Expenses';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['category'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $expenseDate = $_POST['expense_date'] ?? date('Y-m-d');

    if (empty($category) || $amount <= 0) {
        $error = 'Please fill category and positive amount.';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO expenses (category, amount, description, user_id, expense_date, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$category, $amount, $description, $_SESSION['user_id'] ?? 1, $expenseDate]);

            logAudit('EXPENSE_ADDED', "Logged operational expense of Rs. " . number_format($amount, 2) . " under {$category}.", $_SESSION['user_id'] ?? 1);
            $success = 'Expense logged successfully!';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$expenses = $db->query("SELECT e.*, u.name as logged_by FROM expenses e LEFT JOIN users u ON e.user_id = u.id ORDER BY e.expense_date DESC, e.id DESC")->fetchAll();
$totalExpense = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM expenses")->fetch()['total'];
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="background: #f8fafc; min-height: calc(100vh - 65px);">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Store Operational Expenses</h3>
                <p class="text-muted small m-0">Track electricity, refrigeration maintenance, packaging bags, and staff logistics.</p>
            </div>
            <div class="card p-2 px-3 bg-white border-0 shadow-sm rounded-3">
                <span class="small text-muted">Total Recorded Expenses:</span>
                <span class="fs-5 fw-bold text-danger currency-font"><?= formatCurrency((float)$totalExpense) ?></span>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Add Expense Form -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h6 class="fw-bold mb-3"><i class="fa-solid fa-plus-circle text-danger me-2"></i> Log New Store Expense</h6>
                    <form method="POST" action="index.php" class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Expense Category *</label>
                            <select name="category" class="form-select" required>
                                <option value="Electricity & Utilities">Electricity & Utilities</option>
                                <option value="Store Packaging & Bags">Store Packaging & Bags</option>
                                <option value="Chiller & AC Maintenance">Chiller & AC Maintenance</option>
                                <option value="Staff Refreshments & Meals">Staff Refreshments & Meals</option>
                                <option value="Cleaning & Sanitation">Cleaning & Sanitation</option>
                                <option value="Rent & Lease">Rent & Lease</option>
                                <option value="Miscellaneous">Miscellaneous</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Amount (LKR) *</label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" step="0.01" name="amount" class="form-control fw-bold" placeholder="0.00" required>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Date Incurred</label>
                            <input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Description / Bill Notes</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="e.g. CEB electricity bill receipt #84839"></textarea>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-danger w-100 fw-bold py-2"><i class="fa-solid fa-check me-1"></i> Save Expense</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Expense History Table -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                    <div class="p-3 border-bottom">
                        <h6 class="fw-bold m-0"><i class="fa-solid fa-receipt text-secondary me-2"></i> Expense History Ledger</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-muted">
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Staff</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($expenses)): ?>
                                    <tr><td colspan="5" class="text-center py-4 text-muted">No expenses recorded.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($expenses as $ex): ?>
                                        <tr>
                                            <td class="small text-muted"><?= htmlspecialchars($ex['expense_date']) ?></td>
                                            <td class="fw-semibold small"><?= htmlspecialchars($ex['category']) ?></td>
                                            <td class="fw-bold currency-font text-danger small"><?= formatCurrency((float)$ex['amount']) ?></td>
                                            <td class="small text-muted"><?= htmlspecialchars($ex['description'] ?? '-') ?></td>
                                            <td class="small text-muted"><?= htmlspecialchars($ex['logged_by'] ?? 'Staff') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
