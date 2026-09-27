<?php 
require_once __DIR__ . '/../config/auth_guard.php';
requireRole(['System_Admin', 'Admin']);
$activeAdminPage = 'audit'; 
$adminName = !empty($_SESSION['username']) ? ucwords(str_replace('_', ' ', $_SESSION['username'])) : 'System Administrator';

// Structured audit log records
$auditEvents = [
    [
        'id' => 1,
        'time' => '09:42:16',
        'date' => 'Sep 24, 2026',
        'actor' => 'Admin User',
        'actor_code' => 'ADM-001',
        'event' => 'User Account Approved',
        'type' => 'User management',
        'resource' => 'ATH-98234-A',
        'ip' => '192.168.1.42',
        'outcome' => 'Success',
        'details' => 'Administrative verification of varsity registration for Kasun Perera (Faculty of Science).'
    ],
    [
        'id' => 2,
        'time' => '09:18:03',
        'date' => 'Sep 24, 2026',
        'actor' => 'TIC Officer',
        'actor_code' => 'TIC-002',
        'event' => 'Equipment Checkout Approved',
        'type' => 'Configuration',
        'resource' => 'REQ-8841-EQ',
        'ip' => '192.168.1.18',
        'outcome' => 'Success',
        'details' => 'Released 12 match balls and cones for Coach training session at Grounds Pavilion.'
    ],
    [
        'id' => 3,
        'time' => '08:55:48',
        'date' => 'Sep 24, 2026',
        'actor' => 'System Guard',
        'actor_code' => 'SYS-AUTO',
        'event' => 'Repeated Failed Login',
        'type' => 'Authentication',
        'resource' => 'COA-112',
        'ip' => '203.0.113.24',
        'outcome' => 'Warning',
        'details' => '3 incorrect password attempts recorded from external IP. Session temporarily throttled.'
    ],
    [
        'id' => 4,
        'time' => '08:31:27',
        'date' => 'Sep 24, 2026',
        'actor' => 'Admin User',
        'actor_code' => 'ADM-001',
        'event' => 'User Directory Export',
        'type' => 'Data export',
        'resource' => 'USR-DIR-2026',
        'ip' => '192.168.1.42',
        'outcome' => 'Success',
        'details' => 'Exported athlete roster audit log to CSV for Sports Council annual report.'
    ],
    [
        'id' => 5,
        'time' => '07:59:11',
        'date' => 'Sep 24, 2026',
        'actor' => 'Medical Officer',
        'actor_code' => 'MED-003',
        'event' => 'Clearance Status Updated',
        'type' => 'Configuration',
        'resource' => 'ATH-492',
        'ip' => '198.51.100.16',
        'outcome' => 'Success',
        'details' => 'Medical fitness clearance certified after routine physical exam.'
    ]
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Audit Logs | USMS Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Material+Symbols+Outlined">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dashboard">
    <?php require __DIR__ . '/partials/admin_sidebar.php'; ?>
    <div class="main-wrapper">
        <?php 
        $pageTitle = 'Audit Logs';
        require __DIR__ . '/partials/tic_header.php'; 
        ?>
        <div class="page-content">
            <div class="admin-page-header">
                <div>
                    <h1>Audit Logs</h1>
                    <p>Review security audit trails, credential operations, and administrative actions across the USMS platform.</p>
                </div>
            </div>

            <!-- Proportional 4-Stat Metric Grid -->
            <div class="audit-overview">
                <article class="metric">
                    <span>Events Today</span>
                    <strong>386</strong>
                    <small>Normal activity volume</small>
                </article>
                <article class="metric">
                    <span>Admin Changes</span>
                    <strong>42</strong>
                    <small>System operations logged</small>
                </article>
                <article class="metric">
                    <span>Security Notices</span>
                    <strong style="color:var(--admin-danger);">3</strong>
                    <small style="color:var(--admin-danger); font-weight:600;">Monitored events</small>
                </article>
                <article class="metric dark">
                    <span>Retention Policy</span>
                    <strong>365 Days</strong>
                    <small>Policy-compliant logging</small>
                </article>
            </div>

            <section class="admin-card">
                <div class="audit-filters">
                    <div class="admin-field">
                        <label for="audit-search">Keyword or Actor</label>
                        <input id="audit-search" type="search" placeholder="Search event, user, IP..." oninput="handleAuditFilter()">
                    </div>
                    <div class="admin-field">
                        <label for="audit-type">Event Type</label>
                        <select id="audit-type" onchange="handleAuditFilter()">
                            <option value="">All Events</option>
                            <option value="Authentication">Authentication</option>
                            <option value="User management">User Management</option>
                            <option value="Configuration">Configuration</option>
                            <option value="Data export">Data Export</option>
                        </select>
                    </div>
                    <div class="admin-field">
                        <label for="audit-status">Outcome</label>
                        <select id="audit-status" onchange="handleAuditFilter()">
                            <option value="">All Outcomes</option>
                            <option value="Success">Success</option>
                            <option value="Warning">Warning</option>
                        </select>
                    </div>
                    <button type="button" class="admin-secondary-button" style="height:36px;" onclick="resetAuditFilters()">Reset</button>
                </div>

                <div class="admin-table-wrap">
                    <table class="admin-table audit-table">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Actor</th>
                                <th>Event Type</th>
                                <th>Resource</th>
                                <th>IP Address</th>
                                <th>Outcome</th>
                                <th style="text-align:right;">Details</th>
                            </tr>
                        </thead>
                        <tbody id="auditTableBody">
                            <?php foreach ($auditEvents as $ev): ?>
                                <tr class="audit-row" 
                                    data-search="<?= htmlspecialchars(strtolower($ev['actor'] . ' ' . $ev['actor_code'] . ' ' . $ev['event'] . ' ' . $ev['resource'] . ' ' . $ev['ip'])) ?>"
                                    data-type="<?= htmlspecialchars($ev['type']) ?>"
                                    data-outcome="<?= htmlspecialchars($ev['outcome']) ?>">
                                    <td>
                                        <strong><?= htmlspecialchars($ev['time']) ?></strong>
                                        <small class="subline"><?= htmlspecialchars($ev['date']) ?></small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($ev['actor']) ?></strong>
                                        <small class="subline"><?= htmlspecialchars($ev['actor_code']) ?></small>
                                    </td>
                                    <td>
                                        <span class="event-cell">
                                            <span class="event-icon material-symbols-outlined">history</span>
                                            <span><?= htmlspecialchars($ev['event']) ?></span>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="mono"><?= htmlspecialchars($ev['resource']) ?></span>
                                    </td>
                                    <td>
                                        <span class="mono"><?= htmlspecialchars($ev['ip']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($ev['outcome'] === 'Success'): ?>
                                            <span class="admin-chip success">Success</span>
                                        <?php else: ?>
                                            <span class="admin-chip warning">Warning</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <button type="button" class="detail-button" onclick='openAuditDetail(<?= json_encode($ev) ?>)'>View</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="log-footer">
                    <span id="auditCountInfo">Showing 1–<?= count($auditEvents) ?> of <?= count($auditEvents) ?> verified security events</span>
                </div>
        </div>
    </div>

<!-- Event Detail Modal -->
<div id="auditDetailModal" class="modal-backdrop" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h3>Audit Log Event Details</h3>
                <p>Immutable system audit transaction</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('auditDetailModal')">✕</button>
        </div>
        <div class="modal-form">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                <div>
                    <small style="color:var(--admin-muted); font-size:11px; text-transform:uppercase; font-weight:600;">Event Name</small>
                    <div id="modalEventName" style="font-weight:700; margin-top:2px; font-size:13.5px;"></div>
                </div>
                <div>
                    <small style="color:var(--admin-muted); font-size:11px; text-transform:uppercase; font-weight:600;">Timestamp</small>
                    <div id="modalEventTime" style="font-weight:600; margin-top:2px; font-size:13px;"></div>
                </div>
                <div>
                    <small style="color:var(--admin-muted); font-size:11px; text-transform:uppercase; font-weight:600;">Actor ID</small>
                    <div id="modalEventActor" style="font-weight:600; margin-top:2px; font-size:13px;"></div>
                </div>
                <div>
                    <small style="color:var(--admin-muted); font-size:11px; text-transform:uppercase; font-weight:600;">Target Resource</small>
                    <div id="modalEventResource" style="font-weight:600; margin-top:2px; font-size:13px;" class="mono"></div>
                </div>
            </div>
            <div style="border-top:1px solid var(--admin-border); padding-top:12px;">
                <small style="color:var(--admin-muted); font-size:11px; text-transform:uppercase; font-weight:600;">Transaction Log Details</small>
                <p id="modalEventDetails" style="margin:4px 0 0; color:var(--admin-text); line-height:1.55; font-size:13px;"></p>
            </div>
            <div class="modal-actions">
                <button type="button" class="admin-primary-button" onclick="closeModal('auditDetailModal')">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .topbar-search-wrap { display: flex; align-items: center; gap: 8px; background: #f8fafc; padding: 6px 12px; border-radius: 6px; border: 1px solid var(--admin-border); }
    .topbar-search-wrap input { width: min(240px, 25vw); border: 0; outline: 0; background: transparent; font-size: 13px; }

    .audit-overview { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 18px; }
    .audit-overview .metric { min-height: 95px; display: flex; flex-direction: column; justify-content: space-between; padding: 16px 18px; background: #fff; border: 1px solid var(--admin-border); border-radius: 8px; }
    .audit-overview .metric > span { color: #475467; font-size: 11.5px; letter-spacing: .05em; text-transform: uppercase; font-weight: 600; }
    .audit-overview .metric strong { font-size: 24px; font-weight: 700; color: #101828; }
    .audit-overview .metric small { color: var(--admin-teal); font-size: 11.5px; font-weight: 500; }
    .audit-overview .metric.dark { color: #fff; background: var(--admin-navy); }
    .audit-overview .metric.dark > span, .audit-overview .metric.dark small { color: #cbd5e1; }
    .audit-overview .metric.dark strong { color: #fff; }

    .audit-filters { display: grid; grid-template-columns: 1.5fr repeat(2, 1fr) auto; align-items: end; gap: 10px; padding: 14px 18px; border-bottom: 1px solid var(--admin-border); }
    .subline { display: block; margin-top: 2px; color: #64748b; font-size: 11.5px; }
    .mono { font-family: monospace; font-size: 11.5px; color: #475467; }
    .event-cell { display: flex; align-items: center; gap: 8px; }
    .event-icon { width: 30px; height: 30px; display: grid; place-items: center; border-radius: 6px; color: var(--admin-teal); background: #e9f7f7; font-size: 17px; flex-shrink: 0; }
    .detail-button { border: 0; color: var(--admin-teal); background: transparent; font-weight: 700; cursor: pointer; font-size: 12.5px; padding: 3px 6px; }
    .detail-button:hover { text-decoration: underline; }
    
    .log-footer { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 12px 18px; color: #64748b; font-size: 12.5px; border-top: 1px solid var(--admin-border); }

    /* Modals */
    .modal-backdrop { position: fixed; inset: 0; z-index: 999; background: rgba(7, 22, 45, .6); backdrop-filter: blur(2px); display: grid; place-items: center; padding: 18px; }
    .modal-box { width: 100%; max-width: 480px; background: #fff; border-radius: 10px; box-shadow: 0 16px 36px rgba(0,0,0,.2); overflow: hidden; }
    .modal-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 16px 20px; border-bottom: 1px solid var(--admin-border); }
    .modal-header h3 { margin: 0 0 3px; font-size: 16px; font-weight: 700; color: var(--admin-navy); }
    .modal-header p { margin: 0; font-size: 12px; color: var(--admin-muted); }
    .modal-close-btn { border: 0; background: transparent; font-size: 16px; cursor: pointer; color: var(--admin-muted); }
    .modal-form { padding: 18px 20px; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px; padding-top: 12px; border-top: 1px solid var(--admin-border); }

    @media (max-width: 1200px) {
        .audit-overview { grid-template-columns: repeat(2, 1fr); }
        .audit-filters { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 650px) {
        .audit-overview, .audit-filters { grid-template-columns: 1fr; }
    }
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

function openAuditDetail(ev) {
    document.getElementById('modalEventName').textContent = ev.event;
    document.getElementById('modalEventTime').textContent = `${ev.date} at ${ev.time}`;
    document.getElementById('modalEventActor').textContent = `${ev.actor} (${ev.actor_code})`;
    document.getElementById('modalEventResource').textContent = `${ev.resource} [IP: ${ev.ip}]`;
    document.getElementById('modalEventDetails').textContent = ev.details;
    openModal('auditDetailModal');
}

function handleAuditFilter() {
    const q = document.getElementById('audit-search').value.trim().toLowerCase();
    const type = document.getElementById('audit-type').value;
    const outcome = document.getElementById('audit-status').value;
    const rows = document.querySelectorAll('#auditTableBody tr.audit-row');

    let count = 0;
    rows.forEach(row => {
        const text = row.getAttribute('data-search') || '';
        const rowType = row.getAttribute('data-type') || '';
        const rowOutcome = row.getAttribute('data-outcome') || '';

        const matchQ = !q || text.includes(q);
        const matchType = !type || rowType === type;
        const matchOutcome = !outcome || rowOutcome === outcome;

        if (matchQ && matchType && matchOutcome) {
            row.style.display = '';
            count++;
        } else {
            row.style.display = 'none';
        }
    });

    const info = document.getElementById('auditCountInfo');
    if (info) {
        info.textContent = `Showing ${count} verified security events`;
    }
}

function liveFilterAudit(val) {
    const sInput = document.getElementById('audit-search');
    if (sInput) {
        sInput.value = val;
        handleAuditFilter();
    }
}

function resetAuditFilters() {
    document.getElementById('audit-search').value = '';
    document.getElementById('audit-type').value = '';
    document.getElementById('audit-status').value = '';
    const top = document.getElementById('auditTopSearch');
    if (top) top.value = '';
    handleAuditFilter();
}

window.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-backdrop')) {
        e.target.style.display = 'none';
    }
});
</script>
</body>
</html>
