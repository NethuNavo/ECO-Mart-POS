<?php
/**
 * Receive Purchase Order & Automatically Increase Inventory Stock
 */
require_once __DIR__ . '/../config/database.php';
checkAuth();

$id = (int)($_GET['id'] ?? 0);
$db = Database::getConnection();

try {
    $db->beginTransaction();

    $stmt = $db->prepare("SELECT * FROM purchases WHERE id = ? AND status = 'pending' FOR UPDATE");
    $stmt->execute([$id]);
    $po = $stmt->fetch();

    if (!$po) {
        throw new Exception("Purchase Order #{$id} is not in pending state or does not exist.");
    }

    // Fetch items
    $itemsStmt = $db->prepare("SELECT * FROM purchase_items WHERE purchase_id = ?");
    $itemsStmt->execute([$id]);
    $items = $itemsStmt->fetchAll();

    $stockUpdate = $db->prepare("UPDATE products SET quantity = quantity + ?, cost_price = ? WHERE id = ?");
    $movementInsert = $db->prepare("INSERT INTO stock_movements (product_id, type, quantity, balance_after, reference_id, user_id, notes, created_at) VALUES (?, 'purchase', ?, ?, ?, ?, 'Received PO Delivery', NOW())");
    $prodQuery = $db->prepare("SELECT quantity, name FROM products WHERE id = ?");

    foreach ($items as $it) {
        $prodQuery->execute([$it['product_id']]);
        $prod = $prodQuery->fetch();
        $newBalance = $prod['quantity'] + $it['quantity'];

        // Increase product stock & update latest cost price
        $stockUpdate->execute([$it['quantity'], $it['unit_cost'], $it['product_id']]);

        // Record stock movement
        $movementInsert->execute([$it['product_id'], $it['quantity'], $newBalance, $po['po_number'], $_SESSION['user_id'] ?? 1]);
    }

    // Mark PO as received
    $updPO = $db->prepare("UPDATE purchases SET status = 'received', received_at = NOW() WHERE id = ?");
    $updPO->execute([$id]);

    logAudit('PO_RECEIVED', "Received purchase order {$po['po_number']} and added inward stock to catalog.", $_SESSION['user_id'] ?? 1);

    $db->commit();
    header('Location: ' . BASE_URL . 'purchases/index.php?msg=received');
    exit;

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    die("Error receiving PO: " . htmlspecialchars($e->getMessage()));
}
