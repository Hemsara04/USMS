<?php
/**
 * Universal User Profile & Account Settings
 * USMS - University Sports Management System
 * Accessible to all system roles: Athlete, Coach, TIC, Medical Officer, President, Admin
 */

require_once __DIR__ . '/../config/auth_guard.php';

$userId   = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? 'User';

if (!$userId) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../models/User.php';
$userModel = new User();
$userProfile = $userModel->getFullProfileDetails($userId);

if (!$userProfile) {
    die("User profile could not be loaded.");
}

$pageTitle = 'Account Profile & Security';
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Role badge styling
$roleColor = 'var(--teal)';
$roleBg    = 'var(--teal-light)';
$roleIcon  = 'person';

if ($userRole === 'Coach') {
    $roleColor = 'var(--accent)';
    $roleBg    = 'var(--accent-bg)';
    $roleIcon  = 'sports';
} elseif ($userRole === 'TIC') {
    $roleColor = '#D97706';
    $roleBg    = '#FFFBEB';
    $roleIcon  = 'supervisor_account';
} elseif ($userRole === 'Medical_Officer' || $userRole === 'Physiotherapist') {
    $roleColor = '#059669';
    $roleBg    = '#ECFDF5';
    $roleIcon  = 'medical_services';
} elseif ($userRole === 'President') {
    $roleColor = '#7C3AED';
    $roleBg    = '#F5F3FF';
    $roleIcon  = 'shield_person';
} elseif ($userRole === 'System_Admin') {
    $roleColor = '#0099ab';
    $roleBg    = 'rgba(0, 153, 171, 0.12)';
    $roleIcon  = 'admin_panel_settings';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — USMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .profile-grid {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 24px;
            align-items: start;
        }
        @media (max-width: 1024px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }
        .profile-hero-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--r-lg);
            padding: 28px 24px;
            text-align: center;
            box-shadow: var(--shadow-xs);
        }
        .profile-avatar-large {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--side-bg), #1e293b);
            color: #ffffff;
            font-size: 34px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px auto;
            border: 4px solid var(--card);
            box-shadow: 0 4px 14px rgba(0,0,0,0.12);
        }
        .profile-fullname {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-1);
            margin-bottom: 4px;
        }
        .profile-handle {
            font-size: 13px;
            color: var(--text-3);
            margin-bottom: 14px;
        }
        .role-tag-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .meta-list {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            text-align: left;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px dashed var(--border);
            font-size: 13px;
        }
        .meta-row:last-child {
            border-bottom: none;
        }
        .meta-label {
            color: var(--text-3);
            font-weight: 500;
        }
        .meta-value {
            color: var(--text-1);
            font-weight: 700;
            text-align: right;
        }
        .settings-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--r-lg);
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: var(--shadow-xs);
        }
        .settings-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border);
        }
        .settings-card-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-1);
        }
        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        @media (max-width: 640px) {
            .form-grid-2 {
                grid-template-columns: 1fr;
            }
        }
        .input-readonly {
            background: #F8FAFC !important;
            color: var(--text-3) !important;
            cursor: not-allowed;
            border-color: #E2E8F0 !important;
        }
    </style>
</head>
<body class="dashboard">

<?php 
// Include appropriate sidebar based on user role
if ($userRole === 'Coach') {
    include __DIR__ . '/partials/coach_sidebar.php';
} elseif ($userRole === 'TIC') {
    include __DIR__ . '/partials/tic_sidebar.php';
} elseif ($userRole === 'Medical_Officer' || $userRole === 'Physiotherapist') {
    include __DIR__ . '/partials/med_sidebar.php';
} elseif ($userRole === 'President') {
    include __DIR__ . '/partials/pres_sidebar.php';
} elseif ($userRole === 'System_Admin' || $userRole === 'Admin') {
    $activeAdminPage = 'profile';
    include __DIR__ . '/partials/admin_sidebar.php';
} else {
    // Default fallback sidebar for Athlete
?>
    <aside class="sidebar" style="width: 260px; background: var(--side-bg); color: #fff; display: flex; flex-direction: column;">
        <div class="sidebar-header">
            <span class="material-symbols-outlined logo-icon">person</span>
            <div class="logo-text">
                <h1>USMS</h1>
                <p>USER PROFILE</p>
            </div>
        </div>
        <nav class="sidebar-nav">
            <?php if ($userRole === 'Athlete'): ?>
                <a href="athletic_regestration.php" class="sidebar-link">
                    <span class="material-symbols-outlined">arrow_back</span>
                    <span>Return to Athlete Portal</span>
                </a>
            <?php else: ?>
                <a href="login.php" class="sidebar-link">
                    <span class="material-symbols-outlined">arrow_back</span>
                    <span>Return to Portal</span>
                </a>
            <?php endif; ?>
            <a href="profile.php" class="sidebar-link active">
                <span class="material-symbols-outlined">person</span>
                <span>Account Profile</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../controllers/AuthController.php?action=logout" class="sidebar-link">
                <span class="material-symbols-outlined">logout</span>
                <span>Sign Out</span>
            </a>
        </div>
    </aside>
<?php } ?>

<div class="main-wrapper">
    <?php include __DIR__ . '/partials/tic_header.php'; ?>

    <div class="page-content">

        <!-- Page Header -->
        <div class="page-title-row" style="margin-bottom: 24px;">
            <div>
                <h2 class="page-title">Account Profile & Security Settings</h2>
                <p style="font-size: 13px; color: var(--text-3); margin-top: 3px;">
                    Review your official university credentials, update direct contact details, and manage password security.
                </p>
            </div>
            <div>
                <span class="role-tag-pill" style="background: <?= $roleBg ?>; color: <?= $roleColor ?>; border: 1px solid <?= $roleColor ?>33;">
                    <span class="material-symbols-outlined" style="font-size: 16px;"><?= $roleIcon ?></span>
                    <?= htmlspecialchars($userRole) ?>
                </span>
            </div>
        </div>

        <?php if (!empty($flashSuccess)): ?>
            <div style="background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534; padding: 12px 18px; border-radius: var(--r); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 13.5px;">
                <span class="material-symbols-outlined" style="color: #16A34A; font-size: 20px;">check_circle</span>
                <span><?= htmlspecialchars($flashSuccess) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
            <div style="background: #FFFBEB; border: 1px solid #FDE68A; color: #B45309; padding: 12px 18px; border-radius: var(--r); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 13.5px;">
                <span class="material-symbols-outlined" style="color: #D97706; font-size: 20px;">warning</span>
                <span><?= htmlspecialchars($flashError) ?></span>
            </div>
        <?php endif; ?>

        <!-- Two-Column Profile & Settings Grid -->
        <div class="profile-grid">
            
            <!-- Left: Profile Summary Card -->
            <div class="profile-hero-card">
                <div class="profile-avatar-large">
                    <?= strtoupper(substr($userProfile['username'], 0, 1)) ?>
                </div>

                <div class="profile-fullname"><?= htmlspecialchars($userProfile['full_name']) ?></div>
                <div class="profile-handle">@<?= htmlspecialchars($userProfile['username']) ?></div>

                <div style="margin-bottom: 18px;">
                    <span class="role-tag-pill" style="background: <?= $roleBg ?>; color: <?= $roleColor ?>; border: 1px solid <?= $roleColor ?>33;">
                        <span class="material-symbols-outlined" style="font-size: 15px;"><?= $roleIcon ?></span>
                        <?= htmlspecialchars($userRole) ?>
                    </span>
                </div>

                <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 8px 12px; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: #15803D; font-weight: 600;">
                    <span class="material-symbols-outlined" style="font-size: 16px;">verified</span>
                    Authenticated & Verified
                </div>

                <div class="meta-list">
                    <div class="meta-row">
                        <span class="meta-label">User ID</span>
                        <span class="meta-value">USR-<?= str_pad($userProfile['user_id'], 4, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Primary Email</span>
                        <span class="meta-value" style="font-size: 12px;"><?= htmlspecialchars($userProfile['email']) ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Direct Phone</span>
                        <span class="meta-value"><?= htmlspecialchars($userProfile['phone_number'] ?: 'Not Provided') ?></span>
                    </div>

                    <?php foreach ($userProfile['meta'] as $mLabel => $mVal): ?>
                        <div class="meta-row">
                            <span class="meta-label"><?= htmlspecialchars($mLabel) ?></span>
                            <span class="meta-value"><?= htmlspecialchars($mVal) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right: Settings & Password Forms -->
            <div>
                
                <!-- Section 1: Update Contact Details -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <span class="material-symbols-outlined" style="color: var(--teal); font-size: 22px;">contact_mail</span>
                        <div>
                            <div class="settings-card-title">Contact & Communication Details</div>
                            <div style="font-size: 12px; color: var(--text-3);">Keep your official university email and mobile phone number current.</div>
                        </div>
                    </div>

                    <form action="../controllers/AuthController.php?action=update_profile" method="POST">
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label">System Username</label>
                                <input type="text" class="form-input input-readonly" value="<?= htmlspecialchars($userProfile['username']) ?>" readonly>
                                <span style="font-size: 11px; color: var(--text-3); margin-top: 3px; display: block;">Username is fixed to your registration record.</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">System Authority Role</label>
                                <input type="text" class="form-input input-readonly" value="<?= htmlspecialchars($userRole) ?>" readonly>
                                <span style="font-size: 11px; color: var(--text-3); margin-top: 3px; display: block;">Managed via Central Administration.</span>
                            </div>
                        </div>

                        <div class="form-grid-2" style="margin-top: 12px;">
                            <div class="form-group">
                                <label class="form-label">Email Address <span style="color: var(--danger)">*</span></label>
                                <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($userProfile['email']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Contact Phone Number <span style="color: var(--danger)">*</span></label>
                                <input type="text" name="phone_number" class="form-input" value="<?= htmlspecialchars($userProfile['phone_number'] ?: '+94 11 258 1835') ?>" required>
                            </div>
                        </div>

                        <div style="margin-top: 18px; text-align: right;">
                            <button type="submit" class="btn btn-teal">
                                <span class="material-symbols-outlined" style="font-size: 16px;">save</span>
                                Save Contact Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Section 2: Security & Password Management -->
                <div class="settings-card" id="security">
                    <div class="settings-card-header">
                        <span class="material-symbols-outlined" style="color: #2563EB; font-size: 22px;">lock_reset</span>
                        <div>
                            <div class="settings-card-title">Security & Password Management</div>
                            <div style="font-size: 12px; color: var(--text-3);">Update your account credentials using your current password verification.</div>
                        </div>
                    </div>

                    <form action="../controllers/AuthController.php?action=change_password" method="POST">
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label class="form-label">Current Account Password <span style="color: var(--danger)">*</span></label>
                            <input type="password" name="current_password" class="form-input" placeholder="Enter your current password" required>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label">New Password <span style="color: var(--danger)">*</span></label>
                                <input type="password" name="new_password" class="form-input" placeholder="Min. 8 characters" minlength="8" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Confirm New Password <span style="color: var(--danger)">*</span></label>
                                <input type="password" name="confirm_password" class="form-input" placeholder="Re-enter new password" minlength="8" required>
                            </div>
                        </div>

                        <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: 6px; padding: 10px 14px; margin-top: 14px; font-size: 12px; color: var(--text-3); line-height: 1.4;">
                            <span class="material-symbols-outlined" style="font-size: 15px; vertical-align: middle; color: #2563EB;">shield</span>
                            Security Note: Use a combination of uppercase letters, numbers, and symbols to protect your portal operations.
                        </div>

                        <div style="margin-top: 18px; text-align: right;">
                            <button type="submit" class="btn btn-outline" style="border-color: #2563EB; color: #2563EB;">
                                <span class="material-symbols-outlined" style="font-size: 16px;">key</span>
                                Update Account Password
                            </button>
                        </div>
                    </form>
                </div>

            </div>

        </div>

    </div>
</div>

<script src="../assets/js/dashboard.js"></script>
</body>
</html>
