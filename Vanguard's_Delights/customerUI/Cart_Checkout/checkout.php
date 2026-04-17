<?php
session_start();
include_once '../../db/connection.php'; 

try {
    $conn = new PDO("mysql:host=localhost;dbname=vanguards_delights_db", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- USER ID INITIALIZATION ---
    // REPLACEMENT FOR REAL USE: $user_id = $_SESSION['user_id']; 
    // This assumes you store the ID in the session when the user logs in.
    $user_id = 2; 

    if (!$user_id) {
        // Redirect to login if no user is found in session
        header("Location: login.php");
        exit();
    }
    // ------------------------------

    $query = "SELECT u.first_name, u.last_name, c.phone_number, a.street, a.city, a.province 
              FROM users u
              LEFT JOIN contact_numbers c ON u.user_id = c.user_id
              LEFT JOIN addresses a ON u.user_id = a.user_id AND a.is_default = 1
              WHERE u.user_id = :id LIMIT 1";
              
    $stmt = $conn->prepare($query);
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ... rest of your logic remains the same
        $db_name_display = ($user['first_name'] ?? 'Guest') . " " . ($user['last_name'] ?? '') . 
                       " (" . ($user['phone_number'] ?? 'No Phone') . ")";
    
    $db_address = !empty($user['street']) 
        ? "{$user['street']}, {$user['city']}, {$user['province']}" 
        : "No default address provided";
 
} catch (PDOException $e) {
    $db_name_display = "Guest User";
    $db_address = "No address provided";
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vanguard's Delights | Checkout</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="bg-white">
    <?php include '../../header.php'; ?>
    <div class="container mt-5">
        <h1 class="cart-title mb-4" style="color: #7A2E2E; font-weight: 700; font-size: 2rem; letter-spacing: -0.01em;">Checkout</h1>
 
        <div class="address-card p-4 mb-4">
            <h5 class="fw-bold mb-3" style="color: #7A2E2E;">
                <i class="fas fa-map-marker-alt me-2"></i>Delivery Address
            </h5>
            <div class="row align-items-center">
                <div class="col-md-9">
                    <span id="display-name" class="fw-bold"><?php echo htmlspecialchars($db_name_display); ?></span>
                    <span id="display-address" class="ms-md-4 text-secondary d-block d-md-inline">
                        <?php echo htmlspecialchars($db_address); ?>
                    </span>
                </div>
                <div class="col-md-3 text-end">
                    <button class="btn btn-address-cancel btn-sm me-2" onclick="resetToDefault()">Default</button>
                    <button class="btn btn-address-apply btn-sm" onclick="openAddressModal()">Change</button>
                </div>
            </div>
        </div>
 
        <div class="cart-header row mx-0 py-3 mb-0 text-white fw-bold text-center align-items-center" style="background-color: #7A2E2E; border-radius: 8px 8px 0 0;">
            <div class="col-6 text-start ps-5">Product Ordered</div>
            <div class="col-2">Unit Price</div>
            <div class="col-2">Quantity</div>
            <div class="col-2">Item Subtotal</div>
        </div>
 
        <div id="checkout-items-wrapper" class="border-start border-end"></div>
 
        <div class="payment-section mt-4">
            <div class="d-flex justify-content-between py-3 border-top border-bottom mb-4">
                <span class="text-secondary fw-bold uppercase-text">Payment Method</span>
                <span class="fw-bold">Cash on Delivery</span>
            </div>
            <div class="d-flex justify-content-between align-items-center px-4">
                <h3 class="fw-bold mb-0">Total Payment</h3>
                <h2 class="fw-bold mb-0" id="checkout-total-val" style="color: #333;">₱0.00</h2>
            </div>
            <div class="text-end mt-4 px-4">
                <button class="place-order-button" id="place-order-btn">Place Order</button>
            </div>
        </div>
    </div>
 
    <!-- Address Modal -->
    <div id="addressModal" class="custom-modal" style="display:none;">
        <div class="modal-content-custom">
            <h2 class="fw-bold mb-1" style="color: #333;">Edit Delivery Address</h2>
            <p class="text-secondary mb-4" style="font-size: 0.9rem;">Note: This only changes the address for this order.</p>
            <label class="fw-bold mb-2 d-block" style="color: #333;">Delivery Address</label>
            <textarea id="edit-address" class="form-control mb-4" rows="3" style="border-radius: 8px; resize: none;"><?php echo htmlspecialchars($db_address); ?></textarea>
            <div class="text-end">
                <button class="btn btn-address-cancel btn-sm me-2" onclick="closeAddressModal()">Cancel</button>
                <button class="btn btn-address-apply btn-sm" onclick="saveTemporaryAddress()">Apply</button>
            </div>
        </div>
    </div>
 
    <!-- Confirm Order Modal -->
    <div id="confirmOrderModal" class="custom-sleek-overlay" style="display:none;">
        <div class="custom-sleek-box">
            <div class="sleek-icon"><i class="fas fa-question-circle"></i></div>
            <h4 class="sleek-title fw-bold">Confirm Order</h4>
            <p class="sleek-message">Are you sure you want to place this order?</p>
            <div class="d-flex gap-2 justify-content-center">
                <button class="sleek-btn sleek-btn-cancel" id="cancel-order-btn">Cancel</button>
                <button class="sleek-btn sleek-btn-primary" id="confirm-order-btn">Yes, Place Order</button>
            </div>
        </div>
    </div>
 
    <!-- Success Modal -->
    <div id="successOrderModal" class="custom-sleek-overlay" style="display:none;">
        <div class="custom-sleek-box">
            <div class="sleek-icon" style="color: #28a745;"><i class="fas fa-check-circle"></i></div>
            <h4 class="sleek-title fw-bold">Order Placed!</h4>
            <p class="sleek-message">Your treats are being prepared. Thank you!</p>
            <div class="d-flex flex-column gap-2">
                <button class="sleek-btn sleek-btn-primary" onclick="location.href='index.php'">OK</button>
                <button class="sleek-btn sleek-btn-dark" onclick="location.href='my_purchases.php'">Go to My Purchases</button>
            </div>
        </div>
    </div>

    <script>
    window.phpData = {
        name: <?php echo json_encode($db_name_display); ?>,
        address: <?php echo json_encode($db_address); ?>
    };
</script>
<script src="../../js/main.js"></script>
 
    <script src="../../js/main.js"></script>
    <?php include '../../footer.php'; ?>
</body>
</html>