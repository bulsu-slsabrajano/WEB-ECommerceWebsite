<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Vanguard's Delights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <header class="hero-section">
        <div class="collage-container" style="background-image: url('hero-collage.jpg');">
            <div class="overlay"></div>
        </div>
    </header>

    <main class="container my-5">
        <h2 class="explore-title">Explore our Sweets</h2>
        <div class="row g-4 mt-2">
            <div class="col-md-3">
                <a href="ProductInterface.php" class="text-decoration-none">
                    <div class="product-card">
                        <div class="product-image">
                            <img src="mini-cake.png" alt="Mini Cake">
                        </div>
                        <div class="product-details">
                            <h5>Mini Cake</h5>
                            <p>A soft and moist cake topped with smooth whipped cream, finished with rich chocolate drizzle.</p>
                            <div class="price">₱50.00</div>
                        </div>
                    </div>
                </a>
            </div>
            </div> 
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>