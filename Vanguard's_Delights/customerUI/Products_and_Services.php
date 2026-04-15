<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products & Services | Vanguard's Delights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="products-services-page">

    <main class="container my-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" class="breadcrumb-link">Home</a></li>
                <li class="breadcrumb-item active breadcrumb-current">Products and Services</li>
            </ol>
        </nav>

        <section class="text-center my-5">
            <h1 class="section-title">Our Products</h1>
            <p class="section-subtitle">Vanguard's Delights offers a variety of freshly baked desserts made to satisfy every sweet craving.</p>

            <div class="row g-4 mt-2">
                <div class="col-md-4">
                    <img src="../images/P1.png" class="custom-img shadow-sm" alt="Dessert 1">
                </div>
                <div class="col-md-4">
                    <img src="../images/P2.png" class="custom-img shadow-sm" alt="Dessert 2">
                </div>
                <div class="col-md-4">
                    <img src="../images/P3.png" class="custom-img shadow-sm" alt="Dessert 3">
                </div>
            </div>

            <p class="section-desc mt-4">
                Our products include cakes (custom and special occasion cakes), cupcakes, cookies, and pastries. 
                Each product is carefully prepared to ensure freshness, quality, and great taste.
            </p>
            <button class="explore-btn mt-2">Explore Products</button>
        </section>

        <section class="my-5 pt-5">
            <h1 class="section-title text-center">Our Services</h1>
            <p class="section-subtitle text-center mb-5">We provide services that make ordering desserts convenient for our customers.</p>
            
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <img src="../images/F1.png" class="custom-banner-img shadow-sm" alt="Services Banner">
                    
                    <div class="service-list-container mt-4">
                        <h4 class="section-title-small text-start">Services include:</h4>
                        <ul class="custom-list">
                            <li>Custom cake orders for birthdays and events</li>
                            <li>Dessert preparation for small celebrations</li>
                            <li>Online ordering through the website</li>
                            <li>Customer support for inquiries and orders</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer-bg text-white py-5 mt-5">
        <div class="container text-center">
            <p>© 2026 Vanguard's Delights. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

