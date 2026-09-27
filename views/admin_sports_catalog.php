<?php 
require_once __DIR__ . '/../config/auth_guard.php';
requireRole(['System_Admin', 'Admin']);
require_once __DIR__ . '/../config/db.php';

$activeAdminPage = 'sports';
$adminName = !empty($_SESSION['username']) ? ucwords(str_replace('_', ' ', $_SESSION['username'])) : 'System Administrator';

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$pdo = Database::connect();

// Fetch sports with application counts
$sportsList = [];
try {
    $stmt = $pdo->query("
        SELECT 
            s.sport_id,
            s.sport_name,
            COUNT(sa.application_id) AS total_athletes
        FROM `sports` s
        LEFT JOIN `sport_application` sa ON s.sport_id = sa.sport_id
        GROUP BY s.sport_id, s.sport_name
        ORDER BY s.sport_name ASC
    ");
    $sportsList = $stmt->fetchAll();
} catch (Exception $e) {
    $sportsList = [];
}

$totalSports = count($sportsList);
$mostPopularSport = 'Football';
$maxAthletes = 0;
foreach ($sportsList as $sp) {
    if ((int)$sp['total_athletes'] > $maxAthletes) {
        $maxAthletes = (int)$sp['total_athletes'];
        $mostPopularSport = $sp['sport_name'];
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sports Catalog | USMS Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Material+Symbols+Outlined">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dashboard">
    <?php require __DIR__ . '/partials/admin_sidebar.php'; ?>
    <div class="main-wrapper">
        <?php 
        $pageTitle = 'Sports Catalog';
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
                    <h1>Sports Catalog</h1>
                    <p>Manage university varsity and club athletic sports, category programs, and institutional rosters.</p>
                </div>
                <div class="header-actions">
                    <button type="button" class="admin-primary-button" onclick="openAddSportModal()">
                        <span class="material-symbols-outlined">add</span> Add New Sport
                    </button>
                </div>
            </div>

            <!-- Proportional 3-Stat Metric Grid -->
            <div class="catalog-stats">
                <article class="metric">
                    <span>Total Sports</span>
                    <strong><?= number_format($totalSports) ?></strong>
                    <small>Official university sports</small>
                </article>
                <article class="metric">
                    <span>Active Programs</span>
                    <strong><?= number_format($totalSports) ?></strong>
                    <small>100% operational roster</small>
                </article>
                <article class="metric dark">
                    <span>Most Popular</span>
                    <strong><?= htmlspecialchars($mostPopularSport) ?></strong>
                    <small><?= $maxAthletes ?> registered applicants</small>
                </article>
            </div>

            <section class="admin-card">
                <div class="admin-card-header">
                    <div>
                        <h2>Configured University Sports</h2>
                        <p>Institution-recognized athletic disciplines</p>
                    </div>
                    <div class="catalog-filters">
                        <div class="search-box">
                            <span class="material-symbols-outlined">search</span>
                            <input type="search" id="sportSearchInput" placeholder="Filter sports..." oninput="filterSports(this.value)">
                        </div>
                    </div>
                </div>

                <div class="admin-table-wrap">
                    <table class="admin-table" id="sportsTable">
                        <thead>
                            <tr>
                                <th>Sport Name</th>
                                <th>Sport ID</th>
                                <th>Active Athletes</th>
                                <th>Status</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sportsTableBody">
                            <?php if (empty($sportsList)): ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; padding:30px; color:var(--admin-muted);">
                                        No sports configured yet. Click "Add New Sport" to create one.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($sportsList as $s): ?>
                                    <tr class="sport-row" data-name="<?= htmlspecialchars(strtolower($s['sport_name'])) ?>">
                                        <td>
                                            <div class="sport-cell">
                                                <span class="sport-icon material-symbols-outlined">sports</span>
                                                <div>
                                                    <strong><?= htmlspecialchars($s['sport_name']) ?></strong>
                                                    <small>University Athletic Program</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="mono-badge">SPT-<?= str_pad($s['sport_id'], 3, '0', STR_PAD_LEFT) ?></span>
                                        </td>
                                        <td>
                                            <strong><?= (int)$s['total_athletes'] ?></strong>
                                            <small style="color:var(--admin-muted); margin-left:4px;">athletes</small>
                                        </td>
                                        <td>
                                            <span class="admin-chip success">Active</span>
                                        </td>
                                        <td style="text-align:right;">
                                            <button type="button" class="action-btn-link" onclick='openEditSportModal(<?= $s['sport_id'] ?>, "<?= htmlspecialchars($s['sport_name'], ENT_QUOTES) ?>")'>
                                                Edit
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="admin-empty-note" id="sportsCountNote">
                    Showing <?= count($sportsList) ?> sports in the institution catalog
                </div>
        </div>
    </div>

<!-- Add Sport Modal -->
<div id="addSportModal" class="modal-backdrop" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h3>Add New Sport</h3>
                <p>Register a new athletic discipline in the university sports catalog.</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('addSportModal')">✕</button>
        </div>
        <form method="POST" action="../controllers/AdminController.php?action=create_sport" class="modal-form" onsubmit="return validateSportForm(this)">
            <div class="admin-field">
                <label for="newSportName">Sport Name *</label>
                <input id="newSportName" type="text" name="sport_name" required placeholder="e.g. Rowing, Archery, Chess">
                <small class="field-hint">Minimum 2 characters, letters and spaces</small>
            </div>
            <div class="modal-actions">
                <button type="button" class="admin-secondary-button" onclick="closeModal('addSportModal')">Cancel</button>
                <button type="submit" class="admin-primary-button">Add Sport</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Sport Modal -->
<div id="editSportModal" class="modal-backdrop" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h3>Edit Sport Entry</h3>
                <p>Update sport naming or program classification.</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('editSportModal')">✕</button>
        </div>
        <form method="POST" action="../controllers/AdminController.php?action=update_sport" class="modal-form" onsubmit="return validateSportForm(this)">
            <input type="hidden" id="editSportId" name="sport_id" value="">
            <div class="admin-field">
                <label for="editSportName">Sport Name *</label>
                <input id="editSportName" type="text" name="sport_name" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="admin-secondary-button" onclick="closeModal('editSportModal')">Cancel</button>
                <button type="submit" class="admin-primary-button">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<style>
    .topbar-search-wrap { display: flex; align-items: center; gap: 8px; background: #f8fafc; padding: 6px 12px; border-radius: 6px; border: 1px solid var(--admin-border); }
    .topbar-search-wrap input { width: min(240px, 25vw); border: 0; outline: 0; background: transparent; font-size: 13px; }
    .header-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .catalog-filters { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
    .search-box { display: flex; align-items: center; gap: 6px; padding: 0 10px; border-radius: 6px; background: #f8fafc; border: 1px solid var(--admin-border); }
    .search-box input { height: 34px; border: 0; outline: 0; background: transparent; font-size: 12.5px; width: 180px; }
    
    .catalog-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 18px; }
    .catalog-stats .metric { min-height: 95px; display: flex; flex-direction: column; justify-content: space-between; padding: 16px 18px; background: #fff; border: 1px solid var(--admin-border); border-radius: 8px; }
    .catalog-stats .metric > span { color: #475467; font-size: 11.5px; letter-spacing: .05em; text-transform: uppercase; font-weight: 600; }
    .catalog-stats .metric strong { font-size: 24px; font-weight: 700; color: #101828; }
    .catalog-stats .metric small { color: var(--admin-teal); font-size: 11.5px; font-weight: 500; }
    .catalog-stats .metric.dark { color: #fff; background: var(--admin-navy); }
    .catalog-stats .metric.dark > span, .catalog-stats .metric.dark small { color: #cbd5e1; }
    .catalog-stats .metric.dark strong { color: #fff; font-size: 20px; }

    .sport-cell { display: flex; align-items: center; gap: 10px; }
    .sport-cell strong { display: block; font-size: 13.5px; }
    .sport-cell small { display: block; margin-top: 2px; color: #64748b; font-size: 11.5px; }
    .sport-icon { width: 34px; height: 34px; display: grid; place-items: center; border-radius: 6px; background: #edf0f2; color: var(--admin-teal); font-size: 18px; flex-shrink: 0; }
    .mono-badge { font-family: monospace; font-size: 11.5px; color: #475467; background: #f2f4f7; padding: 2px 6px; border-radius: 4px; }
    .action-btn-link { border: 0; color: var(--admin-teal); background: transparent; font-weight: 700; cursor: pointer; font-size: 12.5px; padding: 3px 6px; border-radius: 4px; }
    .action-btn-link:hover { text-decoration: underline; }

    /* Modals */
    .modal-backdrop { position: fixed; inset: 0; z-index: 999; background: rgba(7, 22, 45, .6); backdrop-filter: blur(2px); display: grid; place-items: center; padding: 18px; }
    .modal-box { width: 100%; max-width: 440px; background: #fff; border-radius: 10px; box-shadow: 0 16px 36px rgba(0,0,0,.2); overflow: hidden; }
    .modal-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 16px 20px; border-bottom: 1px solid var(--admin-border); }
    .modal-header h3 { margin: 0 0 3px; font-size: 16px; font-weight: 700; color: var(--admin-navy); }
    .modal-header p { margin: 0; font-size: 12px; color: var(--admin-muted); }
    .modal-close-btn { border: 0; background: transparent; font-size: 16px; cursor: pointer; color: var(--admin-muted); }
    .modal-form { padding: 18px 20px; }
    .field-hint { color: var(--admin-muted); font-size: 11px; margin-top: 2px; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--admin-border); }

    /* Flash Alerts */
    .admin-alert { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 13px; font-weight: 500; }
    .success-alert { background: #ecfdf3; color: #067647; border: 1px solid #abefc6; }
    .error-alert { background: #fffbeb; color: #b54708; border: 1px solid #fedf89; }
    .alert-dismiss { margin-left: auto; border: 0; background: transparent; font-size: 15px; cursor: pointer; color: inherit; }

    @media (max-width: 850px) {
        .catalog-stats { grid-template-columns: 1fr; }
        .header-actions { width: 100%; }
        .header-actions button { flex: 1; }
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

function openAddSportModal() {
    document.getElementById('newSportName').value = '';
    openModal('addSportModal');
}

function openEditSportModal(id, name) {
    document.getElementById('editSportId').value = id;
    document.getElementById('editSportName').value = name;
    openModal('editSportModal');
}

function filterSports(query) {
    const q = query.trim().toLowerCase();
    const rows = document.querySelectorAll('#sportsTableBody tr.sport-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const name = row.getAttribute('data-name') || '';
        if (!q || name.includes(q)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const note = document.getElementById('sportsCountNote');
    if (note) {
        note.textContent = `Showing ${visibleCount} of ${rows.length} sports`;
    }
}

// Logical guard before submit
function validateSportForm(form) {
    const input = form.querySelector('input[name="sport_name"]');
    if (!input) return true;
    const val = input.value.trim();
    if (val.length < 2) {
        alert('Sport name must be at least 2 characters long.');
        input.focus();
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
