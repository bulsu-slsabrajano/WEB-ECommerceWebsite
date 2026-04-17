<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db/connection.php'; // Adjust path if necessary

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Please login first"]);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $data = json_decode(file_get_contents("php://input"), true);
    
    $product_id = (int)$data['product_id'];
    $quantity = (int)$data['quantity'];

    // 1. Get or Create Cart ID for the user
    $stmt = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $cart = $stmt->fetch();

    if (!$cart) {
        $stmt = $conn->prepare("INSERT INTO cart (user_id) VALUES (?)");
        $stmt->execute([$user_id]);
        $cart_id = $conn->lastInsertId();
    } else {
        $cart_id = $cart['cart_id'];
    }

    // 2. Add or Update Product in cart_items
    $stmt = $conn->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $stmt->execute([$cart_id, $product_id]);
    $existing_item = $stmt->fetch();

    if ($existing_item) {
        $new_qty = $existing_item['quantity'] + $quantity;
        $stmt = $conn->prepare("UPDATE cart_items SET quantity = ?, subtotal = quantity * (SELECT price FROM products WHERE product_id = ?) WHERE cart_item_id = ?");
        $stmt->execute([$new_qty, $product_id, $existing_item['cart_item_id']]);
    } else {
        $stmt = $conn->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, subtotal) VALUES (?, ?, ?, ? * (SELECT price FROM products WHERE product_id = ?))");
        $stmt->execute([$cart_id, $product_id, $quantity, $quantity, $product_id]);
    }

    echo json_encode(["success" => true, "message" => "Product added to cart"]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}