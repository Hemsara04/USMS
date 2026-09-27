<?php
/**
 * Administrator Navigation Sidebar
 * USMS - University Sports Management System
 * Standardized with dashboard.css flex layout across all roles
 */
$currentPage = basename($_SERVER['PHP_SELF']);
$activeAdminPage = $activeAdminPage ?? '';
$adminUsername = $_SESSION['username'] ?? 'Admin';
$adminName = !empty($_SESSION['username']) ? ucwords(str_replace('_', ' ', $_SESSION['username'])) : 'System Administrator';

$adminNavLinks = [
    [
        'id'    => 'users',
        'file'  => 'admin_user_management.php',
        'label' => 'User Management',
        'icon'  => 'group'
    ],
    [
        'id'    => 'sports',
        'file'  => 'admin_sports_catalog.php',
        'label' => 'Sports Catalog',
        'icon'  => 'sports_basketball'
    ],
    [
        'id'    => 'seasons',
        'file'  => 'admin_seasons_setup.php',
        'label' => 'Seasons Setup',
        'icon'  => 'calendar_month'
    ],
    [
        'id'    => 'colors',
        'file'  => 'admin_university_colors.php',
        'label' => 'University Colours',
        'icon'  => 'emoji_events'
    ],
    [
        'id'    => 'audit',
        'file'  => 'admin_audit_logs.php',
        'label' => 'Audit Logs',
        'icon'  => 'history'
    ],
    [
        'id'    => 'profile',
        'file'  => 'profile.php',
        'label' => 'My Profile',
        'icon'  => 'manage_accounts'
    ]
];
?>

<style>
    /* Scoped Admin Component Design Tokens & Utilities (No layout overrides) */
    :root {
        --admin-navy: #041427;
        --admin-navy-soft: #041427;
        --admin-teal: #0099ab;
        --admin-teal-soft: rgba(0, 153, 171, 0.12);
        --admin-bg: #f8fafc;
        --admin-card: #ffffff;
        --admin-text: #1e293b;
        --admin-muted: #64748b;
        --admin-border: #e2e8f0;
        --admin-danger: #d97706; /* Amber warning (No red per supervisor guideline) */
        --admin-warning: #b54708;
        --admin-success: #067647;
    }

    .admin-page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .admin-page-header > div:first-child {
        flex: 1;
        min-width: 260px;
    }
    .admin-page-header h1, .admin-page-header h2 {
        font-size: 22px;
        font-weight: 800;
        color: var(--admin-navy, #041427);
        letter-spacing: -0.02em;
        margin: 0 0 4px 0;
    }
    .admin-page-header p {
        font-size: 13.5px;
        color: var(--admin-muted, #64748b);
        margin: 0;
        line-height: 1.45;
        font-weight: 400;
    }

    .admin-primary-button, .admin-secondary-button {
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 14px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all .2s;
        text-decoration: none;
    }
    .admin-primary-button {
        color: #fff;
        background: var(--admin-teal);
        border: 1px solid var(--admin-teal);
    }
    .admin-primary-button:hover {
        background: #007a89;
        border-color: #007a89;
    }
    .admin-secondary-button {
        color: var(--admin-text);
        background: #fff;
        border: 1px solid var(--admin-border);
    }
    .admin-secondary-button:hover {
        background: #f8fafc;
    }

    .admin-card {
        background: var(--admin-card);
        border: 1px solid var(--admin-border);
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .admin-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 14px 18px;
        border-bottom: 1px solid var(--admin-border);
    }
    .admin-card-header h2 {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
    }
    .admin-card-header p {
        margin: 2px 0 0;
        color: var(--admin-muted);
        font-size: 12px;
    }

    .admin-table-wrap {
        overflow-x: auto;
    }
    .admin-table {
        width: 100%;
        border-collapse: collapse;
    }
    .admin-table th {
        padding: 10px 14px;
        color: var(--admin-muted);
        background: #f8fafc;
        font-size: 11.5px;
        letter-spacing: .04em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
        font-weight: 600;
    }
    .admin-table td {
        padding: 12px 14px;
        border-top: 1px solid var(--admin-border);
        font-size: 13px;
        vertical-align: middle;
    }
    .admin-table tbody tr:hover {
        background: #f8fafc;
    }

    .admin-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 600;
        white-space: nowrap;
    }
    .admin-chip::before {
        width: 5px;
        height: 5px;
        content: '';
        border-radius: 50%;
        background: currentColor;
    }
    .admin-chip.success { color: var(--admin-success); background: #ecfdf3; }
    .admin-chip.warning { color: var(--admin-warning); background: #fffaeb; }
    .admin-chip.danger { color: #b54708; background: #fffaeb; } /* Non-red amber */
    .admin-chip.neutral { color: #475467; background: #f2f4f7; }

    .admin-field {
        display: grid;
        gap: 5px;
    }
    .admin-field label {
        color: #344054;
        font-size: 11.5px;
        font-weight: 600;
    }
    .admin-field input, .admin-field select, .admin-field textarea {
        min-height: 36px;
        width: 100%;
        padding: 0 10px;
        border: 1px solid #d0d5dd;
        border-radius: 6px;
        background: #fff;
        outline: none;
        font-size: 13px;
    }
    .admin-field textarea {
        padding: 8px 10px;
    }
    .admin-field input:focus, .admin-field select:focus, .admin-field textarea:focus {
        border-color: var(--admin-teal);
        box-shadow: 0 0 0 3px rgba(0,153,171,.12);
    }
</style>

<aside class="sidebar" aria-label="Administrator navigation">
    <div class="sidebar-header">
        <span class="material-symbols-outlined logo-icon" style="color: var(--teal);">shield_person</span>
        <div class="logo-text">
            <h1>USMS</h1>
            <p>ADMIN PORTAL</p>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php foreach ($adminNavLinks as $item): 
            $isActive = ($currentPage === $item['file']) || ($activeAdminPage === $item['id']);
        ?>
            <a href="<?= htmlspecialchars($item['file']) ?>" class="sidebar-link <?= $isActive ? 'active' : '' ?>">
                <span class="material-symbols-outlined"><?= htmlspecialchars($item['icon']) ?></span>
                <span><?= htmlspecialchars($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="../controllers/AuthController.php?action=logout" class="sidebar-link">
            <span class="material-symbols-outlined">logout</span>
            <span>Sign Out</span>
        </a>
    </div>
</aside>
