<?php
session_start();
require_once '../../db/connection.php';

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../../login.html');
    exit;
}

$username = $_SESSION['username'];

try {
    // 1. Get user info using PDO
    $stmt = $conn->prepare("SELECT User_Id, first_name, last_name FROM USERS WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User session invalid.");
    }

    $userId = $user['User_Id'];

    // 2. Fetch all cancelled orders (pulling price from PRODUCTS table 'p')
    $ordersStmt = $conn->prepare("
        SELECT o.Order_Id, o.order_date, o.total_amount, o.order_status,
               p.name AS product_name, p.image_url, p.price,
               oi.quantity, oi.subtotal
        FROM ORDERS o
        JOIN ORDER_ITEMS oi ON o.Order_Id = oi.order_id
        JOIN PRODUCTS p ON oi.product_id = p.Product_Id
        WHERE o.user_id = ? AND o.order_status = 'Cancelled'
        ORDER BY o.order_date DESC
    ");
    $ordersStmt->execute([$userId]);
    $results = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Group items by order
    $orders = [];
    foreach ($results as $row) {
        $oid = $row['Order_Id'];
        if (!isset($orders[$oid])) {
            $orders[$oid] = [
                'Order_Id'     => $row['Order_Id'],
                'order_date'   => $row['order_date'],
                'total_amount' => $row['total_amount'],
                'items'        => []
            ];
        }
        $orders[$oid]['items'][] = [
            'product_name' => $row['product_name'],
            'image_url'    => $row['image_url'],
            'quantity'     => $row['quantity'],
            'price'        => $row['price'],
            'subtotal'     => $row['subtotal']
        ];
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Purchases – Cancelled | Vanguard's Delights</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .purchases-page { background-color: var(--bg-beige); min-height: 100vh; padding: 40px 0 60px; }
        .account-wrapper { background-color: var(--bg-beige); border: 1px solid #e0e0e0; border-radius: 10px; display: flex; overflow: hidden; min-height: 500px; }
        .account-sidebar { background-color: var(--brand-maroon); width: 230px; flex-shrink: 0; padding: 30px 0 20px; display: flex; flex-direction: column; align-items: center; }
        .profile-avatar { width: 65px; height: 65px; background-color: #d8d8d8; color: #666; font-size: 28px; font-weight: 600; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin-bottom: 10px; }
        .sidebar-username { color: white; font-size: 13px; font-weight: 500; margin-bottom: 25px; text-align: center; padding: 0 15px; word-break: break-all; }
        .account-nav { width: 100%; }
        .account-nav .nav-item { color: white; text-decoration: none; padding: 14px 25px; display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 500; transition: background 0.2s; }
        .account-nav .nav-item:hover, .account-nav .nav-item.active { background-color: rgba(255, 255, 255, 0.15); }
        .account-content { flex: 1; padding: 30px 35px; }
        .page-title { font-family: 'Poppins', sans-serif; font-weight: 800; color: #7A2E2E; font-size: 2rem; margin-bottom: 20px; }
        .purchase-tabs { display: flex; gap: 8px; margin-bottom: 25px; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px; }
        .tab-link { color: #7A2E2E; text-decoration: none; padding: 7px 22px; border-radius: 6px; font-weight: 600; font-size: 14px; transition: background 0.2s; }
        .tab-link:hover, .tab-link.active { background-color: #e3dec9; color: #7A2E2E; }
        .order-card { background: white; border: 1px solid #e0e0e0; border-radius: 10px; margin-bottom: 18px; overflow: hidden; opacity: 0.85; }
        .order-card-header { background-color: #f9f7f3; border-bottom: 1px solid #e8e4da; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #666; }
        .order-card-body { padding: 18px 20px; }
        .order-item-row { display: flex; align-items: center; gap: 18px; padding: 12px 0; border-bottom: 1px solid #f0ebe0; }
        .order-item-row:last-of-type { border-bottom: none; }
        .order-product-img { width: 75px; height: 75px; object-fit: cover; border-radius: 8px; background-color: #f0ebe0; flex-shrink: 0; filter: grayscale(30%); }
        .order-item-info { flex: 1; }
        .order-item-price { font-weight: 700; font-size: 15px; color: #888; text-align: right; min-width: 90px; text-decoration: line-through; }
        .order-card-footer { background-color: #fdfcf9; border-top: 1px solid #e8e4da; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; }
        .badge-cancelled { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; border-radius: 20px; padding: 3px 14px; font-size: 12px; font-weight: 600; }
        .empty-state { text-align: center; padding: 60px 20px; color: #aaa; }
    </style>
</head>
<body>

<?php include '../../header.php'; ?>

<div class="purchases-page">
    <div class="container">
        <div class="account-wrapper">

            <div class="account-sidebar">
                <div class="profile-avatar">
                    <?= htmlspecialchars(strtoupper(substr($user['first_name'] ?? 'U', 0, 1))) ?>
                </div>
                <div class="sidebar-username"><?= htmlspecialchars($username) ?></div>
                   <nav class="account-nav">
                    <a href="../../profile.php" class="nav-item">
                        <i class="fa-regular fa-user"></i> My Account
                    </a>
                    <a href="PendingPurchase.php" class="nav-item active">
                        <i class="fa-solid fa-bag-shopping"></i> My Purchases
                    </a>
                    <a href="../../logout.php" class="nav-item">
                        <i class="fa-solid fa-right-from-bracket"></i> Log Out
                    </a>
                </nav>
            </div>

            <div class="account-content">
                <h2 class="page-title">My Purchases</h2>

                <div class="purchase-tabs">
    <a href="PendingPurchase.php"   class="tab-link">Pending</a>
    <a href="CompletePurchase.php" class="tab-link">Completed</a>
    <a href="CancelledPurchase.php" class="tab-link active">Cancelled</a>
</div>

                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <i class="fa-regular fa-folder-open"></i>
                        <p>No Cancelled Orders for Now.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-card-header">
                                <div>
                                    <strong>Order ID: #<?= htmlspecialchars($order['Order_Id']) ?></strong>
                                    &nbsp;|&nbsp;
                                    <?= date('F j, Y', strtotime($order['order_date'])) ?>
                                </div>
                                <span class="badge-cancelled">Cancelled</span>
                            </div>

                            <div class="order-card-body">
                                <?php foreach ($order['items'] as $item): ?>
                                    <div class="order-item-row">
                                        <img src="<?= htmlspecialchars($item['image_url'] ?? '') ?>" 
                                             alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                             class="order-product-img"
                                             onerror="this.src='../../images/placeholder.png'">
                                        <div class="order-item-info">
                                            <div class="product-name" style="color:#555;"><?= htmlspecialchars($item['product_name']) ?></div>
                                            <div class="item-meta">Qty: <?= htmlspecialchars($item['quantity']) ?> &nbsp;|&nbsp; ₱<?= number_format($item['price'], 2) ?> each</div>
                                        </div>
                                        <div class="order-item-price">₱<?= number_format($item['subtotal'], 2) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="order-card-footer">
                                <div class="order-total" style="color:#888;">
                                    Order Total: <span style="text-decoration: line-through; color:#aaa; font-weight:800; font-size:17px;">₱<?= number_format($order['total_amount'], 2) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php include '../../footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>