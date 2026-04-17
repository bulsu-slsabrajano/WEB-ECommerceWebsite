<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

$userid   = $_SESSION['user_id'];
$products = [];
$count    = 0;

try {
    require 'db/connection.php';
    // 1. ADDED 'product_id' to the SELECT query so we can link to the details page
    $prodStmt = $conn->query("SELECT product_id, name, description, price, image_url FROM products WHERE is_active = 1");
    $products = $prodStmt->fetchAll(PDO::FETCH_ASSOC);
    $count    = count($products);
} catch (PDOException $e) {
    // silently fail
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Explore our Sweets</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'header.php'; ?>

    <div class="hero-carousel">
        <input type="radio" name="hero" id="s1" checked>
        <input type="radio" name="hero" id="s2">
        <input type="radio" name="hero" id="s3">
        <input type="radio" name="hero" id="s4">
        <input type="radio" name="hero" id="s5">

        <div class="hero-slides">
            <div class="hero-slide"><img src="images/C1.jpg" alt="Slide 1"></div>
            <div class="hero-slide"><img src="images/C2.jpg" alt="Slide 2"></div>
            <div class="hero-slide"><img src="images/C3.jpg" alt="Slide 3"></div>
            <div class="hero-slide"><img src="images/C4.jpg" alt="Slide 4"></div>
            <div class="hero-slide"><img src="images/C5.jpg" alt="Slide 5"></div>
        </div>
        </div>

    <h2>Explore our Sweets</h2>

    <?php if ($count > 0): ?>
    <div class="carousel-container">
        <div class="carousel-track">
            <?php foreach ($products as $row): ?>
                <a href="ProductDetails.php?id=<?php echo $row['product_id']; ?>" style="text-decoration: none; color: inherit;">
                    <div class="product-card">
                        <div class="image-box">
                            <img src="<?php echo htmlspecialchars($row['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($row['name']); ?>"
                                 onerror="this.src='images/placeholder.jpg';"> 
                        </div>
                        <div class="details">
                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            <p><?php echo htmlspecialchars($row['description']); ?></p>
                            <div class="price">₱<?php echo number_format($row['price'], 2); ?></div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
        <p style="text-align:center;">No products found.</p>
    <?php endif; ?>

<?php include 'footer.php'; ?>

<script>
let current = 1;
setInterval(() => {
    current = current >= 5 ? 1 : current + 1;
    let radio = document.getElementById('s' + current);
    if(radio) radio.checked = true;
}, 4000);
</script>
</body>
</html>