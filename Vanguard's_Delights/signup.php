<?php
session_start();
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Vanguard's Delights</title>
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
                <h1 class="login-title">SIGN UP</h1>

                <?php if ($error == 'password_mismatch'): ?>
                    <p class="error-message">Passwords do not match!</p>
                <?php elseif ($error == 'user_exists'): ?>
                    <p class="error-message">Username already taken.</p>
                <?php elseif ($error == 'emptyfields'): ?>
                    <p class="error-message">Please fill in all required fields.</p>
                <?php elseif ($error == 'failed'): ?>
                    <p class="error-message">Registration failed. Please try again.</p>
                <?php endif; ?>

                <form action="db/action/toRegister.php" method="POST">

                    <div class="field-container">
                        <input type="text" name="first_name" placeholder="First Name" required>
                    </div>

                    <div class="field-container">
                        <input type="text" name="middle_name" placeholder="Middle Name (Optional)">
                    </div>

                    <div class="field-container">
                        <input type="text" name="last_name" placeholder="Last Name" required>
                    </div>

                    <div class="field-container">
                        <input type="text" name="username" placeholder="Username" required>
                    </div>

                    <div class="field-container">
                        <input type="password" name="password" placeholder="Password" required>
                    </div>

                    <div class="field-container">
                        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                    </div>

                    <button type="submit" name="register_btn" class="btn-login">Sign Up</button>

                    <div class="divider"></div>

                    <div class="signup-text">
                        Already Have an Account? <a href="login.php">Log In</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>

</body>
</html>