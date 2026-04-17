<?php
require_once '../db/action/config.php';
require_once '../db/connection.php';

session_start();

$admin_name = $_SESSION["login_data"]["first_name"] ?? "Admin";

if (empty($_GET['id'])) { header('Location: orders.php'); exit; }
$order_id = (int)$_GET['id'];

// Fetch order
$stmt = $conn->prepare("
    SELECT o.*, 
           CONCAT(u.first_name, ' ', u.last_name) AS customer_name,
           u.email, u.username,
           a.street, a.city, a.province, a.postal_code, a.country
    FROM orders o
    LEFT JOIN users    u ON u.user_id    = o.user_id
    LEFT JOIN addresses a ON a.address_id = o.address_id
    WHERE o.order_id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) { header('Location: orders.php'); exit; }

// Fetch order items
$items_stmt = $conn->prepare("
    SELECT oi.*, p.name AS product_name, p.image_url
    FROM order_items oi
    LEFT JOIN products p ON p.product_id = oi.product_id
    WHERE oi.order_id = ?
");
$items_stmt->execute([$order_id]);
$items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch payment
$pay_stmt = $conn->prepare("SELECT * FROM payment WHERE order_id = ? LIMIT 1");
$pay_stmt->execute([$order_id]);
$payment = $pay_stmt->fetch(PDO::FETCH_ASSOC);

$success = $_SESSION['success'] ?? null;
$error   = $_SESSION['error']   ?? null;
unset($_SESSION['success'], $_SESSION['error']);

$statusMap = [
    'pending'    => ['label' => 'Pending',    'class' => 'os-pending'],
    'paid'       => ['label' => 'Paid',       'class' => 'os-paid'],
    'processing' => ['label' => 'Processing', 'class' => 'os-processing'],
    'shipped'    => ['label' => 'Shipped',    'class' => 'os-shipped'],
    'completed'  => ['label' => 'Completed',  'class' => 'os-completed'],
    'cancelled'  => ['label' => 'Cancelled',  'class' => 'os-cancelled'],
];
$rawStatus = strtolower($order['order_status'] ?? 'pending');
$sc = $statusMap[$rawStatus] ?? ['label' => ucfirst($rawStatus), 'class' => 'os-pending'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?= str_pad($order_id,3,'0',STR_PAD_LEFT) ?> | Vanguard's Delights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
    <style>
        .os-pending    { background:#FFF8E1;color:#B8860B; }
        .os-paid,
        .os-completed  { background:#EDFAF3;color:#1E7D4A; }
        .os-cancelled  { background:#FDF0F0;color:#C0392B; }
        .os-processing { background:#EEF2FF;color:#3730A3; }
        .os-shipped    { background:#F3E8FF;color:#6D28D9; }

        .order-status-badge {
            display:inline-flex;align-items:center;gap:5px;
            padding:5px 14px;border-radius:20px;font-size:13px;font-weight:500;
        }
        .order-status-badge .dot { width:7px;height:7px;border-radius:50%;flex-shrink:0; }
        .os-pending .dot    { background:#F59E0B; }
        .os-paid .dot,
        .os-completed .dot  { background:#2ECC71; }
        .os-cancelled .dot  { background:#E74C3C; }
        .os-processing .dot { background:#6366F1; }
        .os-shipped .dot    { background:#8B5CF6; }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }
        @media (max-width: 768px) { .detail-grid { grid-template-columns: 1fr; } }

        .info-card {
            background: white;
            border-radius: 14px;
            box-shadow: 0 2px 14px rgba(0,0,0,0.05);
            padding: 22px 24px;
        }
        .info-card.full { grid-column: 1 / -1; }

        .info-card-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--maroon-light);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--cream-dark);
        }

        .info-row {
            display: flex;
            align-items: flex-start;
            padding: 8px 0;
            border-bottom: 1px solid #F8F4F0;
            gap: 12px;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label {
            font-size: 12px;font-weight:600;color:var(--maroon-light);
            text-transform:uppercase;letter-spacing:0.5px;
            min-width: 120px;flex-shrink:0;padding-top:1px;
        }
        .info-value { font-size:13.5px;color:#2D2D2D;flex:1; }

        /* Items table */
        .items-table { width:100%;border-collapse:collapse;font-size:13.5px; }
        .items-table thead th {
            font-size:10.5px;font-weight:600;color:var(--maroon-light);
            text-transform:uppercase;letter-spacing:0.8px;
            padding:0 0 10px;border-bottom:1.5px solid var(--cream-dark);white-space:nowrap;
        }
        .items-table td { padding:11px 0;border-bottom:1px solid #F8F4F0;vertical-align:middle; }
        .items-table tbody tr:last-child td { border-bottom:none; }

        .product-thumb {
            width:40px;height:40px;border-radius:8px;object-fit:cover;
            border:1px solid var(--cream-dark);margin-right:10px;
        }
        .product-thumb-ph {
            width:40px;height:40px;border-radius:8px;
            background:var(--maroon-xlight);display:inline-flex;
            align-items:center;justify-content:center;
            color:var(--maroon-light);font-size:16px;margin-right:10px;flex-shrink:0;
        }

        .total-row {
            display:flex;justify-content:flex-end;align-items:center;gap:12px;
            margin-top:14px;padding-top:12px;border-top:1.5px solid var(--cream-dark);
        }
        .total-label { font-size:12px;font-weight:600;color:var(--text-gray);text-transform:uppercase;letter-spacing:0.6px; }
        .total-value { font-size:17px;font-weight:600;color:var(--maroon); }

        /* Status update form */
        .status-update-form {
            display:flex;align-items:center;gap:10px;margin-top:4px;
        }
        .form-select-status {
            font-family:'Poppins',sans-serif;font-size:13px;font-weight:500;
            color:var(--text-gray);border:1.5px solid var(--cream-dark);
            border-radius:9px;padding:7px 32px 7px 12px;
            background:white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237E7E7E' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 10px center;
            -webkit-appearance:none;appearance:none;cursor:pointer;outline:none;
        }
        .form-select-status:focus { border-color:var(--maroon-light); }
        .btn-update {
            background:var(--maroon);color:white;border:none;border-radius:9px;
            font-family:'Poppins',sans-serif;font-size:13px;font-weight:500;
            padding:8px 20px;cursor:pointer;transition:background 0.2s;
        }
        .btn-update:hover { background:var(--maroon-hover); }

        .back-link {
            display:inline-flex;align-items:center;gap:8px;
            color:#2D2D2D;font-size:14px;font-weight:500;
            text-decoration:none;margin-bottom:20px;transition:color 0.2s;
        }
        .back-link:hover { color:var(--maroon); }

        .flash { padding:11px 16px;border-radius:9px;font-size:13px;margin-bottom:14px;font-family:'Poppins',sans-serif; }
        .flash-success { background:#EDFAF3;color:#1E7D4A;border:1px solid #B2EFD4; }
        .flash-error   { background:#FDF0F0;color:#C0392B;border:1px solid #FACAC8; }

        .print-btn {
            display:inline-flex;align-items:center;gap:7px;
            background:white;color:var(--maroon);border:1.5px solid var(--maroon);
            border-radius:9px;font-family:'Poppins',sans-serif;font-weight:500;font-size:13px;
            padding:7px 16px;text-decoration:none;cursor:pointer;transition:0.2s;
        }
        .print-btn:hover { background:var(--maroon);color:white; }
    </style>
</head>
<body>
<div class="d-flex" id="wrapper">

    <!-- SIDEBAR -->
    <div id="sidebar-wrapper">
        <div class="sidebar-heading">
            <img src="../images/logo.png" alt="Logo">
            <h6>Vanguard's<br>Delights</h6>
        </div>
        <div class="nav-section">
            <a href="dashboard.php"  class="list-group-item"><i class="fa-solid fa-gauge-high"></i>Dashboard</a>
            <a href="products.php"   class="list-group-item"><i class="fa-solid fa-box"></i>Products</a>
            <a href="categories.php" class="list-group-item"><i class="fa-solid fa-layer-group"></i>Categories</a>
            <a href="orders.php"     class="list-group-item active"><i class="fa-solid fa-receipt"></i>Orders</a>
            <a href="admin_ui.php"   class="list-group-item"><i class="fa-solid fa-users"></i>Customers</a>
            <a href="reports.php"    class="list-group-item"><i class="fa-solid fa-chart-line"></i>Reports</a>
            <a href="admin_site.php"      class="list-group-item"><i class="fa-solid fa-shield-halved"></i>Admin Site Settings</a>
        </div>
        <div class="sidebar-footer">
            <hr>
            <a href="../db/action/logout.php" class="list-group-item logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i>Log Out
            </a>
        </div>
    </div>

    <!-- MAIN -->
    <div id="page-content-wrapper">
        <nav class="top-navbar">
            <h3 class="page-title">Orders</h3>
            <div class="admin-area">
                <div class="admin-info">
                    <p class="name"><?= htmlspecialchars($admin_name) ?></p>
                    <span class="role">Admin</span>
                </div>
                <div class="admin-profile-icon"><i class="fa-solid fa-circle-user"></i></div>
            </div>
        </nav>

        <div class="content-area">

            <?php if ($success): ?>
                <div class="flash flash-success"><i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="flash flash-error"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                <a href="orders.php" class="back-link" style="margin-bottom:0;">
                    <i class="fa-solid fa-arrow-left"></i> Order Details
                </a>
            </div>

            <div class="detail-grid">

                <!-- Order Summary -->
                <div class="info-card">
                    <div class="info-card-title">Order Summary</div>
                    <div class="info-row">
                        <span class="info-label">Order No.</span>
                        <span class="info-value" style="font-weight:600;color:var(--maroon);">#ORD-<?= str_pad($order_id,3,'0',STR_PAD_LEFT) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Order Date</span>
                        <span class="info-value"><?= !empty($order['order_date']) ? date('M d, Y', strtotime($order['order_date'])) : '—' ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Total Amount</span>
                        <span class="info-value" style="font-weight:600;">₱<?= number_format($order['total_amount'],2) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Payment</span>
                        <span class="info-value"><?= htmlspecialchars($payment['payment_method'] ?? '—') ?></span>
                    </div>
                    <div class="info-row" style="border-bottom:none;">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="order-status-badge <?= $sc['class'] ?>">
                                <span class="dot"></span><?= $sc['label'] ?>
                            </span>
                        </span>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="info-card">
                    <div class="info-card-title">Customer Information</div>
                    <div class="info-row">
                        <span class="info-label">Name</span>
                        <span class="info-value" style="font-weight:500;"><?= htmlspecialchars($order['customer_name'] ?? '—') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value text-muted-sm"><?= htmlspecialchars($order['email'] ?? '—') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Address</span>
                        <span class="info-value text-muted-sm">
                            <?php
                            $addrParts = array_filter([
                                $order['street'], $order['city'],
                                $order['province'], $order['postal_code'], $order['country']
                            ]);
                            echo $addrParts ? htmlspecialchars(implode(', ', $addrParts)) : '—';
                            ?>
                        </span>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="info-card full">
                    <div class="info-card-title">Items Ordered</div>
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($items)): ?>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;">
                                        <?php if (!empty($item['image_url'])): ?>
                                            <img src="<?= htmlspecialchars($item['image_url']) ?>" class="product-thumb" alt="">
                                        <?php else: ?>
                                            <div class="product-thumb-ph"><i class="fa-solid fa-image"></i></div>
                                        <?php endif; ?>
                                        <span style="font-weight:500;"><?= htmlspecialchars($item['product_name'] ?? '—') ?></span>
                                    </div>
                                </td>
                                <td class="text-center"><?= $item['quantity'] ?></td>
                                <td class="text-end">₱<?= number_format($item['price'], 2) ?></td>
                                <td class="text-end" style="font-weight:500;">₱<?= number_format($item['subtotal'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted-sm py-3">No items found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <div class="total-row">
                        <span class="total-label">Total</span>
                        <span class="total-value">₱<?= number_format($order['total_amount'], 2) ?></span>
                    </div>
                </div>

                <!-- Update Status -->
                <div class="info-card full">
                    <div class="info-card-title">Update Order Status</div>
                    <form method="POST" action="../db/action/update_order_status.php" class="status-update-form">
                        <input type="hidden" name="order_id" value="<?= $order_id ?>">
                        <select name="order_status" class="form-select-status">
                            <?php foreach ($statusMap as $val => $info): ?>
                                <option value="<?= $val ?>" <?= $rawStatus === $val ? 'selected' : '' ?>><?= $info['label'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn-update">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Update Status
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>