<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Vanguard's Delights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php 
include('header.php'); 
?>

    <header class="hero-section text-center py-5">
        <div class="container">
            <h1 class="display-4 fw-bold">Welcome to Vanguard's Delights</h1>
            <p class="lead">Cakes and Pastries at their finest.</p>
            
            <?php if (!isset($_SESSION['role'])): ?>
                <div class="mt-4">
    <a href="login.php" class="btn btn-outline-maroon me-2">Login</a>
    
    <a href="signup.php" class="btn btn-maroon">Sign Up</a>
</div>
            <?php endif; ?>
        </div>
    </header>

    <main class="container my-5">
        </main>

<?php 

include('footer.php'); 
?>
</body>
</html>