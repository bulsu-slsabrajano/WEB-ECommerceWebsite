<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../db/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_profile'])) {

    $user_id     = $_SESSION['user_id'];
    $username    = trim($_POST['username']);
    $first_name  = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name   = trim($_POST['last_name']);
    $email       = trim($_POST['email']);
    $phone       = trim($_POST['phone']);
    $gender      = trim($_POST['gender']);
    $birthday    = !empty($_POST['birthday']) ? $_POST['birthday'] : null;

    try {
        // Check if the new username is already taken by another user
        $checkUser = $conn->prepare("SELECT user_id FROM users WHERE username = :username AND user_id != :user_id LIMIT 1");
        $checkUser->execute([':username' => $username, ':user_id' => $user_id]);
        if ($checkUser->fetch()) {
            // Username already taken — redirect with error
            header("Location: ../../profile.php?status=username_taken");
            exit();
        }

        // Update users table
        $stmt = $conn->prepare("
            UPDATE users 
            SET first_name  = :first_name,
                middle_name = :middle_name,
                last_name   = :last_name,
                username    = :username,
                email       = :email,
                gender      = :gender,
                birthday    = :birthday
            WHERE user_id   = :user_id
        ");

        $stmt->execute([
            ':first_name'  => $first_name,
            ':middle_name' => $middle_name,
            ':last_name'   => $last_name,
            ':username'    => $username,
            ':email'       => $email,
            ':gender'      => $gender,
            ':birthday'    => $birthday,
            ':user_id'     => $user_id
        ]);

        // Update or insert phone number
        if (!empty($phone)) {
            $checkPhone = $conn->prepare("SELECT contact_id FROM contact_numbers WHERE user_id = :user_id LIMIT 1");
            $checkPhone->execute([':user_id' => $user_id]);
            $existing = $checkPhone->fetch();

            if ($existing) {
                $updatePhone = $conn->prepare("UPDATE contact_numbers SET phone_number = :phone WHERE user_id = :user_id");
                $updatePhone->execute([':phone' => $phone, ':user_id' => $user_id]);
            } else {
                $insertPhone = $conn->prepare("INSERT INTO contact_numbers (phone_number, user_id) VALUES (:phone, :user_id)");
                $insertPhone->execute([':phone' => $phone, ':user_id' => $user_id]);
            }
        }

        // Update session with new username
        $_SESSION['username'] = $username;

        header("Location: ../../profile.php?status=success");
        exit();

    } catch (PDOException $e) {
        error_log("Profile update error: " . $e->getMessage());
        header("Location: ../../profile.php?status=error");
        exit();
    }

} else {
    header("Location: ../../profile.php");
    exit();
}
?>