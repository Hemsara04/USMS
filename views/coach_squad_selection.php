<?php
require_once __DIR__ . '/../config/auth_guard.php';
requireRole('Coach');

require_once __DIR__ . '/../models/Coach.php';
$coachModel = new Coach();

$stats = $coachModel->getStats();
$fixtures = $coachModel->getFixturesWithSquadStatus();
$selectedFixtureId = isset($_GET['fixture_id']) ? (int)$_GET['fixture_id'] : ($fixtures[0]['fixture_id'] ?? 1);

// Find active fixture
$activeFixture = null;
foreach ($fixtures as $f) {
    if ($f['fixture_id'] == $selectedFixtureId) {
        $activeFixture = $f;
        break;
    }
}
if (!$activeFixture && !empty($fixtures)) {
    $activeFixture = $fixtures[0];
    $selectedFixtureId = $activeFixture['fixture_id'];
}

$availableAthletes = $coachModel->getAvailableAthletesForSport(1);
$currentSquad = $coachModel->getSquadForFixture($selectedFixtureId);

$pageTitle = 'Squad Formulation & Selection';
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
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
        table.dash-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .dash-table thead th {
            background: var(--side-bg);
            color: #CBD5E1;
            padding: 13px 14px;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
            text-align: left;
            vertical-align: middle;
        }
        .dash-table tbody td {
            padding: 13px 14px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            color: var(--text);
        }
        .dash-table tbody tr:hover {
            background: rgba(241, 245, 249, 0.55);
        }
        .dash-table tbody tr:last-child td {
            border-bottom: none;
        }
        .badge-success {
            background: #F0FDF4;
            color: #16A34A;
            border: 1px solid #BBF7D0;
        }
        .squad-dual-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }
        @media (max-width: 1200px) {
            .squad-dual-grid {
                grid-template-columns: 1fr;
            }
        }
        .fixture-selector-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--r-lg);
            padding: 16px 20px;
            margin-bottom: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            box-shadow: var(--shadow-xs);
        }
        .role-select {
            padding: 3px 8px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid var(--border);
            border-radius: 4px;
            background: var(--card);
            color: var(--text);
            cursor: pointer;
        }
        .role-badge-captain {
            background: #FEF3C7;
            color: #B45309;
            border: 1px solid #FDE68A;
            font-weight: 800;
        }
        .role-badge-starter {
            background: #EFF6FF;
            color: #1D4ED8;
            border: 1px solid #BFDBFE;
        }
        .role-badge-sub {
            background: #F1F5F9;
            color: #475569;
            border: 1px solid #CBD5E1;
        }
    </style>
</head>
<body class="dashboard">

<?php include __DIR__ . '/partials/coach_sidebar.php'; ?>

<div class="main-wrapper">
    <?php include __DIR__ . '/partials/tic_header.php'; ?>

    <div class="page-content">

        <!-- Action Header -->
        <div class="page-title-row">
            <div>
                <h2 class="page-title">Squad Formulation & Match Selection</h2>
                <p style="font-size: 13px; color: var(--text-3); margin-top: 3px;">
                    Select starting XI and substitutes with medical clearance validation and submit to TIC for official accreditation
                </p>
            </div>
            <div>
                <button type="button" class="btn btn-teal" onclick="openModal('submitSquadModal')">
                    <span class="material-symbols-outlined" style="font-size: 18px;">verified</span>
                    Submit Squad to TIC for Approval
                </button>
            </div>
        </div>

        <?php if (!empty($flashSuccess)): ?>
            <div style="background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534; padding: 12px 18px; border-radius: var(--r); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 13.5px;">
                <span class="material-symbols-outlined" style="color: #16A34A; font-size: 20px;">check_circle</span>
                <span><?= htmlspecialchars($flashSuccess) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
            <div style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 12px 18px; border-radius: var(--r); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 13.5px;">
                <span class="material-symbols-outlined" style="color: #DC2626; font-size: 20px;">error</span>
                <span><?= htmlspecialchars($flashError) ?></span>
            </div>
        <?php endif; ?>

        <!-- Top Stat Cards (Grid-4) -->
        <div class="grid-4">
            <div class="stat-card">
                <div>
                    <div class="stat-label">Selected Players</div>
                    <div class="stat-value" id="statSelectedCount" style="color: var(--accent-dark);"><?= count($currentSquad) ?> / 18</div>
                </div>
                <div class="stat-icon" style="color: var(--accent); background: var(--accent-bg);">
                    <span class="material-symbols-outlined">group_add</span>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Medically Fit</div>
                    <div class="stat-value" style="color: #16A34A;"><?= $stats['medically_fit'] ?? 24 ?></div>
                </div>
                <div class="stat-icon" style="color: #16A34A; background: #F0FDF4;">
                    <span class="material-symbols-outlined">verified_user</span>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Under Medical Review</div>
                    <div class="stat-value" style="color: #D97706;"><?= $stats['medically_inactive'] ?? 3 ?></div>
                </div>
                <div class="stat-icon" style="color: #D97706; background: #FFFBEB;">
                    <span class="material-symbols-outlined">warning</span>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Matches Scheduled</div>
                    <div class="stat-value" style="color: var(--accent);"><?= count($fixtures) ?></div>
                </div>
                <div class="stat-icon" style="color: var(--accent); background: #EFF6FF;">
                    <span class="material-symbols-outlined">event</span>
                </div>
            </div>
        </div>

        <!-- Fixture Selector Card -->
        <div class="fixture-selector-card">
            <div style="flex: 1; min-width: 280px;">
                <label class="form-label" style="font-size: 12px; text-transform: uppercase; color: var(--text-3); margin-bottom: 4px;">Target Match Fixture</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <select class="form-select" id="fixtureSelect" style="font-weight: 700; color: var(--primary);" onchange="window.location.href='coach_squad_selection.php?fixture_id=' + this.value">
                        <?php foreach ($fixtures as $f): ?>
                            <option value="<?= $f['fixture_id'] ?>" <?= ($f['fixture_id'] == $selectedFixtureId) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['title']) ?> (<?= htmlspecialchars($f['display_date']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 14px;">
                <div>
                    <span style="font-size: 11.5px; color: var(--text-3); display: block;">Draft Status</span>
                    <span class="badge <?= (($activeFixture['squad_status'] ?? '') === 'Approved by TIC') ? 'badge-success' : 'badge-warning' ?>" id="fixtureDraftBadge">
                        <?= htmlspecialchars($activeFixture['squad_status'] ?? 'Draft in Progress') ?>
                    </span>
                </div>
                <div style="border-left: 1px solid var(--border); padding-left: 14px;">
                    <span style="font-size: 11.5px; color: var(--text-3); display: block;">Match Date</span>
                    <strong style="font-size: 13px; color: var(--accent-dark);"><?= htmlspecialchars($activeFixture['display_date'] ?? 'Upcoming') ?></strong>
                </div>
                <button type="button" class="btn btn-outline btn-sm" onclick="alert('Rules: Max 18 players per squad. All athletes must possess valid medical clearance.')">
                    <span class="material-symbols-outlined" style="font-size: 15px;">info</span>
                    Eligibility Rules
                </button>
            </div>
        </div>

        <!-- Dual Table Layout -->
        <div class="squad-dual-grid">

            <!-- LEFT TABLE: Available Athlete Pool -->
            <div class="table-card">
                <div class="table-toolbar">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-outlined" style="color: var(--accent); font-size: 20px;">sports</span>
                        <span class="table-title" style="font-size: 14.5px;">Available Athlete Pool</span>
                        <span class="tab-counter"><?= count($availableAthletes) ?> candidates</span>
                    </div>
                    <div class="search-box" style="width: 170px;">
                        <input type="text" class="search-input" id="searchPoolInput" placeholder="Search pool…" onkeyup="filterPoolTable()">
                        <span class="material-symbols-outlined">search</span>
                    </div>
                </div>

                <div class="table-scroll">
                    <table class="dash-table data-table" id="poolTable">
                        <thead>
                            <tr>
                                <th>Athlete Name & Reg</th>
                                <th>Position</th>
                                <th>Medical Status</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($availableAthletes as $ath): 
                                $medClass = ($ath['med_status'] === 'Fit to Compete') ? 'badge-success' : 'badge-warning';
                            ?>
                            <tr>
                                <td>
                                    <span class="cell-main"><?= htmlspecialchars($ath['name']) ?></span>
                                    <span class="cell-sub"><?= htmlspecialchars($ath['faculty']) ?> • <?= htmlspecialchars($ath['index']) ?></span>
                                </td>
                                <td>
                                    <span style="font-weight: 500; font-size: 12.5px;"><?= htmlspecialchars($ath['position']) ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= $medClass ?>" title="<?= htmlspecialchars($ath['med_note'] ?? 'Cleared') ?>">
                                        <?= htmlspecialchars($ath['med_status']) ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <?php if ($ath['can_add']): ?>
                                        <button type="button" class="btn btn-outline btn-sm" onclick="addToSquad('<?= htmlspecialchars($ath['id']) ?>', '<?= htmlspecialchars(addslashes($ath['name'])) ?>', '<?= htmlspecialchars(addslashes($ath['position'])) ?>', '<?= htmlspecialchars(addslashes($ath['faculty'])) ?>', <?= (int)$ath['athlete_id'] ?>)">
                                            <span class="material-symbols-outlined" style="font-size: 14px; color: var(--accent);">add</span>
                                            + Add
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-outline btn-sm" disabled style="opacity: 0.4; cursor: not-allowed;" title="<?= htmlspecialchars($ath['med_note'] ?? 'Medical clearance pending') ?>">
                                            Blocked
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- RIGHT TABLE: Current Match Squad Roster -->
            <div class="table-card">
                <div class="table-toolbar">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-outlined" style="color: var(--ok); font-size: 20px;">groups</span>
                        <span class="table-title" style="font-size: 14.5px;">Current Match Squad Roster</span>
                        <span class="tab-counter info" id="squadRosterCount"><?= count($currentSquad) ?> / 18</span>
                    </div>
                    <div style="display: flex; gap: 6px;">
                        <span class="badge badge-success" style="font-size: 11px;">Active Match Draft</span>
                    </div>
                </div>

                <div class="table-scroll">
                    <table class="dash-table data-table" id="squadTable">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Jersey</th>
                                <th>Player Name</th>
                                <th>Assigned Role</th>
                                <th>Faculty</th>
                                <th style="text-align: right;">Remove</th>
                            </tr>
                        </thead>
                        <tbody id="squadTableBody">
                            <?php if (empty($currentSquad)): ?>
                            <tr id="emptySquadRow">
                                <td colspan="5" style="text-align: center; padding: 48px 16px;">
                                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                        <div style="width: 48px; height: 48px; border-radius: 50%; background: #F1F5F9; color: var(--text-3); display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                                            <span class="material-symbols-outlined" style="font-size: 24px;">group_add</span>
                                        </div>
                                        <h5 style="font-size: 15px; font-weight: 700; color: var(--text-1); margin-bottom: 4px;">No Players Drafted Yet</h5>
                                        <p style="font-size: 12.5px; color: var(--text-3); max-width: 320px; line-height: 1.4; margin-bottom: 0;">
                                            Click <strong>+ Add</strong> on candidates from the available athlete pool on the left to form your match squad.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($currentSquad as $p): 
                                $roleClass = 'role-badge-starter';
                                if ($p['role'] === 'Captain') $roleClass = 'role-badge-captain';
                                elseif ($p['role'] === 'Substitute') $roleClass = 'role-badge-sub';
                            ?>
                            <tr id="squad-row-<?= htmlspecialchars($p['id']) ?>" data-athlete-id="<?= (int)$p['athlete_id'] ?>">
                                <td>
                                    <span class="mini-avatar" style="background: var(--side-bg); color: #fff; font-size: 11.5px; font-weight: 700;">
                                        #<?= (int)$p['jersey'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="cell-main" style="font-weight: 600;"><?= htmlspecialchars($p['name']) ?></span>
                                    <span class="cell-sub" style="color: var(--text-3);"><?= htmlspecialchars($p['faculty']) ?></span>
                                </td>
                                <td>
                                    <select class="role-select <?= $roleClass ?>" onchange="updateRoleBadge(this)">
                                        <option value="Captain" <?= ($p['role'] === 'Captain') ? 'selected' : '' ?>>Captain (C)</option>
                                        <option value="Starting XI" <?= ($p['role'] === 'Starting XI') ? 'selected' : '' ?>>Starting XI</option>
                                        <option value="Substitute" <?= ($p['role'] === 'Substitute') ? 'selected' : '' ?>>Substitute</option>
                                    </select>
                                </td>
                                <td>
                                    <span style="font-size: 12.5px; color: var(--text-2); font-weight: 500;"><?= htmlspecialchars($p['faculty']) ?></span>
                                </td>
                                <td style="text-align: right;">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeFromSquad('<?= htmlspecialchars($p['id']) ?>', '<?= htmlspecialchars(addslashes($p['name'])) ?>')">
                                        <span class="material-symbols-outlined" style="font-size: 14px;">close</span>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- MODAL: Submit Squad to TIC for Approval Confirmation -->
<div class="modal-overlay" id="submitSquadModal">
    <div class="modal" style="width: 520px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-outlined" style="color: var(--accent); font-size: 22px;">verified_user</span>
                <span class="modal-title">Submit Squad to TIC for Approval</span>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('submitSquadModal')">&times;</button>
        </div>
        <form action="../controllers/CoachController.php?action=submit_squad" method="POST" onsubmit="prepareSquadForm()">
            <input type="hidden" name="fixture_id" value="<?= (int)$selectedFixtureId ?>">
            <div id="squadHiddenInputs"></div>
            <div class="modal-body">
                <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: var(--r); padding: 12px 16px; margin-bottom: 14px; display: flex; align-items: center; gap: 10px;">
                    <span class="material-symbols-outlined" style="color: #16A34A; font-size: 24px;">task_alt</span>
                    <div>
                        <div style="font-weight: 700; color: #16A34A; font-size: 13.5px;">Medical Compliance Verified</div>
                        <div style="font-size: 12px; color: #15803D;">All formulated candidates have valid medical fitness clearance logged in USMS clinic records.</div>
                    </div>
                </div>

                <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--r); padding: 12px 16px; margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px;">
                        <span>Target Match:</span>
                        <strong style="color: var(--accent-dark);"><?= htmlspecialchars($activeFixture['title'] ?? 'Selected Match Fixture') ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px;">
                        <span>Squad Formulation:</span>
                        <strong><span id="modalSquadCount"><?= count($currentSquad) ?></span> Athletes Selected</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px;">
                        <span>Fixture Date:</span>
                        <strong style="color: #D97706;"><?= htmlspecialchars($activeFixture['display_date'] ?? date('M d, Y')) ?></strong>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tactical Formation & Coach Notes for TIC</label>
                    <textarea name="notes" class="form-textarea" placeholder="Provide formation note (e.g. 4-3-3 attacking system), jersey conflicts, or emergency standby notices…" rows="3">Standard team formation. All 15 players inspected and ready for official tournament accreditation.</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('submitSquadModal')">Review Roster</button>
                <button type="submit" class="btn btn-teal">
                    <span class="material-symbols-outlined" style="font-size: 16px;">send</span>
                    Confirm & Submit to TIC
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    var el = document.getElementById(id);
    if (el) el.classList.add('show');
}
function closeModal(id) {
    var el = document.getElementById(id);
    if (el) el.classList.remove('show');
}
window.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('show');
    }
});

// Role select badge color changer
function updateRoleBadge(select) {
    select.className = 'role-select ' + (select.value === 'Captain' ? 'role-badge-captain' : (select.value === 'Substitute' ? 'role-badge-sub' : 'role-badge-starter'));
}

// Add athlete from pool to squad roster
function addToSquad(id, name, position, faculty, athleteId) {
    var existingRows = document.querySelectorAll('#squadTableBody tr[data-athlete-id]');
    if (existingRows.length >= 18) {
        alert('Squad limit reached (Maximum 18 players). Please remove a substitute first.');
        return;
    }
    var existing = document.getElementById('squad-row-' + id);
    if (existing) {
        alert(name + ' is already in the match squad.');
        return;
    }

    var emptyRow = document.getElementById('emptySquadRow');
    if (emptyRow) {
        emptyRow.remove();
    }

    var newJersey = existingRows.length + 1;
    var defaultRole = (newJersey <= 11) ? 'Starting XI' : 'Substitute';
    var roleClass = (newJersey <= 11) ? 'role-badge-starter' : 'role-badge-sub';

    var tr = document.createElement('tr');
    tr.id = 'squad-row-' + id;
    tr.setAttribute('data-athlete-id', athleteId);
    tr.innerHTML = '<td><span class="mini-avatar" style="background: var(--side-bg); color: #fff; font-size: 11.5px; font-weight: 700;">#' + newJersey + '</span></td>' +
        '<td><span class="cell-main" style="font-weight: 600;">' + name + '</span><span class="cell-sub" style="color: var(--text-3);">' + faculty + '</span></td>' +
        '<td><select class="role-select ' + roleClass + '" onchange="updateRoleBadge(this)"><option value="Starting XI"' + (defaultRole === 'Starting XI' ? ' selected' : '') + '>Starting XI</option><option value="Substitute"' + (defaultRole === 'Substitute' ? ' selected' : '') + '>Substitute</option><option value="Captain">Captain (C)</option></select></td>' +
        '<td><span style="font-size: 12.5px; color: var(--text-2); font-weight: 500;">' + faculty + '</span></td>' +
        '<td style="text-align: right;"><button type="button" class="btn btn-danger btn-sm" onclick="removeFromSquad(\'' + id + '\', \'' + name.replace(/'/g, "\\'") + '\')"><span class="material-symbols-outlined" style="font-size: 14px;">close</span></button></td>';

    document.getElementById('squadTableBody').appendChild(tr);
    updateSquadCounters();
}

// Remove athlete from squad roster
function removeFromSquad(id, name) {
    var row = document.getElementById('squad-row-' + id);
    if (row && confirm('Remove ' + name + ' from match squad?')) {
        row.remove();
        updateSquadCounters();

        var remaining = document.querySelectorAll('#squadTableBody tr[data-athlete-id]');
        if (remaining.length === 0) {
            var tbody = document.getElementById('squadTableBody');
            tbody.innerHTML = '<tr id="emptySquadRow"><td colspan="5" style="text-align: center; padding: 48px 16px;"><div style="display: flex; flex-direction: column; align-items: center; justify-content: center;"><div style="width: 48px; height: 48px; border-radius: 50%; background: #F1F5F9; color: var(--text-3); display: flex; align-items: center; justify-content: center; margin-bottom: 12px;"><span class="material-symbols-outlined" style="font-size: 24px;">group_add</span></div><h5 style="font-size: 15px; font-weight: 700; color: var(--text-1); margin-bottom: 4px;">No Players Drafted Yet</h5><p style="font-size: 12.5px; color: var(--text-3); max-width: 320px; line-height: 1.4; margin-bottom: 0;">Click <strong>+ Add</strong> on candidates from the available athlete pool on the left to form your match squad.</p></div></td></tr>';
        }
    }
}

// Update counters
function updateSquadCounters() {
    var count = document.querySelectorAll('#squadTableBody tr[data-athlete-id]').length;
    var statEl = document.getElementById('statSelectedCount');
    if (statEl) statEl.textContent = count + ' / 18';
    var countEl = document.getElementById('squadRosterCount');
    if (countEl) countEl.textContent = count + ' / 18';
    var modalEl = document.getElementById('modalSquadCount');
    if (modalEl) modalEl.textContent = count;
}

// Populate hidden inputs for squad form submission
function prepareSquadForm() {
    var container = document.getElementById('squadHiddenInputs');
    if (!container) return;
    container.innerHTML = '';
    var rows = document.querySelectorAll('#squadTableBody tr');
    rows.forEach(function(row) {
        var athId = row.getAttribute('data-athlete-id');
        if (athId) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_athletes[]';
            input.value = athId;
            container.appendChild(input);
        }
    });
}

// Live Pool Search
function filterPoolTable() {
    var q = document.getElementById('searchPoolInput').value.toLowerCase();
    var rows = document.querySelectorAll('#poolTable tbody tr');
    rows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.indexOf(q) > -1 ? '' : 'none';
    });
}
</script>
<script src="../assets/js/dashboard.js"></script>
</body>
</html>
