<!-- admin/product_form.php -->
<?php
require_once '../db/action/config.php';
require_once '../db/connection.php';

session_start();

$admin_name = $_SESSION["login_data"]["first_name"] ?? "Admin";

// Edit mode?
$product = null;
if (!empty($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) { header('Location: products.php'); exit; }
}

$isEdit = $product !== null;

// Categories
$cats = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Edit' : 'Add' ?> Product | Vanguard's Delights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
    <style>
        .form-page-wrap { max-width: 780px; }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #2D2D2D;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            margin-bottom: 6px;
            transition: color 0.2s;
        }
        .back-link:hover { color: var(--maroon); }

        .page-subtitle {
            font-size: 13px;
            color: var(--text-gray);
            margin: 0 0 24px;
        }

        .form-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.05);
            padding: 28px 30px;
            margin-bottom: 18px;
        }

        .form-card-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--maroon-light);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--cream-dark);
        }

        .form-label {
            font-size: 12.5px;
            font-weight: 600;
            color: #1A1A1A;
            margin-bottom: 6px;
        }

        .form-label span.req { color: var(--maroon); }

        .form-control, .form-select {
            font-family: 'Poppins', sans-serif;
            font-size: 13.5px;
            border: 1.5px solid var(--cream-dark);
            border-radius: 10px;
            padding: 10px 14px;
            color: #2D2D2D;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control::placeholder { color: #BDBDBD; }
        .form-control:focus, .form-select:focus {
            border-color: var(--maroon-light);
            box-shadow: 0 0 0 3px rgba(130,47,47,0.08);
            outline: none;
        }
        textarea.form-control { resize: none; }

        /* Upload zone */
        .upload-zone {
            border: 2px dashed var(--cream-dark);
            border-radius: 12px;
            padding: 36px 20px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s, background 0.2s;
            position: relative;
            background: #FDFAF8;
        }
        .upload-zone:hover, .upload-zone.drag-over {
            border-color: var(--maroon-light);
            background: var(--maroon-xlight);
        }
        .upload-zone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }
        .upload-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            background: var(--cream-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            color: var(--text-gray);
            font-size: 20px;
        }
        .upload-main-text {
            font-size: 14px;
            font-weight: 500;
            color: #2D2D2D;
            margin-bottom: 4px;
        }
        .upload-sub-text {
            font-size: 12px;
            color: var(--text-gray);
        }

        /* Preview */
        .img-preview-box {
            display: none;
            position: relative;
            margin-top: 14px;
        }
        .img-preview-box img {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid var(--cream-dark);
        }
        .remove-img-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            background: white;
            border: 1.5px solid var(--cream-dark);
            border-radius: 8px;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #C0392B;
            font-size: 12px;
            transition: background 0.2s;
        }
        .remove-img-btn:hover { background: #FDF0F0; }

        /* Toggle switch */
        .toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
        }
        .toggle-label-text { font-size: 13.5px; font-weight: 500; color: #2D2D2D; }
        .toggle-label-sub  { font-size: 12px; color: var(--text-gray); }
        .form-switch .form-check-input {
            width: 42px;
            height: 22px;
            cursor: pointer;
        }
        .form-switch .form-check-input:checked { background-color: var(--maroon); border-color: var(--maroon); }
        .form-switch .form-check-input:focus { box-shadow: 0 0 0 3px rgba(130,47,47,0.12); border-color: var(--maroon-light); }

        /* Buttons */
        .btn-maroon {
            background: var(--maroon);
            color: white;
            border: none;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 13.5px;
            font-weight: 500;
            padding: 10px 28px;
            transition: background 0.2s;
            cursor: pointer;
        }
        .btn-maroon:hover { background: var(--maroon-hover); }

        .btn-cancel {
            background: white;
            color: #2D2D2D;
            border: 1.5px solid var(--cream-dark);
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 13.5px;
            font-weight: 500;
            padding: 10px 24px;
            text-decoration: none;
            transition: background 0.2s, border-color 0.2s;
            display: inline-block;
        }
        .btn-cancel:hover { background: var(--cream-dark); color: #1A1A1A; }

        .price-input-wrap { position: relative; }
        .price-input-wrap .currency-sign {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 13.5px;
            color: var(--text-gray);
            pointer-events: none;
        }
        .price-input-wrap .form-control { padding-left: 26px; }
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
            <a href="products.php"   class="list-group-item active"><i class="fa-solid fa-box"></i>Products</a>
            <a href="categories.php" class="list-group-item"><i class="fa-solid fa-layer-group"></i>Categories</a>
            <a href="orders.php"     class="list-group-item"><i class="fa-solid fa-receipt"></i>Orders</a>
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
            <h3 class="page-title"><?= $isEdit ? 'Edit' : 'Add' ?> Product</h3>
            <div class="admin-area">
                <div class="admin-info">
                    <p class="name"><?= htmlspecialchars($admin_name) ?></p>
                    <span class="role">Admin</span>
                </div>
                <div class="admin-profile-icon"><i class="fa-solid fa-circle-user"></i></div>
            </div>
        </nav>

        <div class="content-area">
            <div class="form-page-wrap">

                <a href="products.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Products</a>
                <p class="page-subtitle"><?= $isEdit ? 'Update the details of this product.' : 'Fill in the details to add a new product.' ?></p>

                <form method="POST" enctype="multipart/form-data" action="../db/action/save_product.php">
                    <input type="hidden" name="product_id" value="<?= $isEdit ? $product['product_id'] : '' ?>">

                    <!-- IMAGE -->
                    <div class="form-card">
                        <div class="form-card-title">Product Image</div>
                        <div class="upload-zone" id="uploadZone">
                            <input type="file" name="image" id="imageInput" accept="image/*">
                            <div class="upload-icon"><i class="fa-solid fa-arrow-up-from-bracket"></i></div>
                            <div class="upload-main-text">Click to upload or drag and drop</div>
                            <div class="upload-sub-text">PNG, JPG, WEBP up to 5MB</div>
                        </div>
                        <div class="img-preview-box" id="previewBox">
                            <img id="imgPreview" src="<?= $isEdit && $product['image_url'] ? htmlspecialchars($product['image_url']) : '' ?>" alt="Preview">
                            <button type="button" class="remove-img-btn" onclick="removeImg()" title="Remove"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    </div>

                    <!-- BASIC INFO -->
                    <div class="form-card">
                        <div class="form-card-title">Basic Information</div>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Product Name <span class="req">*</span></label>
                                <input type="text" class="form-control" name="name" placeholder="e.g. Mini Cake, Biscoff Cookie" required value="<?= $isEdit ? htmlspecialchars($product['name']) : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">SKU</label>
                                <input type="text" class="form-control" name="sku" placeholder="e.g. VD-001" value="<?= $isEdit ? htmlspecialchars($product['sku'] ?? '') : '' ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="4" placeholder="Describe the product…"><?= $isEdit ? htmlspecialchars($product['description'] ?? '') : '' ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- PRICING & STOCK -->
                    <div class="form-card">
                        <div class="form-card-title">Pricing & Inventory</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Price (₱) <span class="req">*</span></label>
                                <div class="price-input-wrap">
                                    <span class="currency-sign">₱</span>
                                    <input type="number" step="0.01" min="0" class="form-control" name="price" placeholder="0.00" required value="<?= $isEdit ? $product['price'] : '' ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Stock Quantity <span class="req">*</span></label>
                                <input type="number" min="0" class="form-control" name="stock_quantity" placeholder="0" required value="<?= $isEdit ? $product['stock_quantity'] : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category_id">
                                    <option value="">— None —</option>
                                    <?php foreach ($cats as $c): ?>
                                        <option value="<?= $c['category_id'] ?>" <?= ($isEdit && $product['category_id'] == $c['category_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['category_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- STATUS -->
                    <div class="form-card">
                        <div class="form-card-title">Visibility</div>
                        <div class="toggle-row">
                            <div>
                                <div class="toggle-label-text">Active</div>
                                <div class="toggle-label-sub">Product will be visible to customers</div>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="toggleActive" <?= (!$isEdit || $product['is_active']) ? 'checked' : '' ?>>
                            </div>
                        </div>
                    </div>

                    <!-- ACTIONS -->
                    <div class="d-flex gap-3 pb-4">
                        <a href="products.php" class="btn-cancel">Cancel</a>
                        <button type="submit" class="btn-maroon">
                            <i class="fa-solid fa-floppy-disk me-2"></i><?= $isEdit ? 'Update Product' : 'Save Product' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Show existing image on edit
const previewBox  = document.getElementById('previewBox');
const imgPreview  = document.getElementById('imgPreview');

<?php if ($isEdit && $product['image_url']): ?>
previewBox.style.display = 'block';
<?php endif; ?>

document.getElementById('imageInput').addEventListener('change', function () {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            imgPreview.src = e.target.result;
            previewBox.style.display = 'block';
        };
        reader.readAsDataURL(this.files[0]);
    }
});

function removeImg() {
    document.getElementById('imageInput').value = '';
    imgPreview.src = '';
    previewBox.style.display = 'none';
    // Sabihan ang backend na burahin ang picture
    let flag = document.getElementById('removeImageFlag');
    if(!flag) {
        let input = document.createElement("input");
        input.setAttribute("type", "hidden");
        input.setAttribute("name", "remove_current_image");
        input.setAttribute("id", "removeImageFlag");
        input.setAttribute("value", "1");
        document.querySelector('form').appendChild(input);
    } else {
        flag.value = "1";
    }
}

// Drag & drop
const zone = document.getElementById('uploadZone');
zone.addEventListener('dragover',  e => { e.preventDefault(); zone.classList.add('drag-over'); });
zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file) {
        document.getElementById('imageInput').files = e.dataTransfer.files;
        const reader = new FileReader();
        reader.onload = ev => { imgPreview.src = ev.target.result; previewBox.style.display = 'block'; };
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>