<?php
// db/action/save_admin.php
require_once 'config.php';
require_once '../connection.php';

session_start();

$action = $_POST['action'] ?? '';

function redirectWith($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    header('Location: ../../admin/admin_site.php');
    exit;
}

function handleImageUpload($file, $existingUrl = '') {
    if (empty($file['name'])) return $existingUrl;

    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowed)) return $existingUrl;
    if ($file['size'] > 2 * 1024 * 1024) return $existingUrl;

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'admin_' . uniqid() . '.' . $ext;
    $dest     = '../../images/admins/' . $filename;

    if (!is_dir('../../images/admins/')) mkdir('../../images/admins/', 0755, true);
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return '../images/admins/' . $filename;
    }
    return $existingUrl;
}

/* ══════════════ ADD ══════════════ */
if ($action === 'add') {
    $first_name  = trim($_POST['first_name']  ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name   = trim($_POST['last_name']   ?? '');
    $email       = trim($_POST['email']       ?? '');
    $username    = trim($_POST['username']    ?? '');
    $password    = $_POST['password']         ?? '';
    $confirm     = $_POST['confirm_password'] ?? '';
    $gender      = $_POST['gender']           ?? '';
    $birthday    = $_POST['birthday']         ?? null;
    $status      = $_POST['user_status']      ?? 'active';

    if (!$first_name || !$last_name || !$username || !$password) {
        redirectWith('error', 'Please fill in all required fields.');
    }
    if ($password !== $confirm) {
        redirectWith('error', 'Passwords do not match.');
    }
    if (strlen($password) < 8) {
        redirectWith('error', 'Password must be at least 8 characters.');
    }

    $image_url = handleImageUpload($_FILES['image'] ?? []);
    $hashed    = password_hash($password, PASSWORD_BCRYPT);

    try {
        $stmt = $conn->prepare("
            INSERT INTO users (first_name, middle_name, last_name, username, email, password, gender, birthday, role, user_status, image_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'admin', ?, ?)
        ");
        $stmt->execute([
            $first_name, $middle_name ?: null, $last_name,
            $username, $email ?: null, $hashed,
            $gender ?: null, $birthday ?: null,
            $status, $image_url ?: null
        ]);
        redirectWith('success', 'Admin user added successfully.');
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            redirectWith('error', 'Username already exists. Please choose a different one.');
        }
        redirectWith('error', 'Something went wrong. Please try again.');
    }
}

/* ══════════════ EDIT ══════════════ */
if ($action === 'edit') {
    $user_id     = (int)($_POST['user_id']     ?? 0);
    $first_name  = trim($_POST['first_name']   ?? '');
    $middle_name = trim($_POST['middle_name']  ?? '');
    $last_name   = trim($_POST['last_name']    ?? '');
    $email       = trim($_POST['email']        ?? '');
    $username    = trim($_POST['username']     ?? '');
    $password    = $_POST['password']          ?? '';
    $confirm     = $_POST['confirm_password']  ?? '';
    $gender      = $_POST['gender']            ?? '';
    $birthday    = $_POST['birthday']          ?? null;
    $status      = $_POST['user_status']       ?? 'active';

    if (!$user_id || !$first_name || !$last_name || !$username) {
        redirectWith('error', 'Please fill in all required fields.');
    }

    // Fetch existing image
    $existing = $conn->prepare("SELECT image_url FROM users WHERE user_id = ? AND role = 'admin'");
    $existing->execute([$user_id]);
    $row = $existing->fetch(PDO::FETCH_ASSOC);
    if (!$row) redirectWith('error', 'Admin not found.');

    $image_url = handleImageUpload($_FILES['image'] ?? [], $row['image_url'] ?? '');

    // Build query — only update password if provided
    if (!empty($password)) {
        if ($password !== $confirm) redirectWith('error', 'Passwords do not match.');
        if (strlen($password) < 8)  redirectWith('error', 'Password must be at least 8 characters.');
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("
            UPDATE users SET first_name=?, middle_name=?, last_name=?, username=?, email=?,
            password=?, gender=?, birthday=?, user_status=?, image_url=?
            WHERE user_id=? AND role='admin'
        ");
        $stmt->execute([
            $first_name, $middle_name ?: null, $last_name,
            $username, $email ?: null, $hashed,
            $gender ?: null, $birthday ?: null, $status,
            $image_url ?: null, $user_id
        ]);
    } else {
        $stmt = $conn->prepare("
            UPDATE users SET first_name=?, middle_name=?, last_name=?, username=?, email=?,
            gender=?, birthday=?, user_status=?, image_url=?
            WHERE user_id=? AND role='admin'
        ");
        $stmt->execute([
            $first_name, $middle_name ?: null, $last_name,
            $username, $email ?: null,
            $gender ?: null, $birthday ?: null, $status,
            $image_url ?: null, $user_id
        ]);
    }

    redirectWith('success', 'Admin user updated successfully.');
}

/* ══════════════ DELETE ══════════════ */
if ($action === 'delete') {
    $user_id         = (int)($_POST['user_id'] ?? 0);
    $current_admin   = $_SESSION["login_data"]["user_id"] ?? 0;

    if (!$user_id) redirectWith('error', 'Invalid admin ID.');
    if ($user_id == $current_admin) redirectWith('error', 'You cannot delete your own account.');

    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'admin'");
        $stmt->execute([$user_id]);
        redirectWith('success', 'Admin user deleted successfully.');
    } catch (PDOException $e) {
        redirectWith('error', 'Could not delete admin. Please try again.');
    }
}

redirectWith('error', 'Invalid action.');