<!-- admin/categories.php -->
<?php
require_once '../db/action/config.php';
require_once '../db/connection.php';

session_start();

$admin_name = $_SESSION["login_data"]["first_name"] ?? "Admin";

$success = $_SESSION['success'] ?? null;
$error   = $_SESSION['error']   ?? null;
unset($_SESSION['success'], $_SESSION['error']);

$stmt = $conn->query("
    SELECT c.*, COUNT(p.product_id) AS product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.category_id
    GROUP BY c.category_id
    ORDER BY c.category_name
");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories | Vanguard's Delights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
    <style>
        .cat-img-thumb { width:46px;height:46px;border-radius:10px;object-fit:cover; }
        .cat-img-ph    { width:46px;height:46px;border-radius:10px;background:var(--maroon-xlight);display:flex;align-items:center;justify-content:center;color:var(--maroon-light);font-size:18px; }
        .badge-count   { display:inline-block;padding:3px 11px;border-radius:20px;background:#F8F4F0;border:1px solid var(--cream-dark);color:var(--maroon);font-size:12px;font-weight:500; }

        .status-active   { background:#EDFAF3;color:#1E7D4A; }
        .status-inactive { background:#FDF0F0;color:#C0392B; }
        .status-badge    { display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:20px;font-size:12px;font-weight:500; }
        .status-badge .dot { width:7px;height:7px;border-radius:50%;flex-shrink:0; }
        .status-active .dot   { background:#2ECC71; }
        .status-inactive .dot { background:#E74C3C; }

        .action-btn { background:none;border:none;padding:4px 8px;border-radius:7px;cursor:pointer;transition:background 0.15s;font-size:14px;text-decoration:none;display:inline-block; }
        .action-btn.edit  { color:#1565C0; }
        .action-btn.edit:hover  { background:#EEF2FF; }
        .action-btn.del   { color:#C0392B; }
        .action-btn.del:hover   { background:#FDF0F0; }

        .filter-select {
            font-family:'Poppins',sans-serif;font-size:13px;font-weight:500;
            color:var(--text-gray);border:1.5px solid var(--cream-dark);border-radius:20px;
            padding:6px 34px 6px 14px;
            background:white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237E7E7E' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 12px center;
            -webkit-appearance:none;appearance:none;cursor:pointer;transition:border-color 0.2s;outline:none;
        }
        .filter-select:focus { border-color:var(--maroon-light); }

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
            <a href="dashboard.php"  class="list-group-item"><i class="fa-solid fa-gauge-high"></i>Dashboard</a>
            <a href="products.php"   class="list-group-item"><i class="fa-solid fa-box"></i>Products</a>
            <a href="categories.php" class="list-group-item active"><i class="fa-solid fa-layer-group"></i>Categories</a>
            <a href="orders.php"     class="list-group-item"><i class="fa-solid fa-receipt"></i>Orders</a>
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

    <div id="page-content-wrapper">
        <nav class="top-navbar">
            <h3 class="page-title">Categories</h3>
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
        <select class="filter-select" id="filterSelect" onchange="applyFilter(this.value)">
            <option value="all">All Categories</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>

    <a href="category_form.php" class="btn-export">
        <i class="fa-solid fa-plus"></i> Add Category
    </a>
</div>

            <div class="table-card">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th class="text-center">Products</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody id="catTable">
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $c):
                            $isActive = true; // Adjust when you add is_active column to categories
                        ?>
                        <tr data-status="<?= $isActive ? 'active' : 'inactive' ?>">
                            <td>
                                <?php if (!empty($c['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($c['image_url']) ?>" class="cat-img-thumb" alt="">
                                <?php else: ?>
                                    <div class="cat-img-ph"><i class="fa-solid fa-layer-group"></i></div>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight:500;font-size:13.5px;color:#1A1A1A;"><?= htmlspecialchars($c['category_name']) ?></td>
                            <td class="text-muted-sm"><?= htmlspecialchars(substr($c['description']??'',0,60)) ?><?= strlen($c['description']??'')>60?'…':'' ?></td>
                            <td class="text-center"><span class="badge-count"><?= $c['product_count'] ?></span></td>
                            <td class="text-center">
                                <span class="status-badge <?= $isActive?'status-active':'status-inactive' ?>">
                                    <span class="dot"></span><?= $isActive?'Active':'Inactive' ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="category_form.php?id=<?= $c['category_id'] ?>" class="action-btn edit" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                <button class="action-btn del" title="Delete" onclick="confirmDelete(<?= $c['category_id'] ?>,'<?= addslashes($c['category_name']) ?>',<?= $c['product_count'] ?>)"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted-sm py-4">No categories found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- DELETE MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <form method="POST" action="../db/action/delete_category.php">
                <input type="hidden" name="category_id" id="del_cat_id">
                <div class="modal-body text-center" style="padding:28px 24px;">
                    <div style="font-size:40px;color:#E74C3C;margin-bottom:12px;"><i class="fa-solid fa-circle-exclamation"></i></div>
                    <h5 style="font-size:15px;font-weight:600;font-family:'Poppins',sans-serif;margin-bottom:6px;">Delete Category?</h5>
                    <p class="text-muted-sm" id="del_cat_name" style="margin-bottom:4px;font-size:13px;"></p>
                    <p id="del_cat_warn" style="margin-bottom:20px;font-size:12px;color:#C0392B;"></p>
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

function applyFilter(val) {
    document.querySelectorAll('#catTable tr[data-status]').forEach(row => {
        row.style.display = (val === 'all' || row.dataset.status === val) ? '' : 'none';
    });
}

function confirmDelete(id, name, count) {
    document.getElementById('del_cat_id').value    = id;
    document.getElementById('del_cat_name').textContent = 'Remove "' + name + '"?';
    document.getElementById('del_cat_warn').textContent = count > 0
        ? '⚠ ' + count + ' product(s) will be unlinked from this category.'
        : '';
    deleteModal.show();
}
</script>
</body>
</html>