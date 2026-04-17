<?php
require_once '../db/action/config.php';
require_once '../db/connection.php';

session_start();

$admin_name = $_SESSION["login_data"]["first_name"] ?? "Admin";

$success = $_SESSION['success'] ?? null;
$error   = $_SESSION['error']   ?? null;
unset($_SESSION['success'], $_SESSION['error']);

$stmt = $conn->query("
    SELECT 
        o.order_id,
        o.order_date,
        o.order_status,
        o.total_amount,
        CONCAT(u.first_name, ' ', u.last_name) AS customer_name,
        u.user_id,
        p.payment_status,
        p.payment_method,
        COUNT(oi.order_item_id) AS item_count
    FROM orders o
    LEFT JOIN users u         ON u.user_id      = o.user_id
    LEFT JOIN payment p       ON p.order_id     = o.order_id
    LEFT JOIN order_items oi  ON oi.order_id    = o.order_id
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders | Vanguard's Delights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
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
            <!-- <a href="reviews.php"    class="list-group-item"><i class="fa-solid fa-star"></i>Reviews</a> -->
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

            <!-- Toolbar -->
            <div class="table-toolbar">
                <div class="d-flex align-items-center gap-2">
                    <span class="filter-label-inline">Status:</span>
                    <select class="filter-select" id="filterSelect" onchange="applyFilter(this.value)">
                        <option value="all">All Orders</option>
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <a href="../db/action/export_orders_csv.php" class="btn-export">
                    <i class="fa-solid fa-download"></i> Export CSV
                </a>
            </div>

            <div class="table-card">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th class="text-center">Items</th>
                            <th>Total (₱)</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTable">
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $o):
                            $rawStatus = strtolower($o['order_status'] ?? 'pending');
                            $statusMap = [
                                'pending'    => ['label' => 'Pending',    'class' => 'os-pending'],
                                'paid'       => ['label' => 'Paid',       'class' => 'os-paid'],
                                'processing' => ['label' => 'Processing', 'class' => 'os-processing'],
                                'shipped'    => ['label' => 'Shipped',    'class' => 'os-shipped'],
                                'completed'  => ['label' => 'Completed',  'class' => 'os-completed'],
                                'cancelled'  => ['label' => 'Cancelled',  'class' => 'os-cancelled'],
                            ];
                            $sc = $statusMap[$rawStatus] ?? ['label' => ucfirst($rawStatus), 'class' => 'os-pending'];
                            $dateFormatted = !empty($o['order_date']) ? date('M d, Y', strtotime($o['order_date'])) : '—';
                        ?>
                        <tr data-status="<?= $rawStatus ?>">
                            <td class="order-id-cell">#ORD-<?= str_pad($o['order_id'], 3, '0', STR_PAD_LEFT) ?></td>
                            <td><span style="font-weight:500;color:#1A1A1A;"><?= htmlspecialchars($o['customer_name'] ?? '—') ?></span></td>
                            <td class="text-muted-sm"><?= $dateFormatted ?></td>
                            <td class="text-center"><span class="item-count-badge"><?= $o['item_count'] ?></span></td>
                            <td class="amount-cell">₱<?= number_format($o['total_amount'], 2) ?></td>
                            <td class="text-center">
                                <span class="order-status-badge <?= $sc['class'] ?>">
                                    <span class="dot"></span><?= $sc['label'] ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="view_order.php?id=<?= $o['order_id'] ?>" class="view-link">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted-sm py-4">No orders found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function applyFilter(val) {
    document.querySelectorAll('#ordersTable tr[data-status]').forEach(row => {
        row.style.display = (val === 'all' || row.dataset.status === val) ? '' : 'none';
    });
}
</script>
</body>
</html>