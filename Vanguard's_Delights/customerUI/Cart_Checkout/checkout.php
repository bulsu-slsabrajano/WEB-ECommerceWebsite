<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


include_once '../../db/connection.php'; 


$user_id = $_SESSION['user_id']; 
//$user_id = 3;

if (!$user_id) {
    header("Location: ../../login.php");
    exit();
}

try {
    $query = "SELECT u.first_name, u.last_name, u.user_id, c.phone_number, 
                 a.address_id, a.street, a.city, a.province 
              FROM users u
              LEFT JOIN contact_numbers c ON u.user_id = c.user_id
              LEFT JOIN addresses a ON u.user_id = a.user_id AND a.is_default = 1
              WHERE u.user_id = :id LIMIT 1";
              
    $stmt = $conn->prepare($query);
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $db_name_display = ($user['first_name'] ?? 'Guest') . " " . ($user['last_name'] ?? '') . 
                       " (" . ($user['phone_number'] ?? 'No Phone') . ")";
    
    $db_address = !empty($user['street']) 
        ? "{$user['street']}, {$user['city']}, {$user['province']}" 
        : "No default address provided";

} catch (PDOException $e) {
    $db_name_display = "Guest User";
    $db_address = "No address provided";
    $user = [];
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
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #fff; color: #333;}

        /* Titles */
        .checkout-main-title {
            color: #7A2E2E;
            font-weight: 700;
            font-size: 2.2rem;
            letter-spacing: -0.5px;
            margin-top: 50px;
        }

  
        #display-name {
        color: #333;
        }

        .address-card {
            background-color: #FAF6F2; 
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 25px !important;
        }

        .address-label {
            color: #7A2E2E;
            font-weight: 700;
            font-size: 1.1rem;
        }

      
        .btn-address-default {
            background-color: #fff;
            border: 1px solid #7A2E2E;
            color: #7A2E2E !important;
            font-weight: 600;
            border-radius: 6px;
            padding: 4px 18px;
            font-size: 14px;
            margin-right: 10px;
        }

        .btn-address-default:hover {
            background-color: #e5e4e3;
            border: 1px solid #e5e4e3;
            color: #333 !important;
        }

        .btn-address-change {
            background-color: #7A2E2E;
            color: white !important;
            border: 1px solid #7A2E2E;
            font-weight: 600;
            border-radius: 6px;
            padding: 4px 18px;
            font-size: 14px;
        }

        .btn-address-change:hover {
            background-color: #5a1f1f;
            color: white !important;
        }

        /* Table Header */
        .checkout-header {
            background-color: #8B3232;
            color: white;
            font-weight: 700;
            border-radius: 8px 8px 0 0;
            margin-bottom: 0;
        }

        /* FIX: Align product items with Cart page */
        #checkout-items-wrapper {
            border-left: 1px solid #eee;
            border-right: 1px solid #eee;
        }

        .checkout-row {
            border-bottom: 1px solid #eee;
            padding: 20px 0;
        }

        /* NEW: Added container base to match cart styling and image border */
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
        }

        .product-img-checkout {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }

        /* Summary Section */
        .payment-method-row {
            border-top: 1.5px solid #7A2E2E;
            border-bottom: 1px solid #eee;
            padding: 15px 0;
            color: #5e5e5e;
            font-weight: 700;
        }

        .total-payment-row {
            padding: 25px 0;
        }

        .total-payment-label {
            font-size: 1.7rem;
            font-weight: 700;
            color: #333;
        }

        #checkout-total-val {
            font: 1.8rem sans-serif;
            font-size: 2.0rem;
            font-weight: 600 !important;
            color: #333;
        }

        /* Place Order Button */
        .place-order-button {
            background-color: #7A2E2E;
            color: white;
            border: none;
            padding: 8px 30px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: background 0.2s;
            margin-bottom: 50px;
        }

        .place-order-button:hover {
            background: #5a2222;
        }

        /* ========================================= */
        /* MODALS / OVERLAYS (Restored & Restyled)   */
        /* ========================================= */


/* This forces them to be hidden regardless of other settings */
/* 1. The Background Overlay */
.custom-sleek-overlay {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(4px);
    z-index: 9999 !important; /* One level below the box */
    display: none !important; /* Keep this to prevent flickering on load */
    justify-content: center;
    align-items: center;
}

/* 2. The White Modal Box */
.custom-sleek-box {
    position: relative; /* Allows z-index to work */
    z-index: 10001 !important;       /* Higher than the overlay */
    background: white;
    border-radius: 18px;
    padding: 2rem;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    pointer-events: auto; /* Ensures clicks are captured */
}

        @keyframes sleekIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .sleek-icon-lg { font-size: 4rem; color: #7A2E2E; margin-bottom: 1.5rem; }
        .sleek-icon-success { font-size: 4rem; color: #28a745; margin-bottom: 1.5rem; }
        .sleek-title { font-weight: 700; color: #333; margin-bottom: 5px; letter-spacing: -1.3px; font-size: 1.8rem; }
        .sleek-text { color: #666; margin-bottom: 20px; font-size: 1rem; letter-spacing: -0.3px; line-height: 1.4; }

        .sleek-btn {
            padding: 5px 25px; border-radius: 8px; font-weight: 600; border: none; font-size: 1rem;
        }
        
        .sleek-btn-primary { background: #7A2E2E; color: white; transition: background 0.2s; padding: 8px 20px;}
        .sleek-btn-primary:hover { background: #5a1f1f; color: white; }
        .sleek-btn-cancel { background: #f8f9fa; color: #333; border: 1px solid #ddd; margin-right: 15px;}
        .sleek-btn-cancel:hover { background: #e2e6ea; }

        /* Address Modal Specifics */
        .address-input-area {
            width: 100%;
            height: 100px;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 10px;
            font-family: inherit;
            resize: none;
            margin-bottom: 20px;
        }

        .confirm-box-small {
            max-width: 400px !important;
            padding: 25px 30px !important;
            text-align: center;
        }

        .sleek-icon-container-small {
            font-size: 3.5rem;
            color: #7A2E2E;
            margin-bottom: 10px;
        }

        .sleek-title-center {
            font-size: 1.4rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .sleek-text-single {
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 15px;
        }

        /* FORCE HIDE ALL MODALS ON LOAD */
div#addressModal, 
div#confirmOrderModal, 
div#successOrderModal {
    display: none;
}

/* Container Box - Reduced width and padding */
.design-match {
    max-width: 480px !important; /* Reduced from 550px */
    padding: 30px 35px !important; /* Thinner padding */
    text-align: left !important;
    border-radius: 20px !important;
}

/* Titles - Slightly smaller font */
.modal-main-title {
    font-weight: 800;
    font-size: 1.8rem; /* Reduced from 2.2rem */
    color: #333;
    letter-spacing: -1px;
    margin-bottom: 2px;
}

.modal-subtitle {
    color: #888;
    font-size: 0.85rem; /* Slightly smaller subtext */
    margin-bottom: 20px;
}

.input-label {
    display: block;
    font-weight: 700;
    color: #333;
    margin-bottom: 8px;
    font-size: 1rem;
}

/* Textarea - Shorter height */
.design-textarea {
    width: 100%;
    height: 90px; /* Reduced from 120px */
    border: 1px solid #ced4da;
    border-radius: 10px;
    padding: 12px;
    font-size: 1rem;
    color: #333;
    resize: none;
}

/* Buttons - More compact padding */
.modal-footer-btns {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 20px;
}

.btn-design {
    padding: 8px 25px; /* Smaller button footprint */
    border-radius: 8px;
    font-weight: 700;
    font-size: 1rem;
    transition: 0.2s;
}



.btn-cancel {
    background: transparent;
    border: 1px solid #7A2E2E;
    color: #7A2E2E;
}

.btn-apply {
    background: #7A2E2E;
    border: 1px solid #7A2E2E;
    color: white;
}
    </style>
</head>
<body class="bg-white">
    <?php include '../../header.php'; ?>

    <div class="container mb-5">
        <h1 class="checkout-main-title mb-4">Checkout</h1>
 
        <div class="address-card p-4 mb-4">
            <div class="address-label mb-2">
                <i class="fas fa-map-marker-alt me-2"></i>Delivery Address
            </div>
            <div class="row align-items-center">
                <div class="col-md-9">
                    <span id="display-name" class="fw-bold"><?php echo htmlspecialchars($db_name_display); ?></span>
                    <span id="display-address" class="ms-md-4 text-secondary d-block d-md-inline">
                        <?php echo htmlspecialchars($db_address); ?>
                    </span>
                </div>
                <div class="col-md-3 text-end mt-2 mt-md-0">
                    <button class="btn-address-default" onclick="resetToDefault()">Default</button>
                    <button class="btn-address-change" onclick="openAddressModal()">Change</button>
                </div>
            </div>
        </div>
 
        <div class="checkout-header row mx-0 py-3 text-center align-items-center">
            <div class="col-6 text-start ps-5">Product Ordered</div>
            <div class="col-2">Unit Price</div>
            <div class="col-2">Quantity</div>
            <div class="col-2">Item Subtotal</div>
        </div>
 
        <div id="checkout-items-wrapper">
             <div class="text-center py-5">
                <div class="spinner-border text-danger" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading your items...</p>
            </div>
        </div>
 
        <div class="payment-section mt-4">
            <div class="payment-method-row d-flex justify-content-between px-2">
                <span>Payment Method</span>
                <span class="text-dark">Cash on Delivery</span>
            </div>
            
            <div class="total-payment-row d-flex justify-content-between align-items-center">
                <span class="total-payment-label">Total Payment</span>
                <span class="total-payment-label" id="checkout-total-val">₱0.00</span>
            </div>

            <div class="text-end">
                <button class="place-order-button" id="place-order-btn">Place Order</button>
            </div>
        </div>
    </div>

    <div id="addressModal" class="custom-sleek-overlay">
    <div class="custom-sleek-box design-match">
        <h2 class="modal-main-title">Edit Delivery Address</h2>
        <p class="modal-subtitle">Note: This only changes the address for this order.</p>
        
        <div class="input-section">
            <label class="input-label">Delivery Address</label>
            <textarea id="edit-address" class="design-textarea" placeholder="Enter new delivery address..."></textarea>
        </div>
        
        <div class="modal-footer-btns">
            <button class="btn-address-default" onclick="closeAddressModal()">Cancel</button>
            <button class="btn-address-change" onclick="saveTemporaryAddress()">Apply</button>
        </div>
    </div>
</div>

    <div id="confirmOrderModal" class="custom-sleek-overlay">
        <div class="custom-sleek-box confirm-box-small">
            <div class="sleek-icon-container-small">
                <i class="fas fa-question-circle"></i>
            </div>
            <h4 class="sleek-title-center">Confirm Order</h4>
            <p class="sleek-text-single">Are you sure you want to place this order?</p>
            <div class="d-flex justify-content-center mt-3">
                <button class="btn-address-default" id="cancel-order-btn">Cancel</button>
                <button class="btn-address-change" id="confirm-order-btn">Yes, Place Order</button>
            </div>
        </div>
    </div>

    <div id="successOrderModal" class="custom-sleek-overlay">
        <div class="custom-sleek-box">
            <div class="sleek-icon-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 class="sleek-title">Order Placed!</h3>
            <p class="sleek-text">Your treats are being prepared. Thank you!</p>
            <div class="mt-4">
                <button class="sleek-btn sleek-btn-primary" onclick="window.location.href='../MyPurchase/PendingPurchase.php'">Go to My Purchases</button>
            </div>
        </div>
    </div>

    <script>
        window.phpData = {
            name: <?php echo json_encode($db_name_display); ?>,
            address: <?php echo json_encode($db_address); ?>,
            address_id: <?php echo json_encode($user['address_id'] ?? null); ?>
        };
    </script>
    <script src="../../js/main.js"></script>
    <?php include '../../footer.php'; ?>
</body>
</html>