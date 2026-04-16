<?php
session_start();
header('Content-Type: application/json');

// Re-establish connection specifically for this fetch
try {
    $conn = new PDO("mysql:host=localhost;dbname=vanguards_delights_db", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Use the same ID you are testing in checkout.php
    $user_id = 2; 

    // Query to get items specifically for this user's cart
    $query = "SELECT ci.cart_item_id, p.name, p.price, p.image_url, ci.quantity 
              FROM cart_items ci
              JOIN cart c ON ci.cart_id = c.cart_id
              JOIN products p ON ci.product_id = p.product_id
              WHERE c.user_id = :user_id";

    $stmt = $conn->prepare($query);
    $stmt->execute(['user_id' => $user_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If no items, return an empty array [] instead of an error
    echo json_encode($items ?: []);

} catch (PDOException $e) {
    // If it fails, send a JSON error, not an HTML error
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}