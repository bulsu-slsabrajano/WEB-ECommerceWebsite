<?php
session_start();
require_once __DIR__ . '/../connection.php';
if (isset($_POST['login_btn'])) {
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);
    if (empty($user) || empty($pass)) {
        header("Location: ../../login.php?error=emptyfields");
        exit();
    }
  try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :user LIMIT 1");
        $stmt->bindParam(':user', $user);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($userData && $pass === $userData['password']) {
    // Check if account is active — allow NULL (admin) or 'Active' (customer)
            $status = strtolower($userData['user_status'] ?? '');
            if ($userData['user_status'] !== null && $status !== 'active') {
                header("Location: ../../login.php?error=inactive");
                exit();
            }



            // Set session variables
            $_SESSION['user_id']  = $userData['user_id'];
            $_SESSION['username'] = $userData['username'];
            $_SESSION['role']     = $userData['role'];
            $_SESSION['name']     = $userData['first_name'] . ' ' . $userData['last_name'];

            // Redirect based on role (case-insensitive)
            $role = strtolower($userData['role']);



            switch ($role) {
                case 'admin':
                    header("Location: ../../admin/dashboard.php");
                  break;
                case 'customer':
                    header("Location: ../../home.php");
                    break;
                default:
                    session_destroy();
                    header("Location: ../../login.php?error=unauthorized");
                    break;
            }
            exit();
        } else {
            header("Location: ../../login.php?error=invalid");
            exit();
        }
    } catch (PDOException $e) {

        die("Query failed: " . $e->getMessage());
    }

} else {

    header("Location: ../../login.php");
    exit();

}

?>