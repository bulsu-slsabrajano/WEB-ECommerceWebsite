<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Simulation ng login (Hardcoded muna since wala pang DB)
    if ($username === "admin" && $password === "1234") {
        $_SESSION['user'] = "Admin User";
        echo "success";
    } else {
        echo "error";
    }
}
?>