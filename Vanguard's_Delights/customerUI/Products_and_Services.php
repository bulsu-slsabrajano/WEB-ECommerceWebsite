<?php 
session_start(); 
include '../header.php'; 
?>

<style>
    /* Pinapalitan yung default "/" ng Bootstrap ng ">" */
    .breadcrumb-item + .breadcrumb-item::before {
        content: ">" !important;
        color: #888888 !important; /* Grey color para sa separator */
        padding-right: 12px;
        padding-left: 12px;
    }

    /* Home link - Greyish color */
    .breadcrumb-link {
        color: #888888 !important; 
        text-decoration: none;
        transition: 0.3s;
    }

    .breadcrumb-link:hover {
        color: #000000 !important; /* Mag-black pag hinover */
    }

    /* Products and Services (Current Page) - Solid Black */
    .breadcrumb-current {
        color: #000000 !important;
        font-weight: 600;
    }

    /* Override para sa active class ng Bootstrap */
    .breadcrumb-item.active.breadcrumb-current {
        color: #000000 !important;
    }
</style>

<main class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="../homepage.php" class="breadcrumb-link">Home</a>
            </li>
            <li class="breadcrumb-item active breadcrumb-current" aria-current="page">
                Products and Services
            </li>
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

<?php include '../footer.php'; ?>