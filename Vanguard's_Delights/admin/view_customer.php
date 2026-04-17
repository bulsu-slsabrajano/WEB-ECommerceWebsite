<!-- admin/view_customer.php -->
<?php
require_once '../db/action/config.php';
require_once '../db/connection.php';
require_once '../db/action/fetch_customer.php';

session_start();

$admin_name = $_SESSION["login_data"]["first_name"] ?? "Admin";
$user_id    = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$user_id) {
    header('Location: admin_ui.php');
    exit;
}

$customer  = getCustomerById($conn, $user_id);
if (!$customer) {
    header('Location: admin_ui.php');
    exit;
}

$contacts  = getCustomerContacts($conn, $user_id);
$addresses = getCustomerAddresses($conn, $user_id);
$orders    = getCustomerOrders($conn, $user_id);

$full_name = trim($customer['first_name'].' '.($customer['middle_name'] ? $customer['middle_name'].' ' : '').$customer['last_name']);
$initials  = strtoupper(substr($customer['first_name'],0,1).substr($customer['last_name'],0,1));
$joined    = !empty($customer['date_created']) ? date('F d, Y', strtotime($customer['date_created'])) : '—';
$isActive  = strtolower($customer['user_status'] ?? '') === 'active';
$total_spent = array_sum(array_column($orders, 'total_amount'));

// Status CSS class map
$status_class = [
    'completed'  => 'os-completed',
    'pending'    => 'os-pending',
    'cancelled'  => 'os-cancelled',
    'processing' => 'os-processing',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Customer | Vanguard's Delights</title>
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
            <a href="orders.php"     class="list-group-item"><i class="fa-solid fa-receipt"></i>Orders</a>
            <a href="admin_ui.php"   class="list-group-item active"><i class="fa-solid fa-users"></i>Customers</a>
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
            <h3 class="page-title">Customers</h3>
            <div class="admin-area">
                <div class="admin-info">
                    <p class="name"><?= htmlspecialchars($admin_name) ?></p>
                    <span class="role">Admin</span>
                </div>
                <div class="admin-profile-icon">
                    <i class="fa-solid fa-circle-user"></i>
                </div>
            </div>
        </nav>

        <div class="content-area">

            <!-- Back link -->
            <a href="admin_ui.php" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> View Customer
            </a>

            <!-- Top row: Customer Details + Contact Info -->
            <div class="view-grid" style="margin-bottom: 18px;">

                <!-- Customer Details -->
                <div class="info-card">
                    <div class="info-card-title">Customer Details</div>

                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?php if (!empty($customer['image_url'])): ?>
                                <img src="<?= htmlspecialchars($customer['image_url']) ?>" alt="">
                            <?php else: ?>
                                <?= $initials ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="profile-name"><?= htmlspecialchars($full_name) ?></p>
                            <span class="status-badge <?= $isActive ? 'status-active' : 'status-inactive' ?>" style="font-size:11px; padding:3px 9px;">
                                <span class="dot"></span><?= $isActive ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Username</span>
                        <span class="info-value"><?= htmlspecialchars($customer['username']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Joined</span>
                        <span class="info-value"><?= $joined ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Total Orders</span>
                        <span class="info-value"><?= count($orders) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Total Spent</span>
                        <span class="info-value" style="font-weight:600; color:var(--maroon);">₱<?= number_format($total_spent, 2) ?></span>
                    </div>
                </div>

                <!-- Contact & Address -->
                <div style="display:flex; flex-direction:column; gap:18px;">

                    <!-- Contact Numbers -->
                    <div class="info-card">
                        <div class="info-card-title">Contact Numbers</div>
                        <?php if (!empty($contacts)): ?>
                            <?php foreach ($contacts as $c): ?>
                            <div class="contact-item">
                                <div class="contact-icon"><i class="fa-solid fa-phone"></i></div>
                                <div class="contact-text">
                                    <?= htmlspecialchars($c['phone_number']) ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fa-solid fa-phone-slash"></i>
                                No contact numbers on record.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Addresses -->
                    <div class="info-card">
                        <div class="info-card-title">Addresses</div>
                        <?php if (!empty($addresses)): ?>
                            <?php foreach ($addresses as $a): ?>
                            <div class="address-item">
                                <div class="contact-icon"><i class="fa-solid fa-location-dot"></i></div>
                                <div class="contact-text">
                                    <?php
                                    $parts = array_filter([
                                        $a['street'],
                                        $a['city'],
                                        $a['province'],
                                        $a['postal_code'],
                                        $a['country']
                                    ]);
                                    echo htmlspecialchars(implode(', ', $parts));
                                    ?>
                                    <?php if ($a['is_default']): ?>
                                        <span class="default-badge">Default</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fa-solid fa-map-location-dot"></i>
                                No addresses on record.
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <!-- Purchase History - full width -->
            <div class="info-card view-grid full">
                <div class="info-card-title">Purchase History</div>

                <?php if (!empty($orders)): ?>
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $o):
                                $status_key = strtolower($o['order_status'] ?? '');
                                $css = $status_class[$status_key] ?? 'os-pending';
                            ?>
                            <tr>
                                <td style="font-weight:500;">#<?= str_pad($o['order_id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td class="text-muted-sm"><?= date('M d, Y', strtotime($o['order_date'])) ?></td>
                                <td style="font-weight:500;">₱<?= number_format($o['total_amount'], 2) ?></td>
                                <td>
                                    <span class="order-status <?= $css ?>">
                                        <?= ucfirst($o['order_status'] ?? 'Pending') ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="total-spent-row">
                        <span class="total-spent-label">Total Spent</span>
                        <span class="total-spent-value">₱<?= number_format($total_spent, 2) ?></span>
                    </div>

                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-bag-shopping"></i>
                        This customer has no orders yet.
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>