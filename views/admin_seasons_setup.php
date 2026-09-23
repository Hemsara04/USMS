<?php 
require_once __DIR__ . '/../config/auth_guard.php';
requireRole(['System_Admin', 'Admin']);
$activeAdminPage = 'seasons'; 
?>
<!doctype html>
<html lang="en"><head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seasons Setup | USMS Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Material+Symbols+Outlined">
</head><body><div class="admin-shell">
<?php require __DIR__ . '/partials/admin_sidebar.php'; ?>
<main class="admin-main">
    <header class="admin-topbar"><label class="admin-search"><span class="material-symbols-outlined">search</span><input type="search" placeholder="Search seasons..." aria-label="Search seasons"></label><div class="admin-top-actions"><button class="admin-icon-button" aria-label="Notifications"><span class="material-symbols-outlined">notifications</span></button><a href="../controllers/AuthController.php?action=logout" class="admin-logout" style="text-decoration:none; color:inherit;"><span class="material-symbols-outlined">logout</span> Logout</a></div></header>
    <section class="admin-content">
        <div class="admin-page-header"><div><h1>Seasons Setup</h1><p>Configure academic athletic terms, registration periods, and scheduling windows for every sport.</p></div><button class="admin-primary-button"><span class="material-symbols-outlined">add</span> Create Season</button></div>
        <div class="season-layout">
            <div>
                <div class="season-summary"><article><span>Active Seasons</span><strong>3</strong><small>Fall, Winter, Spring</small></article><article class="current"><span>Current Term</span><strong>Spring '24</strong><small>Ends in 42 days</small></article><article><span>Open Registrations</span><strong>8</strong><small>Across 6 sports</small></article></div>
                <section class="admin-card"><div class="admin-card-header"><div><h2>Season Calendar</h2><p>Configured academic athletic terms</p></div><select class="year-filter" aria-label="Academic year"><option>2024–2025</option><option>2023–2024</option></select></div>
                    <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Season</th><th>Start Date</th><th>End Date</th><th>Sports</th><th>Registration</th><th>Status</th><th></th></tr></thead><tbody>
                        <tr><td><strong>Spring 2024</strong><small class="subline">Current term</small></td><td>Jan 15, 2024</td><td>May 30, 2024</td><td>9</td><td>Closed</td><td><span class="admin-chip success">Active</span></td><td><button class="season-action">Edit</button></td></tr>
                        <tr><td><strong>Fall 2024</strong><small class="subline">Upcoming</small></td><td>Aug 20, 2024</td><td>Dec 15, 2024</td><td>8</td><td>Jun 01–Jul 31</td><td><span class="admin-chip warning">Scheduled</span></td><td><button class="season-action">Edit</button></td></tr>
                        <tr><td><strong>Winter 2023</strong><small class="subline">Previous term</small></td><td>Nov 10, 2023</td><td>Mar 05, 2024</td><td>7</td><td>Closed</td><td><span class="admin-chip neutral">Archived</span></td><td><button class="season-action">View</button></td></tr>
                    </tbody></table></div>
                </section>
            </div>
            <aside class="admin-card timeline"><div class="admin-card-header"><div><h2>Current Season</h2><p>Spring 2024 progress</p></div></div><div class="timeline-body"><div class="progress-ring"><strong>70%</strong><span>Complete</span></div><dl><div><dt>Season begins</dt><dd>Jan 15, 2024</dd></div><div><dt>Roster lock</dt><dd>Feb 02, 2024</dd></div><div><dt>Competition window</dt><dd>Feb 10–May 12</dd></div><div><dt>Season ends</dt><dd>May 30, 2024</dd></div></dl><button class="admin-secondary-button wide">View Full Schedule</button></div></aside>
        </div>
    </section>
</main></div>
<style>
    .season-layout{display:grid;grid-template-columns:minmax(650px,1fr) 310px;gap:18px}.season-summary{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:18px}.season-summary article{min-height:145px;display:flex;flex-direction:column;justify-content:space-between;padding:22px;background:#fff;border:1px solid var(--admin-border);border-radius:12px}.season-summary span{color:#475467;font-size:12px;letter-spacing:.08em;text-transform:uppercase}.season-summary strong{font-size:30px}.season-summary small{color:#667085}.season-summary .current{color:#fff;background:var(--admin-navy)}.season-summary .current span,.season-summary .current small{color:#b9c5d7}.year-filter{min-height:38px;padding:0 12px;border:1px solid var(--admin-border);border-radius:8px;background:#fff}.subline{display:block;margin-top:4px;color:#667085}.season-action{border:0;color:var(--admin-teal);background:transparent;font-weight:700;cursor:pointer}.timeline-body{padding:24px}.progress-ring{width:150px;height:150px;display:flex;flex-direction:column;align-items:center;justify-content:center;margin:0 auto 24px;border:14px solid #dceff0;border-top-color:var(--admin-teal);border-right-color:var(--admin-teal);border-radius:50%}.progress-ring strong{font-size:30px}.progress-ring span{color:#667085;font-size:12px}.timeline dl{margin:0}.timeline dl div{padding:15px 0;border-top:1px solid var(--admin-border)}.timeline dt{color:#667085;font-size:12px}.timeline dd{margin:5px 0 0;font-weight:650}.wide{width:100%;margin-top:12px}
    @media(max-width:1200px){.season-layout{grid-template-columns:1fr}.timeline{max-width:none}.season-summary{grid-template-columns:repeat(3,1fr)}}@media(max-width:700px){.season-summary{grid-template-columns:1fr}}
</style></body></html>

