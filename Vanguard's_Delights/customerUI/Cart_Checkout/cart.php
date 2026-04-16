<?php
// 1. SILENTLY include the connection
// We use include instead of require so the page doesn't crash if the file is missing
include_once '../../db/connection.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vanguard's Delights | Cart</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="bg-white">
    <?php include '../../header.php'; ?>
    <div class="container mt-5">
        <h2 class="cart-title mb-4" style="color: #7A2E2E;; font-weight: 700; font-size: 2rem; letter-spacing: -0.01em;">Your Cart</h2>
        
        <div class="row cart-header align-items-center py-3 px-0 mx-0">
            <div class="col-4 ps-5">Product</div>
            <div class="col-2 text-center">Unit Price</div>
            <div class="col-2 text-center">Quantity</div>
            <div class="col-2 text-center">Total Price</div>
            <div class="col-2 text-center">Actions</div>
        </div>

        <div id="cart-items-wrapper" class="container-fluid px-0">
            <div class="text-center py-5">
                <div class="spinner-border text-danger" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading your treats...</p>
            </div>
        </div>

        <div class="row justify-content-end mt-4">
            <div class="col-md-5 text-end">
                <div class="d-flex justify-content-end align-items-baseline">
                    <span class="h4 fw-bold me-3">Subtotal</span>
                    <span id="cart-subtotal" class="h4 fw-bold">₱0.00</span>
                </div>
                <p class="text-muted small mt-1">Tax included. Shipping calculated at checkout.</p>
                <!-- <a href="checkout.html" class="checkout-button">
                    Checkout
                </a> -->
                
                <button id="checkout-btn" class="btn checkout-button px-5 py-2">Checkout</button>
            </div>
        </div>
    </div>

    <div id="selectionEmptyModal" class="custom-sleek-overlay" style="display:none;">
    <div class="custom-sleek-box">
        <div class="sleek-icon">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <h4 class="sleek-title fw-bold">Selection Empty</h4>
        <p class="sleek-message">Please select at least one product to proceed.</p>
        <button class="sleek-btn" onclick="closeSleekModal()">OK</button>
    </div>
</div>
    <script src="../../js/main.js"></script>
    <?php include '../../footer.php'; ?>
</body>
</html>