<?php
/**
 * header.php — Vanguard's Delights storefront header
 */
session_start(); 

$base_url     = "/Vanguard's_Delights/"; 
$cart_count   = $cart_count   ?? 0;
$search_query = $search_query ?? '';

if ($cart_count === 0 && !empty($_SESSION['cart'])) {
    $cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));
}

$is_logged_in = !empty($_SESSION['login_data']);
$user_name    = $is_logged_in ? ($_SESSION['login_data']['first_name'] ?? '') : '';
$user_image   = $is_logged_in ? ($_SESSION['login_data']['image_url']  ?? '') : '';
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

    <style>
        :root {
            --vd-maroon:      #7A1F1F; /* */
            --vd-maroon-dark: #5E1616;
            --vd-cream:       #F5EFE6;
            --vd-gold:        #D4A96A;
            --vd-gold-light:  #E8C99A;
        }

        .vd-header {
            background-color: var(--vd-maroon);
            padding: 0 40px;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 12px rgba(0,0,0,0.25);
        }

        /* Brand / Logo */
        .vd-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
        }

        .header-logo {
            height: 50px;
            width: auto;
        }

        .vd-brand-name {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            color: #FFFFFF;
            line-height: 1.1;
        }

        /* Search Bar */
        .vd-search-form {
            flex: 0 1 320px;
            margin: 0 24px;
        }

        .vd-search-wrap {
            position: relative;
            background: white;
            border-radius: 22px;
            padding: 5px 15px;
            display: flex;
            align-items: center;
        }

        .search-icon-img {
            height: 18px;
            margin-right: 10px;
        }

        .vd-search-input {
            border: none;
            outline: none;
            width: 100%;
            font-size: 14px;
        }

        /* Action Icons */
        .vd-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-icon-img {
            height: 26px;
            width: auto;
            transition: transform 0.2s;
        }

        .header-icon-img:hover {
            transform: scale(1.1);
        }

        .vd-action-btn { position: relative; text-decoration: none; }

        .vd-cart-badge {
            position: absolute;
            top: -5px;
            right: -8px;
            background: var(--vd-gold);
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 50%;
            border: 2px solid var(--vd-maroon);
        }

        .vd-dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            display: none;
            min-width: 150px;
            padding: 10px 0;
        }
        .vd-dropdown-menu.open { display: block; }
        .vd-dropdown-item { display: block; padding: 8px 15px; color: #333; text-decoration: none; font-size: 14px; }
        .vd-dropdown-item:hover { background: #f8f9fa; }
    </style>
</head>
<body>

<header class="vd-header">
    <a href="<?= $base_url ?>homepage.php" class="vd-brand">
        <img src="<?= $base_url ?>images/logoVanguards.png" alt="Logo" class="header-logo">
        <div class="vd-brand-text">
            <span class="vd-brand-name">Vanguard's<br>Delights</span>
        </div>
    </a>

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
            <div class="vd-user-dropdown">
                <button class="btn p-0 border-0" id="vdUserBtn">
                    <img src="<?= $base_url ?>images/profilepic.png" alt="Profile" class="header-icon-img">
                </button>
                <div class="vd-dropdown-menu" id="vdDropdown">
                    <a href="<?= $base_url ?>profile.php" class="vd-dropdown-item">My Profile</a>
                    <a href="<?= $base_url ?>logout.php" class="vd-dropdown-item text-danger">Logout</a>
                </div>
            </div>
        <?php else: ?>
            <a href="<?= $base_url ?>login.html" class="vd-action-btn">
                <img src="<?= $base_url ?>images/profilepic.png" alt="Login" class="header-icon-img">
            </a>
        <?php endif; ?>
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

</body>
</html>