<?php
$pageTitle = 'Staff & Cashier Users';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = $_POST['role'] ?? 'cashier';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } else {
        try {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
            $stmt->execute([$name, $email, $hashed, $role]);

            logAudit('USER_CREATED', "Created staff user {$name} ({$role}).", $_SESSION['user_id'] ?? 1);
            $success = "User '{$name}' created successfully!";
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$users = $db->query("SELECT * FROM users ORDER BY id ASC")->fetchAll();
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="background: #f8fafc; min-height: calc(100vh - 65px);">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Staff Accounts & Access Roles</h3>
                <p class="text-muted small m-0">Manage cashiers, shift supervisors, store managers, and system administrators.</p>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Add User Form -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h6 class="fw-bold mb-3"><i class="fa-solid fa-user-plus text-primary me-2"></i> Create New Staff Account</h6>
                    <form method="POST" action="index.php" class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Full Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Nimal Fernando" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Email / Username *</label>
                            <input type="email" name="email" class="form-control" placeholder="e.g. nimal@smartmart.com" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Password *</label>
                            <input type="password" name="password" class="form-control" placeholder="Min 6 characters" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Permission Role *</label>
                            <select name="role" class="form-select" required>
                                <option value="cashier">Cashier (POS Fast Lane only)</option>
                                <option value="manager">Manager (Inventory, Purchases, Reports)</option>
                                <option value="admin">Administrator (Full System Access)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100 fw-bold py-2"><i class="fa-solid fa-user-check me-1"></i> Register Account</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                    <div class="p-3 border-bottom">
                        <h6 class="fw-bold m-0"><i class="fa-solid fa-users text-secondary me-2"></i> Registered Supermarket Users</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-muted">
                                <tr>
                                    <th>Staff Member</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Last Login</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold small text-dark"><?= htmlspecialchars($u['name']) ?></div>
                                            <div class="text-muted" style="font-size:0.75rem;">ID: #<?= $u['id'] ?></div>
                                        </td>
                                        <td class="small font-monospace"><?= htmlspecialchars($u['email']) ?></td>
                                        <td>
                                            <span class="badge <?= $u['role'] === 'admin' ? 'bg-danger' : ($u['role'] === 'manager' ? 'bg-primary' : 'bg-success') ?> text-uppercase" style="font-size:0.7rem;">
                                                <?= htmlspecialchars($u['role']) ?>
                                            </span>
                                        </td>
                                        <td class="small text-muted"><?= $u['last_login'] ? date('Y-m-d H:i', strtotime($u['last_login'])) : 'Never' ?></td>
                                        <td><span class="badge bg-success-subtle text-success"><?= ucfirst($u['status']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
