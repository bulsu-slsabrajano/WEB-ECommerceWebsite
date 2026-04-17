<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_url = "/Vanguard's_Delights/";

// --- DB Connection ---
include 'db/connection.php';

// --- Fetch product from DB ---
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header("Location: " . $base_url . "home.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT name, price, stock_quantity, description, image_url 
    FROM products 
    WHERE product_id = ? AND is_active = 1
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: " . $base_url . "home.php");
    exit;
}

include 'header.php';
?>

<main class="pd-main">

    <!-- Breadcrumb -->
    <nav class="pd-breadcrumb">
        <a href="<?= $base_url ?>home.php">Home</a>
        <span class="pd-breadcrumb-sep">›</span>
        <span class="pd-breadcrumb-current"><?= htmlspecialchars($product['name']) ?></span>
    </nav>

    <!-- Product Card -->
    <div class="pd-card">

        <!-- Left: Image -->
        <div class="pd-image-box">
            <img src="<?= htmlspecialchars($product['image_url']) ?>"
                 alt="<?= htmlspecialchars($product['name']) ?>"
                 class="pd-image">
        </div>

        <!-- Right: Details -->
        <div class="pd-details">

            <h1 class="pd-name"><?= htmlspecialchars($product['name']) ?></h1>

           <!-- Stock -->
<div class="pd-stock">
    <?php if ($product['stock_quantity'] > 0): ?>
        <span class="pd-stock-badge">
            Stock: <?= (int)$product['stock_quantity'] ?>
        </span>
    <?php else: ?>
        <span class="pd-stock-badge pd-stock--out">Out of Stock</span>
    <?php endif; ?>
</div>

            <!-- Price -->
            <p class="pd-price">&#8369;<?= number_format($product['price'], 2) ?></p>

            <hr class="pd-divider">

            <!-- Description -->
            <p class="pd-description"><?= htmlspecialchars($product['description']) ?></p>

            <!-- Quantity + Actions (only if in stock) -->
            <?php if ($product['stock_quantity'] > 0): ?>
                <div class="pd-quantity-row">
                    <span class="pd-quantity-label">Quantity</span>
                    <div class="pd-quantity-ctrl">
                        <button class="pd-qty-btn" id="pd-minus">−</button>
                        <span class="pd-qty-value" id="pd-qty">1</span>
                        <button class="pd-qty-btn" id="pd-plus">+</button>
                    </div>
                </div>

                <div class="pd-actions">
                    <button class="pd-btn pd-btn--outline">Add to Cart</button>
                    <button class="pd-btn pd-btn--fill">Buy Now</button>
                </div>
            <?php else: ?>
                <div class="pd-actions">
                    <button class="pd-btn pd-btn--disabled" disabled>Unavailable</button>
                </div>
            <?php endif; ?>

        </div>
    </div>

</main>

<script>
    const maxStock = <?= (int)$product['stock_quantity'] ?>;
    const qtyEl   = document.getElementById('pd-qty');
    const minus   = document.getElementById('pd-minus');
    const plus    = document.getElementById('pd-plus');
    let qty = 1;

    if (minus && plus) {
        minus.addEventListener('click', () => {
            if (qty > 1) { qty--; qtyEl.textContent = qty; }
        });
        plus.addEventListener('click', () => {
            if (qty < maxStock) { qty++; qtyEl.textContent = qty; }
        });
    }
</script>

<?php include 'footer.php'; ?>