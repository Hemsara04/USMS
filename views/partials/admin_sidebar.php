<?php
$activeAdminPage = $activeAdminPage ?? '';
$adminName = $adminName ?? 'Admin User';

$adminNavigation = [
    'dashboard' => ['label' => 'Dashboard', 'icon' => 'dashboard', 'href' => '#'],
    'users' => ['label' => 'User Management', 'icon' => 'group', 'href' => 'admin_user_management.php'],
    'sports' => ['label' => 'Sports Catalog', 'icon' => 'sports_basketball', 'href' => 'admin_sports_catalog.php'],
    'seasons' => ['label' => 'Seasons Setup', 'icon' => 'calendar_month', 'href' => 'admin_seasons_setup.php'],
    'audit' => ['label' => 'Audit Logs', 'icon' => 'history', 'href' => 'admin_audit_logs.php'],
];
?>

<style>
    :root {
        --admin-navy: #07162d;
        --admin-navy-soft: #1b2940;
        --admin-teal: #087f80;
        --admin-teal-soft: #89e4e6;
        --admin-bg: #f5f7f8;
        --admin-card: #ffffff;
        --admin-text: #171a1d;
        --admin-muted: #667085;
        --admin-border: #e1e5e9;
        --admin-danger: #b42318;
        --admin-warning: #b54708;
        --admin-success: #067647;
    }

    * { box-sizing: border-box; }
    body { margin: 0; color: var(--admin-text); background: var(--admin-bg); font-family: Inter, Arial, sans-serif; }
    button, input, select { font: inherit; }
    .admin-shell { min-height: 100vh; }
    .admin-sidebar { position: fixed; inset: 0 auto 0 0; z-index: 20; width: 280px; display: flex; flex-direction: column; background: var(--admin-navy-soft); color: #fff; }
    .admin-brand { height: 84px; display: flex; align-items: center; gap: 14px; padding: 0 24px; border-bottom: 1px solid rgba(255,255,255,.1); font-size: 23px; font-weight: 750; }
    .admin-brand-mark { width: 40px; height: 40px; display: grid; place-items: center; border-radius: 6px; color: var(--admin-navy); background: #fff; }
    .admin-nav { display: grid; gap: 8px; padding: 28px 16px; }
    .admin-nav a { min-height: 48px; display: flex; align-items: center; gap: 14px; padding: 0 18px; color: #aab5ca; border-left: 4px solid transparent; border-radius: 9px; font-size: 14px; font-weight: 700; letter-spacing: .04em; text-decoration: none; text-transform: uppercase; }
    .admin-nav a:hover { color: #fff; background: rgba(255,255,255,.06); }
    .admin-nav a.is-active { color: #075f65; background: var(--admin-teal-soft); border-left-color: var(--admin-teal); }
    .admin-nav .material-symbols-outlined { font-size: 23px; }
    .admin-profile { margin-top: auto; display: flex; align-items: center; gap: 12px; padding: 24px; border-top: 1px solid rgba(255,255,255,.1); }
    .admin-avatar { width: 40px; height: 40px; display: grid; place-items: center; flex: 0 0 auto; border-radius: 50%; background: var(--admin-teal); }
    .admin-profile strong, .admin-profile span { display: block; }
    .admin-profile span { margin-top: 2px; color: #aab5ca; font-size: 12px; }
    .admin-main { min-height: 100vh; margin-left: 280px; }
    .admin-topbar { height: 64px; display: flex; align-items: center; justify-content: space-between; padding: 0 28px; background: #fff; border-bottom: 1px solid var(--admin-border); }
    .admin-search { display: flex; align-items: center; gap: 10px; color: var(--admin-muted); }
    .admin-search input { width: min(340px, 38vw); border: 0; outline: 0; background: transparent; }
    .admin-top-actions { display: flex; align-items: center; gap: 18px; }
    .admin-icon-button { width: 40px; height: 40px; display: grid; place-items: center; border: 0; border-radius: 8px; background: transparent; cursor: pointer; }
    .admin-logout { display: inline-flex; align-items: center; gap: 8px; min-height: 38px; padding: 0 16px; border: 1px solid #98a2b3; border-radius: 8px; background: #fff; cursor: pointer; }
    .admin-content { padding: 30px; }
    .admin-page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; margin-bottom: 24px; }
    .admin-page-header h1 { margin: 0 0 6px; font-size: clamp(28px, 3vw, 38px); letter-spacing: -.035em; }
    .admin-page-header p { max-width: 760px; margin: 0; color: #475467; line-height: 1.55; }
    .admin-primary-button, .admin-secondary-button { min-height: 40px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 16px; border-radius: 8px; font-weight: 700; cursor: pointer; }
    .admin-primary-button { color: #fff; background: var(--admin-navy); border: 1px solid var(--admin-navy); }
    .admin-secondary-button { color: var(--admin-text); background: #fff; border: 1px solid var(--admin-border); }
    .admin-card { background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: 12px; overflow: hidden; }
    .admin-card-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 20px 22px; border-bottom: 1px solid var(--admin-border); }
    .admin-card-header h2 { margin: 0; font-size: 19px; }
    .admin-card-header p { margin: 5px 0 0; color: var(--admin-muted); font-size: 13px; }
    .admin-table-wrap { overflow-x: auto; }
    .admin-table { width: 100%; border-collapse: collapse; }
    .admin-table th { padding: 13px 18px; color: #344054; background: #f8fafb; font-size: 12px; letter-spacing: .06em; text-align: left; text-transform: uppercase; white-space: nowrap; }
    .admin-table td { padding: 16px 18px; border-top: 1px solid var(--admin-border); font-size: 14px; vertical-align: middle; }
    .admin-table tbody tr:hover { background: #fbfcfc; }
    .admin-chip { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 999px; font-size: 12px; font-weight: 650; white-space: nowrap; }
    .admin-chip::before { width: 6px; height: 6px; content: ''; border-radius: 50%; background: currentColor; }
    .admin-chip.success { color: var(--admin-success); background: #ecfdf3; }
    .admin-chip.warning { color: var(--admin-warning); background: #fffaeb; }
    .admin-chip.danger { color: var(--admin-danger); background: #fef3f2; }
    .admin-chip.neutral { color: #475467; background: #f2f4f7; }
    .admin-field { display: grid; gap: 6px; }
    .admin-field label { color: #344054; font-size: 12px; font-weight: 700; }
    .admin-field input, .admin-field select { min-height: 40px; width: 100%; padding: 0 12px; border: 1px solid #d0d5dd; border-radius: 8px; background: #fff; outline: none; }
    .admin-field input:focus, .admin-field select:focus { border-color: var(--admin-teal); box-shadow: 0 0 0 3px rgba(8,127,128,.12); }
    .admin-empty-note { padding: 18px 22px; color: var(--admin-muted); font-size: 13px; border-top: 1px solid var(--admin-border); }

    @media (max-width: 900px) {
        .admin-sidebar { position: static; width: 100%; }
        .admin-brand, .admin-profile { display: none; }
        .admin-nav { grid-template-columns: repeat(4, minmax(145px, 1fr)); padding: 10px; overflow-x: auto; }
        .admin-nav a:first-child { display: none; }
        .admin-main { margin-left: 0; }
        .admin-content { padding: 20px 16px; }
        .admin-page-header { flex-direction: column; }
    }
</style>

<aside class="admin-sidebar" aria-label="Administrator navigation">
    <div class="admin-brand">
        <span class="admin-brand-mark material-symbols-outlined">trophy</span>
        <span>USMS Admin</span>
    </div>

    <nav class="admin-nav">
        <?php foreach ($adminNavigation as $key => $item): ?>
            <a href="<?= htmlspecialchars($item['href']) ?>" class="<?= $activeAdminPage === $key ? 'is-active' : '' ?>">
                <span class="material-symbols-outlined" aria-hidden="true"><?= htmlspecialchars($item['icon']) ?></span>
                <span><?= htmlspecialchars($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="admin-profile">
        <span class="admin-avatar material-symbols-outlined" aria-hidden="true">person</span>
        <div>
            <strong><?= htmlspecialchars($adminName) ?></strong>
            <span>Global Administrator</span>
        </div>
    </div>
</aside>

