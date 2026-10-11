<?php
/**
 * ECO Mart POS - Process Sale AJAX Endpoint
 * Decreases inventory in real-time, logs stock movement, records sales and updates shift
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User session expired. Please log in again.']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || empty($data['items'])) {
    echo json_encode(['success' => false, 'error' => 'No items provided in cart.']);
    exit;
}

$db = Database::getConnection();

try {
    $db->beginTransaction();

    $userId = $_SESSION['user_id'];
    $customerId = (int)($data['customerId'] ?? 1);
    $paymentMethod = $data['paymentMethod'] ?? 'cash';
    $paidAmount = (float)($data['paidAmount'] ?? 0);
    $changeAmount = (float)($data['changeAmount'] ?? 0);
    $grandTotal = (float)($data['grandTotal'] ?? 0);
    $subtotal = 0;
    $totalDiscount = 0;
    $totalTax = 0;

    // Check active shift
    $shiftStmt = $db->prepare("SELECT id FROM cashier_shifts WHERE user_id = ? AND status = 'open' ORDER BY id DESC LIMIT 1");
    $shiftStmt->execute([$userId]);
    $shift = $shiftStmt->fetch();
    $shiftId = $shift ? $shift['id'] : null;

    // Generate Invoice Number
    $invPrefix = getSetting('invoice_prefix', 'INV-');
    $todayCode = date('Ymd');
    $seqStmt = $db->query("SELECT COUNT(*) as cnt FROM sales WHERE DATE(created_at) = CURDATE()");
    $seq = (int)$seqStmt->fetch()['cnt'] + 1;
    $invoiceNumber = sprintf("%s%s-%04d", $invPrefix, $todayCode, $seq);

    // Calculate loyalty points
    $spendPerPoint = (float)getSetting('loyalty_spend_per_point', '200.00');
    $pointsEarned = ($spendPerPoint > 0 && $customerId > 1) ? floor($grandTotal / $spendPerPoint) : 0;

    // 1. Insert into sales table
    $saleStmt = $db->prepare("INSERT INTO sales (invoice_number, user_id, customer_id, shift_id, subtotal, discount_total, tax_total, grand_total, paid_amount, change_amount, payment_method, payment_status, loyalty_points_earned, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'paid', ?, 'completed', NOW())");
    $saleStmt->execute([
        $invoiceNumber,
        $userId,
        $customerId,
        $shiftId,
        $grandTotal,
        $totalDiscount,
        $totalTax,
        $grandTotal,
        $paidAmount,
        $changeAmount,
        $paymentMethod,
        $pointsEarned
    ]);
    $saleId = $db->lastInsertId();

    // 2. Loop through items, verify stock, insert sale_items, decrement inventory, log movement
    $itemInsert = $db->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, cost_price, unit_price, discount_amount, tax_amount, line_total) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stockUpdate = $db->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
    $movementInsert = $db->prepare("INSERT INTO stock_movements (product_id, type, quantity, balance_after, reference_id, user_id, notes, created_at) VALUES (?, 'sale', ?, ?, ?, ?, 'POS Terminal Sale', NOW())");
    $stockQuery = $db->prepare("SELECT quantity, name, cost_price, selling_price FROM products WHERE id = ? FOR UPDATE");

    foreach ($data['items'] as $item) {
        $prodId = (int)$item['id'];
        $qty = (int)$item['qty'];
        $unitPrice = (float)$item['unitPrice'];
        $lineTotal = $unitPrice * $qty;
        $discount = (float)($item['discount'] ?? 0) * $qty;
        $tax = (float)($item['taxPercent'] ?? 0) * $lineTotal / 100;

        // Fetch current product stock
        $stockQuery->execute([$prodId]);
        $prod = $stockQuery->fetch();
        if (!$prod) {
            throw new Exception("Product ID #{$prodId} not found in database.");
        }

        if ($prod['quantity'] < $qty) {
            throw new Exception("Insufficient stock for '{$prod['name']}'. Available: {$prod['quantity']}, Requested: {$qty}");
        }

        $balanceAfter = $prod['quantity'] - $qty;

        // Insert Sale Item
        $itemInsert->execute([
            $saleId,
            $prodId,
            $qty,
            $prod['cost_price'],
            $unitPrice,
            $discount,
            $tax,
            $lineTotal
        ]);

        // Decrement Product Stock
        $stockUpdate->execute([$qty, $prodId]);

        // Record in Stock Movements
        $movementInsert->execute([
            $prodId,
            -$qty,
            $balanceAfter,
            $invoiceNumber,
            $userId
        ]);
    }

    // 3. Update customer loyalty & spend if not walk-in
    if ($customerId > 1) {
        $custUpdate = $db->prepare("UPDATE customers SET loyalty_points = loyalty_points + ?, total_spend = total_spend + ? WHERE id = ?");
        $custUpdate->execute([$pointsEarned, $grandTotal, $customerId]);
    }

    // 4. Update cashier shift figures
    if ($shiftId) {
        if ($paymentMethod === 'cash') {
            $shiftUpdate = $db->prepare("UPDATE cashier_shifts SET cash_sales = cash_sales + ?, expected_cash = expected_cash + ? WHERE id = ?");
            $shiftUpdate->execute([$grandTotal, $grandTotal, $shiftId]);
        } elseif ($paymentMethod === 'card') {
            $shiftUpdate = $db->prepare("UPDATE cashier_shifts SET card_sales = card_sales + ? WHERE id = ?");
            $shiftUpdate->execute([$grandTotal, $shiftId]);
        } elseif ($paymentMethod === 'online') {
            $shiftUpdate = $db->prepare("UPDATE cashier_shifts SET online_sales = online_sales + ? WHERE id = ?");
            $shiftUpdate->execute([$grandTotal, $shiftId]);
        }
    }

    // 5. Log in Audit Trail
    logAudit('SALE_COMPLETED', "Completed sale {$invoiceNumber} for Rs. " . number_format($grandTotal, 2) . " via {$paymentMethod}.", $userId);

    $db->commit();

    echo json_encode([
        'success' => true,
        'sale' => [
            'id' => $saleId,
            'invoice_number' => $invoiceNumber,
            'grandTotal' => $grandTotal,
            'paidAmount' => $paidAmount,
            'changeAmount' => $changeAmount,
            'paymentMethod' => $paymentMethod,
            'items' => $data['items'],
            'pointsEarned' => $pointsEarned
        ]
    ]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
