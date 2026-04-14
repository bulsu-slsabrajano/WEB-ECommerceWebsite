<?php
session_start();
$error = isset($_GET['error']) ? $_GET['error'] : '';
$success = isset($_GET['success']) ? $_GET['success'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Vanguard's Delights</title>
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
                <h1 class="login-title">FORGOT PASSWORD</h1>

                <?php if ($error == 'not_found'): ?>
                    <p class="error-message" style="color: red; text-align: center;">Username not found.</p>
                <?php elseif ($error == 'mismatch'): ?>
                    <p class="error-message" style="color: red; text-align: center;">Passwords do not match.</p>
                <?php elseif ($success == 'changed'): ?>
                    <p class="success-message" style="color: green; text-align: center;">Password updated successfully!</p>
                <?php endif; ?>

                <form action="db/action/toForgotPass.php" method="POST">
                    <div class="field-container">
                        <input type="text" name="username" placeholder="Username" required>
                    </div>
                    
                    <div class="field-container">
                        <input type="password" name="new_password" placeholder="New Password" required>
                    </div>

                    <div class="field-container">
                        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                    </div>
                    
                    <button type="submit" name="reset_btn" class="btn-login">Save</button>
                    
                    <div class="divider"></div>
                    
                    <div class="signup-text">
                        Remember your password? <a href="login.php">Log In</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>

</body>
</html>