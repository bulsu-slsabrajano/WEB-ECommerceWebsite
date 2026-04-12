<?php
session_start();
require_once '../../db/connection.php';

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../../login.html');
    exit;
}

$username = $_SESSION['username'];

// Get user info
$stmt = $conn->prepare("SELECT User_Id, first_name, last_name FROM USERS WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$userId = $user['User_Id'];

// Handle: Mark as Order Received → Completed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'order_received') {
    $orderId = intval($_POST['order_id']);
    $upd = $conn->prepare("UPDATE ORDERS SET order_status = 'Completed' WHERE Order_Id = ? AND user_id = ?");
    $upd->bind_param("ii", $orderId, $userId);
    $upd->execute();
    header('Location: PendingPurchase.php');
    exit;
}

// Handle: Cancel Order → Cancelled
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_order') {
    $orderId = intval($_POST['order_id']);
    $upd = $conn->prepare("UPDATE ORDERS SET order_status = 'Cancelled' WHERE Order_Id = ? AND user_id = ?");
    $upd->bind_param("ii", $orderId, $userId);
    $upd->execute();
    header('Location: PendingPurchase.php');
    exit;
}

// Fetch all pending orders with their items
$ordersStmt = $conn->prepare("
    SELECT o.Order_Id, o.order_date, o.total_amount, o.order_status,
           p.name AS product_name, p.image_url,
           oi.quantity, oi.price, oi.subtotal
    FROM ORDERS o
    JOIN ORDER_ITEMS oi ON o.Order_Id = oi.order_id
    JOIN PRODUCTS p ON oi.product_id = p.Product_Id
    WHERE o.user_id = ? AND o.order_status = 'Pending'
    ORDER BY o.order_date DESC
");
$ordersStmt->bind_param("i", $userId);
$ordersStmt->execute();
$result = $ordersStmt->get_result();

// Group items by order
$orders = [];
while ($row = $result->fetch_assoc()) {
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Purchases – Pending | Vanguard's Delights</title>
    <link rel="stylesheet" href="../../css/style.css">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ── Page wrapper ── */
        .purchases-page {
            background-color: var(--bg-beige);
            min-height: 100vh;
            padding: 40px 0 60px;
        }

        /* ── Account panel ── */
        .account-wrapper {
            background-color: var(--bg-beige);
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            display: flex;
            overflow: hidden;
            min-height: 500px;
        }

        /* ── Sidebar ── */
        .account-sidebar {
            background-color: var(--brand-maroon);
            width: 230px;
            flex-shrink: 0;
            padding: 30px 0 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .profile-avatar {
            width: 65px;
            height: 65px;
            background-color: #d8d8d8;
            color: #666;
            font-size: 28px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .sidebar-username {
            color: white;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 25px;
            text-align: center;
            padding: 0 15px;
            word-break: break-all;
        }

        .account-nav {
            width: 100%;
        }

        .account-nav .nav-item {
            color: white;
            text-decoration: none;
            padding: 14px 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s;
        }

        .account-nav .nav-item:hover,
        .account-nav .nav-item.active {
            background-color: rgba(255, 255, 255, 0.15);
        }

        .account-nav .nav-item i {
            width: 18px;
            text-align: center;
        }

        /* ── Main content ── */
        .account-content {
            flex: 1;
            padding: 30px 35px;
        }

        .page-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            color: #7A2E2E;
            font-size: 2rem;
            margin-bottom: 20px;
            letter-spacing: -0.5px;
        }

        /* ── Tabs ── */
        .purchase-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 25px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }

        .tab-link {
            color: #7A2E2E;
            text-decoration: none;
            padding: 7px 22px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.2s;
        }

        .tab-link:hover {
            background-color: #e3dec9;
            color: #7A2E2E;
        }

        .tab-link.active {
            background-color: #e3dec9;
            color: #7A2E2E;
        }

        /* ── Order card ── */
        .order-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            margin-bottom: 18px;
            overflow: hidden;
        }

        .order-card-header {
            background-color: #f9f7f3;
            border-bottom: 1px solid #e8e4da;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: #666;
        }

        .order-card-header strong {
            color: #333;
            font-weight: 700;
        }

        .order-card-body {
            padding: 18px 20px;
        }

        .order-item-row {
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 12px 0;
            border-bottom: 1px solid #f0ebe0;
        }

        .order-item-row:last-of-type {
            border-bottom: none;
        }

        .order-product-img {
            width: 75px;
            height: 75px;
            object-fit: cover;
            border-radius: 8px;
            background-color: #f0ebe0;
            flex-shrink: 0;
        }

        .order-item-info {
            flex: 1;
        }

        .order-item-info .product-name {
            font-weight: 600;
            font-size: 15px;
            color: #222;
            margin-bottom: 3px;
        }

        .order-item-info .item-meta {
            font-size: 12px;
            color: #888;
        }

        .order-item-price {
            font-weight: 700;
            font-size: 15px;
            color: #222;
            text-align: right;
            min-width: 90px;
        }

        /* ── Order card footer (totals + actions) ── */
        .order-card-footer {
            background-color: #fdfcf9;
            border-top: 1px solid #e8e4da;
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .order-total {
            font-size: 15px;
            color: #333;
        }

        .order-total span {
            font-weight: 800;
            font-size: 17px;
            color: #7A2E2E;
        }

        .order-actions {
            display: flex;
            gap: 10px;
        }

        /* ── Status badge ── */
        .badge-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffc107;
            border-radius: 20px;
            padding: 3px 14px;
            font-size: 12px;
            font-weight: 600;
        }

        /* ── Empty state ── */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #aaa;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
        }

        .empty-state p {
            font-size: 15px;
            font-weight: 500;
        }

        /* ── Modal tweaks ── */
        .modal-header {
            background-color: var(--brand-maroon);
            color: white;
        }

        .modal-title {
            font-weight: 700;
        }

        .modal-header .btn-close {
            filter: invert(1);
        }
    </style>
</head>
<body>

<?php include '../../header.php'; ?>

<div class="purchases-page">
    <div class="container">
        <div class="account-wrapper">

            <!-- ── Sidebar ── -->
            <div class="account-sidebar">
                <div class="profile-avatar">
                    <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                </div>
                <div class="sidebar-username"><?= htmlspecialchars($username) ?></div>
                <nav class="account-nav">
                    <a href="../../MyAccount.php" class="nav-item">
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

            <!-- ── Main Content ── -->
            <div class="account-content">
                <h2 class="page-title">My Purchases</h2>

                <!-- Tabs -->
                <div class="purchase-tabs">
                    <a href="PendingPurchase.php"   class="tab-link active">Pending</a>
                    <a href="CompletedPurchase.php" class="tab-link">Completed</a>
                    <a href="CancelledPurchase.php" class="tab-link">Cancelled</a>
                </div>

                <!-- Orders -->
                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <i class="fa-regular fa-folder-open"></i>
                        <p>No Pending Orders for Now.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <!-- Card Header -->
                            <div class="order-card-header">
                                <div>
                                    <strong>Order ID: #<?= htmlspecialchars($order['Order_Id']) ?></strong>
                                    &nbsp;|&nbsp;
                                    <?= date('F j, Y', strtotime($order['order_date'])) ?>
                                </div>
                                <span class="badge-pending">Pending</span>
                            </div>

                            <!-- Card Body: Items -->
                            <div class="order-card-body">
                                <?php foreach ($order['items'] as $item): ?>
                                    <div class="order-item-row">
                                        <img
                                            src="<?= htmlspecialchars($item['image_url'] ?? '') ?>"
                                            alt="<?= htmlspecialchars($item['product_name']) ?>"
                                            class="order-product-img"
                                            onerror="this.src='../../images/placeholder.png'">
                                        <div class="order-item-info">
                                            <div class="product-name"><?= htmlspecialchars($item['product_name']) ?></div>
                                            <div class="item-meta">Qty: <?= htmlspecialchars($item['quantity']) ?> &nbsp;|&nbsp; ₱<?= number_format($item['price'], 2) ?> each</div>
                                        </div>
                                        <div class="order-item-price">₱<?= number_format($item['subtotal'], 2) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Card Footer: Total + Actions -->
                            <div class="order-card-footer">
                                <div class="order-total">
                                    Order Total: <span>₱<?= number_format($order['total_amount'], 2) ?></span>
                                </div>
                                <div class="order-actions">
                                    <!-- Order Received Button -->
                                    <button class="btn btn-maroon btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modal-received-<?= $order['Order_Id'] ?>">
                                        Order Received
                                    </button>
                                    <!-- Cancel Order Button -->
                                    <button class="btn btn-outline-maroon btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modal-cancel-<?= $order['Order_Id'] ?>">
                                        Cancel Order
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- ── Modal: Order Received ── -->
                        <div class="modal fade" id="modal-received-<?= $order['Order_Id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Confirm Order Received</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center py-4">
                                        <p class="mb-0 fw-500">Have you received this order?</p>
                                    </div>
                                    <div class="modal-footer justify-content-center gap-3">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                        <form method="POST" action="PendingPurchase.php">
                                            <input type="hidden" name="action"   value="order_received">
                                            <input type="hidden" name="order_id" value="<?= $order['Order_Id'] ?>">
                                            <button type="submit" class="btn btn-maroon">Yes</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ── Modal: Cancel Order ── -->
                        <div class="modal fade" id="modal-cancel-<?= $order['Order_Id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Cancel Order</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center py-4">
                                        <p class="mb-0 fw-500">Are you sure you want to cancel your order?</p>
                                    </div>
                                    <div class="modal-footer justify-content-center gap-3">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                        <form method="POST" action="PendingPurchase.php">
                                            <input type="hidden" name="action"   value="cancel_order">
                                            <input type="hidden" name="order_id" value="<?= $order['Order_Id'] ?>">
                                            <button type="submit" class="btn btn-maroon">Yes</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php endif; ?>

            </div><!-- /.account-content -->
        </div><!-- /.account-wrapper -->
    </div><!-- /.container -->
</div><!-- /.purchases-page -->

<?php include '../../footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>