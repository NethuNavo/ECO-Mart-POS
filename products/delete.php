<?php
/**
 * Soft delete / Deactivate Product
 */
require_once __DIR__ . '/../config/database.php';
checkAuth();

$id = (int)($_GET['id'] ?? 0);
$db = Database::getConnection();

if ($id > 0) {
    $stmt = $db->prepare("UPDATE products SET status = 'inactive' WHERE id = ?");
    $stmt->execute([$id]);

    logAudit('PRODUCT_DEACTIVATED', "Deactivated product ID: {$id}", $_SESSION['user_id'] ?? 1);
}

header('Location: ' . BASE_URL . 'products/index.php?msg=deactivated');
exit;
