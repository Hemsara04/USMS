<?php
require_once __DIR__ . '/../config/auth_guard.php';
requireRole(['System_Admin', 'Admin']);
require_once __DIR__ . '/../models/Admin.php';

$activeAdminPage = 'users';
$adminModel = new Admin();

// Retrieve live stats
$stats = $adminModel->getStats();

// Role and search filters (Server-side fallback)
$filterRole = trim($_GET['role'] ?? '');
$search     = trim($_GET['q'] ?? '');

// Retrieve all users (with complete athletic profile join)
$users       = $adminModel->getAllUsers($filterRole, $search);
$pendingApps = $adminModel->getPendingVerifications(10);

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$adminName     = !empty($_SESSION['username']) ? ucwords(str_replace('_', ' ', $_SESSION['username'])) : 'System Administrator';

// Strictly the 6 platform roles
$roleLabels = [
    'System_Admin'    => 'System Admin',
    'TIC'             => 'Teacher In Charge',
    'Coach'           => 'Coach',
    'Athlete'         => 'Athlete',
    'Medical_Officer' => 'Medical Officer',
    'President'       => 'Sports Council President'
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
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dashboard">
    <?php require __DIR__ . '/partials/admin_sidebar.php'; ?>
    <div class="main-wrapper">
        <?php 
        $pageTitle = 'User Management';
        require __DIR__ . '/partials/tic_header.php'; 
        ?>
        <div class="page-content">
            <?php if ($flashSuccess): ?>
                <div class="admin-alert success-alert" role="alert">
                    <span class="material-symbols-outlined">check_circle</span>
                    <div><?= htmlspecialchars($flashSuccess) ?></div>
                    <button type="button" class="alert-dismiss" onclick="this.parentElement.remove()" aria-label="Close">✕</button>
                </div>
            <?php endif; ?>

            <?php if ($flashError): ?>
                <div class="admin-alert error-alert" role="alert">
                    <span class="material-symbols-outlined">warning</span>
                    <div><?= htmlspecialchars($flashError) ?></div>
                    <button type="button" class="alert-dismiss" onclick="this.parentElement.remove()" aria-label="Close">✕</button>
                </div>
            <?php endif; ?>

            <!-- Client-side error banner for validation guards -->
            <div id="clientValidationAlert" class="admin-alert error-alert" style="display:none;" role="alert">
                <span class="material-symbols-outlined">warning</span>
                <div id="clientValidationMsg"></div>
                <button type="button" class="alert-dismiss" onclick="this.parentElement.style.display='none'" aria-label="Close">✕</button>
            </div>

            <div class="admin-page-header">
                <div>
                    <h1>User Management</h1>
                    <p>Manage system credentials, verify sports applicants, and provision university sports personnel across all 6 roles.</p>
                </div>
                <button type="button" class="admin-primary-button" onclick="openProvisionModal()">
                    <span class="material-symbols-outlined">person_add</span> Provision User
                </button>
            </div>

            <!-- Proportional 4-Stat Metric Grid -->
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
                        <?= $stats['pending_approvals'] > 0 ? 'Action required' : 'Queue clear' ?>
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
                        <span class="admin-chip <?= count($pendingApps) > 0 ? 'warning' : 'neutral' ?>">
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

                <!-- Directory Column with Live Search, Filter & Pagination -->
                <section class="admin-card directory-card">
                    <div class="admin-card-header directory-header">
                        <div>
                            <h2>User Directory</h2>
                            <p>All platform accounts &amp; athletic dossiers</p>
                        </div>
                        <div class="directory-filter-bar">
                            <select id="roleFilterSelect" class="filter-select" aria-label="Filter by role" onchange="handleFilterChange()">
                                <option value="">All Roles (<?= count($roleLabels) ?>)</option>
                                <?php foreach ($roleLabels as $rKey => $rLabel): ?>
                                    <option value="<?= $rKey ?>" <?= $filterRole === $rKey ? 'selected' : '' ?>><?= $rLabel ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="directory-search-input">
                                <span class="material-symbols-outlined">search</span>
                                <input type="search" id="directorySearchInput" value="<?= htmlspecialchars($search) ?>" placeholder="Search name, index, email, role..." aria-label="Search users" oninput="handleFilterChange()">
                            </div>
                            <button type="button" class="admin-secondary-button reset-filter-btn" onclick="resetDirectoryFilters()">Reset</button>
                        </div>
                    </div>

                    <div class="admin-table-wrap">
                        <table class="admin-table" id="usersTable">
                            <thead>
                                <tr>
                                    <th>User / Athlete</th>
                                    <th>Role</th>
                                    <th>Contact &amp; Affiliation</th>
                                    <th>Status</th>
                                    <th style="text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <?php if (empty($users)): ?>
                                    <tr class="no-records-row">
                                        <td colspan="5" style="text-align:center; padding:32px; color:var(--admin-muted);">
                                            No users registered in the system.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $u): ?>
                                        <?php 
                                            $searchBlob = strtolower(implode(' ', [
                                                $u['user_id'],
                                                $u['username'],
                                                $u['email'],
                                                $u['phone_number'] ?? '',
                                                $u['role'],
                                                $roleLabels[$u['role']] ?? $u['role'],
                                                $u['athlete_name'] ?? '',
                                                $u['index_number'] ?? '',
                                                $u['faculty'] ?? ''
                                            ]));
                                        ?>
                                        <tr class="user-row" 
                                            data-role="<?= htmlspecialchars($u['role']) ?>" 
                                            data-search="<?= htmlspecialchars($searchBlob) ?>">
                                            <td>
                                                <div style="display:flex; align-items:center; gap:10px;">
                                                    <span class="user-avatar-small"><?= getInitials($u['athlete_name'] ?: $u['username']) ?></span>
                                                    <div>
                                                        <strong><?= htmlspecialchars($u['athlete_name'] ?: $u['username']) ?></strong>
                                                        <small class="subline">
                                                            ID #<?= $u['user_id'] ?> · @<?= htmlspecialchars($u['username']) ?>
                                                            <?php if (!empty($u['index_number'])): ?>
                                                                · <span style="font-weight:600; color:var(--admin-teal);"><?= htmlspecialchars($u['index_number']) ?></span>
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
                                                <div style="font-size:13px; font-weight:500;"><?= htmlspecialchars($u['email']) ?></div>
                                                <small class="subline"><?= htmlspecialchars($u['phone_number'] ?: '—') ?></small>
                                                <?php if (!empty($u['faculty'])): ?>
                                                    <small class="faculty-tag"><?= htmlspecialchars($u['faculty']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="admin-chip success">Active</span>
                                            </td>
                                            <td style="text-align:right; white-space:nowrap;">
                                                <button type="button" class="row-action view-btn" title="View Full Dossier" onclick='openViewModal(<?= json_encode($u, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                                    View
                                                </button>
                                                <button type="button" class="row-action edit-btn" title="Edit User" onclick='openEditModal(<?= json_encode($u, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                                    Edit
                                                </button>
                                                <?php if ($u['user_id'] != $currentUserId): ?>
                                                    <button type="button" class="row-action delete-btn" title="Delete User" onclick='openDeleteModal(<?= $u['user_id'] ?>, "<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>")'>
                                                        Delete
                                                    </button>
                                                <?php else: ?>
                                                    <span style="font-size:11.5px; color:#98a2b3; margin-left:6px;">(You)</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Interactive Table Pagination Controls -->
                    <div class="directory-pagination">
                        <div class="pagination-info" id="paginationInfo">
                            Showing <?= count($users) ?> accounts
                        </div>
                        <div class="pagination-controls" id="paginationControls">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

<!-- ================= PROVISION USER MODAL ================= -->
<div id="provisionModal" class="modal-backdrop" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h3>Provision New Account</h3>
                <p>Register a staff officer or student athlete into the university sports system.</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('provisionModal')">✕</button>
        </div>
        <form method="POST" action="../controllers/AdminController.php?action=create_user" class="modal-form" id="provisionForm" onsubmit="return validateProvisionForm(event)">
            <!-- Account Credentials -->
            <div class="form-grid">
                <div class="admin-field">
                    <label for="createUsername">Username *</label>
                    <input id="createUsername" type="text" name="username" required placeholder="e.g. coach_kasun" autocomplete="off">
                    <small class="field-hint">Minimum 3 characters, alphanumeric</small>
                </div>
                <div class="admin-field">
                    <label for="createEmail">Email Address *</label>
                    <input id="createEmail" type="email" name="email" required placeholder="e.g. kasun@uoc.ac.lk or personal@gmail.com" autocomplete="off">
                    <small class="field-hint">Accepts university or personal email</small>
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
                    <label for="createPhone">Contact Phone</label>
                    <input id="createPhone" type="text" name="phone_number" placeholder="e.g. +94 77 123 4567" autocomplete="off">
                </div>
                <div class="admin-field">
                    <label for="createPassword">Initial Password *</label>
                    <input id="createPassword" type="password" name="password" required placeholder="Minimum 8 characters" minlength="8" autocomplete="new-password">
                    <small class="field-hint">Temporary password (min 8 characters)</small>
                </div>
                <div class="admin-field">
                    <label for="createSecurityQuestion">Security Question *</label>
                    <select id="createSecurityQuestion" name="security_question" required>
                        <option value="What is your favorite sport?" selected>What is your favorite sport?</option>
                        <option value="What was the name of your first school?">What was the name of your first school?</option>
                        <option value="What is your birthplace / hometown?">What is your birthplace / hometown?</option>
                        <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                        <option value="What was your childhood nickname?">What was your childhood nickname?</option>
                    </select>
                    <small class="field-hint">For self-service password recovery</small>
                </div>
                <div class="admin-field full-col">
                    <label for="createSecurityAnswer">Security Verification Answer *</label>
                    <input id="createSecurityAnswer" type="text" name="security_answer" required placeholder="e.g. Football or Royal College" autocomplete="off">
                    <small class="field-hint">Secret answer verified during password reset</small>
                </div>
            </div>

            <!-- Dynamic Athlete Fields (Registration details only) -->
            <div id="provisionAthleteFields" class="athlete-subform" style="display:none;">
                <div class="subform-header">
                    <span class="material-symbols-outlined" style="font-size:18px; color:var(--admin-teal);">badge</span>
                    <h4>Athlete University Sports Details</h4>
                </div>
                <div class="form-grid">
                    <div class="admin-field">
                        <label for="createFullName">Full Name *</label>
                        <input id="createFullName" type="text" name="full_name" placeholder="e.g. Kasun Bandara">
                    </div>
                    <div class="admin-field">
                        <label for="createIndex">Student Index Number *</label>
                        <input id="createIndex" type="text" name="index_number" placeholder="e.g. SC/2023/1042 or 24020486">
                    </div>
                    <div class="admin-field">
                        <label for="createFaculty">Faculty *</label>
                        <select id="createFaculty" name="faculty">
                            <option value="Faculty of Science">Faculty of Science</option>
                            <option value="UCSC">University of Colombo School of Computing (UCSC)</option>
                            <option value="Faculty of Arts">Faculty of Arts</option>
                            <option value="Faculty of Management & Finance">Faculty of Management &amp; Finance</option>
                            <option value="Faculty of Medicine">Faculty of Medicine</option>
                            <option value="Faculty of Technology">Faculty of Technology</option>
                            <option value="Faculty of Law">Faculty of Law</option>
                            <option value="Faculty of Education">Faculty of Education</option>
                            <option value="Faculty of Nursing">Faculty of Nursing</option>
                            <option value="Faculty of Indigenous Medicine">Faculty of Indigenous Medicine</option>
                        </select>
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
                <button type="submit" class="admin-primary-button">Provision Account</button>
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
                <p>Modify role assignments, contact details, or reset credentials.</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('editUserModal')">✕</button>
        </div>
        <form method="POST" action="../controllers/AdminController.php?action=update_user" class="modal-form" id="editForm" onsubmit="return validateEditForm(event)">
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
                    <label for="editPhone">Contact Phone</label>
                    <input id="editPhone" type="text" name="phone_number">
                </div>
                <div class="admin-field">
                    <label for="editPassword">Reset Password (Optional)</label>
                    <input id="editPassword" type="password" name="password" minlength="8" placeholder="Leave blank to retain current password">
                    <small class="field-hint">Min 8 characters if changing password</small>
                </div>
                <div class="admin-field">
                    <label for="editSecurityQuestion">Security Question</label>
                    <select id="editSecurityQuestion" name="security_question">
                        <option value="What is your favorite sport?">What is your favorite sport?</option>
                        <option value="What was the name of your first school?">What was the name of your first school?</option>
                        <option value="What is your birthplace / hometown?">What is your birthplace / hometown?</option>
                        <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                        <option value="What was your childhood nickname?">What was your childhood nickname?</option>
                    </select>
                    <small class="field-hint">For self-service password recovery</small>
                </div>
                <div class="admin-field full-col">
                    <label for="editSecurityAnswer">Update Security Answer (Optional)</label>
                    <input id="editSecurityAnswer" type="text" name="security_answer" placeholder="Leave blank to retain current answer" autocomplete="off">
                    <small class="field-hint">Only fill to change secret recovery answer</small>
                </div>
            </div>

            <!-- Dynamic Athlete Fields for Editing -->
            <div id="editAthleteFields" class="athlete-subform" style="display:none;">
                <div class="subform-header">
                    <span class="material-symbols-outlined" style="font-size:18px; color:var(--admin-teal);">badge</span>
                    <h4>Athlete University Sports Details</h4>
                </div>
                <div class="form-grid">
                    <div class="admin-field">
                        <label for="editFullName">Full Name</label>
                        <input id="editFullName" type="text" name="full_name">
                    </div>
                    <div class="admin-field">
                        <label for="editIndex">Student Index Number</label>
                        <input id="editIndex" type="text" name="index_number">
                    </div>
                    <div class="admin-field">
                        <label for="editFaculty">Faculty</label>
                        <select id="editFaculty" name="faculty">
                            <option value="Faculty of Science">Faculty of Science</option>
                            <option value="UCSC">University of Colombo School of Computing (UCSC)</option>
                            <option value="Faculty of Arts">Faculty of Arts</option>
                            <option value="Faculty of Management & Finance">Faculty of Management &amp; Finance</option>
                            <option value="Faculty of Medicine">Faculty of Medicine</option>
                            <option value="Faculty of Technology">Faculty of Technology</option>
                            <option value="Faculty of Law">Faculty of Law</option>
                            <option value="Faculty of Education">Faculty of Education</option>
                            <option value="Faculty of Nursing">Faculty of Nursing</option>
                            <option value="Faculty of Indigenous Medicine">Faculty of Indigenous Medicine</option>
                        </select>
                    </div>
                    <div class="admin-field">
                        <label for="editAcademicYear">Academic Year</label>
                        <select id="editAcademicYear" name="academic_year">
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                    <div class="admin-field">
                        <label for="editHeight">Height (cm)</label>
                        <input id="editHeight" type="number" step="0.1" min="0" name="height">
                    </div>
                    <div class="admin-field">
                        <label for="editWeight">Weight (kg)</label>
                        <input id="editWeight" type="number" step="0.1" min="0" name="weight">
                    </div>
                    <div class="admin-field">
                        <label for="editBloodGroup">Blood Group</label>
                        <select id="editBloodGroup" name="blood_group">
                            <option value="O+">O+</option>
                            <option value="A+">A+</option>
                            <option value="B+">B+</option>
                            <option value="AB+">AB+</option>
                            <option value="O-">O-</option>
                            <option value="A-">A-</option>
                            <option value="B-">B-</option>
                            <option value="AB-">AB-</option>
                        </select>
                    </div>
                    <div class="admin-field">
                        <label for="editEmergencyContact">Emergency Contact Phone</label>
                        <input id="editEmergencyContact" type="text" name="emergency_Contact">
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

<!-- ================= VIEW USER DOSSIER MODAL ================= -->
<div id="viewUserModal" class="modal-backdrop" style="display:none;">
    <div class="modal-box dossier-box">
        <div class="modal-header">
            <div>
                <h3>User Profile Dossier</h3>
                <p>Comprehensive system credentials and institutional records</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('viewUserModal')">✕</button>
        </div>
        <div class="dossier-body">
            <div class="dossier-hero">
                <span class="user-avatar-large" id="viewAvatar">US</span>
                <div>
                    <h4 id="viewDisplayName" style="margin:0 0 4px; font-size:16px;">Kasun Perera</h4>
                    <span id="viewRoleBadge" class="role-badge">Athlete</span>
                    <div style="font-size:12px; color:var(--admin-muted); margin-top:4px;" id="viewSubId">User ID #10 · @kasun_p</div>
                </div>
            </div>

            <div class="dossier-grid">
                <div class="dossier-item">
                    <span class="d-label">Email Address</span>
                    <strong class="d-val" id="viewEmail">kasun@example.com</strong>
                </div>
                <div class="dossier-item">
                    <span class="d-label">Phone Number</span>
                    <strong class="d-val" id="viewPhone">+94 77 123 4567</strong>
                </div>
                <div class="dossier-item">
                    <span class="d-label">System Role</span>
                    <strong class="d-val" id="viewRole">Athlete</strong>
                </div>
                <div class="dossier-item">
                    <span class="d-label">Account Status</span>
                    <strong class="d-val" style="color:var(--admin-success);">Active Verified</strong>
                </div>
                <div class="dossier-item full-col">
                    <span class="d-label">Security Recovery Question</span>
                    <strong class="d-val" id="viewSecurityQuestion">—</strong>
                </div>
            </div>

            <!-- Athlete Dossier Section -->
            <div id="viewAthleteSection" style="display:none; margin-top:14px; border-top:1px solid var(--admin-border); padding-top:14px;">
                <h5 style="margin:0 0 10px; font-size:13px; color:var(--admin-teal); text-transform:uppercase; letter-spacing:0.04em;">
                    Athletic Dossier &amp; Medical Clearance
                </h5>
                <div class="dossier-grid">
                    <div class="dossier-item">
                        <span class="d-label">Student Index</span>
                        <strong class="d-val" id="viewIndexNumber">—</strong>
                    </div>
                    <div class="dossier-item">
                        <span class="d-label">Faculty</span>
                        <strong class="d-val" id="viewFaculty">—</strong>
                    </div>
                    <div class="dossier-item">
                        <span class="d-label">Academic Year</span>
                        <strong class="d-val" id="viewAcademicYear">—</strong>
                    </div>
                    <div class="dossier-item">
                        <span class="d-label">Blood Group</span>
                        <strong class="d-val" id="viewBloodGroup">—</strong>
                    </div>
                    <div class="dossier-item">
                        <span class="d-label">Height / Weight</span>
                        <strong class="d-val" id="viewPhysical">—</strong>
                    </div>
                    <div class="dossier-item">
                        <span class="d-label">Emergency Contact</span>
                        <strong class="d-val" id="viewEmergencyContact">—</strong>
                    </div>
                    <div class="dossier-item full-col">
                        <span class="d-label">Medical Status</span>
                        <strong class="d-val" id="viewMedicalStatus" style="color:var(--admin-teal);">Fit to Compete</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-actions" style="padding:14px 20px;">
            <button type="button" class="admin-secondary-button" onclick="closeModal('viewUserModal')">Close</button>
            <button type="button" class="admin-primary-button" id="viewEditTrigger">Edit This Account</button>
        </div>
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
            <p style="margin:14px 0; color:#344054; line-height:1.55; font-size:13.5px;">
                Are you sure you want to permanently delete user account <strong id="deleteUserDisplay"></strong> (ID #<span id="deleteUserIdDisplay"></span>)?
            </p>
            <div class="delete-warning-box">
                <span class="material-symbols-outlined" style="color:var(--admin-danger);">warning</span>
                <span>This user's login access and associated athletic profile will be permanently removed. This action cannot be reversed.</span>
            </div>
            <div class="modal-actions">
                <button type="button" class="admin-secondary-button" onclick="closeModal('deleteUserModal')">Cancel</button>
                <button type="submit" class="admin-primary-button danger-btn">Yes, Delete Account</button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Admin Topbar Search */
    .topbar-search-wrap { display: flex; align-items: center; gap: 8px; background: #f8fafc; padding: 6px 12px; border-radius: 6px; border: 1px solid var(--admin-border); }
    .topbar-search-wrap input { width: min(260px, 28vw); border: 0; outline: 0; background: transparent; font-size: 13px; }

    /* Flash Alerts (No Red per Supervisor rule) */
    .admin-alert { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 13px; font-weight: 500; }
    .success-alert { background: #ecfdf3; color: #067647; border: 1px solid #abefc6; }
    .error-alert { background: #fffbeb; color: #b54708; border: 1px solid #fedf89; }
    .alert-dismiss { margin-left: auto; border: 0; background: transparent; font-size: 15px; cursor: pointer; color: inherit; }

    /* Metric Stat Grid */
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 18px; }
    .metric { min-height: 95px; display: flex; flex-direction: column; justify-content: space-between; padding: 16px 18px; background: #fff; border: 1px solid var(--admin-border); border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
    .metric > span { color: #475467; font-size: 11.5px; letter-spacing: .05em; text-transform: uppercase; font-weight: 600; }
    .metric strong { font-size: 24px; font-weight: 700; color: #101828; line-height: 1.2; }
    .metric small { color: var(--admin-teal); font-size: 11.5px; font-weight: 500; }
    .metric small.urgent { color: var(--admin-danger); font-weight: 600; }
    .metric.dark { color: #fff; background: var(--admin-navy); }
    .metric.dark > span, .metric.dark small { color: #cbd5e1; }
    .metric.dark strong { color: #fff; }

    /* Layout */
    .users-layout { display: grid; grid-template-columns: minmax(260px, .72fr) minmax(620px, 1.9fr); gap: 16px; }
    
    /* Verification Queue */
    .verification-list { padding: 12px; max-height: 600px; overflow-y: auto; }
    .verification-item { padding: 14px; border-radius: 8px; background: #f8fafb; border: 1px solid var(--admin-border); }
    .verification-item + .verification-item { margin-top: 10px; }
    .person-row { display: flex; align-items: center; gap: 10px; }
    .person-row time { margin-left: auto; color: #64748b; font-size: 11.5px; }
    .person-row strong { display: block; color: #101828; font-size: 13.5px; }
    .person-row small { display: block; margin-top: 2px; color: #64748b; font-size: 11px; text-transform: uppercase; }
    .initials { width: 34px; height: 34px; display: grid; place-items: center; border-radius: 50%; background: #d0d5dd; font-weight: 700; font-size: 12px; flex-shrink: 0; }
    .initials.teal { color: #086c70; background: #c5f5f5; }
    .user-avatar-small { width: 32px; height: 32px; display: grid; place-items: center; border-radius: 50%; background: #e4e7ec; color: #344054; font-weight: 700; font-size: 11.5px; flex-shrink: 0; }
    .user-avatar-large { width: 48px; height: 48px; display: grid; place-items: center; border-radius: 50%; background: #0099ab; color: #fff; font-weight: 700; font-size: 16px; flex-shrink: 0; }
    
    .verification-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin: 10px 0; padding: 8px 10px; background: #fff; border-radius: 6px; font-size: 11px; border: 1px solid #eaecf0; }
    .verification-meta b { display: block; margin-top: 2px; font-size: 12px; color: #101828; }
    .review-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
    .approve-link, .reject-link { min-height: 32px; border-radius: 6px; font-weight: 700; font-size: 12px; display: grid; place-items: center; text-decoration: none; transition: opacity .15s; }
    .approve-link { color: #fff; background: var(--admin-navy); }
    .approve-link:hover { opacity: 0.9; }
    .reject-link { color: #344054; background: #e4e7ec; }
    .reject-link:hover { background: #d0d5dd; }

    .queue-empty-state { text-align: center; padding: 32px 14px; color: var(--admin-muted); font-size: 13px; }
    .queue-empty-state span { font-size: 32px; color: var(--admin-teal); display: block; margin-bottom: 6px; }

    /* Directory Header & Filter Bar */
    .directory-header { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
    .directory-filter-bar { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .filter-select { min-height: 34px; padding: 0 10px; border: 1px solid var(--admin-border); border-radius: 6px; background: #fff; font-size: 12.5px; outline: none; }
    .directory-search-input { display: flex; align-items: center; gap: 6px; padding: 0 10px; border-radius: 6px; background: #f8fafc; border: 1px solid var(--admin-border); }
    .directory-search-input input { height: 34px; border: 0; outline: 0; background: transparent; font-size: 12.5px; width: 170px; }
    .reset-filter-btn { min-height: 34px; padding: 0 10px; font-size: 12px; }

    /* Role Badges strictly for the 6 roles */
    .role-badge { display: inline-flex; align-items: center; padding: 2px 7px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .02em; }
    .role-badge.athlete { color: #075f65; background: #cbf4f5; }
    .role-badge.coach { color: #175cd3; background: #eff8ff; }
    .role-badge.system-admin { color: #fff; background: var(--admin-navy); }
    .role-badge.medical-officer { color: #067647; background: #ecfdf3; }
    .role-badge.tic { color: #b54708; background: #fffaeb; }
    .role-badge.president { color: #4338ca; background: #e0e7ff; }

    .subline { display: block; margin-top: 2px; color: #667085; font-size: 11.5px; }
    .faculty-tag { display: inline-block; margin-top: 2px; padding: 1px 5px; background: #f2f4f7; border-radius: 3px; color: #475467; font-size: 10.5px; }

    .row-action { border: 0; background: transparent; font-weight: 700; cursor: pointer; font-size: 12.5px; padding: 3px 5px; border-radius: 4px; transition: background .15s; }
    .view-btn { color: #4338ca; }
    .view-btn:hover { background: #eef2ff; }
    .edit-btn { color: var(--admin-teal); margin-left: 4px; }
    .edit-btn:hover { background: rgba(0, 153, 171, 0.08); }
    .delete-btn { color: var(--admin-danger); margin-left: 4px; }
    .delete-btn:hover { background: #fffaeb; }

    /* Table Pagination */
    .directory-pagination { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 18px; border-top: 1px solid var(--admin-border); font-size: 12.5px; color: var(--admin-muted); flex-wrap: wrap; }
    .pagination-controls { display: flex; align-items: center; gap: 4px; }
    .page-btn { min-width: 30px; height: 30px; display: inline-grid; place-items: center; padding: 0 6px; border: 1px solid var(--admin-border); border-radius: 5px; background: #fff; color: var(--admin-text); font-size: 12px; font-weight: 600; cursor: pointer; transition: all .15s; }
    .page-btn:hover:not(:disabled) { border-color: var(--admin-teal); color: var(--admin-teal); }
    .page-btn.active { color: #fff; background: var(--admin-navy); border-color: var(--admin-navy); }
    .page-btn:disabled { opacity: 0.45; cursor: not-allowed; }

    /* Modals */
    .modal-backdrop { position: fixed; inset: 0; z-index: 999; background: rgba(7, 22, 45, .6); backdrop-filter: blur(2px); display: grid; place-items: center; padding: 18px; overflow-y: auto; }
    .modal-box { width: 100%; max-width: 540px; background: #fff; border-radius: 10px; box-shadow: 0 16px 36px rgba(0,0,0,.2); overflow: hidden; animation: modalFade .15s ease-out; }
    .modal-box.delete-box { max-width: 420px; }
    .modal-box.dossier-box { max-width: 580px; }
    @keyframes modalFade { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
    .modal-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 16px 20px; border-bottom: 1px solid var(--admin-border); }
    .modal-header h3 { margin: 0 0 3px; font-size: 16px; font-weight: 700; color: var(--admin-navy); }
    .modal-header p { margin: 0; font-size: 12px; color: var(--admin-muted); }
    .modal-close-btn { border: 0; background: transparent; font-size: 16px; cursor: pointer; color: var(--admin-muted); }
    .modal-form { padding: 18px 20px; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .full-col { grid-column: span 2; }
    .field-hint { color: var(--admin-muted); font-size: 11px; margin-top: 2px; }
    
    .athlete-subform { margin-top: 14px; padding: 12px 14px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; }
    .subform-header { display: flex; align-items: center; gap: 6px; margin-bottom: 10px; }
    .subform-header h4 { margin: 0; font-size: 13px; font-weight: 700; color: var(--admin-teal); }
    .modal-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--admin-border); }
    .danger-btn { background: var(--admin-danger) !important; border-color: var(--admin-danger) !important; color: #fff; }
    .delete-warning-box { display: flex; align-items: center; gap: 8px; padding: 10px 12px; background: #fffbeb; border: 1px solid #fedf89; border-radius: 6px; font-size: 12px; color: #b54708; line-height: 1.45; }

    /* Dossier Body */
    .dossier-body { padding: 18px 20px; }
    .dossier-hero { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; padding-bottom: 14px; border-bottom: 1px solid var(--admin-border); }
    .dossier-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .dossier-item { display: flex; flex-direction: column; }
    .d-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--admin-muted); font-weight: 600; margin-bottom: 2px; }
    .d-val { font-size: 13px; color: var(--admin-text); }

    @media (max-width: 1200px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .users-layout { grid-template-columns: 1fr; }
    }
    @media (max-width: 650px) {
        .stats-grid { grid-template-columns: 1fr; }
        .form-grid { grid-template-columns: 1fr; }
        .full-col { grid-column: span 1; }
        .directory-filter-bar { width: 100%; }
        .directory-search-input input { width: 100%; }
    }
</style>

<script>
// Global state for filtering and pagination
const PAGE_SIZE = 10;
let currentPage = 1;
let filteredRows = [];

document.addEventListener('DOMContentLoaded', function() {
    initTableData();
});

function initTableData() {
    const rows = Array.from(document.querySelectorAll('#usersTableBody tr.user-row'));
    filteredRows = rows;
    renderPagination();
}

function handleFilterChange() {
    const role = document.getElementById('roleFilterSelect').value.trim();
    const query = document.getElementById('directorySearchInput').value.trim().toLowerCase();
    const rows = Array.from(document.querySelectorAll('#usersTableBody tr.user-row'));

    filteredRows = rows.filter(row => {
        const rowRole = row.getAttribute('data-role') || '';
        const searchBlob = row.getAttribute('data-search') || '';

        const roleMatch = !role || rowRole === role;
        const queryMatch = !query || searchBlob.includes(query);
        return roleMatch && queryMatch;
    });

    currentPage = 1;
    renderPagination();
}

function syncSearchToTable(val) {
    const dirInput = document.getElementById('directorySearchInput');
    if (dirInput) {
        dirInput.value = val;
        handleFilterChange();
    }
}

function resetDirectoryFilters() {
    document.getElementById('roleFilterSelect').value = '';
    document.getElementById('directorySearchInput').value = '';
    const topSearch = document.getElementById('topbarSearchInput');
    if (topSearch) topSearch.value = '';
    handleFilterChange();
}

function renderPagination() {
    const allRows = Array.from(document.querySelectorAll('#usersTableBody tr.user-row'));
    const totalRecords = filteredRows.length;
    const totalPages = Math.ceil(totalRecords / PAGE_SIZE) || 1;

    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    const startIdx = (currentPage - 1) * PAGE_SIZE;
    const endIdx = startIdx + PAGE_SIZE;

    // Hide all rows first
    allRows.forEach(row => row.style.display = 'none');

    // Show only the slice for current page
    filteredRows.slice(startIdx, endIdx).forEach(row => {
        row.style.display = '';
    });

    // Handle empty state message
    let emptyRow = document.getElementById('tableEmptyFilterRow');
    if (totalRecords === 0) {
        if (!emptyRow) {
            emptyRow = document.createElement('tr');
            emptyRow.id = 'tableEmptyFilterRow';
            emptyRow.innerHTML = '<td colspan="5" style="text-align:center; padding:32px; color:var(--admin-muted); font-size:13px;">No accounts found matching your filter criteria.</td>';
            document.getElementById('usersTableBody').appendChild(emptyRow);
        }
        emptyRow.style.display = '';
    } else if (emptyRow) {
        emptyRow.style.display = 'none';
    }

    // Update pagination status info
    const infoEl = document.getElementById('paginationInfo');
    if (infoEl) {
        if (totalRecords === 0) {
            infoEl.textContent = 'Showing 0 accounts';
        } else {
            const displayStart = startIdx + 1;
            const displayEnd = Math.min(endIdx, totalRecords);
            infoEl.textContent = `Showing ${displayStart}–${displayEnd} of ${totalRecords} accounts`;
        }
    }

    // Build pagination buttons
    const controlsEl = document.getElementById('paginationControls');
    if (!controlsEl) return;
    controlsEl.innerHTML = '';

    if (totalPages <= 1) return; // No buttons needed if just 1 page

    // Previous Button
    const prevBtn = document.createElement('button');
    prevBtn.type = 'button';
    prevBtn.className = 'page-btn';
    prevBtn.textContent = '‹';
    prevBtn.disabled = (currentPage === 1);
    prevBtn.onclick = () => { currentPage--; renderPagination(); };
    controlsEl.appendChild(prevBtn);

    // Page number buttons
    for (let p = 1; p <= totalPages; p++) {
        // Limit button sprawl if many pages
        if (totalPages > 7 && Math.abs(p - currentPage) > 2 && p !== 1 && p !== totalPages) {
            if (p === 2 || p === totalPages - 1) {
                const dots = document.createElement('span');
                dots.style.padding = '0 4px';
                dots.textContent = '...';
                controlsEl.appendChild(dots);
            }
            continue;
        }

        const pageBtn = document.createElement('button');
        pageBtn.type = 'button';
        pageBtn.className = 'page-btn' + (p === currentPage ? ' active' : '');
        pageBtn.textContent = p;
        pageBtn.onclick = () => { currentPage = p; renderPagination(); };
        controlsEl.appendChild(pageBtn);
    }

    // Next Button
    const nextBtn = document.createElement('button');
    nextBtn.type = 'button';
    nextBtn.className = 'page-btn';
    nextBtn.textContent = '›';
    nextBtn.disabled = (currentPage === totalPages);
    nextBtn.onclick = () => { currentPage++; renderPagination(); };
    controlsEl.appendChild(nextBtn);
}

// Modal handling
function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'grid';
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
}

function openProvisionModal() {
    // Clean empty fields (no hardcoded prefill)
    document.getElementById('createUsername').value = '';
    document.getElementById('createEmail').value = '';
    document.getElementById('createPhone').value = '';
    document.getElementById('createPassword').value = '';
    document.getElementById('createRole').value = 'Coach';

    // Athlete subform fields clean
    document.getElementById('createFullName').value = '';
    document.getElementById('createIndex').value = '';
    document.getElementById('createFaculty').value = 'Faculty of Science';
    document.getElementById('createAcademicYear').value = '1';

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
    document.getElementById('editSecurityQuestion').value = user.security_question || 'What is your favorite sport?';
    document.getElementById('editSecurityAnswer').value = '';
    
    // Athlete fields
    document.getElementById('editFullName').value = user.athlete_name || '';
    document.getElementById('editIndex').value = user.index_number || '';
    document.getElementById('editFaculty').value = user.faculty || 'Faculty of Science';
    document.getElementById('editAcademicYear').value = user.academic_year || '1';
    document.getElementById('editHeight').value = (user.height && user.height > 0) ? user.height : '';
    document.getElementById('editWeight').value = (user.weight && user.weight > 0) ? user.weight : '';
    document.getElementById('editBloodGroup').value = user.blood_group || 'O+';
    document.getElementById('editEmergencyContact').value = user.emergency_Contact || '';
    
    toggleAthleteFields('edit', user.role);
    openModal('editUserModal');
}

function openViewModal(user) {
    document.getElementById('viewDisplayName').textContent = user.athlete_name || user.username || 'User';
    document.getElementById('viewAvatar').textContent = (user.athlete_name || user.username || 'US').substring(0, 2).toUpperCase();
    document.getElementById('viewRoleBadge').textContent = user.role.replace('_', ' ');
    document.getElementById('viewRoleBadge').className = 'role-badge ' + user.role.toLowerCase().replace('_', '-');
    document.getElementById('viewSubId').textContent = `User ID #${user.user_id} · @${user.username}`;
    document.getElementById('viewEmail').textContent = user.email || '—';
    document.getElementById('viewPhone').textContent = user.phone_number || '—';
    document.getElementById('viewRole').textContent = user.role.replace('_', ' ');
    document.getElementById('viewSecurityQuestion').textContent = user.security_question || 'Not Configured';

    const athleteSec = document.getElementById('viewAthleteSection');
    if (user.role === 'Athlete') {
        athleteSec.style.display = 'block';
        document.getElementById('viewIndexNumber').textContent = user.index_number || 'Pending Assignment';
        document.getElementById('viewFaculty').textContent = user.faculty || '—';
        document.getElementById('viewAcademicYear').textContent = user.academic_year ? `${user.academic_year} Year` : '1st Year';
        document.getElementById('viewBloodGroup').textContent = user.blood_group || 'O+';
        
        const ht = (user.height && user.height > 0) ? `${user.height} cm` : '—';
        const wt = (user.weight && user.weight > 0) ? `${user.weight} kg` : '—';
        document.getElementById('viewPhysical').textContent = `${ht} / ${wt}`;
        document.getElementById('viewEmergencyContact').textContent = user.emergency_Contact || '—';
        document.getElementById('viewMedicalStatus').textContent = user.medical_status || 'Fit to Compete';
    } else {
        athleteSec.style.display = 'none';
    }

    document.getElementById('viewEditTrigger').onclick = function() {
        closeModal('viewUserModal');
        openEditModal(user);
    };

    openModal('viewUserModal');
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

// Logical Client-side Guards before triggering backend/database
function validateProvisionForm(e) {
    const username = document.getElementById('createUsername').value.trim();
    const email = document.getElementById('createEmail').value.trim();
    const password = document.getElementById('createPassword').value;
    const role = document.getElementById('createRole').value;
    const secAnswer = document.getElementById('createSecurityAnswer').value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (username.length < 3) {
        showClientAlert('Username must be at least 3 characters long.');
        document.getElementById('createUsername').focus();
        e.preventDefault();
        return false;
    }

    if (!emailRegex.test(email)) {
        showClientAlert('Please enter a valid email address (e.g. kasun@example.com).');
        document.getElementById('createEmail').focus();
        e.preventDefault();
        return false;
    }

    if (password.length < 8) {
        showClientAlert('Initial password must be at least 8 characters long.');
        document.getElementById('createPassword').focus();
        e.preventDefault();
        return false;
    }

    if (!secAnswer) {
        showClientAlert('Please provide a secret answer for security verification.');
        document.getElementById('createSecurityAnswer').focus();
        e.preventDefault();
        return false;
    }

    return true;
}

function validateEditForm(e) {
    const username = document.getElementById('editUsername').value.trim();
    const email = document.getElementById('editEmail').value.trim();
    const password = document.getElementById('editPassword').value;
    const role = document.getElementById('editRole').value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (username.length < 3) {
        showClientAlert('Username must be at least 3 characters long.');
        document.getElementById('editUsername').focus();
        e.preventDefault();
        return false;
    }

    if (!emailRegex.test(email)) {
        showClientAlert('Please enter a valid email address.');
        document.getElementById('editEmail').focus();
        e.preventDefault();
        return false;
    }

    if (password && password.length < 8) {
        showClientAlert('Password must be at least 8 characters long if changed.');
        document.getElementById('editPassword').focus();
        e.preventDefault();
        return false;
    }

    if (role === 'Athlete') {
        const height = document.getElementById('editHeight').value;
        const weight = document.getElementById('editWeight').value;

        if (height !== '' && parseFloat(height) < 0) {
            showClientAlert('Height cannot be a negative value.');
            document.getElementById('editHeight').focus();
            e.preventDefault();
            return false;
        }

        if (weight !== '' && parseFloat(weight) < 0) {
            showClientAlert('Weight cannot be a negative value.');
            document.getElementById('editWeight').focus();
            e.preventDefault();
            return false;
        }
    }

    return true;
}

function showClientAlert(msg) {
    if (typeof showToast === 'function') {
        showToast(msg, 'warning');
    }
    const alertBox = document.getElementById('clientValidationAlert');
    const msgBox = document.getElementById('clientValidationMsg');
    if (alertBox && msgBox) {
        msgBox.textContent = msg;
        alertBox.style.display = 'flex';
        window.scrollTo({ top: 0, behavior: 'smooth' });
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
