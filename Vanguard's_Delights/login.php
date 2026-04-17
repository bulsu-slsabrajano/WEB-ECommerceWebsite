<?php
$error = '';
$allowed_errors = ['emptyfields', 'invalid', 'inactive', 'unauthorized'];

// Redirect to clean URL if already logged in
session_start();
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: home.php");
    }
    exit();
}

if (isset($_GET['error']) && in_array($_GET['error'], $allowed_errors)) {
    switch ($_GET['error']) {
        case 'emptyfields':  $error = 'Please fill in all fields.'; break;
        case 'invalid':      $error = 'Incorrect username or password.'; break;
        case 'inactive':     $error = 'Your account is inactive. Please contact support.'; break;
        case 'unauthorized': $error = 'You do not have permission to access this system.'; break;
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In | Vanguard's Delights</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-body">

    <div class="login-split-container">
        <div class="login-left">
            <div class="logo-wrapper">
                <img src="images/logoVanguards.png" alt="Vanguard's Delights Logo">
            </div>
        </div>

        <div class="login-right">
            <div class="login-form-box">
                <h1 class="login-title">LOG IN</h1>

                <?php if ($error): ?>
                    <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form action="db/action/toLogin.php" method="POST">
                    <div class="field-container">
                        <input type="text" name="username" placeholder="Username" required>
                    </div>
                    
                    <div class="field-container">
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    
                    <div class="forgot-link">
                        <a href="ForgotPassword.php">Forgot Password?</a>
                    </div>
                    
                    <button type="submit" name="login_btn" class="btn-login">Log In</button>
                    
                    <div class="divider"></div>
                    
                    <div class="signup-text">
                        Don't Have an Account? <a href="signup.php">Sign up</a>
                    </div>
                </form>
            </div>
        </div>
    </div>


</body>
</html>