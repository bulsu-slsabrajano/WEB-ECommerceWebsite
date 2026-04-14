<?php
session_start();
require_once __DIR__ . '/../connection.php';

if (isset($_POST['reset_btn'])) {
    $username = trim($_POST['username'] ?? '');
    $new_pass = $_POST['new_password'] ?? '';
    $conf_pass = $_POST['confirm_password'] ?? '';

    // 1. Check if passwords match
    if ($new_pass !== $conf_pass) {
        header("Location: ../../ForgotPassword.php?error=mismatch");
        exit();
    }

    try {
        // 2. Check if user exists
        $check = $conn->prepare("SELECT user_id FROM users WHERE username = :username LIMIT 1");
        $check->bindParam(':username', $username);
        $check->execute();

        if ($check->rowCount() == 0) {
            header("Location: ../../ForgotPassword.php?error=not_found");
            exit();
        }

        // 3. Update the password in the database
        $sql = "UPDATE users SET password = :pass WHERE username = :user";
        $stmt = $conn->prepare($sql);
        
        $stmt->execute([
            ':pass' => $new_pass, // Plain text gaya ng iyong toRegister logic
            ':user' => $username
        ]);

        header("Location: ../../ForgotPassword.php?success=changed");
        exit();

    } catch (PDOException $e) {
        die("Reset failed: " . $e->getMessage());
    }

} else {
    header("Location: ../../ForgotPassword.php");
    exit();
}