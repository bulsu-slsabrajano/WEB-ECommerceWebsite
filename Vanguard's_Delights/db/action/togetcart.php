<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../connection.php';

try {
    $user_id = $_SESSION['user_id']; // when login is ready

    $query = "SELECT ci.cart_item_id, p.name, p.price, ci.quantity, ci.subtotal,
                     REPLACE(p.image_url, '../', '../../') AS image_url
              FROM cart_items ci
              JOIN cart c ON ci.cart_id = c.cart_id
              JOIN products p ON ci.product_id = p.product_id
              WHERE c.user_id = :user_id";

    $stmt = $conn->prepare($query);
    $stmt->execute(['user_id' => $user_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($items ?: []);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}