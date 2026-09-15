<?php
require_once '../config/auth_guard.php';
requireRole('Medical_Officer');
// ===== BACKEND CONNECTION PLACEHOLDER =====
// require_once '../config/database.php';
// require_once '../models/MedicalModel.php';
// $medModel          = new MedicalModel($db);
// $totalAthletes     = $medModel->getTotalAthletes();
// $activeInjuries    = $medModel->getActiveInjuryCount();
// $pendingClearances = $medModel->getPendingClearanceCount();
// $pendingList       = $medModel->getPendingClearances();
// $historyList       = $medModel->getClearanceHistory();

$pageTitle         = 'Medical Dashboard';
$totalAthletes     = 248;
$activeInjuries    = 12;
$pendingClearances = 4;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> — USMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dashboard">

<?php include 'partials/med_sidebar.php'; ?>

<div class="main-wrapper">
    <?php include 'partials/tic_header.php'; ?>

    <div class="page-content">

        <!-- Page Title -->
        <div class="page-title-row">
            <h2 class="page-title">Medical Officer Dashboard</h2>
        </div>

        <!-- 3 Simple Stat Cards -->
        <div class="grid-3">
            <div class="stat-card">
                <div>
                    <div class="stat-label">Total Monitored</div>
                    <div class="stat-value"><?= $totalAthletes ?></div>
                </div>
                <div class="stat-icon">
                    <span class="material-symbols-outlined">groups</span>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Active Injuries</div>
                    <div class="stat-value" style="color:var(--danger);"><?= $activeInjuries ?></div>
                </div>
                <div class="stat-icon" style="color:var(--danger);">
                    <span class="material-symbols-outlined">personal_injury</span>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Awaiting Clearance</div>
                    <div class="stat-value" style="color:#D97706;"><?= $pendingClearances ?></div>
                </div>
                <div class="stat-icon" style="color:#D97706;">
                    <span class="material-symbols-outlined">pending_actions</span>
                </div>
            </div>
        </div>

        <!-- Toolbar: Tabs + Log Injury -->
        <div class="toolbar">
            <div class="tab-group">
                <button class="tab-btn active" onclick="switchTab(this,'medTabs','tab-pending')">
                    Pending Clearances <span class="tab-counter critical"><?= $pendingClearances ?></span>
                </button>
                <button class="tab-btn" onclick="switchTab(this,'medTabs','tab-history')">
                    Clearance History
                </button>
            </div>

            <button class="btn btn-primary" onclick="openModal('logInjuryModal')">
                <span class="material-symbols-outlined" style="font-size:16px">add_circle</span> Log New Injury
            </button>
        </div>

        <!-- ======= TAB 1: Pending Clearances ======= -->
        <div class="tab-content active" id="tab-pending" data-tab-group="medTabs">
            <div class="table-card">
                <div class="table-toolbar">
                    <span class="table-title">Athletes Awaiting Clearance</span>
                    <div class="table-filters">
                        <div class="search-box">
                            <span class="material-symbols-outlined">search</span>
                            <input type="text" class="search-input" id="searchPending" placeholder="Search athlete, ID, sport…">
                        </div>
                    </div>
                </div>

                <div class="table-scroll">
                    <table class="data-table" id="pendingTable">
                        <thead>
                            <tr>
                                <th>Athlete</th>
                                <th>Student ID</th>
                                <th>Sport</th>
                                <th>Injury / Condition</th>
                                <th>Priority</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $pendingList = [
                                ['Dilshan Perera', 'DP', '10000045', 'Rugby',         'Concussion Protocol (Stage 5)', 'critical', 'High Risk'],
                                ['Darby Hayes',    'DH', '10000226', 'Cricket',       'Grade II Ankle Sprain',          'critical', 'High Risk'],
                                ['Saman Arachchi', 'SA', '10000089', 'Football',      'AC Joint Shoulder Strain',       'warn',     'Moderate'],
                                ['Maria Smith',    'MS', '10000104', 'Track & Field', 'Patellar Tendinopathy (Knee)',   'warn',     'Moderate'],
                            ];
                            foreach ($pendingList as $p): ?>
                            <tr>
                                <td>
                                    <div class="cell-flex">
                                        <div class="mini-avatar"><?= $p[1] ?></div>
                                        <span class="cell-main"><?= $p[0] ?></span>
                                    </div>
                                </td>
                                <td><span class="cell-mono"><?= $p[2] ?></span></td>
                                <td><span class="cell-main"><?= $p[3] ?></span></td>
                                <td><span class="cell-main"><?= $p[4] ?></span></td>
                                <td>
                                    <span class="badge <?= $p[5] === 'critical' ? 'badge-critical' : 'badge-warn' ?>"><?= $p[6] ?></span>
                                </td>
                                <td>
                                    <div class="cell-actions">
                                        <button class="btn btn-success btn-sm"
                                                onclick="openClearanceModal('<?= addslashes($p[0]) ?>', '<?= $p[2] ?>', '<?= $p[3] ?>', '<?= addslashes($p[4]) ?>')">
                                            Issue Clearance
                                        </button>
                                        <button class="btn btn-outline btn-sm"
                                                onclick="openViewRecordModal('<?= addslashes($p[0]) ?>', '<?= $p[2] ?>', '<?= $p[3] ?>', '<?= addslashes($p[4]) ?>')">
                                            View
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <span>Showing 1–4 of <?= $pendingClearances ?> pending</span>
                    <div class="page-btns">
                        <button class="page-btn" disabled><span class="material-symbols-outlined">chevron_left</span></button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn"><span class="material-symbols-outlined">chevron_right</span></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======= TAB 2: Clearance History ======= -->
        <div class="tab-content" id="tab-history" data-tab-group="medTabs">
            <div class="table-card">
                <div class="table-toolbar">
                    <span class="table-title">Clearance History</span>
                    <div class="table-filters">
                        <div class="search-box">
                            <span class="material-symbols-outlined">search</span>
                            <input type="text" class="search-input" id="searchHistory" placeholder="Search history…">
                        </div>
                    </div>
                </div>

                <div class="table-scroll">
                    <table class="data-table" id="historyTable">
                        <thead>
                            <tr>
                                <th>Athlete</th>
                                <th>Sport</th>
                                <th>Resolved Condition</th>
                                <th>Cleared On</th>
                                <th>Certifying Doctor</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $historyList = [
                                ['Payton Reese',  'PR', '10000701', 'Basketball', 'Pre-Season Routine Screen', 'Aug 12, 2026', 'Dr. A. Fernando', 'Full Clearance', 'badge-ok'],
                                ['Olivia Smith',  'OS', '10000117', 'Swimming',   'Rotator Cuff Tendinitis',   'Aug 08, 2026', 'Dr. A. Fernando', 'Full Clearance', 'badge-ok'],
                                ['Kevin Raj',     'KR', '10000832', 'Track',      'Hamstring Strain',          'Aug 04, 2026', 'Dr. N. Silva',    'Conditional',    'badge-warn'],
                                ['Nimal Perera',  'NP', '10000941', 'Football',   'Wrist Fracture',            'Jul 29, 2026', 'Dr. A. Fernando', 'Full Clearance', 'badge-ok'],
                                ['Tharushi Silva','TS', '10000995', 'Cricket',    'Heat Exhaustion',           'Jul 21, 2026', 'Dr. N. Silva',    'Full Clearance', 'badge-ok'],
                            ];
                            foreach ($historyList as $h): ?>
                            <tr>
                                <td>
                                    <div class="cell-flex">
                                        <div class="mini-avatar"><?= $h[1] ?></div>
                                        <span class="cell-main"><?= $h[0] ?></span>
                                    </div>
                                </td>
                                <td><span class="cell-main"><?= $h[3] ?></span></td>
                                <td><span class="cell-main"><?= $h[4] ?></span></td>
                                <td><span class="cell-main"><?= $h[5] ?></span></td>
                                <td><span class="cell-sub"><?= $h[6] ?></span></td>
                                <td><span class="badge <?= $h[8] ?>"><?= $h[7] ?></span></td>
                                <td>
                                    <div class="cell-actions">
                                        <button class="btn btn-outline btn-sm"
                                                onclick="openViewRecordModal('<?= addslashes($h[0]) ?>', '<?= $h[2] ?>', '<?= $h[3] ?>', '<?= addslashes($h[4]) ?>')">
                                            View
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <span>Showing 1–5 of 186 records</span>
                    <div class="page-btns">
                        <button class="page-btn" disabled><span class="material-symbols-outlined">chevron_left</span></button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn">2</button>
                        <button class="page-btn">3</button>
                        <button class="page-btn"><span class="material-symbols-outlined">chevron_right</span></button>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- .page-content -->
</div><!-- .main-wrapper -->

<!-- ===== MODAL 1: LOG INJURY ===== -->
<div class="modal-overlay" id="logInjuryModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Log New Injury</span>
            <button class="modal-close" onclick="closeModal('logInjuryModal')">&times;</button>
        </div>
        <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Injury logged successfully!'); closeModal('logInjuryModal');">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Athlete</label>
                    <select class="form-select" required>
                        <option value="">Select athlete…</option>
                        <option>Dilshan Perera — 10000045 (Rugby)</option>
                        <option>Darby Hayes — 10000226 (Cricket)</option>
                        <option>Saman Arachchi — 10000089 (Football)</option>
                        <option>Maria Smith — 10000104 (Track & Field)</option>
                        <option>Payton Reese — 10000701 (Basketball)</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Injury / Condition</label>
                        <input type="text" class="form-input" placeholder="e.g. Hamstring Strain" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-input" required value="2026-08-15">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Priority</label>
                        <select class="form-select">
                            <option value="critical">High Risk</option>
                            <option value="warn" selected>Moderate</option>
                            <option value="info">Low / Routine</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Est. Recovery (Days)</label>
                        <input type="number" class="form-input" min="1" value="14">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Notes</label>
                    <input type="text" class="form-input" placeholder="Clinical assessment notes…">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('logInjuryModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Injury</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL 2: ISSUE CLEARANCE ===== -->
<div class="modal-overlay" id="clearanceModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Issue Medical Clearance</span>
            <button class="modal-close" onclick="closeModal('clearanceModal')">&times;</button>
        </div>
        <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Clearance issued successfully!'); closeModal('clearanceModal');">
            <div class="modal-body">
                <div style="background:#F8FAFC; border:1px solid var(--border); border-radius:6px; padding:10px 12px; margin-bottom:14px;">
                    <div style="font-weight:700; font-size:14px;" id="clrName">Dilshan Perera</div>
                    <div style="font-size:12px; color:var(--text-3);" id="clrInfo">ID: 10000045 · Rugby · Concussion Protocol</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Clearance Decision</label>
                    <select class="form-select" required>
                        <option>Full Clearance (Fit to Compete)</option>
                        <option>Conditional / Practice Only</option>
                        <option>Not Cleared / Extend Rehab</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Clearance Date</label>
                    <input type="date" class="form-input" required value="2026-08-15">
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Notes for Coach / Staff</label>
                    <input type="text" class="form-input" placeholder="e.g. Cleared for full match play…">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('clearanceModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Authorize Clearance</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL 3: VIEW RECORD ===== -->
<div class="modal-overlay" id="viewRecordModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Athlete Medical Record</span>
            <button class="modal-close" onclick="closeModal('viewRecordModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:14px; padding-bottom:12px; border-bottom:1px solid var(--border);">
                <div class="mini-avatar" id="recAvatar" style="width:40px; height:40px; font-size:14px;">DP</div>
                <div>
                    <div style="font-weight:700; font-size:15px;" id="recName">Dilshan Perera</div>
                    <div style="font-size:12px; color:var(--text-3);" id="recMeta">ID: 10000045 · Rugby</div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px 16px; font-size:13px;">
                <div>
                    <div style="color:var(--text-3); font-size:11px; text-transform:uppercase; font-weight:700;">Condition</div>
                    <div style="font-weight:600;" id="recCondition">Concussion Protocol</div>
                </div>
                <div>
                    <div style="color:var(--text-3); font-size:11px; text-transform:uppercase; font-weight:700;">Status</div>
                    <span class="badge badge-warn" id="recStatus">Active Review</span>
                </div>
                <div>
                    <div style="color:var(--text-3); font-size:11px; text-transform:uppercase; font-weight:700;">Attending Doctor</div>
                    <div style="font-weight:600;">Dr. A. Fernando</div>
                </div>
                <div>
                    <div style="color:var(--text-3); font-size:11px; text-transform:uppercase; font-weight:700;">Blood Group</div>
                    <div style="font-weight:600; color:var(--danger);">O+ Positive</div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('viewRecordModal')">Close</button>
        </div>
    </div>
</div>

<script src="../assets/js/dashboard.js"></script>
<script>
    setupSearch('searchPending', 'pendingTable');
    setupSearch('searchHistory', 'historyTable');

    function openClearanceModal(name, id, sport, injury) {
        document.getElementById('clrName').textContent = name;
        document.getElementById('clrInfo').textContent = 'ID: ' + id + ' · ' + sport + ' · ' + injury;
        openModal('clearanceModal');
    }

    function openViewRecordModal(name, id, sport, injury) {
        document.getElementById('recName').textContent = name;
        document.getElementById('recMeta').textContent = 'ID: ' + id + ' · ' + sport;
        document.getElementById('recCondition').textContent = injury;
        var initials = name.split(' ').map(function(w){ return w[0]; }).join('').toUpperCase();
        document.getElementById('recAvatar').textContent = initials;
        openModal('viewRecordModal');
    }
</script>
</body>
</html>
