<?php
header('Content-Type: application/json');
require_once '../connection.php';
$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'];
$id = $data['id'];

try {
    if ($action === 'update') {
        $qty = $data['qty'];
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = :qty WHERE cart_item_id = :id");
        $stmt->execute(['qty' => $qty, 'id' => $id]);
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_item_id = :id");
        $stmt->execute(['id' => $id]);
    }
    echo json_encode(["status" => "success"]);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>