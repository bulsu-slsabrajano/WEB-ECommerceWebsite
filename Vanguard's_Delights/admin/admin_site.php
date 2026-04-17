<!-- admin/admin_site.php -->
<?php
require_once '../db/action/config.php';
require_once '../db/connection.php';

session_start();

$admin_name = $_SESSION["login_data"]["first_name"] ?? "Admin";
$current_admin_id = $_SESSION["login_data"]["user_id"] ?? 0;

// Fetch all admins
try {
    $stmt = $conn->prepare("
        SELECT u.user_id, u.first_name, u.middle_name, u.last_name,
               u.username, u.email, u.gender, u.birthday,
               u.user_status, u.date_created, u.image_url,
               (SELECT s.login_time FROM sessions s WHERE s.user_id = u.user_id ORDER BY s.login_time DESC LIMIT 1) AS last_login
        FROM users u
        WHERE u.role = 'admin'
        ORDER BY u.date_created DESC
    ");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $admins = [];
}

// Flash messages
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Site Settings | Vanguard's Delights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
    <style>
        /* ── Modal overrides ── */
        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.12);
            font-family: 'Poppins', sans-serif;
        }
        .modal-header {
            border-bottom: 1px solid var(--cream-dark);
            padding: 20px 24px 16px;
        }
        .modal-title {
            font-size: 15px;
            font-weight: 600;
            color: #1A1A1A;
        }
        .modal-body { padding: 20px 24px; }
        .modal-footer {
            border-top: 1px solid var(--cream-dark);
            padding: 14px 24px;
            gap: 8px;
        }
        .btn-close:focus { box-shadow: none; }

        /* ── Avatar upload in modal ── */
        .avatar-upload-wrap {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--cream-dark);
        }
        .avatar-preview {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: var(--maroon-xlight);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 600;
            color: var(--maroon);
            flex-shrink: 0;
            overflow: hidden;
        }
        .avatar-preview img {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            object-fit: cover;
        }
        .avatar-upload-btn {
            font-family: 'Poppins', sans-serif;
            font-size: 12.5px;
            font-weight: 500;
            color: var(--maroon);
            border: 1.5px solid var(--maroon);
            background: white;
            border-radius: 8px;
            padding: 6px 14px;
            cursor: pointer;
            transition: 0.2s;
        }
        .avatar-upload-btn:hover { background: var(--maroon-xlight); }

        /* ── Table extras ── */
        .admin-name-cell {
            display: flex;
            align-items: center;
            gap: 11px;
        }
        .last-login-text {
            font-size: 12px;
            color: var(--text-gray);
        }

        /* ── Delete confirm modal ── */
        .delete-warn-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: #FDF0F0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #C0392B;
            font-size: 20px;
            margin: 0 auto 14px;
        }
    </style>
</head>
<body>
<div class="d-flex" id="wrapper">

    <!-- SIDEBAR -->
    <div id="sidebar-wrapper">
        <div class="sidebar-heading">
            <img src="../images/logo.png" alt="Logo">
            <h6>Vanguard's<br>Delights</h6>
        </div>
        <div class="nav-section">
            <a href="dashboard.php"  class="list-group-item"><i class="fa-solid fa-gauge-high"></i>Dashboard</a>
            <a href="products.php"   class="list-group-item"><i class="fa-solid fa-box"></i>Products</a>
            <a href="categories.php" class="list-group-item"><i class="fa-solid fa-layer-group"></i>Categories</a>
            <a href="orders.php"     class="list-group-item"><i class="fa-solid fa-receipt"></i>Orders</a>
            <a href="admin_ui.php"   class="list-group-item"><i class="fa-solid fa-users"></i>Customers</a>
            <a href="reports.php"    class="list-group-item"><i class="fa-solid fa-chart-line"></i>Reports</a>
            <a href="admin_site.php" class="list-group-item active"><i class="fa-solid fa-shield-halved"></i>Admin Site Settings</a>
        </div>
        <div class="sidebar-footer">
            <hr>
            <a href="../db/action/logout.php" class="list-group-item logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i>Log Out
            </a>
        </div>
    </div>

    <!-- MAIN -->
    <div id="page-content-wrapper">
        <nav class="top-navbar">
            <h3 class="page-title">Admin Site Settings</h3>
            <div class="admin-area">
                <div class="admin-info">
                    <p class="name"><?= htmlspecialchars($admin_name) ?></p>
                    <span class="role">Admin</span>
                </div>
                <div class="admin-profile-icon">
                    <i class="fa-solid fa-circle-user"></i>
                </div>
            </div>
        </nav>

        <div class="content-area">

            <!-- Flash -->
            <?php if ($flash): ?>
                <div class="flash flash-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
            <?php endif; ?>

            <!-- Toolbar -->
            <div class="table-toolbar">
                <div class="filter-group" style="align-items:center; gap:10px;">
                    <span class="filter-label-inline">Status</span>
                    <select class="filter-select" id="statusFilter" onchange="applyFilters()">
                        <option value="all">All</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <button class="btn-export" onclick="openAddModal()">
                    <i class="fa-solid fa-plus"></i> Add Admin
                </button>
            </div>

            <!-- Table -->
            <div class="table-card">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Admin</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Gender</th>
                            <th>Birthday</th>
                            <th>Last Login</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="adminTable">
                        <?php if (!empty($admins)): ?>
                            <?php foreach ($admins as $row):
                                $isActive = strtolower($row['user_status'] ?? '') === 'active';
                                $initials = strtoupper(substr($row['first_name'],0,1).substr($row['last_name'],0,1));
                                $full_name = trim($row['first_name'].' '.($row['middle_name'] ? $row['middle_name'].' ' : '').$row['last_name']);
                                $bday = !empty($row['birthday']) ? date('M d, Y', strtotime($row['birthday'])) : '—';
                                $last_login = !empty($row['last_login']) ? date('M d, Y g:i A', strtotime($row['last_login'])) : 'Never';
                                $isSelf = ($row['user_id'] == $current_admin_id);
                            ?>
                            <tr data-status="<?= strtolower($row['user_status'] ?? 'inactive') ?>">
                                <td>
                                    <div class="admin-name-cell">
                                        <?php if (!empty($row['image_url'])): ?>
                                            <img src="<?= htmlspecialchars($row['image_url']) ?>" class="profile-img" alt="">
                                        <?php else: ?>
                                            <div class="avatar-placeholder"><?= $initials ?></div>
                                        <?php endif; ?>
                                        <div>
                                            <span class="cust-name"><?= htmlspecialchars($full_name) ?></span>
                                            <?php if ($isSelf): ?>
                                                <span class="default-badge" style="margin-left:4px;">You</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted-sm"><?= htmlspecialchars($row['email'] ?? '—') ?></td>
                                <td class="text-muted-sm"><?= htmlspecialchars($row['username']) ?></td>
                                <td class="text-muted-sm"><?= htmlspecialchars(ucfirst($row['gender'] ?? '—')) ?></td>
                                <td class="text-muted-sm"><?= $bday ?></td>
                                <td class="last-login-text"><?= $last_login ?></td>
                                <td class="text-center">
                                    <span class="status-badge <?= $isActive ? 'status-active' : 'status-inactive' ?>">
                                        <span class="dot"></span><?= $isActive ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button class="action-btn edit" title="Edit"
                                        onclick="openEditModal(<?= htmlspecialchars(json_encode($row)) ?>)">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <?php if (!$isSelf): ?>
                                    <button class="action-btn del" title="Delete"
                                        onclick="openDeleteModal(<?= $row['user_id'] ?>, '<?= htmlspecialchars($full_name) ?>')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    <?php else: ?>
                                    <button class="action-btn del" title="Cannot delete yourself" disabled style="opacity:0.35; cursor:not-allowed;">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center text-muted-sm py-4">No admin users found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- ══════════════════════════════
     ADD ADMIN MODAL
══════════════════════════════ -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-user-plus me-2" style="color:var(--maroon)"></i>Add Admin User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../db/action/save_admin.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">

                    <!-- Avatar -->
                    <div class="avatar-upload-wrap">
                        <div class="avatar-preview" id="addAvatarPreview">AD</div>
                        <div>
                            <p style="font-size:13px;font-weight:500;margin:0 0 4px;">Profile Photo</p>
                            <p style="font-size:12px;color:var(--text-gray);margin:0 0 10px;">JPG or PNG, max 2MB</p>
                            <label class="avatar-upload-btn">
                                <i class="fa-solid fa-upload me-1"></i> Upload Photo
                                <input type="file" name="image" accept="image/*" style="display:none" onchange="previewAvatar(this,'addAvatarPreview','addInitials')">
                            </label>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">First Name <span class="req">*</span></label>
                            <input type="text" name="first_name" class="form-control" required placeholder="e.g. Juan" oninput="updateInitials('addAvatarPreview','addFirst','addLast')">
                            <input type="hidden" id="addFirst">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control" placeholder="Optional">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name <span class="req">*</span></label>
                            <input type="text" name="last_name" class="form-control" required placeholder="e.g. Dela Cruz" oninput="updateInitials('addAvatarPreview','addFirst','addLast')">
                            <input type="hidden" id="addLast">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="admin@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username <span class="req">*</span></label>
                            <input type="text" name="username" class="form-control" required placeholder="e.g. admin01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="req">*</span></label>
                            <div style="position:relative">
                                <input type="password" name="password" id="addPass" class="form-control" required placeholder="Min. 8 characters">
                                <span onclick="togglePass('addPass',this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:var(--text-gray);font-size:13px;">
                                    <i class="fa-solid fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password <span class="req">*</span></label>
                            <div style="position:relative">
                                <input type="password" name="confirm_password" id="addConfirm" class="form-control" required placeholder="Re-enter password">
                                <span onclick="togglePass('addConfirm',this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:var(--text-gray);font-size:13px;">
                                    <i class="fa-solid fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Birthday</label>
                            <input type="date" name="birthday" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status <span class="req">*</span></label>
                            <select name="user_status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn-cancel" data-bs-dismiss="modal">Cancel</a>
                    <button type="submit" class="btn-maroon"><i class="fa-solid fa-check me-1"></i>Add Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════════════════════════
     EDIT ADMIN MODAL
══════════════════════════════ -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-pen me-2" style="color:var(--maroon)"></i>Edit Admin User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../db/action/save_admin.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="user_id" id="editUserId">
                <div class="modal-body">

                    <!-- Avatar -->
                    <div class="avatar-upload-wrap">
                        <div class="avatar-preview" id="editAvatarPreview">AD</div>
                        <div>
                            <p style="font-size:13px;font-weight:500;margin:0 0 4px;">Profile Photo</p>
                            <p style="font-size:12px;color:var(--text-gray);margin:0 0 10px;">Upload new photo to replace existing</p>
                            <label class="avatar-upload-btn">
                                <i class="fa-solid fa-upload me-1"></i> Change Photo
                                <input type="file" name="image" accept="image/*" style="display:none" onchange="previewAvatar(this,'editAvatarPreview',null)">
                            </label>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">First Name <span class="req">*</span></label>
                            <input type="text" name="first_name" id="editFirst" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Middle Name</label>
                            <input type="text" name="middle_name" id="editMiddle" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name <span class="req">*</span></label>
                            <input type="text" name="last_name" id="editLast" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="editEmail" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username <span class="req">*</span></label>
                            <input type="text" name="username" id="editUsername" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">New Password <span style="color:var(--text-gray);font-weight:400;">(leave blank to keep)</span></label>
                            <div style="position:relative">
                                <input type="password" name="password" id="editPass" class="form-control" placeholder="Enter new password">
                                <span onclick="togglePass('editPass',this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:var(--text-gray);font-size:13px;">
                                    <i class="fa-solid fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm New Password</label>
                            <div style="position:relative">
                                <input type="password" name="confirm_password" id="editConfirm" class="form-control" placeholder="Re-enter new password">
                                <span onclick="togglePass('editConfirm',this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:var(--text-gray);font-size:13px;">
                                    <i class="fa-solid fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select name="gender" id="editGender" class="form-select">
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Birthday</label>
                            <input type="date" name="birthday" id="editBirthday" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status <span class="req">*</span></label>
                            <select name="user_status" id="editStatus" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn-cancel" data-bs-dismiss="modal">Cancel</a>
                    <button type="submit" class="btn-maroon"><i class="fa-solid fa-check me-1"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════════════════════════
     DELETE CONFIRM MODAL
══════════════════════════════ -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center" style="padding:28px 24px">
                <div class="delete-warn-icon">
                    <i class="fa-solid fa-trash"></i>
                </div>
                <p style="font-size:15px;font-weight:600;color:#1A1A1A;margin-bottom:6px;">Delete Admin?</p>
                <p style="font-size:13px;color:var(--text-gray);margin-bottom:20px;">
                    Are you sure you want to delete <strong id="deleteAdminName"></strong>? This action cannot be undone.
                </p>
                <form action="../db/action/save_admin.php" method="POST" style="display:flex;gap:8px;justify-content:center">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal" style="padding:8px 20px">Cancel</button>
                    <button type="submit" style="background:#C0392B;color:white;border:none;border-radius:10px;font-family:'Poppins',sans-serif;font-size:13px;font-weight:500;padding:8px 20px;cursor:pointer;transition:0.2s">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ── Filter ── */
function applyFilters() {
    const status = document.getElementById('statusFilter').value;
    document.querySelectorAll('#adminTable tr').forEach(row => {
        const rowStatus = row.dataset.status;
        row.style.display = (status === 'all' || rowStatus === status) ? '' : 'none';
    });
}

/* ── Add modal ── */
function openAddModal() {
    document.getElementById('addAvatarPreview').innerHTML = 'AD';
    new bootstrap.Modal(document.getElementById('addModal')).show();
}

/* ── Edit modal ── */
function openEditModal(data) {
    document.getElementById('editUserId').value   = data.user_id;
    document.getElementById('editFirst').value    = data.first_name   || '';
    document.getElementById('editMiddle').value   = data.middle_name  || '';
    document.getElementById('editLast').value     = data.last_name    || '';
    document.getElementById('editEmail').value    = data.email        || '';
    document.getElementById('editUsername').value = data.username     || '';
    document.getElementById('editGender').value   = data.gender       || '';
    document.getElementById('editBirthday').value = data.birthday     || '';
    document.getElementById('editStatus').value   = data.user_status  || 'active';
    document.getElementById('editPass').value     = '';
    document.getElementById('editConfirm').value  = '';

    // Avatar preview
    const prev = document.getElementById('editAvatarPreview');
    if (data.image_url) {
        prev.innerHTML = `<img src="${data.image_url}" alt="">`;
    } else {
        const initials = (data.first_name.charAt(0) + data.last_name.charAt(0)).toUpperCase();
        prev.innerHTML = initials;
    }

    new bootstrap.Modal(document.getElementById('editModal')).show();
}

/* ── Delete modal ── */
function openDeleteModal(id, name) {
    document.getElementById('deleteUserId').value = id;
    document.getElementById('deleteAdminName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

/* ── Avatar preview ── */
function previewAvatar(input, previewId) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById(previewId).innerHTML = `<img src="${e.target.result}" alt="" style="width:64px;height:64px;border-radius:50%;object-fit:cover;">`;
    };
    reader.readAsDataURL(input.files[0]);
}

/* ── Initials live update (Add modal) ── */
document.querySelectorAll('[name="first_name"], [name="last_name"]').forEach(el => {
    el.addEventListener('input', () => {
        const form = el.closest('form');
        const f = (form.querySelector('[name="first_name"]').value.charAt(0) || 'A').toUpperCase();
        const l = (form.querySelector('[name="last_name"]').value.charAt(0)  || 'D').toUpperCase();
        const prev = form.querySelector('.avatar-preview');
        if (prev && !prev.querySelector('img')) prev.textContent = f + l;
    });
});

/* ── Password toggle ── */
function togglePass(inputId, icon) {
    const inp = document.getElementById(inputId);
    const isText = inp.type === 'text';
    inp.type = isText ? 'password' : 'text';
    icon.querySelector('i').className = isText ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
}
</script>
</body>
</html>