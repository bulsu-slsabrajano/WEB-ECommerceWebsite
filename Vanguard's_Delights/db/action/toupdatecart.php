<?php
header('Content-Type: application/json');


require_once __DIR__ . '/../connection.php';


$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$id     = $data['id']     ?? null;

try {
    if ($action === 'update') {
        $qty = $data['qty'];
        
        $priceStmt = $conn->prepare("
            SELECT p.price FROM cart_items ci
            JOIN products p ON ci.product_id = p.product_id
            WHERE ci.cart_item_id = :id
        ");
        $priceStmt->execute(['id' => $id]);
        $row = $priceStmt->fetch();
        $newSubtotal = $row ? ($row['price'] * $qty) : 0;

        $stmt = $conn->prepare("
            UPDATE cart_items SET quantity = :qty, subtotal = :subtotal
            WHERE cart_item_id = :id
        ");
        $stmt->execute(['qty' => $qty, 'subtotal' => $newSubtotal, 'id' => $id]);

    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_item_id = :id");
        $stmt->execute(['id' => $id]);
    }

    echo json_encode(["status" => "success"]);

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}