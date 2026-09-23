<?php $activeAdminPage = 'sports'; ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sports Catalog | USMS Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Material+Symbols+Outlined">
</head>
<body><div class="admin-shell">
<?php require __DIR__ . '/partials/admin_sidebar.php'; ?>
<main class="admin-main">
    <header class="admin-topbar"><label class="admin-search"><span class="material-symbols-outlined">search</span><input type="search" placeholder="Search sports..." aria-label="Search sports"></label><div class="admin-top-actions"><button class="admin-icon-button" aria-label="Notifications"><span class="material-symbols-outlined">notifications</span></button><button class="admin-logout"><span class="material-symbols-outlined">logout</span> Logout</button></div></header>
    <section class="admin-content">
        <div class="admin-page-header"><div><h1>Sports Catalog</h1><p>Manage university sports, athletic categories, roster types, and availability across the institution.</p></div><div class="header-actions"><button class="admin-secondary-button"><span class="material-symbols-outlined">download</span> Export List</button><button class="admin-primary-button"><span class="material-symbols-outlined">add</span> Add New Sport</button></div></div>
        <div class="catalog-stats"><article><span>Total Sports</span><strong>24</strong><small>+2 this year</small></article><article><span>Active Programs</span><strong>21</strong><small>87.5% of catalog</small></article><article class="dark"><span>Most Popular</span><strong>Football</strong><small>186 registered athletes</small></article></div>
        <section class="admin-card">
            <div class="admin-card-header"><div><h2>Configured Sports</h2><p>University-recognized athletic programs</p></div><div class="catalog-filters"><select aria-label="Filter by program"><option>All programs</option><option>Men's</option><option>Women's</option><option>Co-ed</option></select><select aria-label="Filter by status"><option>All statuses</option><option>Active</option><option>Archived</option></select></div></div>
            <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Sport Name</th><th>Program</th><th>Category</th><th>Athletes</th><th>Status</th><th>Actions</th></tr></thead><tbody>
                <tr><td><span class="sport-cell"><span class="sport-icon material-symbols-outlined">sports_football</span><span><strong>Football</strong><small>Outdoor team sport</small></span></span></td><td>Men's Varsity</td><td>Fall Sport</td><td>186</td><td><span class="admin-chip success">Active</span></td><td><button class="action-menu">Edit</button></td></tr>
                <tr><td><span class="sport-cell"><span class="sport-icon material-symbols-outlined">pool</span><span><strong>Swimming &amp; Diving</strong><small>Aquatic sport</small></span></span></td><td>Co-ed Varsity</td><td>Winter Sport</td><td>74</td><td><span class="admin-chip success">Active</span></td><td><button class="action-menu">Edit</button></td></tr>
                <tr><td><span class="sport-cell"><span class="sport-icon material-symbols-outlined">sports_tennis</span><span><strong>Tennis</strong><small>Racquet sport</small></span></span></td><td>Women's Club</td><td>Spring Sport</td><td>32</td><td><span class="admin-chip neutral">Archived</span></td><td><button class="action-menu">Review</button></td></tr>
                <tr><td><span class="sport-cell"><span class="sport-icon material-symbols-outlined">sprint</span><span><strong>Track &amp; Field</strong><small>Indoor and outdoor</small></span></span></td><td>Co-ed Varsity</td><td>Spring Sport</td><td>112</td><td><span class="admin-chip success">Active</span></td><td><button class="action-menu">Edit</button></td></tr>
                <tr><td><span class="sport-cell"><span class="sport-icon material-symbols-outlined">sports_basketball</span><span><strong>Basketball</strong><small>Indoor team sport</small></span></span></td><td>Men's &amp; Women's</td><td>Winter Sport</td><td>58</td><td><span class="admin-chip success">Active</span></td><td><button class="action-menu">Edit</button></td></tr>
            </tbody></table></div><div class="admin-empty-note">Showing 1–5 of 24 sports</div>
        </section>
    </section>
</main></div>
<style>
    .header-actions,.catalog-filters{display:flex;gap:10px;flex-wrap:wrap}.catalog-filters select{min-height:38px;padding:0 12px;border:1px solid var(--admin-border);border-radius:8px;background:#fff}
    .catalog-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-bottom:18px}.catalog-stats article{min-height:145px;display:flex;flex-direction:column;justify-content:space-between;padding:22px;background:#fff;border:1px solid var(--admin-border);border-radius:12px}.catalog-stats span{color:#475467;font-size:12px;letter-spacing:.08em;text-transform:uppercase}.catalog-stats strong{font-size:36px}.catalog-stats small{color:var(--admin-teal)}.catalog-stats .dark{color:#fff;background:var(--admin-navy)}.catalog-stats .dark span,.catalog-stats .dark small{color:#cbd5e1}.catalog-stats .dark strong{font-size:28px}
    .sport-cell{display:flex;align-items:center;gap:12px;min-width:210px}.sport-cell strong,.sport-cell small{display:block}.sport-cell small{margin-top:3px;color:#667085}.sport-icon{width:40px;height:40px;display:grid;place-items:center;border-radius:8px;background:#edf0f2}.action-menu{border:0;color:var(--admin-teal);background:transparent;font-weight:700;cursor:pointer}
    @media(max-width:850px){.catalog-stats{grid-template-columns:1fr}.header-actions{width:100%}.header-actions button{flex:1}}
</style></body></html>

