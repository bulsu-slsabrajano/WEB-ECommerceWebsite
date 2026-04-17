<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../connection.php';

try {
    $user_id = $_SESSION['user_id']; // Replace with $_SESSION['user_id'] when login is ready

    $input        = json_decode(file_get_contents('php://input'), true);
    $total_amount = $input['total_amount'] ?? 0;
    $item_ids     = $input['item_ids']     ?? [];
    
    // ✅ 1. CAPTURE the address_id sent from JavaScript
    $address_id   = $input['address_id']   ?? null; 

    if (empty($item_ids)) {
        echo json_encode(['status' => 'error', 'message' => 'No items provided.']);
        exit;
    }

    $conn->beginTransaction();

    // ✅ 2. UPDATE the INSERT query to include the address_id column
    $stmt = $conn->prepare("INSERT INTO orders (user_id, address_id, order_status, total_amount) VALUES (?, ?, 'pending', ?)");
    
    // ✅ 3. ADD the variable to the execute array in the correct order
    $stmt->execute([$user_id, $address_id, $total_amount]);
    
    $order_id = $conn->lastInsertId();

    // 2. Move checked items into order_items
    $placeholders = implode(',', array_fill(0, count($item_ids), '?'));
    $moveItems = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, subtotal)
        SELECT ?, ci.product_id, ci.quantity, ci.subtotal
        FROM cart_items ci
        JOIN cart c ON ci.cart_id = c.cart_id
        WHERE c.user_id = ?
        AND ci.cart_item_id IN ($placeholders)
    ");
    $moveItems->execute(array_merge([$order_id, $user_id], $item_ids));

    // 3. Delete the checked items from cart_items
    $deleteItems = $conn->prepare("
        DELETE ci FROM cart_items ci
        JOIN cart c ON ci.cart_id = c.cart_id
        WHERE c.user_id = ?
        AND ci.cart_item_id IN ($placeholders)
    ");
    $deleteItems->execute(array_merge([$user_id], $item_ids));

    $conn->commit();
    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    if (isset($conn)) $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}