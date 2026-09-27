<?php 
require_once __DIR__ . '/../config/auth_guard.php';
requireRole(['System_Admin', 'Admin']);
require_once __DIR__ . '/../config/db.php';

$activeAdminPage = 'seasons';
$adminName = !empty($_SESSION['username']) ? ucwords(str_replace('_', ' ', $_SESSION['username'])) : 'System Administrator';

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$pdo = Database::connect();

// Fetch seasons from DB
$seasons = [];
try {
    $stmt = $pdo->query("SELECT * FROM `seasons` ORDER BY `season_year` DESC, `season_id` DESC");
    $seasons = $stmt->fetchAll();
} catch (Exception $e) {
    $seasons = [];
}

$activeCount = 0;
foreach ($seasons as $s) {
    if ($s['status'] === 'Active') $activeCount++;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seasons Setup | USMS Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Material+Symbols+Outlined">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dashboard">
    <?php require __DIR__ . '/partials/admin_sidebar.php'; ?>
    <div class="main-wrapper">
        <?php 
        $pageTitle = 'Seasons Setup';
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

            <div class="admin-page-header">
                <div>
                    <h1>Seasons Setup</h1>
                    <p>Configure academic athletic terms, competition calendar windows, and active registration terms across all sports.</p>
                </div>
                <button type="button" class="admin-primary-button" onclick="openCreateSeasonModal()">
                    <span class="material-symbols-outlined">add</span> Create Season
                </button>
            </div>

            <div class="season-layout">
                <div>
                    <!-- Proportional 3-Stat Metric Grid -->
                    <div class="season-summary">
                        <article class="metric">
                            <span>Total Seasons</span>
                            <strong><?= count($seasons) ?></strong>
                            <small>Institutional history</small>
                        </article>
                        <article class="metric dark">
                            <span>Active Season</span>
                            <strong><?= $activeCount > 0 ? 'Active' : 'Archived' ?></strong>
                            <small><?= $activeCount ?> current operational term</small>
                        </article>
                        <article class="metric">
                            <span>Registered Term</span>
                            <strong>Term 2026</strong>
                            <small>Inter-University Championship</small>
                        </article>
                    </div>

                    <section class="admin-card">
                        <div class="admin-card-header">
                            <div>
                                <h2>Configured Academic Athletic Terms</h2>
                                <p>Operational terms and scheduling windows</p>
                            </div>
                        </div>

                        <div class="admin-table-wrap">
                            <table class="admin-table" id="seasonsTable">
                                <thead>
                                    <tr>
                                        <th>Season Record</th>
                                        <th>Calendar Year</th>
                                        <th>Academic Term</th>
                                        <th>Operational Status</th>
                                    </tr>
                                </thead>
                                <tbody id="seasonsTableBody">
                                    <?php if (empty($seasons)): ?>
                                        <tr>
                                            <td colspan="4" style="text-align:center; padding:30px; color:var(--admin-muted);">
                                                No seasons configured. Click "Create Season" to set up an athletic calendar.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($seasons as $sn): ?>
                                            <tr class="season-row" data-search="<?= htmlspecialchars(strtolower($sn['season_year'] . ' term ' . $sn['academic_year'])) ?>">
                                                <td>
                                                    <strong>Season <?= htmlspecialchars($sn['season_year']) ?></strong>
                                                    <small class="subline">Season ID #<?= $sn['season_id'] ?></small>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($sn['season_year']) ?>
                                                </td>
                                                <td>
                                                    Term <?= htmlspecialchars($sn['academic_year']) ?>
                                                </td>
                                                <td>
                                                    <?php if ($sn['status'] === 'Active'): ?>
                                                        <span class="admin-chip success">Active</span>
                                                    <?php else: ?>
                                                        <span class="admin-chip neutral">Archived</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <aside class="admin-card timeline">
                    <div class="admin-card-header">
                        <div>
                            <h2>Current Term</h2>
                            <p>Academic year progress</p>
                        </div>
                    </div>
                    <div class="timeline-body">
                        <div class="progress-ring">
                            <strong>85%</strong>
                            <span>Operational</span>
                        </div>
                        <dl>
                            <div>
                                <dt>Season Cycle</dt>
                                <dd>Academic Year 2026</dd>
                            </div>
                            <div>
                                <dt>Institutional Meet</dt>
                                <dd>Inter-Faculty &amp; SLUSA</dd>
                            </div>
                            <div>
                                <dt>Roster Status</dt>
                                <dd>Active Registrations</dd>
                            </div>
                        </dl>
                    </div>
                </aside>
        </div>
    </div>

<!-- Create Season Modal -->
<div id="createSeasonModal" class="modal-backdrop" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h3>Create Athletic Season</h3>
                <p>Establish a new calendar year and academic term for athletics.</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('createSeasonModal')">✕</button>
        </div>
        <form method="POST" action="../controllers/AdminController.php?action=create_season" class="modal-form" onsubmit="return validateSeasonForm(this)">
            <div class="form-grid">
                <div class="admin-field">
                    <label for="seasonYearInput">Calendar Year *</label>
                    <input id="seasonYearInput" type="number" name="season_year" min="2020" max="2040" value="2026" required>
                    <small class="field-hint">Must be between 2020 and 2040 (non-negative)</small>
                </div>
                <div class="admin-field">
                    <label for="academicYearInput">Academic Term *</label>
                    <select id="academicYearInput" name="academic_year" required>
                        <option value="1">Term 1 (Semester 1)</option>
                        <option value="2">Term 2 (Semester 2)</option>
                        <option value="3" selected>Term 3 (Championship)</option>
                        <option value="4">Term 4 (Off-season)</option>
                    </select>
                </div>
                <div class="admin-field full-col">
                    <label for="seasonStatusInput">Status *</label>
                    <select id="seasonStatusInput" name="status" required>
                        <option value="Active">Active (Current Operational Term)</option>
                        <option value="Archived">Archived (Historical Record)</option>
                    </select>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="admin-secondary-button" onclick="closeModal('createSeasonModal')">Cancel</button>
                <button type="submit" class="admin-primary-button">Establish Season</button>
            </div>
        </form>
    </div>
</div>

<style>
    .topbar-search-wrap { display: flex; align-items: center; gap: 8px; background: #f8fafc; padding: 6px 12px; border-radius: 6px; border: 1px solid var(--admin-border); }
    .topbar-search-wrap input { width: min(240px, 25vw); border: 0; outline: 0; background: transparent; font-size: 13px; }

    .season-layout { display: grid; grid-template-columns: minmax(620px, 1fr) 280px; gap: 16px; }
    .season-summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 16px; }
    .season-summary .metric { min-height: 95px; display: flex; flex-direction: column; justify-content: space-between; padding: 16px 18px; background: #fff; border: 1px solid var(--admin-border); border-radius: 8px; }
    .season-summary .metric > span { color: #475467; font-size: 11.5px; letter-spacing: .05em; text-transform: uppercase; font-weight: 600; }
    .season-summary .metric strong { font-size: 24px; font-weight: 700; color: #101828; }
    .season-summary .metric small { color: var(--admin-teal); font-size: 11.5px; font-weight: 500; }
    .season-summary .metric.dark { color: #fff; background: var(--admin-navy); }
    .season-summary .metric.dark > span, .season-summary .metric.dark small { color: #cbd5e1; }
    .season-summary .metric.dark strong { color: #fff; }

    .timeline-body { padding: 18px; }
    .progress-ring { width: 120px; height: 120px; display: flex; flex-direction: column; align-items: center; justify-content: center; margin: 0 auto 18px; border: 10px solid #e0f2fe; border-top-color: var(--admin-teal); border-right-color: var(--admin-teal); border-radius: 50%; }
    .progress-ring strong { font-size: 22px; font-weight: 700; color: var(--admin-navy); }
    .progress-ring span { color: #64748b; font-size: 11px; }
    .timeline dl { margin: 0; }
    .timeline dl div { padding: 10px 0; border-top: 1px solid var(--admin-border); }
    .timeline dt { color: #64748b; font-size: 11.5px; }
    .timeline dd { margin: 3px 0 0; font-weight: 600; font-size: 13px; color: var(--admin-text); }

    /* Modals */
    .modal-backdrop { position: fixed; inset: 0; z-index: 999; background: rgba(7, 22, 45, .6); backdrop-filter: blur(2px); display: grid; place-items: center; padding: 18px; }
    .modal-box { width: 100%; max-width: 480px; background: #fff; border-radius: 10px; box-shadow: 0 16px 36px rgba(0,0,0,.2); overflow: hidden; }
    .modal-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 16px 20px; border-bottom: 1px solid var(--admin-border); }
    .modal-header h3 { margin: 0 0 3px; font-size: 16px; font-weight: 700; color: var(--admin-navy); }
    .modal-header p { margin: 0; font-size: 12px; color: var(--admin-muted); }
    .modal-close-btn { border: 0; background: transparent; font-size: 16px; cursor: pointer; color: var(--admin-muted); }
    .modal-form { padding: 18px 20px; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .full-col { grid-column: span 2; }
    .field-hint { color: var(--admin-muted); font-size: 11px; margin-top: 2px; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--admin-border); }

    /* Flash Alerts */
    .admin-alert { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 13px; font-weight: 500; }
    .success-alert { background: #ecfdf3; color: #067647; border: 1px solid #abefc6; }
    .error-alert { background: #fffbeb; color: #b54708; border: 1px solid #fedf89; }
    .alert-dismiss { margin-left: auto; border: 0; background: transparent; font-size: 15px; cursor: pointer; color: inherit; }

    @media (max-width: 1200px) {
        .season-layout { grid-template-columns: 1fr; }
        .timeline { max-width: none; }
    }
    @media (max-width: 700px) {
        .season-summary { grid-template-columns: 1fr; }
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

function openCreateSeasonModal() {
    openModal('createSeasonModal');
}

function filterSeasons(query) {
    const q = query.trim().toLowerCase();
    const rows = document.querySelectorAll('#seasonsTableBody tr.season-row');
    rows.forEach(row => {
        const text = row.getAttribute('data-search') || '';
        row.style.display = (!q || text.includes(q)) ? '' : 'none';
    });
}

// In-memory logical guard before submission
function validateSeasonForm(form) {
    const yrInput = form.querySelector('input[name="season_year"]');
    if (!yrInput) return true;
    const yr = parseInt(yrInput.value, 10);
    if (isNaN(yr) || yr < 2020 || yr > 2040) {
        showToast('Season year must be a realistic positive year (between 2020 and 2040).', 'warning', 'Invalid Year');
        yrInput.focus();
        return false;
    }
    return true;
}

window.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-backdrop')) {
        e.target.style.display = 'none';
    }
});
</script>
</body>
</html>
