<?php
require_once '../db/connection.php';

session_start();

if (empty($_SESSION['login_data'])) { header('Location: ../index.html'); exit; }

$admin_id  = $_SESSION["login_data"]["user_id"] ?? null;
$success = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name   = trim($_POST['first_name']       ?? '');
    $middle_name  = trim($_POST['middle_name']      ?? '');
    $last_name    = trim($_POST['last_name']        ?? '');
    $email        = trim($_POST['email']            ?? '');
    $username     = trim($_POST['username']         ?? '');
    $new_pass     = trim($_POST['new_password']     ?? '');
    $confirm_pass = trim($_POST['confirm_password'] ?? '');

    try {
        $stmt = $conn->prepare("SELECT image_url FROM users WHERE user_id = ?");
        $stmt->execute([$admin_id]);
        $current   = $stmt->fetch(PDO::FETCH_ASSOC);
        $image_url = $current['image_url'];

        if (!empty($_FILES['profile_pic']['name'])) {
            $allowed = ['jpg','jpeg','png','webp'];
            $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) throw new Exception("Only JPG, PNG, WEBP allowed.");
            $upload_dir = '../images/profile/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $filename = 'admin_'.$admin_id.'_'.time().'.'.$ext;
            if (!move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_dir.$filename))
                throw new Exception("Upload failed.");
            $image_url = $url . '/images/profile/' . $filename;
        }

        if (!empty($new_pass)) {
            if ($new_pass !== $confirm_pass) throw new Exception("Passwords do not match.");
            if (strlen($new_pass) < 6) throw new Exception("Password must be at least 6 characters.");
            $hashed = password_hash($new_pass, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET first_name=?,middle_name=?,last_name=?,email=?,username=?,password=?,image_url=? WHERE user_id=?");
            $stmt->execute([$first_name,$middle_name,$last_name,$email,$username,$hashed,$image_url,$admin_id]);
        } else {
            $stmt = $conn->prepare("UPDATE users SET first_name=?,middle_name=?,last_name=?,email=?,username=?,image_url=? WHERE user_id=?");
            $stmt->execute([$first_name,$middle_name,$last_name,$email,$username,$image_url,$admin_id]);
        }

        $_SESSION["login_data"]["first_name"] = $first_name;
        $_SESSION["login_data"]["image_url"]  = $image_url;
        $success = "Profile updated successfully.";
    } catch (Exception $e) { $error = $e->getMessage(); }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

$admin_name = $admin['first_name'] ?? 'Admin';
$image_url  = $admin['image_url']  ?? null;
$full_name  = trim($admin['first_name'].' '.($admin['middle_name']?$admin['middle_name'].' ':'').$admin['last_name']);
$initials   = strtoupper(substr($admin['first_name'],0,1).substr($admin['last_name'],0,1));
$joined     = date('F d, Y', strtotime($admin['date_created'] ?? 'now'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | Vanguard's Delights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
<div class="d-flex" id="wrapper">

    <?php include 'partials/sidebar.php'; ?>

    <div id="page-content-wrapper">
        <?php include 'partials/topnav.php'; ?>

        <div class="content-area">
            <div class="profile-page-grid">

                <!-- LEFT CARD -->
                <div class="profile-left-card">
                    <div class="profile-pic-wrap">
                        <?php if (!empty($admin['image_url'])): ?>
                            <img src="<?= htmlspecialchars($admin['image_url']) ?>" alt="Profile" id="previewImg"
                                 style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid var(--cream-dark);">
                            <label class="profile-pic-edit" for="profile_pic_trigger" title="Change photo">
                                <i class="fa-solid fa-camera"></i>
                            </label>
                        <?php else: ?>
                            <div class="profile-pic-initials" id="initialsDiv"><?= $initials ?></div>
                            <img src="" alt="" id="previewImg" style="display:none;width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid var(--cream-dark);">
                            <label class="profile-pic-edit" for="profile_pic_trigger" title="Change photo">
                                <i class="fa-solid fa-camera"></i>
                            </label>
                        <?php endif; ?>
                    </div>

                    <p class="profile-left-name"><?= htmlspecialchars($full_name) ?></p>
                    <span class="profile-left-role">Admin</span>

                    <div class="profile-meta-row">
                        <i class="fa-solid fa-envelope"></i>
                        <div>
                            <div class="profile-meta-label">Email</div>
                            <div class="profile-meta-value"><?= htmlspecialchars($admin['email'] ?? '—') ?></div>
                        </div>
                    </div>
                    <div class="profile-meta-row">
                        <i class="fa-solid fa-at"></i>
                        <div>
                            <div class="profile-meta-label">Username</div>
                            <div class="profile-meta-value"><?= htmlspecialchars($admin['username']) ?></div>
                        </div>
                    </div>
                    <div class="profile-meta-row">
                        <i class="fa-solid fa-calendar-days"></i>
                        <div>
                            <div class="profile-meta-label">Member Since</div>
                            <div class="profile-meta-value"><?= $joined ?></div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT CARD -->
                <div class="profile-right-card">
                    <?php if ($success): ?>
                        <div class="alert-success-custom"><i class="fa-solid fa-circle-check"></i><?= $success ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert-error-custom"><i class="fa-solid fa-circle-exclamation"></i><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" id="profileForm">
                        <input type="file" name="profile_pic" id="profile_pic_trigger" accept="image/*" style="display:none;">

                        <div class="profile-section-title">
                            <i class="fa-solid fa-user me-2" style="color:var(--maroon);"></i>Personal Information
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" required
                                       value="<?= htmlspecialchars($admin['first_name']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="middle_name" class="form-control" placeholder="Optional"
                                       value="<?= htmlspecialchars($admin['middle_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" required
                                       value="<?= htmlspecialchars($admin['last_name']) ?>">
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" required
                                       value="<?= htmlspecialchars($admin['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required
                                       value="<?= htmlspecialchars($admin['username']) ?>">
                            </div>
                        </div>

                        <div class="profile-divider"></div>

                        <div class="profile-section-title">
                            <i class="fa-solid fa-lock me-2" style="color:var(--maroon);"></i>Change Password
                            <span style="font-size:11px;font-weight:400;color:var(--text-gray);margin-left:8px;">Leave blank to keep current</span>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <div class="pass-wrap">
                                    <input type="password" name="new_password" id="newPass" class="form-control" placeholder="Min. 6 characters">
                                    <i class="fa-solid fa-eye pass-toggle" onclick="togglePass('newPass',this)"></i>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password</label>
                                <div class="pass-wrap">
                                    <input type="password" name="confirm_password" id="confirmPass" class="form-control" placeholder="Re-enter new password">
                                    <i class="fa-solid fa-eye pass-toggle" onclick="togglePass('confirmPass',this)"></i>
                                </div>
                            </div>
                        </div>

                        <div class="profile-divider"></div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn-save">
                                <i class="fa-solid fa-floppy-disk"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('profile_pic_trigger').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) { alert('Please select an image file.'); return; }
    const reader = new FileReader();
    reader.onload = e => {
        const preview  = document.getElementById('previewImg');
        const initials = document.getElementById('initialsDiv');
        preview.src = e.target.result;
        preview.style.display = 'block';
        if (initials) initials.style.display = 'none';
        // Auto-submit to upload immediately
        document.getElementById('profileForm').submit();
    };
    reader.readAsDataURL(file);
});

function togglePass(id, icon) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
}
</script>
</body>
</html>