<?php
session_start();
include_once '../db/connection.php';
 
header('Content-Type: application/json');
 
try {
    $pdo = new PDO("mysql:host=localhost;dbname=vanguards_delights_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 
    $user_id = 2;
 
    // ✅ Read JSON body (sent from main.js as JSON)
    $input = json_decode(file_get_contents('php://input'), true);
    $total_amount = $input['total_amount'] ?? 0;
    $item_ids = $input['item_ids'] ?? []; // only the checked item IDs
 
    if (empty($item_ids)) {
        echo json_encode(['status' => 'error', 'message' => 'No items provided.']);
        exit;
    }
 
    $pdo->beginTransaction();
 
    // 1. Insert into orders table
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_status, total_amount) VALUES (?, 'pending', ?)");
    $stmt->execute([$user_id, $total_amount]);
    $order_id = $pdo->lastInsertId();
 
    // 2. Move only the checked items into order_items
    //    Build a safe placeholder list: ?,?,? for each ID
    $placeholders = implode(',', array_fill(0, count($item_ids), '?'));
 
    $moveItems = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, subtotal)
        SELECT ?, ci.product_id, ci.quantity, ci.subtotal
        FROM cart_items ci
        JOIN cart c ON ci.cart_id = c.cart_id
        WHERE c.user_id = ?
        AND ci.cart_item_id IN ($placeholders)
    ");
    // Merge params: order_id, user_id, then all item IDs
    $moveItems->execute(array_merge([$order_id, $user_id], $item_ids));
 
    // 3. Delete only the checked items from cart_items
    $deleteItems = $pdo->prepare("
        DELETE FROM cart_items
        WHERE cart_item_id IN ($placeholders)
    ");
    $deleteItems->execute($item_ids);
 
    $pdo->commit();
    echo json_encode(['status' => 'success']);
 
} catch (Exception $e) {
    if (isset($pdo)) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>