<?php
$pageTitle = 'System Audit Trail';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = Database::getConnection();
$logs = $db->query("SELECT al.*, u.name as user_name, u.role as user_role 
    FROM audit_logs al 
    LEFT JOIN users u ON al.user_id = u.id 
    ORDER BY al.id DESC LIMIT 100")->fetchAll();
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="background: #f8fafc; min-height: calc(100vh - 65px);">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">System Audit Trail & Security Logs</h3>
                <p class="text-muted small m-0">Immutable compliance log of user logins, checkout billing, price changes, stock adjustments, and till closures.</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-muted">
                        <tr>
                            <th>Timestamp</th>
                            <th>Action / Event</th>
                            <th>Staff Member</th>
                            <th>Role</th>
                            <th>IP Address</th>
                            <th>Audit Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="small text-muted font-monospace"><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size:0.7rem;">
                                        <?= htmlspecialchars($log['action']) ?>
                                    </span>
                                </td>
                                <td class="small fw-semibold"><?= htmlspecialchars($log['user_name'] ?? 'System') ?></td>
                                <td>
                                    <span class="badge <?= ($log['user_role'] ?? '') === 'admin' ? 'bg-danger' : 'bg-light text-dark border' ?>" style="font-size:0.7rem;">
                                        <?= htmlspecialchars($log['user_role'] ?? 'SYSTEM') ?>
                                    </span>
                                </td>
                                <td class="small font-monospace text-muted"><?= htmlspecialchars($log['ip_address'] ?? '127.0.0.1') ?></td>
                                <td class="small text-dark"><?= htmlspecialchars($log['details']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
