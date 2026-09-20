<?php
require_once '../config/auth_guard.php';
requireRole('Medical_Officer');

require_once '../models/Medical.php';
$medModel          = new Medical();
$stats             = $medModel->getDashboardStats();
$totalAthletes     = $stats['total_monitored'];
$activeInjuries    = $stats['active_injuries'];
$pendingClearances = $stats['pending_clearances'];

$pendingList       = $medModel->getActiveInjuries();
$historyList       = $medModel->getClearanceHistory();
$allAthletes       = $medModel->getAthleteRegistry();
$allSports         = $medModel->getAllSports();

$pageTitle         = 'Medical Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> — USMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .injury-summary-box {
            background: #F8FAFC;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body class="dashboard">

<?php include 'partials/med_sidebar.php'; ?>

<div class="main-wrapper">
    <?php include 'partials/tic_header.php'; ?>

    <div class="page-content">

        <!-- Flash Messages -->
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success" style="margin-bottom:18px; display:flex; align-items:center; gap:8px;">
                <span class="material-symbols-outlined" style="font-size:20px;">check_circle</span>
                <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger" style="margin-bottom:18px; display:flex; align-items:center; gap:8px;">
                <span class="material-symbols-outlined" style="font-size:20px;">error</span>
                <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <!-- Page Title -->
        <div class="page-title-row">
            <div>
                <h2 class="page-title">Clinical Medical Dashboard</h2>
                <p style="font-size:13px; color:var(--text-3); margin-top:2px;">Track active sports injuries, recovery timelines, and authorized match clearances</p>
            </div>
        </div>

        <!-- 3 Live Stat Cards -->
        <div class="grid-3">
            <div class="stat-card">
                <div>
                    <div class="stat-label">Total Monitored Athletes</div>
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
                    Pending Clearances <span class="tab-counter critical"><?= count($pendingList) ?></span>
                </button>
                <button class="tab-btn" onclick="switchTab(this,'medTabs','tab-history')">
                    Clearance History <span class="tab-counter" style="background:#E2E8F0; color:var(--text-2);"><?= count($historyList) ?></span>
                </button>
            </div>

            <button class="btn btn-primary" onclick="openNewInjuryModal()">
                <span class="material-symbols-outlined" style="font-size:16px">add_circle</span> Log New Injury
            </button>
        </div>

        <!-- ======= TAB 1: Pending Clearances ======= -->
        <div class="tab-content active" id="tab-pending" data-tab-group="medTabs">
            <div class="table-card">
                <div class="table-toolbar">
                    <span class="table-title">Athletes Awaiting Medical Clearance (<?= count($pendingList) ?>)</span>
                    <div class="table-filters">
                        <div class="search-box">
                            <span class="material-symbols-outlined">search</span>
                            <input type="text" class="search-input" id="searchPending" placeholder="Search athlete, ID, condition, sport…">
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
                                <th>Est. Recovery</th>
                                <th>Priority</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendingList)): ?>
                                <tr>
                                    <td colspan="7" style="text-align:center; padding:32px; color:var(--text-3);">
                                        <span class="material-symbols-outlined" style="font-size:36px; color:var(--accent); display:block; margin:0 auto 8px;">check_circle</span>
                                        All monitored athletes are medically cleared! No active injuries pending review.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pendingList as $p): 
                                    $nameParts = explode(' ', trim($p['full_name']));
                                    $initials = count($nameParts) >= 2 
                                        ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts)-1], 0, 1))
                                        : strtoupper(substr($p['full_name'], 0, 2));

                                    $sevBadge = 'badge-warn';
                                    if ($p['severity'] === 'Major') $sevBadge = 'badge-critical';
                                    elseif ($p['severity'] === 'Minor') $sevBadge = 'badge-info';
                                ?>
                                <tr>
                                    <td>
                                        <div class="cell-flex">
                                            <div class="mini-avatar"><?= htmlspecialchars($initials) ?></div>
                                            <div>
                                                <span class="cell-main"><?= htmlspecialchars($p['full_name']) ?></span>
                                                <span class="cell-sub">Injured: <?= htmlspecialchars($p['date_of_injury']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="cell-mono"><?= htmlspecialchars($p['index_number']) ?></span></td>
                                    <td><span class="cell-main"><?= htmlspecialchars($p['sport_name']) ?></span></td>
                                    <td>
                                        <div>
                                            <span class="cell-main"><?= htmlspecialchars($p['injury_type']) ?></span>
                                            <?php if (!empty($p['notes'])): ?>
                                                <span class="cell-sub" style="max-width:240px; display:inline-block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?= htmlspecialchars($p['notes']) ?>"><?= htmlspecialchars($p['notes']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="cell-main" style="font-size:12.5px;"><?= htmlspecialchars($p['estimated_recovery_date']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $sevBadge ?>"><?= htmlspecialchars($p['severity']) ?></span>
                                    </td>
                                    <td>
                                        <div class="cell-actions">
                                            <button class="btn btn-teal btn-sm"
                                                    onclick="openClearanceModal(<?= htmlspecialchars(json_encode($p)) ?>)">
                                                Issue Clearance
                                            </button>
                                            <button class="btn btn-outline btn-sm"
                                                    onclick="openViewRecordModal(<?= htmlspecialchars(json_encode($p)) ?>)">
                                                View
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <span>Showing <?= count($pendingList) ?> of <?= count($pendingList) ?> pending clearances</span>
                    <div class="page-btns">
                        <button class="page-btn" disabled><span class="material-symbols-outlined">chevron_left</span></button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn" disabled><span class="material-symbols-outlined">chevron_right</span></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======= TAB 2: Clearance History ======= -->
        <div class="tab-content" id="tab-history" data-tab-group="medTabs">
            <div class="table-card">
                <div class="table-toolbar">
                    <span class="table-title">Medical Clearance History (<?= count($historyList) ?>)</span>
                    <div class="table-filters" style="display:flex; align-items:center; gap:10px;">
                        <div class="search-box">
                            <span class="material-symbols-outlined">search</span>
                            <input type="text" class="search-input" id="searchHistory" placeholder="Search history…">
                        </div>
                        <?php if (!empty($historyList)): ?>
                        <form action="../controllers/MedicalController.php?action=clear_clearance_history" method="POST" style="display:inline;"
                              onsubmit="return USMS.confirmForm(this, {
                                  title: 'Clear All Clearance History',
                                  message: 'Are you sure you want to permanently delete all medical clearance history records? This action cannot be undone.',
                                  type: 'danger',
                                  confirmText: 'Yes, Clear All'
                              });">
                            <button type="submit" class="btn btn-outline btn-sm" style="color:var(--danger); border-color:#FECACA; background:#FEF2F2; display:flex; align-items:center; gap:4px;" title="Clear All History">
                                <span class="material-symbols-outlined" style="font-size:16px;">delete_sweep</span>
                                Clear All
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="table-scroll">
                    <table class="data-table" id="historyTable">
                        <thead>
                            <tr>
                                <th>Athlete</th>
                                <th>Sport</th>
                                <th>Clinical Observation / Notes</th>
                                <th>Cleared Date</th>
                                <th>Expiry Date</th>
                                <th>Certifying Doctor</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($historyList)): ?>
                                <tr>
                                    <td colspan="8" style="text-align:center; padding:32px; color:var(--text-3);">
                                        No historical clearance records available.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($historyList as $h): 
                                    $nameParts = explode(' ', trim($h['full_name']));
                                    $initials = count($nameParts) >= 2 
                                        ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts)-1], 0, 1))
                                        : strtoupper(substr($h['full_name'], 0, 2));

                                    $statusBadge = ($h['status'] === 'Cleared') ? 'badge-ok' : (($h['status'] === 'Blocked') ? 'badge-critical' : 'badge-warn');
                                ?>
                                <tr>
                                    <td>
                                        <div class="cell-flex">
                                            <div class="mini-avatar"><?= htmlspecialchars($initials) ?></div>
                                            <div>
                                                <span class="cell-main"><?= htmlspecialchars($h['full_name']) ?></span>
                                                <span class="cell-sub"><?= htmlspecialchars($h['index_number']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="cell-main"><?= htmlspecialchars($h['sport_name']) ?></span></td>
                                    <td><span class="cell-main" style="max-width:280px; display:inline-block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?= htmlspecialchars($h['notes'] ?? 'Cleared') ?>"><?= htmlspecialchars($h['notes'] ?? 'Cleared') ?></span></td>
                                    <td><span class="cell-main"><?= !empty($h['cleared_date']) ? htmlspecialchars($h['cleared_date']) : 'N/A' ?></span></td>
                                    <td><span class="cell-sub"><?= htmlspecialchars($h['expiry_date']) ?></span></td>
                                    <td><span class="cell-sub" style="font-weight:600;"><?= htmlspecialchars($h['certifying_doctor'] ?? 'Dr. A. Fernando') ?></span></td>
                                    <td><span class="badge <?= $statusBadge ?>"><?= htmlspecialchars($h['status']) ?></span></td>
                                    <td>
                                        <div class="cell-actions">
                                            <button class="btn btn-outline btn-sm"
                                                    onclick="openViewHistoryModal(<?= htmlspecialchars(json_encode($h)) ?>)">
                                                View
                                            </button>
                                            <form action="../controllers/MedicalController.php?action=delete_clearance" method="POST" style="display:inline;"
                                                  onsubmit="return USMS.confirmForm(this, {
                                                      title: 'Delete Clearance Record',
                                                      message: 'Are you sure you want to delete this clearance record for <?= htmlspecialchars(addslashes($h['full_name'])) ?>?',
                                                      type: 'danger',
                                                      confirmText: 'Delete'
                                                  });">
                                                <input type="hidden" name="clearance_id" value="<?= (int)$h['clearance_id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete record">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <span>Showing <?= count($historyList) ?> records</span>
                    <div class="page-btns">
                        <button class="page-btn" disabled><span class="material-symbols-outlined">chevron_left</span></button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn" disabled><span class="material-symbols-outlined">chevron_right</span></button>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- .page-content -->
</div><!-- .main-wrapper -->

<!-- ===== MODAL 1: LOG INJURY ===== -->
<div class="modal-overlay" id="logInjuryModal">
    <div class="modal" style="max-width:620px;">
        <div class="modal-header">
            <div>
                <span class="modal-title">Log New Sports Injury</span>
                <p style="font-size:12px; color:var(--text-3); margin-top:2px;">Record incident and place athlete under medical review</p>
            </div>
            <button class="modal-close" onclick="closeModal('logInjuryModal')">&times;</button>
        </div>
        <form action="../controllers/MedicalController.php?action=log_injury" method="POST" onsubmit="return USMS.confirmForm(this, { title: 'Log Sports Injury', message: 'Are you sure you want to log this injury and update the athlete status to Under Review?', type: 'warning' });">
            <div class="modal-body">
                
                <!-- Athlete Searchable Select -->
                <div class="form-group">
                    <label class="form-label">Athlete <span style="color:var(--danger)">*</span></label>
                    <select class="form-select searchable" name="athlete_id" id="injuryAthleteSelect" data-placeholder="-- Choose Athlete --" data-ajax-url="../controllers/MedicalController.php?action=ajax_search_athletes" required>
                        <option value="" disabled selected>-- Choose Athlete --</option>
                        <?php foreach ($allAthletes as $a): ?>
                            <option value="<?= $a['athlete_id'] ?>">
                                <?= htmlspecialchars($a['full_name']) ?> — <?= htmlspecialchars($a['index_number']) ?> (<?= htmlspecialchars($a['faculty']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Sport & Injury Description -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Sport Involved <span style="color:var(--danger)">*</span></label>
                        <select class="form-select" name="sport_id" required>
                            <?php foreach ($allSports as $sp): ?>
                                <option value="<?= $sp['sport_id'] ?>"><?= htmlspecialchars($sp['sport_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date of Injury <span style="color:var(--danger)">*</span></label>
                        <!-- Defaulted to TODAY -->
                        <input type="date" class="form-input" name="date_of_injury" id="injuryDateField" required value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Injury / Clinical Diagnosis <span style="color:var(--danger)">*</span></label>
                    <input type="text" class="form-input" name="injury_type" placeholder="e.g. Grade II Hamstring Strain, Ankle Inversion Sprain" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Severity & Priority <span style="color:var(--danger)">*</span></label>
                        <select class="form-select" name="severity" required>
                            <option value="Minor">Minor (Routine sprain / contusion)</option>
                            <option value="Moderate" selected>Moderate (Ligament strain, requiring 2-3 weeks rehab)</option>
                            <option value="Major">Major (High risk, concussion, fracture, surgery)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Est. Recovery (Days)</label>
                        <input type="number" class="form-input" name="recovery_days" min="1" max="365" value="14" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Attending Doctor / Examiner</label>
                    <input type="text" class="form-input" name="reported_by" value="Dr. A. Fernando" required>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Clinical Assessment Notes</label>
                    <textarea class="form-input" name="notes" rows="3" placeholder="Provide incident mechanism, initial treatment (RICE), medication prescribed, and scheduled review dates..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('logInjuryModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <span class="material-symbols-outlined" style="font-size:16px;">save</span> Save Injury Record
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL 2: ISSUE CLEARANCE ===== -->
<div class="modal-overlay" id="clearanceModal">
    <div class="modal" style="max-width:580px;">
        <div class="modal-header">
            <div>
                <span class="modal-title">Authorize Medical Clearance</span>
                <p style="font-size:12px; color:var(--text-3); margin-top:2px;">Authorize competition readiness and clear athlete injury status</p>
            </div>
            <button class="modal-close" onclick="closeModal('clearanceModal')">&times;</button>
        </div>
        <form action="../controllers/MedicalController.php?action=issue_clearance" method="POST" onsubmit="return USMS.confirmForm(this, { title: 'Authorize Clearance', message: 'Are you sure you want to authorize this medical clearance decision?', type: 'teal' });">
            <input type="hidden" name="injury_id" id="clrInjuryId" value="0">
            <input type="hidden" name="athlete_id" id="clrAthleteId" value="0">
            <input type="hidden" name="sport_id" id="clrSportId" value="1">

            <div class="modal-body">
                <div class="injury-summary-box">
                    <div style="font-weight:700; font-size:15px; color:var(--text-1);" id="clrName">Kasun Perera</div>
                    <div style="font-size:12.5px; color:var(--text-3); margin-top:2px;" id="clrInfo">ID: SC/2022/1042 · Rugby · Concussion Protocol</div>
                    <div style="font-size:12px; color:var(--danger); font-weight:600; margin-top:4px;" id="clrSeverity">Severity: Major</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Clearance Decision <span style="color:var(--danger)">*</span></label>
                    <select class="form-select" name="decision" id="clrDecision" required>
                        <option value="Full Clearance (Fit to Compete)">Full Clearance (Fit to Compete in Matches)</option>
                        <option value="Conditional / Practice Only">Conditional / Non-Contact Practice Only</option>
                        <option value="Not Cleared / Extend Rehab">Not Cleared / Extend Rehabilitation (Temporarily Unfit)</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Clearance Date <span style="color:var(--danger)">*</span></label>
                        <!-- Defaulted to TODAY -->
                        <input type="date" class="form-input" name="clearance_date" id="clrDateField" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Certifying Doctor <span style="color:var(--danger)">*</span></label>
                        <input type="text" class="form-input" name="certifying_doctor" id="clrDoctor" value="Dr. A. Fernando" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Clearance Remarks & Coach Instructions</label>
                    <textarea class="form-input" name="notes" id="clrNotes" rows="3" placeholder="e.g. Completed concussion return-to-play protocol stage 6. Cleared for full match participation with no restrictions."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('clearanceModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <span class="material-symbols-outlined" style="font-size:16px;">verified</span> Authorize Clearance
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL 3: VIEW INJURY RECORD ===== -->
<div class="modal-overlay" id="viewRecordModal">
    <div class="modal" style="max-width:580px;">
        <div class="modal-header">
            <span class="modal-title">Sports Injury Clinical Record</span>
            <button class="modal-close" onclick="closeModal('viewRecordModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:14px; padding-bottom:14px; border-bottom:1px solid var(--border);">
                <div class="mini-avatar" id="recAvatar" style="width:44px; height:44px; font-size:15px; border-radius:8px;">KP</div>
                <div style="flex:1;">
                    <div style="font-weight:700; font-size:16px; color:var(--text-1);" id="recName">Kasun Perera</div>
                    <div style="font-size:12px; color:var(--text-3);" id="recMeta">ID: SC/2022/1042 · Rugby</div>
                </div>
                <span class="badge badge-warn" id="recSeverityBadge">Moderate</span>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; font-size:13px; margin-bottom:14px;">
                <div>
                    <div style="color:var(--text-3); font-size:11px; text-transform:uppercase; font-weight:700;">Condition / Diagnosis</div>
                    <div style="font-weight:600; color:var(--text-1);" id="recCondition">Concussion Protocol</div>
                </div>
                <div>
                    <div style="color:var(--text-3); font-size:11px; text-transform:uppercase; font-weight:700;">Date of Injury</div>
                    <div style="font-weight:600; color:var(--text-1);" id="recDate">2026-09-12</div>
                </div>
                <div>
                    <div style="color:var(--text-3); font-size:11px; text-transform:uppercase; font-weight:700;">Est. Recovery Date</div>
                    <div style="font-weight:600; color:var(--text-1);" id="recRecovery">2026-09-26</div>
                </div>
                <div>
                    <div style="color:var(--text-3); font-size:11px; text-transform:uppercase; font-weight:700;">Attending Doctor</div>
                    <div style="font-weight:600; color:var(--text-1);" id="recDoctor">Dr. A. Fernando</div>
                </div>
            </div>

            <div style="background:#F8FAFC; border:1px solid var(--border); border-radius:6px; padding:10px 12px;">
                <div style="color:var(--text-3); font-size:11px; text-transform:uppercase; font-weight:700; margin-bottom:4px;">Clinical Assessment & Notes</div>
                <div style="font-size:12.5px; color:var(--text-1); line-height:1.5;" id="recNotes">No additional notes provided.</div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('viewRecordModal')">Close</button>
            <button class="btn btn-teal" id="recClearBtn" onclick="issueClearanceFromView()">Authorize Clearance</button>
        </div>
    </div>
</div>

<script src="../assets/js/dashboard.js"></script>
<script>
    setupSearch('searchPending', 'pendingTable');
    setupSearch('searchHistory', 'historyTable');

    // Auto-switch to history tab if requested via URL
    (function() {
        var params = new URLSearchParams(window.location.search);
        if (params.get('tab') === 'history' || window.location.hash === '#tab-history') {
            var tabs = document.querySelectorAll('.tab-btn');
            if (tabs.length > 1) {
                switchTab(tabs[1], 'medTabs', 'tab-history');
            }
        }
    })();

    var currentViewedInjury = null;

    function openNewInjuryModal() {
        var sel = document.getElementById('injuryAthleteSelect');
        sel.value = '';
        sel.dispatchEvent(new Event('change'));
        
        // Ensure today's date default
        document.getElementById('injuryDateField').value = new Date().toISOString().split('T')[0];
        
        openModal('logInjuryModal');
    }

    function openClearanceModal(injury) {
        document.getElementById('clrInjuryId').value = injury.injury_id || 0;
        document.getElementById('clrAthleteId').value = injury.athletic_id || 0;
        document.getElementById('clrSportId').value = injury.sport_id || 1;
        document.getElementById('clrName').textContent = injury.full_name;
        document.getElementById('clrInfo').textContent = 'ID: ' + injury.index_number + ' · ' + injury.sport_name + ' · ' + injury.injury_type;
        document.getElementById('clrSeverity').textContent = 'Severity: ' + injury.severity;
        
        // Ensure today's date default
        document.getElementById('clrDateField').value = new Date().toISOString().split('T')[0];
        document.getElementById('clrNotes').value = '';

        openModal('clearanceModal');
    }

    function openViewRecordModal(injury) {
        currentViewedInjury = injury;
        document.getElementById('recName').textContent = injury.full_name;
        document.getElementById('recMeta').textContent = 'ID: ' + injury.index_number + ' · ' + injury.sport_name;
        document.getElementById('recCondition').textContent = injury.injury_type;
        document.getElementById('recDate').textContent = injury.date_of_injury;
        document.getElementById('recRecovery').textContent = injury.estimated_recovery_date;
        document.getElementById('recDoctor').textContent = injury.reported_by || 'Dr. A. Fernando';
        document.getElementById('recNotes').textContent = injury.notes || 'No special clinical notes recorded.';

        var sevBadge = document.getElementById('recSeverityBadge');
        sevBadge.textContent = injury.severity;
        sevBadge.className = 'badge ' + (injury.severity === 'Major' ? 'badge-critical' : (injury.severity === 'Minor' ? 'badge-info' : 'badge-warn'));

        var initials = injury.full_name.split(' ').map(function(w){ return w[0]; }).join('').toUpperCase();
        document.getElementById('recAvatar').textContent = initials;

        openModal('viewRecordModal');
    }

    function issueClearanceFromView() {
        if (currentViewedInjury) {
            closeModal('viewRecordModal');
            openClearanceModal(currentViewedInjury);
        }
    }

    function openViewHistoryModal(item) {
        openViewRecordModal({
            full_name: item.full_name,
            index_number: item.index_number,
            sport_name: item.sport_name,
            injury_type: 'Clearance: ' + item.status,
            date_of_injury: item.cleared_date || 'N/A',
            estimated_recovery_date: 'Expires: ' + item.expiry_date,
            reported_by: item.certifying_doctor || 'Dr. A. Fernando',
            notes: item.notes || 'Clearance recorded in official ledger.',
            severity: item.status === 'Cleared' ? 'Minor' : 'Major'
        });
        document.getElementById('recClearBtn').style.display = 'none';
    }
</script>
</body>
</html>
