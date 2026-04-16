<?php 
    session_start();
    $pageTitle = "Terms and Use - Vanguard's Delights";
    $effectiveDate = "March 15, 2026";
    include '../header.php'; 
?>

<style>
    /* --- BREADCRUMBS --- */
    .breadcrumb-nav {
        font-size: 15px;
        margin-bottom: 60px;
        color: #888;
        padding-top: 20px; /* Dagdag space para hindi dikit sa header */
    }

    .breadcrumb-nav a {
        text-decoration: none;
        color: #888;
    }

    .breadcrumb-nav span {
        margin: 0 8px;
    }

    .breadcrumb-nav .active-page {
        color: #222;
        font-weight: 600;
    }

    /* --- TITLE (CENTERED & RED) --- */
    .terms-title {
        text-align: center;
        color: #7e2a2a; /* Maroon/Red */
        font-weight: 800;
        font-size: 42px;
        margin-bottom: 50px;
    }

    /* --- CONTENT BODY (LEFT ALIGNED) --- */
    .content-body {
        max-width: 1100px;
        margin: 0 auto;
        text-align: left;
    }

    .effective-date {
        font-size: 15px;
        margin-bottom: 35px;
        color: #222;
    }

    .terms-intro {
        font-size: 16px;
        margin-bottom: 15px;
        color: #222;
        line-height: 1.6;
    }

    /* --- SUMMARY LIST --- */
    .summary-list {
        list-style-position: inside;
        margin-bottom: 50px;
    }

    .summary-list li {
        font-size: 16px;
        margin-bottom: 8px;
        color: #222;
    }

    /* --- DETAILED SECTIONS --- */
    .term-item {
        margin-bottom: 35px;
    }

    .term-heading {
        font-weight: 700;
        font-size: 20px;
        color: #222;
        margin-bottom: 12px;
    }

    .term-description {
        font-size: 16px;
        line-height: 1.6;
        color: #333;
        padding-left: 28px;
    }
</style>

<div class="container">
    <nav class="breadcrumb-nav">
        <a href="../homepage.php">Home</a> <span>&gt;</span> <span class="active-page">Terms and Use</span>
    </nav>

    <main class="terms-container">
        <h1 class="terms-title">Terms and Use</h1>

        <div class="content-body">
            <p class="effective-date">Effective Date: <?php echo $effectiveDate; ?></p>
            
            <p class="terms-intro">By accessing and using the Vanguard’s Delights website, you agree to follow the terms and conditions outlined below.</p>
            
            <ol class="summary-list">
                <li>Website Use</li>
                <li>Product Availability</li>
                <li>Order Accuracy</li>
                <li>Pricing</li>
                <li>Intellectual Property</li>
            </ol>

            <div class="detailed-sections">
                <?php
                    $sections = [
                        "1. Website Use" => "The website is intended for browsing products, placing orders, and learning more about Vanguard’s Delights. Users are expected to use the website responsibly and respectfully.",
                        "2. Product Availability" => "Product availability may change depending on stock and production capacity. Vanguard’s Delights reserves the right to update product listings when necessary.",
                        "3. Order Accuracy" => "Customers are responsible for ensuring that all order details such as delivery address, contact information, and product selection are correct before confirming their purchase.",
                        "4. Pricing" => "All product prices displayed on the website may change without prior notice.",
                        "5. Intellectual Property" => "All images, logos, and content found on this website belong to Vanguard’s Delights and may not be copied, reproduced, or distributed without permission."
                    ];

                    foreach ($sections as $title => $text) {
                        echo '<section class="term-item">';
                        echo '<h2 class="term-heading">' . htmlspecialchars($title) . '</h2>';
                        echo '<p class="term-description">' . htmlspecialchars($text) . '</p>';
                        echo '</section>';
                    }
                ?>
            </div>
        </div>
    </main>
</div>

<?php include '../footer.php'; ?>