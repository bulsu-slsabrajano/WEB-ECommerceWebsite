<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db/connection.php';

$user_id = $_SESSION['user_id'];

// Fetch user data from DB
$stmt = $conn->prepare("
    SELECT u.first_name, u.middle_name, u.last_name, u.username, u.email, u.gender, u.birthday,
           c.phone_number
    FROM users u
    LEFT JOIN contact_numbers c ON u.user_id = c.user_id
    WHERE u.user_id = :user_id
    LIMIT 1
");
$stmt->execute([':user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Vanguard's Delights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>

    <main class="container my-5">
        <h2 class="fw-bold mb-4" style="color: #7a2a2a;">Profile</h2>
        
        <div class="row g-0 shadow-sm rounded-4 overflow-hidden" style="min-height: 500px;">
            
            <!-- SIDEBAR -->
            <div class="col-md-4 brand-bg p-5 text-white">
                <div class="d-flex align-items-center mb-5">
                    <span class="fw-bold fs-5">User Profile</span>
                </div>
                
                <div class="profile-nav">
                    <!-- My Account -->
                    <a href="javascript:void(0)" id="nav-account" onclick="showTab('account')" class="d-flex align-items-center text-white text-decoration-none mb-4 active-link gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="white" viewBox="0 0 24 24">
                            <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                        </svg>
                        My Account
                    </a>

                    <!-- My Purchases -->
                    <a href="javascript:void(0)" id="nav-purchases" onclick="showTab('purchases')" class="d-flex align-items-center text-white text-decoration-none mb-4 opacity-75 gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <path d="M16 10a4 4 0 0 1-8 0"/>
                        </svg>
                        My Purchases
                    </a>

                    <!-- Log Out -->
                    <a href="javascript:void(0)" id="nav-logout" onclick="showTab('logout')" class="d-flex align-items-center text-white text-decoration-none opacity-75 gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Log Out
                    </a>
                </div>
            </div>

            <!-- CONTENT AREA -->
            <div class="col-md-8 bg-white p-5">
                
                <!-- MY ACCOUNT TAB -->
                <div id="tab-account" class="w-100">
                    <h4 class="fw-bold" style="color: #7a2a2a;">My Profile</h4>
                    <p class="text-muted small">Manage and protect your account</p>
                    <hr>

                    <form action="db/action/toProfile.php" method="POST" class="mt-4">

                        <!-- Username (now editable) -->
                        <div class="row mb-3 align-items-center">
                            <label class="col-sm-3 text-muted small">Username</label>
                            <div class="col-sm-9">
                                <input type="text" name="username" class="form-control"
                                    value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <!-- First Name -->
                        <div class="row mb-3 align-items-center">
                            <label class="col-sm-3 text-muted small">First Name</label>
                            <div class="col-sm-9">
                                <input type="text" name="first_name" class="form-control"
                                    value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>"
                                    placeholder="First Name" required>
                            </div>
                        </div>

                        <!-- Middle Name -->
                        <div class="row mb-3 align-items-center">
                            <label class="col-sm-3 text-muted small">Middle Name</label>
                            <div class="col-sm-9">
                                <input type="text" name="middle_name" class="form-control"
                                    value="<?php echo htmlspecialchars($user['middle_name'] ?? ''); ?>"
                                    placeholder="Middle Name (optional)">
                            </div>
                        </div>

                        <!-- Last Name -->
                        <div class="row mb-3 align-items-center">
                            <label class="col-sm-3 text-muted small">Last Name</label>
                            <div class="col-sm-9">
                                <input type="text" name="last_name" class="form-control"
                                    value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>"
                                    placeholder="Last Name" required>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="row mb-3 align-items-center">
                            <label class="col-sm-3 text-muted small">Email Address</label>
                            <div class="col-sm-9">
                                <input type="email" name="email" class="form-control"
                                    value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                                    placeholder="example@email.com" required>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="row mb-3 align-items-center">
                            <label class="col-sm-3 text-muted small">Phone Number</label>
                            <div class="col-sm-9">
                                <input type="text" name="phone" class="form-control"
                                    value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>"
                                    placeholder="09xx xxx xxxx">
                            </div>
                        </div>

                        <!-- Gender -->
                        <div class="row mb-3 align-items-center">
                            <label class="col-sm-3 text-muted small">Gender</label>
                            <div class="col-sm-9">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="male" value="Male"
                                        <?php echo (($user['gender'] ?? 'Male') === 'Male') ? 'checked' : ''; ?>>
                                    <label class="form-check-label small" for="male">Male</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="female" value="Female"
                                        <?php echo (($user['gender'] ?? '') === 'Female') ? 'checked' : ''; ?>>
                                    <label class="form-check-label small" for="female">Female</label>
                                </div>
                            </div>
                        </div>

                        <!-- Birthday -->
                        <div class="row mb-4 align-items-center">
                            <label class="col-sm-3 text-muted small">Birthday</label>
                            <div class="col-sm-9">
                                <input type="date" name="birthday" class="form-control" style="max-width: 200px;"
                                    value="<?php echo htmlspecialchars($user['birthday'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" name="submit_profile" class="btn btn-maroon px-5 text-white">Save</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- MY PURCHASES TAB -->
                <div id="tab-purchases" class="w-100" style="display: none;">
                    <h4 class="fw-bold" style="color: #7a2a2a;">My Purchases</h4>
                    <p class="text-muted small">Track your recent orders</p>
                    <hr>
                    <div class="text-center py-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" fill="none" stroke="#aaa" stroke-width="1.5" viewBox="0 0 24 24" class="mb-3">
                            <path d="M5 8h14l-1.5 9H6.5L5 8z" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="9" cy="20" r="1" fill="#aaa" stroke="none"/>
                            <circle cx="15" cy="20" r="1" fill="#aaa" stroke="none"/>
                        </svg>
                        <p class="text-muted">No purchases yet.</p>
                    </div>
                </div>

                <!-- LOGOUT TAB -->
               <div id="tab-logout" style="display: none; height: 100%;">
    <div class="d-flex justify-content-center align-items-center" style="min-height: 400px;">
        <div class="card border-0 rounded-4 shadow-sm" style="background-color: #f3ece4; width: 380px;">
            <div class="card-body p-4">
                <h6 class="border-bottom pb-2 mb-4 fw-bold" style="color: #7a2a2a; border-color: #7a2a2a !important;">
                    Confirm Log out
                </h6>
                <div class="text-center">
                    <p class="fw-bold mb-4" style="color: #333;">Are you sure you want to Log out?</p>
                    <div class="d-flex justify-content-center gap-3">
                        <button onclick="showTab('account')" class="btn btn-light px-4 border shadow-sm rounded-3">No</button>
                        <a href="db/action/toLogout.php" class="btn btn-maroon px-4 text-white shadow-sm">Yes</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    </main>

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

    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
    <script>
        alert("Information saved successfully!");
        window.history.replaceState({}, document.title, "profile.php");
    </script>
    <?php elseif (isset($_GET['status']) && $_GET['status'] == 'error'): ?>
    <script>
        alert("Something went wrong. Please try again.");
        window.history.replaceState({}, document.title, "profile.php");
    </script>
    <?php endif; ?>

    <?php include 'footer.php'; ?>
</body>
</html>