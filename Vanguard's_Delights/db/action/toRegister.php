<?php
session_start();
require_once __DIR__ . '/../connection.php';

if (isset($_POST['register_btn'])) {
    // Kunin ang data at siguraduhing hindi empty
    $first_name    = trim($_POST['first_name'] ?? '');
    $middle_name   = trim($_POST['middle_name'] ?? '');
    $last_name     = trim($_POST['last_name'] ?? '');
    $username      = trim($_POST['username'] ?? '');
    $password      = trim($_POST['password'] ?? ''); // Plain text trim
    $confirm_pass  = trim($_POST['confirm_password'] ?? '');

    // 1. Validation: Siguraduhing may laman ang required fields
    if (empty($first_name) || empty($last_name) || empty($username) || empty($password)) {
        header("Location: ../../signup.php?error=emptyfields");
        exit();
    }

    // 2. Password Match Check
    if ($password !== $confirm_pass) {
        header("Location: ../../signup.php?error=password_mismatch");
        exit();
    }

    try {
        // 3. Check kung existing na ang username
        $check = $conn->prepare("SELECT user_id FROM users WHERE username = :username LIMIT 1");
        $check->bindParam(':username', $username);
        $check->execute();

        if ($check->rowCount() > 0) {
            header("Location: ../../signup.php?error=user_exists");
            exit();
        }

        // 4. INSERT Query - Plain text password ang gagamitin
        $sql = "INSERT INTO users 
                (first_name, middle_name, last_name, username, password, role, user_status) 
                VALUES (:first_name, :middle_name, :last_name, :username, :password, 'customer', 'Active')";

        $stmt = $conn->prepare($sql);
        
        $stmt->execute([
            ':first_name'  => $first_name,
            ':middle_name' => !empty($middle_name) ? $middle_name : null,
            ':last_name'   => $last_name,
            ':username'    => $username,
            ':password'    => $password, // Plain text
        ]);

        // 5. Success Redirect sa login page
        header("Location: ../../login.php?success=registered");
        exit();

    } catch (PDOException $e) {
        die("Registration failed: " . $e->getMessage());
    }

} else {
    header("Location: ../../signup.php");
    exit();
}
?>