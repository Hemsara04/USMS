<?php
require_once __DIR__ . '/../config/auth_guard.php';
requireRole(['System_Admin', 'Admin']);
require_once __DIR__ . '/../models/Admin.php';

$activeAdminPage = 'users';
$adminModel = new Admin();

// Retrieve live stats
$stats = $adminModel->getStats();

// Role and search filters
$filterRole = trim($_GET['role'] ?? '');
$search     = trim($_GET['q'] ?? '');

// Retrieve data
$users       = $adminModel->getAllUsers($filterRole, $search);
$pendingApps = $adminModel->getPendingVerifications(10);

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$adminName     = !empty($_SESSION['username']) ? ucwords(str_replace('_', ' ', $_SESSION['username'])) : 'System Administrator';

$roleLabels = [
    'System_Admin'       => 'System Admin',
    'Coach'              => 'Coach',
    'Athlete'            => 'Athlete',
    'TIC'                => 'Teacher In Charge',
    'Medical_Officer'    => 'Medical Officer',
    'President'          => 'Sports Council President',
    'Physiotherapist'    => 'Physiotherapist',
    'Facility_Manager'   => 'Facility Manager'
];

function getInitials($name) {
    $words = explode(' ', trim(str_replace(['_', '-'], ' ', $name)));
    $initials = '';
    foreach ($words as $w) {
        if (!empty($w)) {
            $initials .= strtoupper($w[0]);
            if (strlen($initials) >= 2) break;
        }
    }
    return $initials ?: 'US';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Management | USMS Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Material+Symbols+Outlined">
</head>
<body>
<div class="admin-shell">
    <?php require __DIR__ . '/partials/admin_sidebar.php'; ?>
    <main class="admin-main">
        <header class="admin-topbar">
            <form method="GET" action="admin_user_management.php" class="admin-search" style="margin:0;">
                <span class="material-symbols-outlined">search</span>
                <input type="search" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search records..." aria-label="Search records">
                <?php if (!empty($filterRole)): ?>
                    <input type="hidden" name="role" value="<?= htmlspecialchars($filterRole) ?>">
                <?php endif; ?>
            </form>
            <div class="admin-top-actions">
                <a href="profile.php" class="admin-icon-button" aria-label="Profile" title="My Profile" style="text-decoration:none; color:inherit;">
                    <span class="material-symbols-outlined">account_circle</span>
                </a>
                <a href="../controllers/AuthController.php?action=logout" class="admin-logout" style="text-decoration:none; color:inherit;">
                    <span class="material-symbols-outlined">logout</span> Logout
                </a>
            </div>
        </header>

        <section class="admin-content">
            <?php if ($flashSuccess): ?>
                <div class="admin-alert success-alert" role="alert">
                    <span class="material-symbols-outlined">check_circle</span>
                    <div><?= htmlspecialchars($flashSuccess) ?></div>
                    <button type="button" class="alert-dismiss" onclick="this.parentElement.remove()" aria-label="Close">✕</button>
                </div>
            <?php endif; ?>

            <?php if ($flashError): ?>
                <div class="admin-alert error-alert" role="alert">
                    <span class="material-symbols-outlined">error</span>
                    <div><?= htmlspecialchars($flashError) ?></div>
                    <button type="button" class="alert-dismiss" onclick="this.parentElement.remove()" aria-label="Close">✕</button>
                </div>
            <?php endif; ?>

            <div class="admin-page-header">
                <div>
                    <h1>User Management</h1>
                    <p>Manage system access, verify new athlete registrations, and provision staff accounts across university athletics.</p>
                </div>
                <button type="button" class="admin-primary-button" onclick="openProvisionModal()">
                    <span class="material-symbols-outlined">person_add</span> Provision User
                </button>
            </div>

            <div class="stats-grid">
                <article class="metric">
                    <span>Total Users</span>
                    <strong><?= number_format($stats['total_users']) ?></strong>
                    <small>System-wide accounts</small>
                </article>
                <article class="metric">
                    <span>Active Athletes</span>
                    <strong><?= number_format($stats['active_athletes']) ?></strong>
                    <small>Varsity &amp; Club rosters</small>
                </article>
                <article class="metric">
                    <span>Pending Approvals</span>
                    <strong style="<?= $stats['pending_approvals'] > 0 ? 'color:var(--admin-danger);' : '' ?>"><?= number_format($stats['pending_approvals']) ?></strong>
                    <small class="<?= $stats['pending_approvals'] > 0 ? 'urgent' : '' ?>">
                        <?= $stats['pending_approvals'] > 0 ? 'Requires attention' : 'Queue clear' ?>
                    </small>
                </article>
                <article class="metric dark">
                    <span>Staff Accounts</span>
                    <strong><?= number_format($stats['staff_accounts']) ?></strong>
                    <small>Admins, Coaches &amp; Officers</small>
                </article>
            </div>

            <div class="users-layout">
                <!-- Verification Queue Column -->
                <section class="admin-card">
                    <div class="admin-card-header">
                        <div>
                            <h2>Verification Queue</h2>
                            <p>New registrations awaiting review</p>
                        </div>
                        <span class="admin-chip <?= count($pendingApps) > 0 ? 'danger' : 'neutral' ?>">
                            <?= count($pendingApps) ?> pending
                        </span>
                    </div>
                    <div class="verification-list">
                        <?php if (empty($pendingApps)): ?>
                            <div class="queue-empty-state">
                                <span class="material-symbols-outlined">verified</span>
                                <p>All registration requests have been processed!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($pendingApps as $app): ?>
                                <article class="verification-item">
                                    <div class="person-row">
                                        <span class="initials teal"><?= getInitials($app['full_name'] ?: 'ST') ?></span>
                                        <div>
                                            <strong><?= htmlspecialchars($app['full_name'] ?: 'Athlete') ?></strong>
                                            <small>Athlete · <?= htmlspecialchars($app['sport_name']) ?></small>
                                        </div>
                                        <time title="<?= htmlspecialchars($app['applied_date']) ?>">
                                            <?= date('M d', strtotime($app['applied_date'])) ?>
                                        </time>
                                    </div>
                                    <div class="verification-meta">
                                        <span>Index <b><?= htmlspecialchars($app['index_number']) ?></b></span>
                                        <span>Faculty <b><?= htmlspecialchars($app['faculty']) ?></b></span>
                                    </div>
                                    <div class="review-actions">
                                        <a href="../controllers/AdminController.php?action=verify_app&id=<?= $app['application_id'] ?>&decision=Approved"
                                           class="approve-link"
                                           onclick="return confirm('Approve sports registration for <?= htmlspecialchars($app['full_name'], ENT_QUOTES) ?>?');">
                                            ✓ Approve
                                        </a>
                                        <a href="../controllers/AdminController.php?action=verify_app&id=<?= $app['application_id'] ?>&decision=Rejected"
                                           class="reject-link"
                                           onclick="return confirm('Reject sports registration for <?= htmlspecialchars($app['full_name'], ENT_QUOTES) ?>?');">
                                            ✕ Reject
                                        </a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Directory Column -->
                <section class="admin-card">
                    <div class="admin-card-header directory-header">
                        <div>
                            <h2>Directory</h2>
                            <p>All registered platform users</p>
                        </div>
                        <form method="GET" action="admin_user_management.php" class="directory-filter-form">
                            <select name="role" onchange="this.form.submit()" aria-label="Filter by role" class="directory-role-select">
                                <option value="">All Roles</option>
                                <?php foreach ($roleLabels as $rKey => $rLabel): ?>
                                    <option value="<?= $rKey ?>" <?= $filterRole === $rKey ? 'selected' : '' ?>><?= $rLabel ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="directory-search">
                                <span class="material-symbols-outlined">search</span>
                                <input type="search" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search ID, name, role..." aria-label="Search users">
                            </div>
                            <button type="submit" class="admin-secondary-button" style="min-height:38px; padding:0 12px;">Filter</button>
                            <?php if (!empty($filterRole) || !empty($search)): ?>
                                <a href="admin_user_management.php" class="admin-secondary-button" style="text-decoration:none; min-height:38px; padding:0 12px; display:inline-flex; align-items:center;">Reset</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Contact Details</th>
                                    <th>Status</th>
                                    <th style="text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align:center; padding:32px; color:var(--admin-muted);">
                                            No users found matching your search criteria.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $u): ?>
                                        <tr>
                                            <td>
                                                <div style="display:flex; align-items:center; gap:10px;">
                                                    <span class="user-avatar-small"><?= getInitials($u['athlete_name'] ?: $u['username']) ?></span>
                                                    <div>
                                                        <strong><?= htmlspecialchars($u['athlete_name'] ?: $u['username']) ?></strong>
                                                        <small class="subline">
                                                            ID #<?= $u['user_id'] ?> · @<?= htmlspecialchars($u['username']) ?>
                                                            <?php if (!empty($u['index_number'])): ?>
                                                                (<?= htmlspecialchars($u['index_number']) ?>)
                                                            <?php endif; ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="role-badge <?= strtolower(str_replace('_', '-', $u['role'])) ?>">
                                                    <?= htmlspecialchars($roleLabels[$u['role']] ?? $u['role']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="font-size:13px;"><?= htmlspecialchars($u['email']) ?></div>
                                                <small class="subline"><?= htmlspecialchars($u['phone_number'] ?: '—') ?></small>
                                                <?php if (!empty($u['faculty'])): ?>
                                                    <small class="faculty-tag"><?= htmlspecialchars($u['faculty']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="admin-chip success">Active</span>
                                            </td>
                                            <td style="text-align:right; white-space:nowrap;">
                                                <button type="button" class="row-action edit-btn" onclick='openEditModal(<?= json_encode($u, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                                    Edit
                                                </button>
                                                <?php if ($u['user_id'] != $currentUserId): ?>
                                                    <button type="button" class="row-action delete-btn" onclick='openDeleteModal(<?= $u['user_id'] ?>, "<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>")'>
                                                        Delete
                                                    </button>
                                                <?php else: ?>
                                                    <span style="font-size:12px; color:#98a2b3; margin-left:6px;">(You)</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="admin-empty-note">
                        Showing <?= count($users) ?> of <?= number_format($stats['total_users']) ?> registered accounts
                    </div>
                </section>
            </div>
        </section>
    </main>
</div>

<!-- ================= PROVISION USER MODAL ================= -->
<div id="provisionModal" class="modal-backdrop" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h3>Provision New Account</h3>
                <p>Create credentials and grant system role privileges.</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('provisionModal')">✕</button>
        </div>
        <form method="POST" action="../controllers/AdminController.php?action=create_user" class="modal-form">
            <div class="form-grid">
                <div class="admin-field">
                    <label for="createUsername">Username *</label>
                    <input id="createUsername" type="text" name="username" required placeholder="e.g. coach_kasun">
                </div>
                <div class="admin-field">
                    <label for="createEmail">Email Address *</label>
                    <input id="createEmail" type="email" name="email" required placeholder="kasun@uoc.ac.lk">
                </div>
                <div class="admin-field">
                    <label for="createRole">Role *</label>
                    <select id="createRole" name="role" required onchange="toggleAthleteFields('provision', this.value)">
                        <?php foreach ($roleLabels as $rKey => $rLabel): ?>
                            <option value="<?= $rKey ?>" <?= $rKey === 'Coach' ? 'selected' : '' ?>><?= $rLabel ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-field">
                    <label for="createPhone">Phone Number</label>
                    <input id="createPhone" type="text" name="phone_number" value="+94 77 123 4567" placeholder="+94 77 123 4567">
                </div>
                <div class="admin-field full-col">
                    <label for="createPassword">Initial Password *</label>
                    <input id="createPassword" type="password" name="password" required value="password123">
                    <small style="color:var(--admin-muted); font-size:12px;">Default temporary password is "password123". User can change it upon login.</small>
                </div>
            </div>

            <!-- Dynamic Athlete Fields -->
            <div id="provisionAthleteFields" class="athlete-subform" style="display:none;">
                <h4 style="margin:12px 0 8px; font-size:14px; color:var(--admin-teal);">Athlete Profile Information</h4>
                <div class="form-grid">
                    <div class="admin-field">
                        <label for="createFullName">Full Name</label>
                        <input id="createFullName" type="text" name="full_name" placeholder="Kasun Bandara">
                    </div>
                    <div class="admin-field">
                        <label for="createIndex">Index Number</label>
                        <input id="createIndex" type="text" name="index_number" placeholder="CS/2023/102">
                    </div>
                    <div class="admin-field">
                        <label for="createFaculty">Faculty</label>
                        <input id="createFaculty" type="text" name="faculty" value="Science" placeholder="Science / UCSC / Arts">
                    </div>
                    <div class="admin-field">
                        <label for="createAcademicYear">Academic Year</label>
                        <select id="createAcademicYear" name="academic_year">
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="admin-secondary-button" onclick="closeModal('provisionModal')">Cancel</button>
                <button type="submit" class="admin-primary-button">Provision User</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= EDIT USER MODAL ================= -->
<div id="editUserModal" class="modal-backdrop" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h3>Edit User Account</h3>
                <p>Modify role assignments, contact details, or reset password.</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('editUserModal')">✕</button>
        </div>
        <form method="POST" action="../controllers/AdminController.php?action=update_user" class="modal-form">
            <input type="hidden" id="editUserId" name="user_id" value="">
            <div class="form-grid">
                <div class="admin-field">
                    <label for="editUsername">Username *</label>
                    <input id="editUsername" type="text" name="username" required>
                </div>
                <div class="admin-field">
                    <label for="editEmail">Email Address *</label>
                    <input id="editEmail" type="email" name="email" required>
                </div>
                <div class="admin-field">
                    <label for="editRole">Role *</label>
                    <select id="editRole" name="role" required onchange="toggleAthleteFields('edit', this.value)">
                        <?php foreach ($roleLabels as $rKey => $rLabel): ?>
                            <option value="<?= $rKey ?>"><?= $rLabel ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-field">
                    <label for="editPhone">Phone Number</label>
                    <input id="editPhone" type="text" name="phone_number">
                </div>
                <div class="admin-field full-col">
                    <label for="editPassword">Reset Password (Optional)</label>
                    <input id="editPassword" type="password" name="password" placeholder="Leave blank to keep unchanged">
                </div>
            </div>

            <!-- Dynamic Athlete Fields -->
            <div id="editAthleteFields" class="athlete-subform" style="display:none;">
                <h4 style="margin:12px 0 8px; font-size:14px; color:var(--admin-teal);">Athlete Profile Information</h4>
                <div class="form-grid">
                    <div class="admin-field">
                        <label for="editFullName">Full Name</label>
                        <input id="editFullName" type="text" name="full_name">
                    </div>
                    <div class="admin-field">
                        <label for="editIndex">Index Number</label>
                        <input id="editIndex" type="text" name="index_number">
                    </div>
                    <div class="admin-field full-col">
                        <label for="editFaculty">Faculty</label>
                        <input id="editFaculty" type="text" name="faculty">
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="admin-secondary-button" onclick="closeModal('editUserModal')">Cancel</button>
                <button type="submit" class="admin-primary-button">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= DELETE USER MODAL ================= -->
<div id="deleteUserModal" class="modal-backdrop" style="display:none;">
    <div class="modal-box delete-box">
        <div class="modal-header">
            <div>
                <h3 style="color:var(--admin-danger);">Delete User Account</h3>
                <p>Permanent administrative action</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('deleteUserModal')">✕</button>
        </div>
        <form method="POST" action="../controllers/AdminController.php?action=delete_user" class="modal-form">
            <input type="hidden" id="deleteUserId" name="user_id" value="">
            <p style="margin:16px 0; color:#344054; line-height:1.6;">
                Are you sure you want to permanently delete user account <strong id="deleteUserDisplay"></strong> (ID #<span id="deleteUserIdDisplay"></span>)?
            </p>
            <div class="delete-warning-box">
                <span class="material-symbols-outlined" style="color:var(--admin-danger);">warning</span>
                <span>This user's login access and associated profile data will be permanently removed. This action cannot be undone.</span>
            </div>
            <div class="modal-actions">
                <button type="button" class="admin-secondary-button" onclick="closeModal('deleteUserModal')">Cancel</button>
                <button type="submit" class="admin-primary-button danger-btn">Yes, Delete Account</button>
            </div>
        </form>
    </div>
</div>

<style>
    .stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:18px; }
    .metric { min-height:145px; display:flex; flex-direction:column; justify-content:space-between; padding:22px; background:#fff; border:1px solid var(--admin-border); border-radius:12px; }
    .metric > span { color:#475467; font-size:12px; letter-spacing:.08em; text-transform:uppercase; font-weight:600; }
    .metric strong { font-size:36px; font-weight:750; color:#101828; }
    .metric small { color:var(--admin-teal); font-weight:500; }
    .metric small.urgent { color:var(--admin-danger); font-weight:600; }
    .metric.dark { color:#fff; background:var(--admin-navy); }
    .metric.dark > span, .metric.dark small { color:#cbd5e1; }
    .metric.dark strong { color:#fff; }

    .users-layout { display:grid; grid-template-columns:minmax(280px,.78fr) minmax(620px,1.8fr); gap:18px; }
    .verification-list { padding:14px; max-height:640px; overflow-y:auto; }
    .verification-item { padding:16px; border-radius:10px; background:#f8fafb; border:1px solid var(--admin-border); }
    .verification-item + .verification-item { margin-top:12px; }
    .person-row { display:flex; align-items:center; gap:10px; }
    .person-row time { margin-left:auto; color:#475467; font-size:12px; }
    .person-row strong { display:block; color:#101828; font-size:14px; }
    .person-row small { display:block; margin-top:3px; color:#475467; font-size:11px; text-transform:uppercase; }
    .initials { width:38px; height:38px; display:grid; place-items:center; border-radius:50%; background:#d0d5dd; font-weight:700; font-size:13px; flex-shrink:0; }
    .initials.teal { color:#086c70; background:#c5f5f5; }
    .user-avatar-small { width:34px; height:34px; display:grid; place-items:center; border-radius:50%; background:#e4e7ec; color:#344054; font-weight:700; font-size:12px; flex-shrink:0; }

    .verification-meta { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin:14px 0; padding:10px; background:#fff; border-radius:7px; font-size:11px; border:1px solid #eaecf0; }
    .verification-meta b { display:block; margin-top:4px; font-size:12px; color:#101828; }
    .review-actions { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
    .approve-link, .reject-link { min-height:35px; border-radius:6px; font-weight:700; font-size:13px; display:grid; place-items:center; text-decoration:none; transition:opacity .15s; }
    .approve-link { color:#fff; background:var(--admin-navy); }
    .approve-link:hover { opacity:0.9; }
    .reject-link { color:#344054; background:#e4e7ec; }
    .reject-link:hover { background:#d0d5dd; }

    .queue-empty-state { text-align:center; padding:36px 16px; color:var(--admin-muted); }
    .queue-empty-state span { font-size:36px; color:var(--admin-teal); display:block; margin-bottom:8px; }

    .directory-header { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
    .directory-filter-form { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .directory-role-select { min-height:38px; padding:0 10px; border:1px solid var(--admin-border); border-radius:8px; background:#fff; font-size:13px; outline:none; }
    .directory-search { display:flex; align-items:center; gap:8px; padding:0 12px; border-radius:8px; background:#f7f8fa; border:1px solid var(--admin-border); }
    .directory-search input { height:36px; border:0; outline:0; background:transparent; font-size:13px; width:160px; }
    .subline { display:block; margin-top:3px; color:#667085; font-size:12px; }
    .faculty-tag { display:inline-block; margin-top:3px; padding:1px 6px; background:#f2f4f7; border-radius:4px; color:#475467; font-size:11px; }

    /* Role Badges */
    .role-badge { display:inline-flex; align-items:center; padding:3px 8px; border-radius:6px; font-size:11.5px; font-weight:700; text-transform:uppercase; letter-spacing:.02em; }
    .role-badge.athlete { color:#075f65; background:#cbf4f5; }
    .role-badge.coach { color:#175cd3; background:#eff8ff; }
    .role-badge.system-admin { color:#fff; background:var(--admin-navy); }
    .role-badge.medical-officer, .role-badge.physiotherapist { color:#067647; background:#ecfdf3; }
    .role-badge.tic, .role-badge.president { color:#b54708; background:#fffaeb; }
    .role-badge.facility-manager { color:#5925dc; background:#f4f3ff; }

    .row-action { border:0; background:transparent; font-weight:700; cursor:pointer; font-size:13px; padding:4px 6px; }
    .edit-btn { color:var(--admin-teal); }
    .edit-btn:hover { text-decoration:underline; }
    .delete-btn { color:var(--admin-danger); margin-left:8px; }
    .delete-btn:hover { text-decoration:underline; }

    /* Flash Alerts */
    .admin-alert { display:flex; align-items:center; gap:12px; padding:14px 18px; border-radius:8px; margin-bottom:20px; font-size:14px; font-weight:500; }
    .success-alert { background:#ecfdf3; color:#067647; border:1px solid #abefc6; }
    .error-alert { background:#fef3f2; color:#b42318; border:1px solid #fecdca; }
    .alert-dismiss { margin-left:auto; border:0; background:transparent; font-size:16px; cursor:pointer; color:inherit; }

    /* Modal Dialogs */
    .modal-backdrop { position:fixed; inset:0; z-index:999; background:rgba(7,22,45,.65); backdrop-filter:blur(3px); display:grid; place-items:center; padding:20px; overflow-y:auto; }
    .modal-box { width:100%; max-width:540px; background:#fff; border-radius:14px; box-shadow:0 20px 40px rgba(0,0,0,.25); overflow:hidden; animation:modalFade .2s ease-out; }
    .modal-box.delete-box { max-width:440px; }
    @keyframes modalFade { from { opacity:0; transform:translateY(-12px); } to { opacity:1; transform:translateY(0); } }
    .modal-header { display:flex; align-items:flex-start; justify-content:space-between; gap:14px; padding:20px 24px; border-bottom:1px solid var(--admin-border); }
    .modal-header h3 { margin:0 0 4px; font-size:18px; }
    .modal-header p { margin:0; font-size:13px; color:var(--admin-muted); }
    .modal-close-btn { border:0; background:transparent; font-size:18px; cursor:pointer; color:var(--admin-muted); }
    .modal-form { padding:22px 24px; }
    .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .full-col { grid-column:span 2; }
    .athlete-subform { margin-top:14px; padding:14px; background:#f8fafb; border:1px dashed #d0d5dd; border-radius:8px; }
    .modal-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:22px; padding-top:16px; border-top:1px solid var(--admin-border); }
    .danger-btn { background:var(--admin-danger)!important; border-color:var(--admin-danger)!important; }
    .delete-warning-box { display:flex; align-items:center; gap:10px; padding:12px; background:#fef3f2; border:1px solid #fee4e2; border-radius:8px; font-size:12.5px; color:#b42318; line-height:1.45; }

    @media(max-width:1200px){.stats-grid{grid-template-columns:repeat(2,1fr)}.users-layout{grid-template-columns:1fr}.admin-card{min-width:0}}
    @media(max-width:600px){.stats-grid{grid-template-columns:1fr}.form-grid{grid-template-columns:1fr}.full-col{grid-column:span 1}.admin-topbar{padding:0 14px}.admin-logout{font-size:0}.admin-logout .material-symbols-outlined{font-size:22px}}
</style>

<script>
function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'grid';
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
}

function openProvisionModal() {
    document.getElementById('createUsername').value = '';
    document.getElementById('createEmail').value = '';
    document.getElementById('createPassword').value = 'password123';
    document.getElementById('createRole').value = 'Coach';
    toggleAthleteFields('provision', 'Coach');
    openModal('provisionModal');
}

function openEditModal(user) {
    document.getElementById('editUserId').value = user.user_id;
    document.getElementById('editUsername').value = user.username || '';
    document.getElementById('editEmail').value = user.email || '';
    document.getElementById('editRole').value = user.role || 'Athlete';
    document.getElementById('editPhone').value = user.phone_number || '';
    document.getElementById('editPassword').value = '';
    
    // Athlete fields
    document.getElementById('editFullName').value = user.athlete_name || '';
    document.getElementById('editIndex').value = user.index_number || '';
    document.getElementById('editFaculty').value = user.faculty || '';
    
    toggleAthleteFields('edit', user.role);
    openModal('editUserModal');
}

function openDeleteModal(userId, username) {
    document.getElementById('deleteUserId').value = userId;
    document.getElementById('deleteUserIdDisplay').textContent = userId;
    document.getElementById('deleteUserDisplay').textContent = username;
    openModal('deleteUserModal');
}

function toggleAthleteFields(prefix, role) {
    const container = document.getElementById(prefix + 'AthleteFields');
    if (container) {
        container.style.display = (role === 'Athlete') ? 'block' : 'none';
    }
}

// Close modals when clicking backdrop
window.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-backdrop')) {
        e.target.style.display = 'none';
    }
});
</script>
</body>
</html>
