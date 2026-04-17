<!-- admin/admin_ui.php -->
<?php
require_once '../db/action/config.php'; 
require_once '../db/connection.php'; 
require_once '../db/action/fetch_customer.php'; 

session_start();

$customers  = getAllCustomers($conn);
$admin_name = $_SESSION["login_data"]["first_name"] ?? "Admin";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers | Vanguard's Delights</title>
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
            <!-- <a href="reviews.php"    class="list-group-item"><i class="fa-solid fa-star"></i>Reviews</a> -->
            <a href="reports.php"    class="list-group-item"><i class="fa-solid fa-chart-line"></i>Reports</a>
            <a href="admin_site.php" class="list-group-item"><i class="fa-solid fa-shield-halved"></i>Admin Site Settings</a>
        </div>
        <div class="sidebar-footer">
            <hr>
            <a href="../login.php" class="list-group-item logout-btn">
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

            <!-- Toolbar: filters LEFT, export RIGHT -->
            <div class="table-toolbar">
                <div class="filter-group">
                    <button class="filter-btn active" onclick="filterStatus(this,'all')">All</button>
                    <button class="filter-btn" onclick="filterStatus(this,'active')">Active</button>
                    <button class="filter-btn" onclick="filterStatus(this,'inactive')">Inactive</button>
                </div>
                <a href="../db/action/export_customers.php" class="btn-export">
                    <i class="fa-solid fa-download"></i> Export
                </a>
            </div>

            <div class="table-card">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th class="text-center">Orders</th>
                            <th>Total Spent</th>
                            <th>Joined</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody id="customerTable">
                        <?php if (!empty($customers)): ?>
                            <?php foreach ($customers as $row):
                                $isActive = strtolower($row['user_status'] ?? '') === 'active';
                                $initials = strtoupper(substr($row['first_name'],0,1).substr($row['last_name'],0,1));
                                $joined   = !empty($row['date_created']) ? date('M d, Y', strtotime($row['date_created'])) : '—';
                            ?>
                            <tr data-status="<?= strtolower($row['user_status'] ?? 'inactive') ?>">
                                <td>
                                    <div class="customer-cell">
                                        <?php if (!empty($row['image_url'])): ?>
                                            <img src="<?= htmlspecialchars($row['image_url']) ?>" class="profile-img" alt="">
                                        <?php else: ?>
                                            <div class="avatar-placeholder"><?= $initials ?></div>
                                        <?php endif; ?>
                                        <span class="cust-name"><?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?></span>
                                    </div>
                                </td>
                                <td class="text-muted-sm"><?= htmlspecialchars($row['email'] ?? $row['username'].'@gmail.com') ?></td>
                                <td class="text-muted-sm"><?= htmlspecialchars($row['username']) ?></td>
                                <td class="text-center"><span class="badge-orders"><?= $row['order_count'] ?></span></td>
                                <td class="spent-value">₱<?= number_format($row['total_spent'], 2) ?></td>
                                <td class="text-muted-sm"><?= $joined ?></td>
                                <td class="text-center">
                                    <span class="status-badge <?= $isActive ? 'status-active' : 'status-inactive' ?>">
                                        <span class="dot"></span><?= $isActive ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="view_customer.php?id=<?= $row['user_id'] ?>" class="view-link">View</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center text-muted-sm py-4">No customers found.</td></tr>
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
function filterStatus(btn, status) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('#customerTable tr').forEach(row => {
        row.style.display = (status === 'all' || row.dataset.status === status) ? '' : 'none';
    });
}
</script>
</body>
</html>