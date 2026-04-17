<?php
require_once '../db/action/config.php';
require_once '../db/connection.php';

session_start();

$admin_name = $_SESSION["login_data"]["first_name"] ?? "Admin";

$success = $_SESSION['success'] ?? null;
$error   = $_SESSION['error']   ?? null;
unset($_SESSION['success'], $_SESSION['error']);

// 1. QUERY PARA SA PRODUCTS TABLE
$stmt = $conn->query("
    SELECT p.*, c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    ORDER BY p.product_creation DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. QUERY PARA SA CATEGORY FILTER DROPDOWN
$cat_stmt = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
$filter_categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products | Vanguard's Delights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
    <style>
        .product-img-thumb { width:46px;height:46px;border-radius:10px;object-fit:cover; }
        .img-placeholder   { width:46px;height:46px;border-radius:10px;background:var(--maroon-xlight);display:flex;align-items:center;justify-content:center;color:var(--maroon-light);font-size:18px; }
        .badge-cat         { display:inline-block;padding:3px 10px;border-radius:20px;background:var(--maroon-xlight);color:var(--maroon);font-size:11.5px;font-weight:500; }
        .stock-low         { color:#C0392B;font-weight:600; }

        .status-active   { background:#EDFAF3;color:#1E7D4A; }
        .status-inactive { background:#FDF0F0;color:#C0392B; }
        .status-oos      { background:#FFF3E0;color:#E65100; }
        .status-badge    { display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:20px;font-size:12px;font-weight:500; }
        .status-badge .dot { width:7px;height:7px;border-radius:50%;flex-shrink:0; }
        .status-active .dot   { background:#2ECC71; }
        .status-inactive .dot { background:#E74C3C; }
        .status-oos .dot      { background:#FB8C00; }

        .action-btn { background:none;border:none;padding:4px 8px;border-radius:7px;cursor:pointer;transition:background 0.15s;font-size:14px; text-decoration:none; display:inline-block; }
        .action-btn.edit  { color:#1565C0; }
        .action-btn.edit:hover  { background:#EEF2FF; }
        .action-btn.del   { color:#C0392B; }
        .action-btn.del:hover   { background:#FDF0F0; }

        .flash { padding:11px 16px;border-radius:9px;font-size:13px;margin-bottom:14px;font-family:'Poppins',sans-serif; }
        .flash-success { background:#EDFAF3;color:#1E7D4A;border:1px solid #B2EFD4; }
        .flash-error   { background:#FDF0F0;color:#C0392B;border:1px solid #FACAC8; }
    </style>
</head>
<body>
<div class="d-flex" id="wrapper">

    <div id="sidebar-wrapper">
        <div class="sidebar-heading">
            <img src="../images/logo.png" alt="Logo">
            <h6>Vanguard's<br>Delights</h6>
        </div>
        <div class="nav-section">
            <a href="dashboard.php"   class="list-group-item"><i class="fa-solid fa-gauge-high"></i>Dashboard</a>
            <a href="products.php"    class="list-group-item active"><i class="fa-solid fa-box"></i>Products</a>
            <a href="categories.php"  class="list-group-item"><i class="fa-solid fa-layer-group"></i>Categories</a>
            <a href="orders.php"      class="list-group-item"><i class="fa-solid fa-receipt"></i>Orders</a>
            <a href="admin_ui.php"    class="list-group-item"><i class="fa-solid fa-users"></i>Customers</a>
            <a href="reports.php"     class="list-group-item"><i class="fa-solid fa-chart-line"></i>Reports</a>
            <a href="admin_site.php"  class="list-group-item"><i class="fa-solid fa-shield-halved"></i>Admin Site Settings</a>
        </div>
        <div class="sidebar-footer">
            <hr>
            <a href="../db/action/logout.php" class="list-group-item logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i>Log Out
            </a>
        </div>
    </div>

    <div id="page-content-wrapper">
        <nav class="top-navbar">
            <h3 class="page-title">Products</h3>
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

            <div class="table-toolbar">
                <div class="filter-group" style="align-items: center; gap: 10px;">
                    <span class="filter-label-inline">Status:</span>
                    <select class="filter-select" id="filterStatus" onchange="applyFilters()">
                        <option value="all">All Products</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="low">Low Stock (≤5)</option>
                        <option value="oos">Out of Stock</option>
                    </select>

                    <span class="filter-label-inline" style="margin-left: 10px;">Category:</span>
                    <select class="filter-select" id="filterCategory" onchange="applyFilters()">
                        <option value="all">All Categories</option>
                        <?php foreach ($filter_categories as $fc): ?>
                            <option value="<?= $fc['category_id'] ?>"><?= htmlspecialchars($fc['category_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <a href="product_form.php" class="btn-export">
                    <i class="fa-solid fa-plus"></i> Add New Product
                </a>
            </div>

            <div class="table-card">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price (₱)</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody id="productTable">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $p):
                            $isActive = (bool)($p['is_active'] ?? 1);
                            $stock    = (int)($p['stock_quantity'] ?? 0);
                            $isOos    = $stock === 0;
                            $isLow    = $stock > 0 && $stock <= 5;

                            if ($isOos)        { $sc = 'status-oos';      $sl = 'Out of Stock'; }
                            elseif ($isActive) { $sc = 'status-active';   $sl = 'Active'; }
                            else               { $sc = 'status-inactive'; $sl = 'Inactive'; }
                        ?>
                        <tr data-status="<?= $isActive ? 'active' : 'inactive' ?>"
                            data-low="<?= $isLow ? '1':'0' ?>"
                            data-oos="<?= $isOos ? '1':'0' ?>"
                            data-category="<?= $p['category_id'] ?>"> <td>
                                <?php if (!empty($p['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($p['image_url']) ?>" class="product-img-thumb" alt="">
                                <?php else: ?>
                                    <div class="img-placeholder"><i class="fa-solid fa-image"></i></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight:500;font-size:13.5px;color:#1A1A1A;"><?= htmlspecialchars($p['name']) ?></div>
                                <div class="text-muted-sm" style="font-size:12px;"><?= htmlspecialchars(substr($p['description'] ?? '',0,50)) ?><?= strlen($p['description']??'')>50?'…':'' ?></div>
                            </td>
                            <td><span class="badge-cat"><?= htmlspecialchars($p['category_name'] ?? '—') ?></span></td>
                            <td style="font-weight:500;">₱<?= number_format($p['price'],2) ?></td>
                            <td class="text-center">
                                <span class="<?= ($isOos||$isLow)?'stock-low':'' ?>">
                                    <?= $stock ?>
                                    <?= $isLow ? '<i class="fa-solid fa-triangle-exclamation ms-1" title="Low Stock"></i>' : '' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="status-badge <?= $sc ?>"><span class="dot"></span><?= $sl ?></span>
                            </td>
                            <td class="text-end">
                                <a href="product_form.php?id=<?= $p['product_id'] ?>" class="action-btn edit" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                <button class="action-btn del" title="Delete" onclick="confirmDelete(<?= $p['product_id'] ?>,'<?= addslashes($p['name']) ?>')"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted-sm py-4">No products found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <form method="POST" action="../db/action/delete_product.php">
                <input type="hidden" name="product_id" id="del_product_id">
                <div class="modal-body text-center" style="padding:28px 24px;">
                    <div style="font-size:40px;color:#E74C3C;margin-bottom:12px;"><i class="fa-solid fa-circle-exclamation"></i></div>
                    <h5 style="font-size:15px;font-weight:600;font-family:'Poppins',sans-serif;margin-bottom:6px;">Delete Product?</h5>
                    <p class="text-muted-sm" id="del_msg" style="margin-bottom:20px;font-size:13px;"></p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal" style="border-radius:8px;font-family:'Poppins',sans-serif;">Cancel</button>
                        <button type="submit" class="btn btn-sm" style="background:#C0392B;color:white;border-radius:8px;font-family:'Poppins',sans-serif;padding:6px 20px;">Delete</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

// PINALAKAS NA FILTER FUNCTION
function applyFilters() {
    const statusVal = document.getElementById('filterStatus').value;
    const catVal = document.getElementById('filterCategory').value;
    
    document.querySelectorAll('#productTable tr[data-status]').forEach(row => {
        // 1. Check Status logic
        let statusMatch = false;
        if      (statusVal === 'all')      statusMatch = true;
        else if (statusVal === 'active')   statusMatch = (row.dataset.status === 'active' && row.dataset.oos === '0');
        else if (statusVal === 'inactive') statusMatch = (row.dataset.status === 'inactive');
        else if (statusVal === 'low')      statusMatch = (row.dataset.low === '1');
        else if (statusVal === 'oos')      statusMatch = (row.dataset.oos === '1');

        // 2. Check Category logic
        let catMatch = (catVal === 'all' || row.dataset.category === catVal);

        // 3. Ipakita lang kung pasok sa parehong filters
        row.style.display = (statusMatch && catMatch) ? '' : 'none';
    });
}

function confirmDelete(id, name) {
    document.getElementById('del_product_id').value = id;
    document.getElementById('del_msg').textContent  = 'This will permanently remove "' + name + '".';
    deleteModal.show();
}
</script>
</body>
</html>