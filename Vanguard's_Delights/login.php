<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Vanguard's Delights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <main class="login-wrapper">
        <div class="row g-0 vh-100">
            <div class="col-md-5 brand-bg d-flex align-items-center justify-content-center">
                <div class="logo-box text-center">
                    <img src="logo.png" alt="Vanguard's Delights Logo" class="img-fluid" style="max-width: 80%;">
                </div>
            </div>

            <div class="col-md-7 d-flex align-items-center justify-content-center bg-white">
                <div class="login-container px-4" style="max-width: 400px; width: 100%;">
                    <h2 class="login-title text-center mb-4 fw-bold" style="color: #7a2a2a;">LOG IN</h2>
                    
                    <form action="home.php" method="POST">
                        <div class="mb-3">
                            <input type="text" class="form-control" name="username" placeholder="Username" required>
                        </div>
                        <div class="mb-2">
                            <input type="password" class="form-control" name="password" placeholder="Password" required>
                        </div>
                        
                        <div class="text-end mb-4">
                            <a href="ForgotPassword.php" class="text-decoration-none small" style="color: #7a2a2a;">Forgot Password?</a>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-login w-100 py-2 text-white" style="background-color: #7a2a2a; border: none;">Log in</button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="signup-text small">
                            Don't Have an Account? <a href="signup.php" class="text-decoration-none fw-bold" style="color: #7a2a2a;">Sign up</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>