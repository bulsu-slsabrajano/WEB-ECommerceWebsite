<?php
// db/action/save_product.php
require_once 'config.php';
require_once '../connection.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/products.php');
    exit;
}

$id          = trim($_POST['product_id']     ?? '');
$name        = trim($_POST['name']           ?? '');
$description = trim($_POST['description']    ?? '');
$price       = trim($_POST['price']          ?? '');
$stock       = trim($_POST['stock_quantity'] ?? '');
$sku         = trim($_POST['sku']            ?? '');
$category_id = trim($_POST['category_id']   ?? '') ?: null;
$is_active   = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 0;

// Validation
if (!$name || !$price || $stock === '') {
    $_SESSION['error'] = 'Name, price, and stock are required.';
    header('Location: ../../admin/products.php');
    exit;
}

/**
 * Handle Image Upload logic base sa save_admin.php
 */
function handleProductImage($file, $existingUrl = '') {
    if (empty($file['name'])) return $existingUrl;

    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowed)) return $existingUrl;

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'prod_' . uniqid() . '.' . $ext;
    $dest     = '../../images/products/' . $filename;

    // Siguraduhin na existing ang directory
    if (!is_dir('../../images/products/')) mkdir('../../images/products/', 0755, true);

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        // Burahin ang lumang file sa server kung may bago nang inupload
        if ($existingUrl) {
            $oldPath = '../../' . ltrim($existingUrl, '../');
            if (file_exists($oldPath)) @unlink($oldPath);
        }
        // I-save ang relative path (eto ang nakukuha ang picture sa frontend)
        return '../images/products/' . $filename;
    }
    return $existingUrl;
}

try {
    // 1. Kunin muna ang existing data kung UPDATE
    $existing_image = null;
    if ($id) {
        $stmt = $conn->prepare("SELECT image_url FROM products WHERE product_id = ?");
        $stmt->execute([$id]);
        $existing_image = $stmt->fetchColumn();
    }

    // 2. I-process ang Image
    $image_url = handleProductImage($_FILES['image'] ?? [], $existing_image ?: '');

    // 3. I-check kung pinindot ang 'Remove' (Base sa logic ng admin update)
    if (isset($_POST['remove_current_image']) && $_POST['remove_current_image'] == '1') {
        if ($existing_image) {
            $oldPath = '../../' . ltrim($existing_image, '../');
            if (file_exists($oldPath)) @unlink($oldPath);
        }
        $image_url = null;
    }

    if ($id) {
        // UPDATE
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock_quantity=?, sku=?, category_id=?, is_active=?, image_url=? WHERE product_id=?");
        $stmt->execute([$name, $description, $price, $stock, $sku, $category_id, $is_active, $image_url, $id]);
        $_SESSION['success'] = 'Product updated successfully.';
    } else {
        // INSERT
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock_quantity, sku, category_id, is_active, image_url) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$name, $description, $price, $stock, $sku, $category_id, $is_active, $image_url]);
        $_SESSION['success'] = 'Product added successfully.';
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
}

header('Location: ../../admin/products.php');
exit;