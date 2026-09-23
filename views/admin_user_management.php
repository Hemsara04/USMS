<?php
$activeAdminPage = 'users';
$adminName = 'Admin User';
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
            <label class="admin-search"><span class="material-symbols-outlined">search</span><input type="search" placeholder="Search records..." aria-label="Search records"></label>
            <div class="admin-top-actions"><button class="admin-icon-button" aria-label="Notifications"><span class="material-symbols-outlined">notifications</span></button><button class="admin-logout"><span class="material-symbols-outlined">logout</span> Logout</button></div>
        </header>

        <section class="admin-content">
            <div class="admin-page-header">
                <div><h1>User Management</h1><p>Manage system access, verify new registrations, and provision staff accounts across university athletics.</p></div>
                <button class="admin-primary-button"><span class="material-symbols-outlined">person_add</span> Provision Staff</button>
            </div>

            <div class="stats-grid">
                <article class="metric"><span>Total Users</span><strong>1,248</strong><small>↑ 12% this term</small></article>
                <article class="metric"><span>Active Athletes</span><strong>892</strong><small>↑ 5% this term</small></article>
                <article class="metric"><span>Pending Approvals</span><strong>14</strong><small class="urgent">2 require attention</small></article>
                <article class="metric dark"><span>Staff Accounts</span><strong>126</strong><small>Administrators and coaches</small></article>
            </div>

            <div class="users-layout">
                <section class="admin-card">
                    <div class="admin-card-header"><div><h2>Verification Queue</h2><p>New registrations awaiting review</p></div><span class="admin-chip danger">14 pending</span></div>
                    <div class="verification-list">
                        <article class="verification-item"><div class="person-row"><span class="initials">MJ</span><div><strong>Marcus Johnson</strong><small>Athlete · Basketball</small></div><time>2h ago</time></div><div class="verification-meta"><span>ID <b>98234-A</b></span><span>Eligibility <b>Freshman</b></span></div><div class="review-actions"><button class="approve">✓ Approve</button><button>✕ Reject</button></div></article>
                        <article class="verification-item"><div class="person-row"><span class="initials teal">SR</span><div><strong>Sarah Richards</strong><small>Staff · Medical</small></div><time>5h ago</time></div><div class="verification-meta"><span>Department <b>Sports Medicine</b></span><span>License <b>MD-4492</b></span></div><div class="review-actions"><button class="approve">✓ Approve</button><button>✕ Reject</button></div></article>
                    </div>
                </section>

                <section class="admin-card">
                    <div class="admin-card-header directory-header"><div><h2>Directory</h2><p>All registered users</p></div><label class="directory-search"><span class="material-symbols-outlined">search</span><input type="search" placeholder="Search ID, name, role..." aria-label="Search users"></label></div>
                    <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>User</th><th>Role</th><th>Department/Sport</th><th>Status</th><th>Actions</th></tr></thead><tbody>
                        <tr><td><strong>Dr. Robert Chen</strong><small class="subline">ADM-001</small></td><td>Admin</td><td>Athletic Governance</td><td><span class="admin-chip success">Active</span></td><td><button class="row-action">Manage</button></td></tr>
                        <tr><td><strong>Maya Patel</strong><small class="subline">ATH-492</small></td><td>Athlete</td><td>Swimming &amp; Diving</td><td><span class="admin-chip success">Active</span></td><td><button class="row-action">Manage</button></td></tr>
                        <tr><td><strong>James Taylor</strong><small class="subline">COA-112</small></td><td>Coach</td><td>Football</td><td><span class="admin-chip neutral">Inactive</span></td><td><button class="row-action">Manage</button></td></tr>
                        <tr><td><strong>David O'Connor</strong><small class="subline">STF-883</small></td><td>Equipment Manager</td><td>Facilities</td><td><span class="admin-chip danger">Suspended</span></td><td><button class="row-action">Review</button></td></tr>
                        <tr><td><strong>Linda Wu</strong><small class="subline">MED-205</small></td><td>Medical Officer</td><td>Sports Medicine</td><td><span class="admin-chip success">Active</span></td><td><button class="row-action">Manage</button></td></tr>
                    </tbody></table></div>
                    <div class="admin-empty-note">Showing 1–5 of 1,248 users</div>
                </section>
            </div>
        </section>
    </main>
</div>
<style>
    .stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:18px; }
    .metric { min-height:145px; display:flex; flex-direction:column; justify-content:space-between; padding:22px; background:#fff; border:1px solid var(--admin-border); border-radius:12px; }
    .metric > span { color:#475467; font-size:12px; letter-spacing:.08em; text-transform:uppercase; }
    .metric strong { font-size:36px; }
    .metric small { color:var(--admin-teal); }.metric small.urgent { color:var(--admin-danger); }.metric.dark { color:#fff; background:var(--admin-navy); }.metric.dark > span,.metric.dark small{color:#cbd5e1}
    .users-layout { display:grid; grid-template-columns:minmax(280px,.75fr) minmax(620px,1.8fr); gap:18px; }
    .verification-list { padding:14px; }.verification-item { padding:16px; border-radius:10px; background:#f8fafb; }.verification-item + .verification-item{margin-top:12px}
    .person-row { display:flex; align-items:center; gap:10px; }.person-row time{margin-left:auto;color:#475467;font-size:12px}.person-row strong,.person-row small{display:block}.person-row small{margin-top:3px;color:#475467;font-size:11px;text-transform:uppercase}
    .initials { width:38px;height:38px;display:grid;place-items:center;border-radius:50%;background:#d0d5dd;font-weight:700}.initials.teal{color:#086c70;background:#9ff0f0}
    .verification-meta { display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:14px 0;padding:10px;background:#fff;border-radius:7px;font-size:11px}.verification-meta b{display:block;margin-top:4px;font-size:12px}
    .review-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px}.review-actions button{min-height:35px;border:0;border-radius:6px;background:#e4e7ec;font-weight:700}.review-actions .approve{color:#fff;background:var(--admin-navy)}
    .directory-header{flex-wrap:wrap}.directory-search{display:flex;align-items:center;gap:8px;padding:0 12px;border-radius:8px;background:#f7f8fa}.directory-search input{height:40px;border:0;outline:0;background:transparent}.subline{display:block;margin-top:4px;color:#667085}.row-action{border:0;color:var(--admin-teal);background:transparent;font-weight:700;cursor:pointer}
    @media(max-width:1200px){.stats-grid{grid-template-columns:repeat(2,1fr)}.users-layout{grid-template-columns:1fr}.admin-card{min-width:0}}
    @media(max-width:600px){.stats-grid{grid-template-columns:1fr}.admin-topbar{padding:0 14px}.admin-logout{font-size:0}.admin-logout .material-symbols-outlined{font-size:22px}}
</style>
</body>
</html>

