<?php
session_start();
require_once '../../db/connection.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../../login.html");
    exit;
}

$username = $_SESSION['username'];

try {
    $stmt = $conn->prepare("SELECT user_id, first_name, last_name, image_url FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) die("User session invalid.");

    $userId = $user['user_id'];

    $ordersStmt = $conn->prepare("
        SELECT
            o.order_id, o.order_date, o.total_amount, o.order_status,
            oi.quantity, p.price, oi.subtotal,
            p.product_id, p.name AS product_name, p.image_url
        FROM orders o
        INNER JOIN order_items oi ON o.order_id = oi.order_id
        INNER JOIN products p ON oi.product_id = p.product_id
        WHERE o.user_id = ? AND TRIM(o.order_status) = 'Pending'
        ORDER BY o.order_date DESC
    ");
    $ordersStmt->execute([$userId]);
    $results = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

    $orders = [];
    foreach ($results as $row) {
        $oid = $row['order_id'];
        if (!isset($orders[$oid])) {
            $orders[$oid] = [
                'order_id'     => $row['order_id'],
                'order_date'   => $row['order_date'],
                'total_amount' => $row['total_amount'],
                'items'        => []
            ];
        }
        $orders[$oid]['items'][] = $row;
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
<title>Pending Purchases | Vanguard's Delights</title>
<link rel="stylesheet" href="../../css/style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body { background: #fff; }
.purchases-page { background: #fff; min-height: 100vh; padding: 40px 0; }
.page-title { color: #7A2E2E; font-weight: 800; font-size: 2rem; margin-bottom: 20px; }
.account-container { display: flex; background: #fff; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,.08); min-height: 600px; }
.account-sidebar { width: 280px; background: #7A2E2E; color: #fff; padding: 40px 0; flex-shrink: 0; }
.profile-info { text-align: center; padding-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,.1); margin-bottom: 20px; }
.avatar-circle { width: 70px; height: 70px; border-radius: 50%; overflow: hidden; background: #ddd; display: flex; align-items: center; justify-content: center; margin: auto; font-weight: bold; font-size: 1.5rem; color: #7A2E2E; }
.avatar-circle img { width: 100%; height: 100%; object-fit: cover; }
.nav-item-link { display: flex; gap: 12px; padding: 15px 30px; color: rgba(255,255,255,.8); text-decoration: none; align-items: center; }
.nav-item-link:hover, .nav-item-link.active { background: rgba(255,255,255,.15); color: #fff; }
.account-main-content { flex: 1; padding: 40px; background: #fff; }
.tabs-row { display: flex; gap: 10px; border-bottom: 2px solid #7A2E2E; padding-bottom: 0; margin-bottom: 30px; }
.tab-btn { text-decoration: none; color: #7A2E2E; font-weight: 700; padding: 8px 22px; border-radius: 8px 8px 0 0; display: inline-block; }
.tab-btn.active { background: #E3DEC9; color: #7A2E2E; }
.tab-btn:hover { background: #f0ebe0; color: #7A2E2E; }
.order-card { border: 1px solid #e0e0e0; border-radius: 10px; padding: 20px; margin-bottom: 20px; background: #fff; }
.product-img { width: 100px; height: 85px; object-fit: cover; border-radius: 10px; border: 1px solid #ddd; }
.btn-order-received { border: 1px solid #7A2E2E; color: #7A2E2E; background: transparent; border-radius: 8px; padding: 6px 16px; font-weight: 600; cursor: pointer; }
.btn-order-received:hover { background: #f0ebe0; }
.btn-cancel-order { background: #7A2E2E; color: #fff; border: none; border-radius: 8px; padding: 6px 16px; font-weight: 600; margin-left: 10px; cursor: pointer; }
.btn-cancel-order:hover { background: #5e2323; }
.empty-state { text-align: center; padding: 60px 0; color: #aaa; }

/* Modal */
.modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.45); z-index: 9999; justify-content: center; align-items: center; }
.modal-overlay.show { display: flex; }
.modal-box { background: #F4EFE6; width: 90%; max-width: 450px; border-radius: 8px; border: 1px solid #00A2E8; overflow: hidden; box-shadow: 0 8px 30px rgba(0,0,0,0.2); }
.modal-header-text { padding: 18px 20px; font-size: 1.15rem; font-weight: bold; color: #7A2E2E; border-bottom: 1px solid #dcdcdc; }
.modal-body-text { padding: 30px 20px; text-align: center; font-weight: bold; font-size: 1.05rem; color: #333; }
.modal-footer-btns { display: flex; padding: 0 20px 25px; justify-content: space-around; }
.btn-modal { width: 120px; padding: 9px; border-radius: 8px; font-weight: bold; border: none; cursor: pointer; font-size: 0.95rem; }
.btn-modal-no { background: #F8F9FA; color: #333; border: 1px solid #ccc; }
.btn-modal-no:hover { background: #e9ecef; }
.btn-modal-yes { background: #7A2E2E; color: #fff; }
.btn-modal-yes:hover { background: #5e2323; }
</style>
</head>
<body>
<?php include '../../header.php'; ?>

<div class="purchases-page">
    <div class="container">
        <h2 class="page-title">Purchases</h2>
        <div class="account-container">

            <!-- SIDEBAR -->
            <div class="account-sidebar">
                <div class="profile-info">
                    <div class="avatar-circle">
                        <?php if (!empty($user['image_url'])): ?>
                            <img src="../../uploads/<?= htmlspecialchars($user['image_url']) ?>">
                        <?php else: ?>
                            <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div class="mt-2 fw-bold"><?= htmlspecialchars($username) ?></div>
                </div>
                <nav>
                    <a href="../../profile.php" class="nav-item-link"><i class="fa-regular fa-user"></i> My Account</a>
                    <a href="PendingPurchase.php" class="nav-item-link active"><i class="fa-solid fa-bag-shopping"></i> My Purchases</a>
                    <a href="../../logout.php" class="nav-item-link"><i class="fa-solid fa-right-from-bracket"></i> Log Out</a>
                </nav>
            </div>

            <!-- MAIN -->
            <div class="account-main-content">
                <h2 style="color:#7A2E2E;font-weight:bold;">My Purchases</h2>
                <p class="text-muted">View and track your order history</p>

                <div class="tabs-row">
                    <a href="PendingPurchase.php" class="tab-btn active">Pending</a>
                    <a href="CompletePurchase.php" class="tab-btn">Completed</a>
                    <a href="CancelledPurchase.php" class="tab-btn">Cancelled</a>
                </div>

                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-clock fa-3x mb-3"></i>
                        <p>No Pending Orders for now.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card" id="order-card-<?= $order['order_id'] ?>">
                            <div class="mb-3">
                                <span class="text-muted small">
                                    <?= date("F j, Y", strtotime($order['order_date'])) ?>
                                </span>
                            </div>

                            <?php foreach ($order['items'] as $item): ?>
                                <div class="row align-items-center mb-3">
                                    <div class="col-auto">
                                        <img src="<?= !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : '../../images/placeholder.png' ?>" class="product-img">
                                    </div>
                                    <div class="col">
                                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($item['product_name']) ?></h6>
                                        <p class="mb-0 text-muted small">Qty: <?= $item['quantity'] ?> | ₱<?= number_format($item['price'], 2) ?></p>
                                    </div>
                                    <div class="col-auto text-end fw-bold">
                                        ₱<?= number_format($item['subtotal'], 2) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-2">
                                <div>
                                    <button class="btn-order-received" onclick="openConfirmModal(<?= $order['order_id'] ?>, 'received')">Order Received</button>
                                    <button class="btn-cancel-order" onclick="openConfirmModal(<?= $order['order_id'] ?>, 'cancel')">Cancel Order</button>
                                </div>
                                <div class="text-end">
                                    <span class="text-muted">Order Total: </span>
                                    <span class="fw-bold fs-5" style="color:#7A2E2E;">₱<?= number_format($order['total_amount'], 2) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- CONFIRM MODAL -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal-box">
        <div class="modal-header-text" id="modalTitle">Confirm Action</div>
        <div class="modal-body-text" id="modalQuestion">Are you sure?</div>
        <div class="modal-footer-btns">
            <button class="btn-modal btn-modal-no" onclick="closeConfirmModal()">No</button>
            <button class="btn-modal btn-modal-yes" id="confirmYesBtn">Yes</button>
        </div>
    </div>
</div>

<?php include '../../footer.php'; ?>

<script>
let currentOrderId = null;
let currentAction  = null;

function openConfirmModal(orderId, action) {
    currentOrderId = orderId;
    currentAction  = action;

    const title    = document.getElementById('modalTitle');
    const question = document.getElementById('modalQuestion');

    if (action === 'received') {
        title.innerText    = 'Confirm Order Received';
        question.innerText = 'Have you received this order?';
    } else {
        title.innerText    = 'Confirm Cancellation';
        question.innerText = 'Are you sure you want to cancel this order?';
    }

    document.getElementById('confirmModal').classList.add('show');
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.remove('show');
    const btn = document.getElementById('confirmYesBtn');
    btn.disabled  = false;
    btn.innerText = 'Yes';
}

document.getElementById('confirmYesBtn').addEventListener('click', function () {
    if (!currentOrderId) return;

    const btn    = this;
    btn.disabled = true;
    btn.innerText = 'Processing...';

    const formData = new FormData();
    formData.append('order_id', currentOrderId);
    formData.append('action',   currentAction);

    fetch('update_order_status.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // DB updated successfully — now redirect
            if (currentAction === 'received') {
                window.location.href = 'CompletePurchase.php';
            } else {
                window.location.href = 'CancelledPurchase.php';
            }
        } else {
            alert('Something went wrong: ' + data.message);
            btn.disabled  = false;
            btn.innerText = 'Yes';
        }
    })
    .catch(err => {
        console.error('Fetch Error:', err);
        alert('Network error. Please try again.');
        btn.disabled  = false;
        btn.innerText = 'Yes';
    });
});

document.getElementById('confirmModal').addEventListener('click', function (e) {
    if (e.target === this) closeConfirmModal();
});
</script>
</body>
</html>