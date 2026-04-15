<?php
// 1. CRITICAL: Start the session so $is_logged_in works
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_url     = "/Vanguard's_Delights/"; 
$cart_count   = $cart_count   ?? 0;
$search_query = $search_query ?? '';

if ($cart_count === 0 && !empty($_SESSION['cart'])) {
    $cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));
}

// 2. Check if the user is actually logged in
$is_logged_in = !empty($_SESSION['login_data']);
$user_name    = $is_logged_in ? ($_SESSION['login_data']['first_name'] ?? '') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vanguard's Delights</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= $base_url ?>css/style.css">
</head>
<body>

<header class="vd-header">
    <a href="<?= $base_url ?>homepage.php" class="vd-brand">
        <img src="<?= $base_url ?>images/logoVanguards.png" alt="Logo" class="header-logo">
        <div class="vd-brand-text">
            <span class="vd-brand-name">Vanguard's<br>Delights</span>
        </div>
    </a>

    <div class="vd-nav-group">
        <form class="vd-search-form" method="GET" action="<?= $base_url ?>search.php">
            <div class="vd-search-wrap">
                <img src="<?= $base_url ?>images/search.png" alt="Search" class="search-icon-img">
                <input type="text" name="q" class="vd-search-input" placeholder="Search" value="<?= htmlspecialchars($search_query) ?>">
            </div>
        </form>

        <div class="vd-actions">
            <a href="<?= $base_url ?>cart.php" class="vd-action-btn">
                <img src="<?= $base_url ?>images/shopcart.png" alt="Cart" class="header-icon-img">
                <?php if ($cart_count > 0): ?>
                    <span class="vd-cart-badge"><?= $cart_count ?></span>
                <?php endif; ?>
            </a>

            <?php if ($is_logged_in): ?>
                <div class="vd-user-dropdown" style="position: relative; display: flex; align-items: center;">
                    <a href="<?= $base_url ?>profile.php">
                        <img src="<?= $base_url ?>images/profilepic.png" alt="Profile" class="header-icon-img">
                    </a>
                    
                    <button class="btn p-0 border-0 ms-1" id="vdUserBtn" style="color: white; font-size: 10px; background: transparent;">
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>

                    <div class="vd-dropdown-menu" id="vdDropdown">
                        <a href="<?= $base_url ?>profile.php" class="vd-dropdown-item">My Profile</a>
                        <a href="<?= $base_url ?>logout.php" class="vd-dropdown-item text-danger">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= $base_url ?>login.php" class="vd-action-btn">
                    <img src="<?= $base_url ?>images/profilepic.png" alt="Login" class="header-icon-img">
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $('#vdUserBtn').on('click', function (e) {
        e.stopPropagation();
        $('#vdDropdown').toggleClass('open');
    });
    $(document).on('click', function () {
        $('#vdDropdown').removeClass('open');
    });
</script>