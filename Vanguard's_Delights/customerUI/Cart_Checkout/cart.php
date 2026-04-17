<?php
// FIX: This check stops the "Session already started" warning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once '../../db/connection.php'; 

/**
 * --- REAL USE LOGIN CHECK ---
 * Uncomment these lines later when the login system is done.
 */
// if (!isset($_SESSION['user_id'])) {
//     header("Location: ../../login.php");
//     exit();
// }
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
    
    <style>
        /* ========================================= */
        /* CORE CONFIGURATION                        */
        /* ========================================= */
        :root {
            --brand-maroon: #7a2a2a;
            --brand-maroon-dark: #5a1f1f;
            --bg-beige: #f6f4ee; 
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fff;
            color: #333;
        }

        /* ========================================= */
        /* CART PAGE SPECIFIC STYLING                */
        /* ========================================= */
        .cart-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 700 !important;
            color: #7A2E2E;
            font-size: 2.2rem;
            margin-bottom: 35px;
            letter-spacing: -0.5px;
        }

        .cart-header {
            background-color: #8B3232 !important;
            color: white !important;
            font-weight: 700;
            border-radius: 4px;
        }

        .cart-header .col-4 {
            padding-left: 90px !important; 
        }

        .cart-row {
            border-bottom: 1px solid #7A2E2E !important;
            padding: 25px 0;
            display: flex;
            align-items: center;
        }

        /* Product Images */
        .img-container-base {
            width: 80px;
            height: 80px;
            border: 1px solid #ddd;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #fff;
            overflow: hidden;
            margin-left: 25px;
        }

        .product-img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }

        /* Controls & Icons */
        .qty-controls i {
            cursor: pointer;
            font-size: 1.25rem;
            color: #333;
            transition: color 0.2s;
        }

        .qty-controls i:hover {
            color: #8B3232;
        }

        .fa-trash-alt {
            font-size: 1.2rem;
            color: #333;
            cursor: pointer;
        }

        .fa-trash-alt:hover {
            color: #dc3545;
        }

        /* Summary & Checkout */
        #cart-subtotal {
            font-size: 1.5rem;
            font-weight: 800;
            color: #212529;
        }

        .checkout-button {
            background-color: #7A2E2E;
            color: white;
            border: none;
            padding: 8px 30px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-block;
            transition: background 0.2s;
            margin-top: 10px;
            margin-bottom: 100px;
        }

        .checkout-button:hover {
            background: #5a2222;
            color: white;
        }

        /* ========================================= */
        /* MODAL / OVERLAY STYLES                    */
        /* ========================================= */
        .custom-sleek-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        }

        .custom-sleek-box {
            background: white;
            padding: 1.5rem;
            border-radius: 18px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            max-width: 320px;
            width: 85%;
            animation: sleekIn 0.3s ease-out;
        }

        @keyframes sleekIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .sleek-icon {
            font-size: 3rem;
            color: #7A2E2E;
            margin-bottom: 1rem;
        }

        .sleek-btn {
            background: #7A2E2E;
            color: white;
            border: none;
            padding: 8px 45px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }

        .sleek-btn:hover {
            background: #5a2222;
            color: white;
        }
    </style>
</head>
<body class="bg-white">
    <?php include '../../header.php'; ?>
    
    <div class="container mt-5">
        <h2 class="cart-title">Your Cart</h2>
        
        <div class="row cart-header align-items-center py-3 px-0 mx-0">
            <div class="col-4">Product</div>
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
                    <span class="h4 fw-bold me-3" id="cart-subtotal">₱0.00</span>
                </div>
                <p class="text-muted small mt-1">Tax included. Shipping calculated at checkout.</p>
                <button id="checkout-btn" class="checkout-button">Checkout</button>
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
            <button class="sleek-btn" onclick="document.getElementById('selectionEmptyModal').style.display='none'">OK</button>
        </div>
    </div>

    <script src="../../js/main.js"></script>
    <?php include '../../footer.php'; ?>
</body>
</html>