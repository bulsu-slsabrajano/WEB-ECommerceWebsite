<?php
require_once '../../db/action/config.php';
require_once '../../db/connection.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['category_id'])) {
    header('Location: ../../admin/categories.php');
    exit;
}

$id = (int)$_POST['category_id'];

try {
    // Unlink products first (set category_id to NULL)
    $conn->prepare("UPDATE products SET category_id = NULL WHERE category_id = ?")->execute([$id]);

    $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = 'Category deleted successfully.';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Cannot delete: ' . $e->getMessage();
}

header('Location: ../../admin/categories.php');
exit;