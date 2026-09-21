<?php
require_once '../config/auth_guard.php';
requireRole('Medical_Officer');

require_once '../models/Medical.php';
$medModel       = new Medical();
$stats          = $medModel->getRegistryStats();
$totalAthletes  = $stats['total'];
$fitAthletes    = $stats['fit'];
$reviewAthletes = $stats['review'];
$athletes       = $medModel->getAthleteRegistry();

$pageTitle = 'Athlete Health Registry';
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
        .bmi-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            background: #F1F5F9;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-2);
            margin-top: 6px;
        }
        .athlete-preview-box {
            background: #F8FAFC;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 16px;
            transition: all 0.2s ease;
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

        <!-- Page Title & Add Button -->
        <div class="page-title-row" style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h2 class="page-title">Athlete Health Registry</h2>
                <p style="font-size:13px; color:var(--text-3); margin-top:2px;">Centralized clinical health records, biometric monitoring, and competition clearances</p>
            </div>
            <button class="btn btn-primary" onclick="openNewRecordModal()">
                <span class="material-symbols-outlined" style="font-size:16px">person_add</span> Add / Update Health Record
            </button>
        </div>

        <!-- 3 Live Stat Cards -->
        <div class="grid-3">
            <div class="stat-card">
                <div>
                    <div class="stat-label">Total Registered Athletes</div>
                    <div class="stat-value"><?= $totalAthletes ?></div>
                </div>
                <div class="stat-icon">
                    <span class="material-symbols-outlined">groups</span>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Fit to Compete</div>
                    <div class="stat-value" style="color:var(--accent-dark);"><?= $fitAthletes ?></div>
                </div>
                <div class="stat-icon" style="color:var(--accent-dark);">
                    <span class="material-symbols-outlined">check_circle</span>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Under Medical Review</div>
                    <div class="stat-value" style="color:#D97706;"><?= $reviewAthletes ?></div>
                </div>
                <div class="stat-icon" style="color:#D97706;">
                    <span class="material-symbols-outlined">health_metrics</span>
                </div>
            </div>
        </div>

        <!-- Registry Table Card -->
        <div class="table-card">
            <div class="table-toolbar">
                <span class="table-title">Registered Student Athletes (<?= count($athletes) ?>)</span>
                <div class="table-filters">
                    <div class="search-box">
                        <span class="material-symbols-outlined">search</span>
                        <input type="text" class="search-input" id="searchRegistry" placeholder="Search athlete, index, faculty, sport…">
                    </div>
                </div>
            </div>

            <div class="table-scroll">
                <table class="data-table" id="registryTable">
                    <thead>
                        <tr>
                            <th>Athlete Name</th>
                            <th>Student ID</th>
                            <th>Faculty</th>
                            <th>Sports</th>
                            <th class="col-blood text-center">Blood</th>
                            <th>Height / Weight</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($athletes)): ?>
                            <tr>
                                <td colspan="8" style="text-align:center; padding:28px; color:var(--text-3);">
                                    No registered athletes found in the database.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($athletes as $a): 
                                $nameParts = explode(' ', trim($a['full_name']));
                                $initials = count($nameParts) >= 2 
                                    ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts)-1], 0, 1))
                                    : strtoupper(substr($a['full_name'], 0, 2));

                                $statusBadge = 'badge-ok';
                                if ($a['medical_status'] === 'Under Review') {
                                    $statusBadge = 'badge-warn';
                                } elseif ($a['medical_status'] === 'Temporarily Unfit') {
                                    $statusBadge = 'badge-critical';
                                }

                                $h = (float)$a['height'];
                                $w = (float)$a['weight'];
                                $hwText = ($h > 0 && $w > 0) ? "{$h} cm / {$w} kg" : 'Not Recorded';
                            ?>
                            <tr>
                                <td>
                                    <div class="cell-flex">
                                        <div class="mini-avatar"><?= htmlspecialchars($initials) ?></div>
                                        <div>
                                            <span class="cell-main"><?= htmlspecialchars($a['full_name']) ?></span>
                                            <span class="cell-sub">Examined: <?= !empty($a['last_examined_date']) ? htmlspecialchars($a['last_examined_date']) : 'Pending' ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="cell-mono"><?= htmlspecialchars($a['index_number']) ?></span></td>
                                <td><span class="cell-main"><?= htmlspecialchars($a['faculty']) ?></span></td>
                                <td><span class="cell-main" style="max-width:140px; display:inline-block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?= htmlspecialchars($a['sports_list']) ?>"><?= htmlspecialchars($a['sports_list']) ?></span></td>
                                <td class="col-blood text-center"><span class="badge badge-purple" style="padding:2px 7px; font-size:11.5px; font-weight:700;"><?= htmlspecialchars($a['blood_group']) ?></span></td>
                                <td><span class="cell-sub" style="font-weight:600;"><?= $hwText ?></span></td>
                                <td><span class="badge <?= $statusBadge ?>"><?= htmlspecialchars($a['medical_status']) ?></span></td>
                                <td>
                                    <div class="cell-actions">
                                        <button class="btn btn-outline btn-sm"
                                                onclick="openProfileModal(<?= htmlspecialchars(json_encode($a)) ?>)">
                                            View
                                        </button>
                                        <button class="btn btn-teal btn-sm"
                                                onclick="openEditRecordModal(<?= htmlspecialchars(json_encode($a)) ?>)"
                                                title="Update Health & Biometrics">
                                            Update
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
                <span>Showing <?= count($athletes) ?> of <?= $totalAthletes ?> registered athletes</span>
                <div class="page-btns">
                    <button class="page-btn" disabled><span class="material-symbols-outlined">chevron_left</span></button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn" disabled><span class="material-symbols-outlined">chevron_right</span></button>
                </div>
            </div>
        </div>

    </div><!-- .page-content -->
</div><!-- .main-wrapper -->

<!-- ===== MODAL 1: ADD / UPDATE STUDENT MEDICAL RECORD ===== -->
<div class="modal-overlay" id="addAthleteModal">
    <div class="modal" style="max-width:680px;">
        <div class="modal-header">
            <div>
                <span class="modal-title" id="formModalTitle">Record Student Medical Assessment</span>
                <p style="font-size:12px; color:var(--text-3); margin-top:2px;">Select registered athlete and record clinical parameters and clearance status</p>
            </div>
            <button class="modal-close" onclick="closeModal('addAthleteModal')">&times;</button>
        </div>
        <form action="../controllers/MedicalController.php?action=save_health_record" method="POST" onsubmit="return USMS.confirmForm(this, { title: 'Save Medical Assessment', message: 'Are you sure you want to update this athlete\'s clinical record and competition status?', type: 'teal' });">
            <div class="modal-body">
                
                <!-- 1. Searchable Athlete Selection -->
                <div class="form-group">
                    <label class="form-label">Select Registered Student Athlete <span style="color:var(--danger)">*</span></label>
                    <select class="form-select searchable" id="athleteSelect" name="athlete_id" data-placeholder="-- Choose Athlete --" data-ajax-url="../controllers/MedicalController.php?action=ajax_search_athletes" required onchange="onAthleteSelected(this.value)">
                        <option value="" disabled selected>-- Choose Athlete --</option>
                        <?php foreach ($athletes as $a): ?>
                            <option value="<?= $a['athlete_id'] ?>" 
                                    data-index="<?= htmlspecialchars($a['index_number']) ?>"
                                    data-faculty="<?= htmlspecialchars($a['faculty']) ?>"
                                    data-contact="<?= htmlspecialchars($a['emergency_Contact']) ?>"
                                    data-blood="<?= htmlspecialchars($a['blood_group']) ?>"
                                    data-height="<?= htmlspecialchars($a['height']) ?>"
                                    data-weight="<?= htmlspecialchars($a['weight']) ?>"
                                    data-status="<?= htmlspecialchars($a['medical_status']) ?>"
                                    data-allergies="<?= htmlspecialchars($a['allergies']) ?>"
                                    data-notes="<?= htmlspecialchars($a['medical_notes'] ?? '') ?>"
                                    data-exam="<?= htmlspecialchars($a['last_examined_date'] ?? date('Y-m-d')) ?>"
                                    data-sports="<?= htmlspecialchars($a['sports_list']) ?>">
                                <?= htmlspecialchars($a['full_name']) ?> — <?= htmlspecialchars($a['index_number']) ?> (<?= htmlspecialchars($a['faculty']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Auto-populated Student Profile Summary -->
                <div class="athlete-preview-box" id="athletePreviewBox" style="display:none;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <strong id="prevAthleteName" style="color:var(--primary); font-size:14px;">Kasun Perera</strong>
                        <span id="prevAthleteStatus" class="badge badge-ok">Fit to Compete</span>
                    </div>
                    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:8px; font-size:12px; color:var(--text-3);">
                        <div>Faculty: <strong id="prevFaculty" style="color:var(--text-1);">Science</strong></div>
                        <div>Index No: <strong id="prevIndex" style="color:var(--text-1);">SC/2022/1042</strong></div>
                        <div>Emergency: <strong id="prevContact" style="color:var(--text-1);">+94771234567</strong></div>
                    </div>
                    <div style="font-size:12px; color:var(--text-3); margin-top:6px;">
                        Sports: <strong id="prevSports" style="color:var(--accent);">University Sports</strong>
                    </div>
                </div>

                <!-- 2. Medical Status & Blood Group -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Blood Group <span style="color:var(--danger)">*</span></label>
                        <select class="form-select" name="blood_group" id="fieldBloodGroup" required>
                            <option value="O+">O+ (Positive)</option>
                            <option value="A+">A+ (Positive)</option>
                            <option value="B+">B+ (Positive)</option>
                            <option value="AB+">AB+ (Positive)</option>
                            <option value="O-">O- (Negative)</option>
                            <option value="A-">A- (Negative)</option>
                            <option value="B-">B- (Negative)</option>
                            <option value="AB-">AB- (Negative)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Medical Clearance Status <span style="color:var(--danger)">*</span></label>
                        <select class="form-select" name="medical_status" id="fieldMedStatus" required>
                            <option value="Fit to Compete">Fit to Compete (Cleared)</option>
                            <option value="Under Review">Under Review (Observation)</option>
                            <option value="Temporarily Unfit">Temporarily Unfit (Injured / Blocked)</option>
                        </select>
                    </div>
                </div>

                <!-- 3. Physical Parameters (Height, Weight, BMI) -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Height (cm)</label>
                        <input type="number" step="0.1" min="100" max="250" class="form-input" name="height" id="fieldHeight" placeholder="e.g. 178.5" oninput="calculateBMI()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Weight (kg)</label>
                        <input type="number" step="0.1" min="30" max="200" class="form-input" name="weight" id="fieldWeight" placeholder="e.g. 72.0" oninput="calculateBMI()">
                    </div>
                </div>
                <div style="margin-top:-6px; margin-bottom:12px;">
                    <div class="bmi-pill" id="bmiDisplay">
                        <span class="material-symbols-outlined" style="font-size:16px;">monitor_weight</span>
                        <span>BMI: <strong id="bmiVal">--</strong> (<span id="bmiCat">Enter height & weight</span>)</span>
                    </div>
                </div>

                <!-- 4. Examination & Clearance Dates (Default to TODAY) -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Examination Date <span style="color:var(--danger)">*</span></label>
                        <input type="date" class="form-input" name="last_examined_date" id="fieldExamDate" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Clearance Expiry Date <span style="color:var(--danger)">*</span></label>
                        <input type="date" class="form-input" name="clearance_expiry_date" id="fieldExpiryDate" required value="<?= date('Y-m-d', strtotime('+1 year')) ?>">
                    </div>
                </div>

                <!-- 5. Allergies & Clinical Notes -->
                <div class="form-group">
                    <label class="form-label">Known Allergies / Chronic Conditions</label>
                    <input type="text" class="form-input" name="allergies" id="fieldAllergies" placeholder="e.g. Penicillin, Asthma, Dust allergy, or None" value="None">
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Special Medical Requirements & Clinical Notes</label>
                    <textarea class="form-input" name="medical_notes" id="fieldNotes" rows="3" placeholder="Specify any dietary restrictions, braces/inhalers required on match days, rehab observations, or cardiology notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addAthleteModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <span class="material-symbols-outlined" style="font-size:16px;">save</span> Save Medical Record
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL 2: VIEW STUDENT FULL MEDICAL PROFILE ===== -->
<div class="modal-overlay" id="viewProfileModal">
    <div class="modal" style="max-width:620px;">
        <div class="modal-header">
            <span class="modal-title">Student Medical Profile</span>
            <button class="modal-close" onclick="closeModal('viewProfileModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div style="display:flex; align-items:center; gap:14px; margin-bottom:14px; padding-bottom:14px; border-bottom:1px solid var(--border);">
                <div class="mini-avatar" id="profAvatar" style="width:48px; height:48px; font-size:16px; border-radius:10px;">DP</div>
                <div style="flex:1;">
                    <div style="font-weight:700; font-size:16px; color:var(--text-1);" id="profName">Kasun Perera</div>
                    <div style="font-size:12px; color:var(--text-3);" id="profMeta">ID: SC/2022/1042 · Faculty of Science</div>
                    <div style="font-size:12px; color:var(--accent); font-weight:600; margin-top:2px;" id="profSports">Sports: Rugby</div>
                </div>
                <span class="badge badge-ok" id="profStatusBadge" style="font-size:12px; padding:6px 12px;">Fit to Compete</span>
            </div>

            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:12px; margin-bottom:14px; background:#F8FAFC; border:1px solid var(--border); border-radius:8px; padding:12px;">
                <div>
                    <div style="color:var(--text-3); font-size:11px; text-transform:uppercase; font-weight:700;">Blood Group</div>
                    <div style="font-weight:700; font-size:15px; color:var(--danger);" id="profBlood">O+</div>
                </div>
                <div>
                    <div style="color:var(--text-3); font-size:11px; text-transform:uppercase; font-weight:700;">Height / Weight</div>
                    <div style="font-weight:600; font-size:13px; color:var(--text-1);" id="profHW">178 cm / 72 kg</div>
                </div>
                <div>
                    <div style="color:var(--text-3); font-size:11px; text-transform:uppercase; font-weight:700;">Body Mass Index</div>
                    <div style="font-weight:600; font-size:13px; color:var(--text-1);" id="profBMI">22.7 (Normal)</div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; font-size:13px; margin-bottom:14px;">
                <div>
                    <div style="color:var(--text-3); font-size:11px; text-transform:uppercase; font-weight:700;">Allergies & Sensitivities</div>
                    <div style="font-weight:600; color:var(--text-1);" id="profAllergies">None</div>
                </div>
                <div>
                    <div style="color:var(--text-3); font-size:11px; text-transform:uppercase; font-weight:700;">Last Examined</div>
                    <div style="font-weight:600; color:var(--text-1);" id="profExamDate">2026-09-12</div>
                </div>
                <div style="grid-column: span 2;">
                    <div style="color:var(--text-3); font-size:11px; text-transform:uppercase; font-weight:700;">Emergency Contact</div>
                    <div style="font-weight:600; color:var(--text-1);" id="profContact">+94771234567</div>
                </div>
            </div>

            <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:6px; padding:10px 12px;">
                <div style="color:#B45309; font-size:11px; text-transform:uppercase; font-weight:700; margin-bottom:4px;">Clinical Notes & Observations</div>
                <div style="font-size:12.5px; color:#78350F; line-height:1.5;" id="profNotes">Under concussion recovery protocol. Restricting contact drills.</div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('viewProfileModal')">Close</button>
            <button class="btn btn-teal" id="profEditBtn" onclick="editCurrentProfile()">Update Assessment</button>
        </div>
    </div>
</div>

<script src="../assets/js/dashboard.js"></script>
<script>
    setupSearch('searchRegistry', 'registryTable');

    var currentViewedAthlete = null;

    function calculateBMI() {
        var h = parseFloat(document.getElementById('fieldHeight').value);
        var w = parseFloat(document.getElementById('fieldWeight').value);
        var valEl = document.getElementById('bmiVal');
        var catEl = document.getElementById('bmiCat');

        if (h > 0 && w > 0) {
            var hm = h / 100.0;
            var bmi = (w / (hm * hm)).toFixed(1);
            valEl.textContent = bmi;
            if (bmi < 18.5) catEl.textContent = 'Underweight';
            else if (bmi < 25) catEl.textContent = 'Normal';
            else if (bmi < 30) catEl.textContent = 'Overweight';
            else catEl.textContent = 'Obese';
        } else {
            valEl.textContent = '--';
            catEl.textContent = 'Enter height & weight';
        }
    }

    function onAthleteSelected(athleteId) {
        var sel = document.getElementById('athleteSelect');
        var opt = sel.options[sel.selectedIndex];
        var box = document.getElementById('athletePreviewBox');

        if (!athleteId || !opt) {
            box.style.display = 'none';
            return;
        }

        box.style.display = 'block';
        document.getElementById('prevAthleteName').textContent = opt.textContent.split('—')[0].trim();
        document.getElementById('prevFaculty').textContent = opt.dataset.faculty || 'N/A';
        document.getElementById('prevIndex').textContent = opt.dataset.index || 'N/A';
        document.getElementById('prevContact').textContent = opt.dataset.contact || 'N/A';
        document.getElementById('prevSports').textContent = opt.dataset.sports || 'University Sports';

        var stBadge = document.getElementById('prevAthleteStatus');
        stBadge.textContent = opt.dataset.status || 'Fit to Compete';
        stBadge.className = 'badge ' + (opt.dataset.status === 'Fit to Compete' ? 'badge-ok' : (opt.dataset.status === 'Under Review' ? 'badge-warn' : 'badge-critical'));

        // Autofill existing medical attributes if available
        if (opt.dataset.blood) document.getElementById('fieldBloodGroup').value = opt.dataset.blood;
        if (opt.dataset.status) document.getElementById('fieldMedStatus').value = opt.dataset.status;
        if (parseFloat(opt.dataset.height) > 0) document.getElementById('fieldHeight').value = opt.dataset.height;
        if (parseFloat(opt.dataset.weight) > 0) document.getElementById('fieldWeight').value = opt.dataset.weight;
        if (opt.dataset.allergies) document.getElementById('fieldAllergies').value = opt.dataset.allergies;
        if (opt.dataset.notes) document.getElementById('fieldNotes').value = opt.dataset.notes;

        calculateBMI();
    }

    function openNewRecordModal() {
        document.getElementById('formModalTitle').textContent = 'Record Student Medical Assessment';
        var sel = document.getElementById('athleteSelect');
        sel.value = '';
        sel.dispatchEvent(new Event('change'));
        document.getElementById('athletePreviewBox').style.display = 'none';
        
        // Ensure today's date default
        var today = new Date().toISOString().split('T')[0];
        document.getElementById('fieldExamDate').value = today;
        
        var nextYear = new Date();
        nextYear.setFullYear(nextYear.getFullYear() + 1);
        document.getElementById('fieldExpiryDate').value = nextYear.toISOString().split('T')[0];

        document.getElementById('fieldBloodGroup').value = 'O+';
        document.getElementById('fieldMedStatus').value = 'Fit to Compete';
        document.getElementById('fieldHeight').value = '';
        document.getElementById('fieldWeight').value = '';
        document.getElementById('fieldAllergies').value = 'None';
        document.getElementById('fieldNotes').value = '';
        calculateBMI();

        openModal('addAthleteModal');
    }

    function openEditRecordModal(athlete) {
        document.getElementById('formModalTitle').textContent = 'Update Health Record: ' + athlete.full_name;
        var sel = document.getElementById('athleteSelect');
        sel.value = athlete.athlete_id;
        sel.dispatchEvent(new Event('change'));
        onAthleteSelected(athlete.athlete_id);

        if (athlete.last_examined_date) {
            document.getElementById('fieldExamDate').value = athlete.last_examined_date;
        } else {
            document.getElementById('fieldExamDate').value = new Date().toISOString().split('T')[0];
        }

        openModal('addAthleteModal');
    }

    function openProfileModal(athlete) {
        currentViewedAthlete = athlete;
        document.getElementById('profName').textContent = athlete.full_name;
        document.getElementById('profMeta').textContent = 'ID: ' + athlete.index_number + ' · ' + athlete.faculty;
        document.getElementById('profSports').textContent = 'Sports: ' + (athlete.sports_list || 'University Sports');
        document.getElementById('profBlood').textContent = athlete.blood_group || 'O+';
        document.getElementById('profContact').textContent = athlete.emergency_Contact || 'N/A';
        document.getElementById('profAllergies').textContent = athlete.allergies || 'None';
        document.getElementById('profExamDate').textContent = athlete.last_examined_date || 'Pending Initial Screen';
        document.getElementById('profNotes').textContent = athlete.medical_notes || 'No special requirements or clinical restrictions noted.';

        var h = parseFloat(athlete.height);
        var w = parseFloat(athlete.weight);
        if (h > 0 && w > 0) {
            document.getElementById('profHW').textContent = h + ' cm / ' + w + ' kg';
            var hm = h / 100.0;
            var bmi = (w / (hm * hm)).toFixed(1);
            var cat = (bmi < 18.5 ? 'Underweight' : (bmi < 25 ? 'Normal' : (bmi < 30 ? 'Overweight' : 'Obese')));
            document.getElementById('profBMI').textContent = bmi + ' (' + cat + ')';
        } else {
            document.getElementById('profHW').textContent = 'Not Recorded';
            document.getElementById('profBMI').textContent = 'N/A';
        }

        var stBadge = document.getElementById('profStatusBadge');
        stBadge.textContent = athlete.medical_status || 'Fit to Compete';
        stBadge.className = 'badge ' + (athlete.medical_status === 'Fit to Compete' ? 'badge-ok' : (athlete.medical_status === 'Under Review' ? 'badge-warn' : 'badge-critical'));

        var initials = athlete.full_name.split(' ').map(function(w){ return w[0]; }).join('').toUpperCase();
        document.getElementById('profAvatar').textContent = initials;

        openModal('viewProfileModal');
    }

    function editCurrentProfile() {
        if (currentViewedAthlete) {
            closeModal('viewProfileModal');
            openEditRecordModal(currentViewedAthlete);
        }
    }
</script>
</body>
</html>
