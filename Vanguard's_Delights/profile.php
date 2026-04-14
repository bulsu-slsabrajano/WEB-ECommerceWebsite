<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Vanguard's Delights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>
  

    <main class="container my-5">
        <h2 class="fw-bold mb-4" style="color: #7a2a2a;">Profile</h2>
        
        <div class="row g-0 shadow-sm rounded-4 overflow-hidden" style="min-height: 500px;">
            <div class="col-md-4 brand-bg p-5 text-white">
                <div class="d-flex align-items-center mb-5">
                    <span class="fw-bold fs-5">User Profile</span>
                </div>
                
                <div class="profile-nav">
                    <a href="javascript:void(0)" id="nav-account" onclick="showTab('account')" class="d-block text-white text-decoration-none mb-4 active-link">
                        <i class="fas fa-user-check me-2"></i> My Account
                    </a>
                    <a href="javascript:void(0)" id="nav-purchases" onclick="showTab('purchases')" class="d-block text-white text-decoration-none mb-4 opacity-75">
                        <i class="fas fa-shopping-bag me-2"></i> My Purchases
                    </a>
                    <a href="javascript:void(0)" id="nav-logout" onclick="showTab('logout')" class="d-block text-white text-decoration-none opacity-75">
                        <i class="fas fa-sign-out-alt me-2"></i> Log Out
                    </a>
                </div>
            </div>

            <div class="col-md-8 bg-white p-5 d-flex align-items-center justify-content-center">
                
                <div id="tab-account" class="w-100 align-self-start">
                    <h4 class="fw-bold" style="color: #7a2a2a;">My Profile</h4>
                    <p class="text-muted small">Manage and protect your account</p>
                    <hr>

                    <form class="mt-4">
                        <div class="row mb-3 align-items-center">
                            <label class="col-sm-3 text-muted small">Username</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control bg-light" placeholder="Username" readonly>
                            </div>
                        </div>
                        <div class="row mb-3 align-items-center">
                            <label class="col-sm-3 text-muted small">Name</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" placeholder="Enter Full Name">
                            </div>
                        </div>
                        <div class="row mb-3 align-items-center">
                            <label class="col-sm-3 text-muted small">Email Address</label>
                            <div class="col-sm-9">
                                <input type="email" class="form-control" placeholder="example@email.com">
                            </div>
                        </div>
                        <div class="row mb-3 align-items-center">
                            <label class="col-sm-3 text-muted small">Phone Number</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" placeholder="09xx xxx xxxx">
                            </div>
                        </div>
                        <div class="row mb-3 align-items-center">
                            <label class="col-sm-3 text-muted small">Gender</label>
                            <div class="col-sm-9">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="male">
                                    <label class="form-check-label small" for="male">Male</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="female">
                                    <label class="form-check-label small" for="female">Female</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-4 align-items-center">
                            <label class="col-sm-3 text-muted small">Birthday</label>
                            <div class="col-sm-9">
                                <div class="d-flex gap-2">
                                    <input type="text" class="form-control text-center" placeholder="YYYY" style="width: 80px;">
                                    <input type="text" class="form-control text-center" placeholder="mm" style="width: 60px;">
                                    <input type="text" class="form-control text-center" placeholder="dd" style="width: 60px;">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn btn-maroon px-5 text-white">Save</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="tab-purchases" class="w-100 align-self-start" style="display: none;">
                    <h4 class="fw-bold" style="color: #7a2a2a;">My Purchases</h4>
                    <p class="text-muted small">Track your recent orders</p>
                    <hr>
                    <div class="text-center py-5">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No purchases yet.</p>
                    </div>
                </div>

                <div id="tab-logout" style="display: none;">
                    <div class="card border-0 rounded-3 shadow-sm" style="background-color: #f3ece4; width: 350px;">
                        <div class="card-body p-4">
                            <h6 class="border-bottom pb-2 mb-4" style="color: #7a2a2a; border-color: #7a2a2a !important;">Confirm Log out</h6>
                            <div class="text-center">
                                <p class="fw-bold mb-4">Are you sure you want to Log out?</p>
                                <div class="d-flex justify-content-center gap-3">
                                    <button onclick="showTab('account')" class="btn btn-light px-4 border shadow-sm">No</button>
                                    <a href="login.php" class="btn btn-maroon px-4 text-white shadow-sm">Yes</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script>
    function showTab(tabName) {
        const tabs = ['account', 'purchases', 'logout'];
        
        tabs.forEach(tab => {
            const tabElement = document.getElementById('tab-' + tab);
            const navElement = document.getElementById('nav-' + tab);
            
            if (tab === tabName) {
                tabElement.style.display = 'block';
                navElement.classList.add('active-link');
                navElement.classList.remove('opacity-75');
            } else {
                tabElement.style.display = 'none';
                navElement.classList.remove('active-link');
                navElement.classList.add('opacity-75');
            }
        });
    }
    </script>

</body>
</html>