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
    
    <style>
    .hero-section {
  
        background: linear-gradient( rgb(231, 228, 228,0.7),rgba(255, 255, 255, 0.9)), 
                    url('images/collage.png');
        
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;

        padding: 300px 0; 
        min-height: 50vh;
        display: flex;
        align-items: center;
    }
</style>
</head>
<body>

    <header class="hero-section text-center">
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


</body>
</html>