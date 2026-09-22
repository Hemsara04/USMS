<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'Coach';
}
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'Coach Silva';
}

$pageTitle = 'Squad Formulation & Selection';

// Stat Cards (Grid-4)
$stats = [
    'selected_players' => '15 / 18',
    'medically_fit' => 24,
    'under_review' => 3,
    'disqualified' => 1
];

// Target Fixtures
$fixtures = [
    [
        'id' => 'FIX-2026-08',
        'title' => 'SLUG Championship 2026 - Semi Final vs University of Peradeniya',
        'match_date' => '2026-10-04 • 03:30 PM',
        'venue' => 'Bogambara Stadium, Kandy',
        'sport' => 'Men\'s Football',
        'status' => 'Draft in Progress',
        'deadline' => 'Sep 28, 2026 (6 days remaining)'
    ],
    [
        'id' => 'FIX-2026-07',
        'title' => 'Inter-Faculty Championship - Group Stage vs Faculty of Science',
        'match_date' => '2026-09-28 • 04:00 PM',
        'venue' => 'Main University Ground',
        'sport' => 'Men\'s Football',
        'status' => 'Approved by TIC',
        'deadline' => 'Finalized'
    ],
    [
        'id' => 'FIX-2026-09',
        'title' => 'National Universities Invitational vs University of Moratuwa',
        'match_date' => '2026-10-12 • 09:00 AM',
        'venue' => 'University of Moratuwa Complex',
        'sport' => 'Men\'s Football',
        'status' => 'Upcoming Draft',
        'deadline' => 'Oct 05, 2026'
    ]
];

// Available Athlete Pool (Not yet selected or in pool)
$availableAthletes = [
    ['id' => 'ATH-021', 'name' => 'Kusal Mendis', 'index' => 'SC/2022/1029', 'faculty' => 'Science', 'position' => 'Winger / Forward', 'med_status' => 'Fit to Compete', 'can_add' => true],
    ['id' => 'ATH-034', 'name' => 'Dhananjaya Silva', 'index' => 'MG/2021/8831', 'faculty' => 'Management', 'position' => 'Central Midfielder', 'med_status' => 'Fit to Compete', 'can_add' => true],
    ['id' => 'ATH-049', 'name' => 'Akila Dananjaya', 'index' => 'AR/2023/4190', 'faculty' => 'Arts', 'position' => 'Full Back', 'med_status' => 'Fit to Compete', 'can_add' => true],
    ['id' => 'ATH-058', 'name' => 'Pathum Nissanka', 'index' => 'TC/2022/6721', 'faculty' => 'Technology', 'position' => 'Striker', 'med_status' => 'Fit to Compete', 'can_add' => true],
    ['id' => 'ATH-066', 'name' => 'Wanindu Hasaranga', 'index' => 'SC/2021/3392', 'faculty' => 'Science', 'position' => 'Defensive Midfielder', 'med_status' => 'Under Review', 'can_add' => false, 'med_note' => 'Awaiting hamstring ultrasound report from Medical Center'],
    ['id' => 'ATH-072', 'name' => 'Lahiru Kumara', 'index' => 'EN/2023/9102', 'faculty' => 'Engineering', 'position' => 'Goalkeeper', 'med_status' => 'Fit to Compete', 'can_add' => true],
    ['id' => 'ATH-081', 'name' => 'Kasun Rajitha', 'index' => 'LW/2022/1183', 'faculty' => 'Law', 'position' => 'Center Back', 'med_status' => 'Temporarily Unfit', 'can_add' => false, 'med_note' => 'Sprained ankle grade II - Cleared for non-contact training only'],
    ['id' => 'ATH-095', 'name' => 'Bhanuka Rajapaksa', 'index' => 'MG/2023/5021', 'faculty' => 'Management', 'position' => 'Forward', 'med_status' => 'Under Review', 'can_add' => false, 'med_note' => 'Cardiovascular stress ECG test scheduled']
];

// Current Match Squad Roster (Already formulated in draft)
$currentSquad = [
    ['jersey' => 10, 'name' => 'Kavindu Perera', 'id' => 'ATH-042', 'role' => 'Captain', 'position' => 'Attacking Mid', 'faculty' => 'Science', 'status' => 'Cleared'],
    ['jersey' => 1,  'name' => 'Chamara Silva', 'id' => 'ATH-089', 'role' => 'Starting XI', 'position' => 'Goalkeeper', 'faculty' => 'Arts', 'status' => 'Cleared'],
    ['jersey' => 4,  'name' => 'Dineth Jayawardena', 'id' => 'ATH-055', 'role' => 'Starting XI', 'position' => 'Center Back', 'faculty' => 'Engineering', 'status' => 'Cleared'],
    ['jersey' => 5,  'name' => 'Praveen Jayawickrama', 'id' => 'ATH-012', 'role' => 'Starting XI', 'position' => 'Center Back', 'faculty' => 'Science', 'status' => 'Cleared'],
    ['jersey' => 2,  'name' => 'Malith Bandara', 'id' => 'ATH-031', 'role' => 'Starting XI', 'position' => 'Right Back', 'faculty' => 'Law', 'status' => 'Cleared'],
    ['jersey' => 3,  'name' => 'Asitha Fernando', 'id' => 'ATH-019', 'role' => 'Starting XI', 'position' => 'Left Back', 'faculty' => 'Management', 'status' => 'Cleared'],
    ['jersey' => 6,  'name' => 'Sahan Wickramasinghe', 'id' => 'ATH-063', 'role' => 'Starting XI', 'position' => 'Defensive Mid', 'faculty' => 'Science', 'status' => 'Cleared'],
    ['jersey' => 8,  'name' => 'Charith Asalanka', 'id' => 'ATH-027', 'role' => 'Starting XI', 'position' => 'Central Mid', 'faculty' => 'Technology', 'status' => 'Cleared'],
    ['jersey' => 7,  'name' => 'Nuwan Fernando', 'id' => 'ATH-018', 'role' => 'Starting XI', 'position' => 'Right Winger', 'faculty' => 'Management', 'status' => 'Cleared'],
    ['jersey' => 11, 'name' => 'Maheesh Theekshana', 'id' => 'ATH-061', 'role' => 'Starting XI', 'position' => 'Left Winger', 'faculty' => 'Arts', 'status' => 'Cleared'],
    ['jersey' => 9,  'name' => 'Sadeera Samarawickrama', 'id' => 'ATH-088', 'role' => 'Starting XI', 'position' => 'Striker', 'faculty' => 'Medicine', 'status' => 'Cleared'],
    ['jersey' => 12, 'name' => 'Dunith Wellalage', 'id' => 'ATH-039', 'role' => 'Substitute', 'position' => 'Midfielder', 'faculty' => 'Science', 'status' => 'Cleared'],
    ['jersey' => 14, 'name' => 'Pramod Madushan', 'id' => 'ATH-044', 'role' => 'Substitute', 'position' => 'Defender', 'faculty' => 'Technology', 'status' => 'Cleared'],
    ['jersey' => 16, 'name' => 'Matheesha Pathirana', 'id' => 'ATH-051', 'role' => 'Substitute', 'position' => 'Forward', 'faculty' => 'Management', 'status' => 'Cleared'],
    ['jersey' => 18, 'name' => 'Nuwanidu Fernando', 'id' => 'ATH-090', 'role' => 'Substitute', 'position' => 'Reserve GK', 'faculty' => 'Engineering', 'status' => 'Cleared']
];
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
            padding: 10px 12px;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
            text-align: left;
            vertical-align: middle;
        }
        .dash-table tbody td {
            padding: 9px 12px;
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

        <!-- Top Stat Cards (Grid-4) -->
        <div class="grid-4">
            <div class="stat-card">
                <div>
                    <div class="stat-label">Selected Players</div>
                    <div class="stat-value" id="statSelectedCount" style="color: var(--accent-dark);"><?= $stats['selected_players'] ?></div>
                </div>
                <div class="stat-icon" style="color: var(--accent); background: var(--accent-bg);">
                    <span class="material-symbols-outlined">group_add</span>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Medically Fit</div>
                    <div class="stat-value" style="color: #16A34A;"><?= $stats['medically_fit'] ?></div>
                </div>
                <div class="stat-icon" style="color: #16A34A; background: #F0FDF4;">
                    <span class="material-symbols-outlined">verified_user</span>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Under Medical Review</div>
                    <div class="stat-value" style="color: #D97706;"><?= $stats['under_review'] ?></div>
                </div>
                <div class="stat-icon" style="color: #D97706; background: #FFFBEB;">
                    <span class="material-symbols-outlined">warning</span>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Disqualified / Unfit</div>
                    <div class="stat-value" style="color: #DC2626;"><?= $stats['disqualified'] ?></div>
                </div>
                <div class="stat-icon" style="color: #DC2626; background: #FEF2F2;">
                    <span class="material-symbols-outlined">block</span>
                </div>
            </div>
        </div>

        <!-- Fixture Selector Card -->
        <div class="fixture-selector-card">
            <div style="flex: 1; min-width: 280px;">
                <label class="form-label" style="font-size: 12px; text-transform: uppercase; color: var(--text-3); margin-bottom: 4px;">Target Match Fixture</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <select class="form-select" id="fixtureSelect" style="font-weight: 700; color: var(--primary);" onchange="updateFixtureDisplay(this.value)">
                        <?php foreach ($fixtures as $f): ?>
                            <option value="<?= htmlspecialchars($f['id']) ?>"><?= htmlspecialchars($f['title']) ?> (<?= htmlspecialchars($f['match_date']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 14px;">
                <div>
                    <span style="font-size: 11.5px; color: var(--text-3); display: block;">Draft Status</span>
                    <span class="badge badge-warning" id="fixtureDraftBadge">Draft in Progress</span>
                </div>
                <div style="border-left: 1px solid var(--border); padding-left: 14px;">
                    <span style="font-size: 11.5px; color: var(--text-3); display: block;">Submission Deadline</span>
                    <strong style="font-size: 13px; color: var(--accent-dark);">Sep 28, 2026 (6 days)</strong>
                </div>
                <button type="button" class="btn btn-outline btn-sm" onclick="alert('Rules: Max 18 players per squad. All athletes must possess valid medical clearance issued within 30 days.')">
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
                                $medClass = 'badge-success';
                                if ($ath['med_status'] === 'Under Review') $medClass = 'badge-warning';
                                elseif ($ath['med_status'] === 'Temporarily Unfit') $medClass = 'badge-danger';
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
                                    <span class="badge <?= $medClass ?>" title="<?= htmlspecialchars($ath['med_note'] ?? 'Cleared by University Health Center') ?>">
                                        <?= htmlspecialchars($ath['med_status']) ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <?php if ($ath['can_add']): ?>
                                        <button type="button" class="btn btn-outline btn-sm" onclick="addToSquad('<?= htmlspecialchars($ath['id']) ?>', '<?= htmlspecialchars(addslashes($ath['name'])) ?>', '<?= htmlspecialchars(addslashes($ath['position'])) ?>', '<?= htmlspecialchars($ath['faculty']) ?>')">
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
                        <span class="badge badge-success" style="font-size: 11px;">100% Medically Fit</span>
                    </div>
                </div>

                <div class="table-scroll">
                    <table class="dash-table data-table" id="squadTable">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Jersey</th>
                                <th>Player Name</th>
                                <th>Assigned Role</th>
                                <th>Position</th>
                                <th style="text-align: right;">Remove</th>
                            </tr>
                        </thead>
                        <tbody id="squadTableBody">
                            <?php foreach ($currentSquad as $p): 
                                $roleClass = 'role-badge-starter';
                                if ($p['role'] === 'Captain') $roleClass = 'role-badge-captain';
                                elseif ($p['role'] === 'Substitute') $roleClass = 'role-badge-sub';
                            ?>
                            <tr id="squad-row-<?= htmlspecialchars($p['id']) ?>">
                                <td>
                                    <span class="mini-avatar" style="background: var(--side-bg); color: #fff; font-size: 11.5px; font-weight: 700;">
                                        #<?= (int)$p['jersey'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="cell-main"><?= htmlspecialchars($p['name']) ?></span>
                                    <span class="cell-sub"><?= htmlspecialchars($p['faculty']) ?></span>
                                </td>
                                <td>
                                    <select class="role-select <?= $roleClass ?>" onchange="updateRoleBadge(this)">
                                        <option value="Captain" <?= ($p['role'] === 'Captain') ? 'selected' : '' ?>>Captain (C)</option>
                                        <option value="Starting XI" <?= ($p['role'] === 'Starting XI') ? 'selected' : '' ?>>Starting XI</option>
                                        <option value="Substitute" <?= ($p['role'] === 'Substitute') ? 'selected' : '' ?>>Substitute</option>
                                    </select>
                                </td>
                                <td>
                                    <span style="font-size: 12.5px; color: var(--text-2); font-weight: 500;"><?= htmlspecialchars($p['position']) ?></span>
                                </td>
                                <td style="text-align: right;">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeFromSquad('<?= htmlspecialchars($p['id']) ?>', '<?= htmlspecialchars(addslashes($p['name'])) ?>')">
                                        <span class="material-symbols-outlined" style="font-size: 14px;">close</span>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
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
        <form onsubmit="event.preventDefault(); alert('Official match squad submitted to TIC Officer Mr. Wickramasinghe for accreditation!'); closeModal('submitSquadModal');">
            <div class="modal-body">
                <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: var(--r); padding: 12px 16px; margin-bottom: 14px; display: flex; align-items: center; gap: 10px;">
                    <span class="material-symbols-outlined" style="color: #16A34A; font-size: 24px;">task_alt</span>
                    <div>
                        <div style="font-weight: 700; color: #16A34A; font-size: 13.5px;">Medical Compliance Verified</div>
                        <div style="font-size: 12px; color: #15803D;">All 15 formulated athletes have active medical clearance certificates logged in the USMS clinic.</div>
                    </div>
                </div>

                <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--r); padding: 12px 16px; margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px;">
                        <span>Target Match:</span>
                        <strong style="color: var(--accent-dark);">SLUG Semi Final vs UoP</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px;">
                        <span>Squad Formulation:</span>
                        <strong>15 Athletes (1 Captain, 10 Starters, 4 Subs)</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px;">
                        <span>Submission Deadline:</span>
                        <strong style="color: #D97706;">Sep 28, 2026</strong>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tactical Formation & Coach Notes for TIC</label>
                    <textarea class="form-textarea" placeholder="Provide formation note (e.g. 4-3-3 attacking system), jersey conflicts, or emergency standby notices…" rows="3">Standard 4-3-3 high-press formation. Medical staff cleared Kavindu Perera following mild calf assessment. All jerseys inspected.</textarea>
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
function addToSquad(id, name, position, faculty) {
    var rows = document.querySelectorAll('#squadTableBody tr');
    if (rows.length >= 18) {
        alert('Squad limit reached (Maximum 18 players). Please remove a substitute first.');
        return;
    }
    var newJersey = rows.length + 1;
    var tr = document.createElement('tr');
    tr.id = 'squad-row-' + id;
    tr.innerHTML = '<td><span class="mini-avatar" style="background: var(--side-bg); color: #fff; font-size: 11.5px; font-weight: 700;">#' + newJersey + '</span></td>' +
        '<td><span class="cell-main">' + name + '</span><span class="cell-sub">' + faculty + '</span></td>' +
        '<td><select class="role-select role-badge-sub" onchange="updateRoleBadge(this)"><option value="Starting XI">Starting XI</option><option value="Substitute" selected>Substitute</option><option value="Captain">Captain (C)</option></select></td>' +
        '<td><span style="font-size: 12.5px; color: var(--text-2); font-weight: 500;">' + position + '</span></td>' +
        '<td style="text-align: right;"><button type="button" class="btn btn-danger btn-sm" onclick="removeFromSquad(\'' + id + '\', \'' + name.replace(/'/g, "\\'") + '\')"><span class="material-symbols-outlined" style="font-size: 14px;">close</span></button></td>';

    document.getElementById('squadTableBody').appendChild(tr);
    updateSquadCounters();
    alert(name + ' added to match squad substitute bench.');
}

// Remove athlete from squad roster
function removeFromSquad(id, name) {
    var row = document.getElementById('squad-row-' + id);
    if (row && confirm('Remove ' + name + ' from match squad?')) {
        row.remove();
        updateSquadCounters();
    }
}

// Update counters
function updateSquadCounters() {
    var count = document.querySelectorAll('#squadTableBody tr').length;
    document.getElementById('statSelectedCount').textContent = count + ' / 18';
    document.getElementById('squadRosterCount').textContent = count + ' / 18';
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

function updateFixtureDisplay(val) {
    var badge = document.getElementById('fixtureDraftBadge');
    if (val === 'FIX-2026-07') {
        badge.textContent = 'Approved by TIC';
        badge.className = 'badge badge-success';
    } else {
        badge.textContent = 'Draft in Progress';
        badge.className = 'badge badge-warning';
    }
}
</script>

</body>
</html>
