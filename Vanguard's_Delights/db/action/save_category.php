<?php
// db/action/save_category.php
require_once '../../db/action/config.php';
require_once '../../db/connection.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/categories.php');
    exit;
}

$id            = trim($_POST['category_id']   ?? '');
$category_name = trim($_POST['category_name'] ?? '');
$description   = trim($_POST['description']   ?? '');

if (!$category_name) {
    $_SESSION['error'] = 'Category name is required.';
    header('Location: ../../admin/categories.php');
    exit;
}

/**
 * Handle Category Image Upload (Relative Path)
 */
function handleCategoryImage($file, $existingUrl = '') {
    if (empty($file['name'])) return $existingUrl;

    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];
    if (!in_array($file['type'], $allowed)) return $existingUrl;

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'cat_' . uniqid() . '.' . $ext;
    $dest     = '../../images/categories/' . $filename;

    if (!is_dir('../../images/categories/')) mkdir('../../images/categories/', 0755, true);

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        // Burahin ang lumang file kung may bago nang inupload
        if ($existingUrl) {
            $oldPath = '../../' . ltrim($existingUrl, '../');
            if (file_exists($oldPath)) @unlink($oldPath);
        }
        // I-save ang relative path para mabasa ng admin side
        return '../images/categories/' . $filename;
    }
    return $existingUrl;
}

try {
    // Kunin ang existing image URL para sa update
    $existing_image = null;
    if ($id) {
        $stmt = $conn->prepare("SELECT image_url FROM categories WHERE category_id = ?");
        $stmt->execute([$id]);
        $existing_image = $stmt->fetchColumn();
    }

    $image_url = handleCategoryImage($_FILES['image'] ?? [], $existing_image ?: '');

    if ($id) {
        // UPDATE: Isama na ang image_url sa lahat ng update
        $stmt = $conn->prepare("UPDATE categories SET category_name=?, description=?, image_url=? WHERE category_id=?");
        $stmt->execute([$category_name, $description, $image_url, $id]);
        $_SESSION['success'] = 'Category updated successfully.';
    } else {
        // INSERT
        $stmt = $conn->prepare("INSERT INTO categories (category_name, description, image_url) VALUES (?,?,?)");
        $stmt->execute([$category_name, $description, $image_url]);
        $_SESSION['success'] = 'Category added successfully.';
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
}

header('Location: ../../admin/categories.php');
exit;