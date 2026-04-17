<?php
require_once '../../db/action/config.php';
require_once '../../db/connection.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['product_id'])) {
    header('Location: ../../admin/products.php');
    exit;
}

$id = (int)$_POST['product_id'];

try {
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = 'Product deleted successfully.';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Cannot delete: ' . $e->getMessage();
}

header('Location: ../../admin/products.php');
exit;