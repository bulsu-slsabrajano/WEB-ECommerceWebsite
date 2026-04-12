<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Vanguard's Delights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'header.php'; ?>
    <main class="login-wrapper">
        <div class="row g-0 vh-100">
            <div class="col-md-5 brand-bg d-flex align-items-center justify-content-center">
                <div class="logo-box text-center">
                    <img src="logo.png" alt="Vanguard's Delights Logo" class="img-fluid" style="max-width: 80%;">
                </div>
            </div>

            <div class="col-md-7 d-flex align-items-center justify-content-center bg-white">
                <div class="login-container px-4" style="max-width: 400px; width: 100%;">
                    <h2 class="login-title text-center mb-4 fw-bold" style="color: #7a2a2a;">FORGOT PASSWORD</h2>
                    
                    <form action="reset_process.php" method="POST">
                        <div class="mb-3">
                            <input type="text" class="form-control" name="username" placeholder="Username" required>
                        </div>
                        <div class="mb-3">
                            <input type="password" class="form-control" name="new_password" placeholder="New Password" required>
                        </div>
                        <div class="mb-4">
                            <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-login w-100 py-2 text-white" style="background-color: #7a2a2a;">Submit</button>
                        </div>

                        <div class="text-center mt-3">
                            <small class="text-muted">Remember your password? <a href="login.php" class="text-decoration-none" style="color: #7a2a2a;">Log In</a></small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>