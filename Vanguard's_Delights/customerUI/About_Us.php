<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Vanguard's Delights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="company-footer-about-us-container1">

<section class="about-hero-section w-100 position-relative">
    <div id="heroCarousel" class="carousel slide carousel-fade position-absolute w-100 h-100" data-bs-ride="carousel" style="top:0; left:0; z-index:1;">
        <div class="carousel-inner h-100">
            <div class="carousel-item active h-100" style="background: linear-gradient(rgba(126, 42, 42, 0.7), rgba(126, 42, 42, 0.7)), url('../images/headerPic.png') center/cover no-repeat;"></div>
            <div class="carousel-item h-100" style="background: linear-gradient(rgba(126, 42, 42, 0.7), rgba(126, 42, 42, 0.7)), url('../images/P1.png') center/cover no-repeat;"></div>
            <div class="carousel-item h-100" style="background: linear-gradient(rgba(126, 42, 42, 0.7), rgba(126, 42, 42, 0.7)), url('../images/P2.png') center/cover no-repeat;"></div>
        </div>
    </div>

    <div class="container text-white position-relative" style="z-index: 2;">
        <div class="row">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="index.php" class="breadcrumb-link text-white">Home</a></li>
                        <li class="breadcrumb-item active text-white breadcrumb-current" aria-current="page">About Us</li>
                    </ol>
                </nav>

                <div class="hero-text-content">
                    <h2 class="hero-title">Stay Ahead.</h2>
                    <h2 class="hero-title spacing">Stay Sweet.</h2>
                    <p class="hero-subtitle">Your cravings just found their leader.</p>
                </div>
            </div>
        </div>
    </div>
    <button class="thq-button-filled hero-explore-btn" style="z-index: 2;">
        Explore Shop →
    </button>
</section>

<main class="container my-5 py-5">
    <h2 class="section-title text-center mb-5">About Vanguard's Delights</h2>
    
    <div class="row align-items-center">
        <div class="col-md-5">
            <img src="../images/crepe.jpg" 
                 alt="Vanguard's Delights Featured Image" 
                 class="img-fluid rounded shadow custom-img-main">
        </div>
        
        <div class="col-md-7">
            <p class="section-desc">
                Vanguard’s Delights is a dessert shop that specializes in freshly
                baked cakes, pastries, and sweet treats made with quality
                ingredients and passion. Established in 2020, the business aims to
                provide delicious desserts that make every celebration more
                memorable.
            </p>
            <p class="section-desc mt-3">
                Located in Pinaod, San Ildefonso, Philippines, Vanguard’s Delights 
                proudly serves the community with a variety of baked goods perfect 
                for birthdays, gatherings, and special occasions. Our goal is to 
                create desserts that not only taste great but also bring joy and 
                satisfaction to our customers.
            </p>
        </div>
    </div>

  <div class="row mt-5 justify-content-center">
    <div class="col-md-6 mb-4">
        <div class="vision-mission-card shadow-sm text-center">
            <h3 class="card-headline">Our Vision</h3>
            <p class="card-body-text">
                Our vision is to become a trusted and well-known dessert shop in the community, recognized for our delicious cakes, creative pastries, and commitment to quality.
            </p>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="vision-mission-card shadow-sm text-center">
            <h3 class="card-headline">Our Mission</h3>
            <p class="card-body-text">
                Our mission is to provide high-quality baked products that bring happiness to every customer, while maintaining excellent customer service and affordable pricing.
            </p>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-12 text-center">
        <h2 class="section-heading-maroon mb-5">Core Values</h2>
    </div>
</div>

<div class="core-values-container">
    <div class="value-item shadow-sm">
        <h4 class="value-title">1. Quality</h4>
        <p class="value-desc">We ensure that every dessert is made using quality ingredients and proper preparation.</p>
    </div>
    <div class="value-item shadow-sm">
        <h4 class="value-title">2. Customer Satisfaction</h4>
        <p class="value-desc">We aim to provide excellent service and create a positive experience for every customer.</p>
    </div>
    <div class="value-item shadow-sm">
        <h4 class="value-title">3. Creativity</h4>
        <p class="value-desc">We continuously develop new cake designs and dessert ideas for special occasions.</p>
    </div>
    <div class="value-item shadow-sm">
        <h4 class="value-title">4. Integrity</h4>
        <p class="value-desc">We operate our business with honesty and respect for our customers.</p>
    </div>
</div>

</main>

<?php include 'footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


