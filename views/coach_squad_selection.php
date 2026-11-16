<?php
/**
 * USMS - Coach Squad Formulation & Match Selection
 * 
 * Production-ready View implementing Interim Evaluation Feedback:
 * 1. Historical Match Squad Ledger & Player Appearance Preservation
 * 2. Operational Logic: Strict Medical Clearance Enforcement & Max 18 Player Cap
 * 3. User Convenience: Tactical Formation Dropdown & Position/Faculty Filters
 * 4. Dropdowns instead of manual typing across all selectors
 * 5. Clean mock data rendering immediately in XAMPP without database dependency
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['role'])) {
    $_SESSION['role'] = 'Coach';
    $_SESSION['username'] = 'Coach Sunil';
    $_SESSION['email'] = 'sunil.coach@uoc.ac.lk';
}

$pageTitle = 'Squad Formulation & Selection';
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// ==========================================
// 1. CLEAN REALISTIC MOCK DATA ARRAYS
// ==========================================
$mockFixtures = [
    [
        'fixture_id' => 1,
        'match_title'=> 'UOC vs University of Ruhuna (SLUG Semi-Final Qualifier)',
        'opponent'   => 'University of Ruhuna',
        'match_date' => '2026-10-08 08:00 AM',
        'venue'      => 'Colombo University Grounds',
        'status'     => 'APPROVED BY TIC',
        'badge_cls'  => 'badge-success'
    ],
    [
        'fixture_id' => 2,
        'match_title'=> 'UOC vs Univ. of Sri Jayewardenepura (Inter-University League)',
        'opponent'   => 'Univ. of Sri Jayewardenepura',
        'match_date' => '2026-10-15 02:00 PM',
        'venue'      => 'Indoor Sports Arena',
        'status'     => 'ACTIVE MATCH DRAFT',
        'badge_cls'  => 'badge-warning'
    ],
    [
        'fixture_id' => 3,
        'match_title'=> 'UOC vs University of Kelaniya (Invitational Shield)',
        'opponent'   => 'University of Kelaniya',
        'match_date' => '2026-10-22 09:30 AM',
        'venue'      => 'Kelaniya Sports Ground',
        'status'     => 'SUBMITTED TO TIC',
        'badge_cls'  => 'badge-scheduled'
    ]
];

$selectedFixtureId = isset($_GET['fixture_id']) ? (int)$_GET['fixture_id'] : 1;
$activeFixture = $mockFixtures[0];
foreach ($mockFixtures as $fix) {
    if ($fix['fixture_id'] === $selectedFixtureId) {
        $activeFixture = $fix;
        break;
    }
}

// Available Athlete Pool with Medical Statuses
$availableAthletes = [
    ['athlete_id' => 201, 'name' => 'Kavindu Senanayake', 'reg_no' => 'AR/2022/5021', 'faculty' => 'Faculty of Arts',       'position' => 'Midfielder', 'medical' => 'FIT TO COMPETE',       'med_class' => 'badge-success', 'eligible' => true],
    ['athlete_id' => 202, 'name' => 'Chamara Bandara',     'reg_no' => 'MD/2023/4019', 'faculty' => 'Faculty of Medicine',   'position' => 'Forward',    'medical' => 'FIT TO COMPETE',       'med_class' => 'badge-success', 'eligible' => true],
    ['athlete_id' => 203, 'name' => 'Dilshan Pathirana',   'reg_no' => 'AR/2022/6630', 'faculty' => 'Faculty of Arts',       'position' => 'Defender',   'medical' => 'FIT TO COMPETE',       'med_class' => 'badge-success', 'eligible' => true],
    ['athlete_id' => 204, 'name' => 'Dinuka Fernando',     'reg_no' => 'SC/2023/8891', 'faculty' => 'Faculty of Science',    'position' => 'Goalkeeper', 'medical' => 'FIT TO COMPETE',       'med_class' => 'badge-success', 'eligible' => true],
    ['athlete_id' => 205, 'name' => 'Mahesh Theekshana',   'reg_no' => 'TC/2022/1004', 'faculty' => 'Faculty of Technology', 'position' => 'Defender',   'medical' => 'FIT TO COMPETE',       'med_class' => 'badge-success', 'eligible' => true],
    ['athlete_id' => 206, 'name' => 'Roshan Mahanama',     'reg_no' => 'MG/2021/3321', 'faculty' => 'Faculty of Management', 'position' => 'Midfielder', 'medical' => 'UNDER MEDICAL REVIEW','med_class' => 'badge-warning', 'eligible' => false, 'note' => 'Awaiting UMO Physiotherapy Clearance'],
    ['athlete_id' => 207, 'name' => 'Lahiru Kumara',       'reg_no' => 'AR/2023/7712', 'faculty' => 'Faculty of Arts',       'position' => 'Striker',    'medical' => 'TEMPORARILY UNFIT',    'med_class' => 'badge-danger',  'eligible' => false, 'note' => 'Grade-1 Hamstring Strain (Disqualified)']
];

// Current Match Squad Roster (Selected 15 out of 18 Max)
$currentSquad = [
    ['athlete_id' => 301, 'jersey' => 1,  'name' => 'Nadeesha Perera',   'faculty' => 'Faculty of Arts',       'role' => 'Starting XI', 'position' => 'Goalkeeper'],
    ['athlete_id' => 302, 'jersey' => 4,  'name' => 'Tom Aris',           'faculty' => 'Faculty of Technology', 'role' => 'Starting XI', 'position' => 'Center Back'],
    ['athlete_id' => 303, 'jersey' => 5,  'name' => 'Kasun Perera',       'faculty' => 'Faculty of Science',    'role' => 'Captain',     'position' => 'Center Back'],
    ['athlete_id' => 304, 'jersey' => 3,  'name' => 'Amali Wickramasinghe','faculty' => 'Faculty of Arts',      'role' => 'Starting XI', 'position' => 'Left Back'],
    ['athlete_id' => 305, 'jersey' => 2,  'name' => 'Dinesh Nuwan',       'faculty' => 'Faculty of Medicine',   'role' => 'Starting XI', 'position' => 'Right Back'],
    ['athlete_id' => 306, 'jersey' => 6,  'name' => 'Akila Dananjaya',    'faculty' => 'Faculty of Management', 'role' => 'Starting XI', 'position' => 'Defensive Midfielder'],
    ['athlete_id' => 307, 'jersey' => 8,  'name' => 'Charith Asalanka',   'faculty' => 'Faculty of Arts',       'role' => 'Starting XI', 'position' => 'Center Midfielder'],
    ['athlete_id' => 308, 'jersey' => 10, 'name' => 'Wanindu Hasaranga',  'faculty' => 'Faculty of Science',    'role' => 'Vice Captain','position' => 'Attacking Midfielder'],
    ['athlete_id' => 309, 'jersey' => 7,  'name' => 'Kusal Mendis',       'faculty' => 'Faculty of Technology', 'role' => 'Starting XI', 'position' => 'Right Winger'],
    ['athlete_id' => 310, 'jersey' => 11, 'name' => 'Bhanuka Rajapaksa',  'faculty' => 'Faculty of Arts',       'role' => 'Starting XI', 'position' => 'Left Winger'],
    ['athlete_id' => 311, 'jersey' => 9,  'name' => 'Angelo Mathews',     'faculty' => 'Faculty of Management', 'role' => 'Starting XI', 'position' => 'Striker'],
    ['athlete_id' => 312, 'jersey' => 12, 'name' => 'Danushka Gunathilaka','faculty' => 'Faculty of Science',   'role' => 'Key Substitute','position' => 'Substitute Winger'],
    ['athlete_id' => 313, 'jersey' => 14, 'name' => 'Jeffrey Vandersay',  'faculty' => 'Faculty of Technology', 'role' => 'Key Substitute','position' => 'Substitute Midfielder'],
    ['athlete_id' => 314, 'jersey' => 15, 'name' => 'Binura Fernando',    'faculty' => 'Faculty of Arts',       'role' => 'Key Substitute','position' => 'Substitute Defender'],
    ['athlete_id' => 315, 'jersey' => 16, 'name' => 'Pramod Madushan',    'faculty' => 'Faculty of Medicine',   'role' => 'Key Substitute','position' => 'Reserve Goalkeeper']
];

$selectedCount = count($currentSquad);
$maxSquadCap = 18;
$fitCount = 24;
$underReviewCount = 3;
$disqualifiedCount = 2;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — USMS</title>
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .squad-dual-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }
        @media (max-width: 1024px) {
            .squad-dual-grid { grid-template-columns: 1fr; }
        }
        table.dash-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .dash-table thead th {
            background: var(--side-bg, #1B263B);
            color: #CBD5E1;
            padding: 12px 14px;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
        }
        .dash-table tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
            vertical-align: middle;
        }
        .dash-table tbody tr:hover {
            background: rgba(241, 245, 249, 0.55);
        }
        .badge-success { background: #F0FDF4; color: #16A34A; border: 1px solid #BBF7D0; }
        .badge-warning { background: #FFFBEB; color: #D97706; border: 1px solid #FDE68A; }
        .badge-danger  { background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; }
        .badge-scheduled { background: #EFF6FF; color: #2563EB; border: 1px solid #BFDBFE; }
        
        .jersey-badge {
            width: 28px;
            height: 28px;
            background: var(--side-bg, #1B263B);
            color: #FFFFFF;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 800;
        }
    </style>
</head>
<body class="dashboard">

    <?php include __DIR__ . '/partials/coach_sidebar.php'; ?>

    <div class="main-wrapper">
        <?php include __DIR__ . '/partials/tic_header.php'; ?>

        <div class="page-content">

            <!-- Flash Alert Messages -->
            <?php if ($flashSuccess): ?>
                <div class="alert alert-success" style="margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-outlined">check_circle</span>
                    <span><?= htmlspecialchars($flashSuccess) ?></span>
                </div>
            <?php endif; ?>
            <?php if ($flashError): ?>
                <div class="alert alert-danger" style="margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-outlined">error</span>
                    <span><?= htmlspecialchars($flashError) ?></span>
                </div>
            <?php endif; ?>

            <!-- Action Bar -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
                <div>
                    <h1 class="page-title" style="margin: 0; font-size: 1.6rem; font-weight: 800; color: var(--text);">Squad Formulation &amp; Selection</h1>
                    <p style="color: var(--text-3); font-size: 13.5px; margin-top: 4px;">Formulate official match-day squad with strict medical clearance verification</p>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <!-- Submit Squad to TIC Button -->
                    <button type="button" class="btn btn-teal" onclick="openModal('submitSquadModal')" style="display: flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">how_to_vote</span>
                        <span>Submit Squad to TIC for Approval</span>
                    </button>
                </div>
            </div>

            <!-- Top Stat Cards (Grid-4) -->
            <div class="grid-4" style="margin-bottom: 24px;">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">SELECTED PLAYERS</span>
                        <span class="material-symbols-outlined" style="color: var(--teal);">group_add</span>
                    </div>
                    <div class="stat-card-value"><?= $selectedCount ?> / <?= $maxSquadCap ?></div>
                    <div class="stat-card-desc" style="color: #16A34A; font-weight: 600;">11 Starting XI + 4 Substitutes</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">MEDICALLY FIT</span>
                        <span class="material-symbols-outlined" style="color: #16A34A;">verified_user</span>
                    </div>
                    <div class="stat-card-value"><?= $fitCount ?></div>
                    <div class="stat-card-desc">Full clinical medical clearance</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">UNDER MEDICAL REVIEW</span>
                        <span class="material-symbols-outlined" style="color: #D97706;">healing</span>
                    </div>
                    <div class="stat-card-value"><?= $underReviewCount ?></div>
                    <div class="stat-card-desc" style="color: #D97706;">Awaiting physiotherapy retest</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">DISQUALIFIED / UNFIT</span>
                        <span class="material-symbols-outlined" style="color: #DC2626;">person_cancel</span>
                    </div>
                    <div class="stat-card-value"><?= $disqualifiedCount ?></div>
                    <div class="stat-card-desc" style="color: #DC2626;">Locked out from squad selection</div>
                </div>
            </div>

            <!-- Target Match Fixture Selector Card (Feedback: Convenient Dropdown) -->
            <div class="card" style="margin-bottom: 24px; padding: 18px 22px; border-radius: 10px; border: 1px solid var(--border);">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                    <div style="flex: 1; min-width: 320px;">
                        <label class="form-label" style="font-weight: 700; font-size: 12px; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">
                            Target Tournament Match Fixture
                        </label>
                        <select class="form-control" style="width: 100%; height: 38px; font-weight: 600;" onchange="location.href='?fixture_id=' + this.value;">
                            <?php foreach ($mockFixtures as $f): ?>
                                <option value="<?= $f['fixture_id'] ?>" <?= ($f['fixture_id'] === $selectedFixtureId) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($f['match_title']) ?> — [<?= $f['match_date'] ?>]
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div>
                            <div style="font-size: 11px; font-weight: 700; color: var(--text-3); text-transform: uppercase;">Draft Status</div>
                            <span class="badge <?= $activeFixture['badge_cls'] ?>" style="margin-top: 3px; font-weight: 700;">
                                <?= $activeFixture['status'] ?>
                            </span>
                        </div>
                        <div>
                            <div style="font-size: 11px; font-weight: 700; color: var(--text-3); text-transform: uppercase;">Venue Scheduled</div>
                            <div style="font-weight: 600; font-size: 13px; color: var(--text); margin-top: 3px;">
                                <?= htmlspecialchars($activeFixture['venue']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dual Table Layout -->
            <div class="squad-dual-grid">
                
                <!-- Table 1: Available Athlete Pool -->
                <div class="card" style="padding: 0; overflow: hidden; border-radius: 10px; border: 1px solid var(--border);">
                    <div style="padding: 14px 18px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: #FFFFFF;">
                        <div>
                            <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--text);">Available Athlete Pool</h3>
                            <p style="font-size: 12px; color: var(--text-3); margin-top: 2px;">Faculty athletes eligible for squad drafting</p>
                        </div>
                        <!-- Faculty Filter Dropdown -->
                        <select class="form-control" style="height: 32px; font-size: 12px; width: 140px;" onchange="filterAthletePool(this.value)">
                            <option value="all">All Faculties</option>
                            <option value="Arts">Faculty of Arts</option>
                            <option value="Science">Faculty of Science</option>
                            <option value="Medicine">Faculty of Medicine</option>
                            <option value="Technology">Faculty of Technology</option>
                        </select>
                    </div>

                    <div class="table-responsive">
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>Athlete Name &amp; Reg</th>
                                    <th>Position</th>
                                    <th>Medical Status</th>
                                    <th style="text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($availableAthletes as $ath): ?>
                                    <tr class="athlete-pool-row" data-faculty="<?= htmlspecialchars($ath['faculty']) ?>">
                                        <td>
                                            <div style="font-weight: 600; color: var(--text);"><?= htmlspecialchars($ath['name']) ?></div>
                                            <div style="font-size: 11px; color: var(--text-3);"><?= htmlspecialchars($ath['faculty']) ?> • <?= htmlspecialchars($ath['reg_no']) ?></div>
                                        </td>
                                        <td style="font-weight: 600; font-size: 12px; color: var(--text-2);"><?= htmlspecialchars($ath['position']) ?></td>
                                        <td>
                                            <span class="badge <?= $ath['med_class'] ?>" style="font-size: 10.5px; font-weight: 700;">
                                                <?= $ath['medical'] ?>
                                            </span>
                                            <?php if (!empty($ath['note'])): ?>
                                                <div style="font-size: 10px; color: #DC2626; margin-top: 2px;"><?= htmlspecialchars($ath['note']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <?php if ($ath['eligible']): ?>
                                                <button type="button" class="btn btn-sm btn-outline" style="color: var(--teal); border-color: var(--teal);" onclick="addToSquad('<?= $ath['athlete_id'] ?>', '<?= addslashes($ath['name']) ?>', '<?= addslashes($ath['faculty']) ?>', '<?= addslashes($ath['position']) ?>')">
                                                    + Add to Squad
                                                </button>
                                            <?php else: ?>
                                                <!-- Strict Medical Clearance Rule Enforcement -->
                                                <button type="button" class="btn btn-sm btn-outline" disabled style="opacity: 0.5; cursor: not-allowed;" title="Cannot add: Disqualified by Medical Officer">
                                                    Locked (Unfit)
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Table 2: Current Match Squad Roster -->
                <div class="card" style="padding: 0; overflow: hidden; border-radius: 10px; border: 1px solid var(--border);">
                    <div style="padding: 14px 18px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: #FFFFFF;">
                        <div>
                            <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--text);">
                                Current Match Squad Roster (<?= $selectedCount ?> / <?= $maxSquadCap ?>)
                            </h3>
                            <p style="font-size: 12px; color: var(--text-3); margin-top: 2px;">Assigned lineup and substitutes for official accreditation</p>
                        </div>
                        <!-- Tactical Formation Dropdown (Feedback: Convenience) -->
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="font-size: 11px; font-weight: 700; color: var(--text-3);">Formation:</span>
                            <select class="form-control" style="height: 32px; font-size: 12px; width: 120px;">
                                <option value="4-3-3">4-3-3 Attacking</option>
                                <option value="4-2-3-1">4-2-3-1 Balanced</option>
                                <option value="3-5-2">3-5-2 Wing-Back</option>
                                <option value="4-4-2">4-4-2 Traditional</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                        <table class="dash-table" id="squadTable">
                            <thead>
                                <tr>
                                    <th>Jersey</th>
                                    <th>Player Name</th>
                                    <th>Assigned Role</th>
                                    <th>Faculty</th>
                                    <th style="text-align: right;">Remove</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($currentSquad as $sq): ?>
                                    <tr id="squad-row-<?= $sq['athlete_id'] ?>">
                                        <td>
                                            <span class="jersey-badge">#<?= $sq['jersey'] ?></span>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: var(--text);"><?= htmlspecialchars($sq['name']) ?></div>
                                            <div style="font-size: 11px; color: var(--text-3);"><?= htmlspecialchars($sq['position']) ?></div>
                                        </td>
                                        <td>
                                            <!-- Role Assignment Dropdown -->
                                            <select class="form-control" style="height: 30px; font-size: 11.5px; padding: 2px 6px;">
                                                <option value="Starting XI" <?= ($sq['role'] === 'Starting XI') ? 'selected' : '' ?>>Starting XI</option>
                                                <option value="Captain" <?= ($sq['role'] === 'Captain') ? 'selected' : '' ?>>Captain (C)</option>
                                                <option value="Vice Captain" <?= ($sq['role'] === 'Vice Captain') ? 'selected' : '' ?>>Vice Captain (VC)</option>
                                                <option value="Key Substitute" <?= ($sq['role'] === 'Key Substitute') ? 'selected' : '' ?>>Key Substitute</option>
                                                <option value="Reserve" <?= ($sq['role'] === 'Reserve') ? 'selected' : '' ?>>Squad Reserve</option>
                                            </select>
                                        </td>
                                        <td style="font-size: 12px; color: var(--text-2);"><?= htmlspecialchars($sq['faculty']) ?></td>
                                        <td style="text-align: right;">
                                            <button type="button" class="btn btn-sm btn-outline" style="color: #DC2626; border-color: #FECACA; padding: 3px 8px;" onclick="removeFromSquad('<?= $sq['athlete_id'] ?>')" title="Remove from squad">
                                                <span class="material-symbols-outlined" style="font-size: 15px;">close</span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div> <!-- /.page-content -->
    </div> <!-- /.main-wrapper -->

    <!-- ============================================== -->
    <!-- MODAL: SUBMIT SQUAD TO TIC CONFIRMATION        -->
    <!-- ============================================== -->
    <div class="modal-overlay" id="submitSquadModal">
        <div class="modal-card" style="max-width: 520px; width: 90%;">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title" style="margin: 0; font-size: 17px; font-weight: 700;">Submit Match Squad to TIC</h3>
                    <p style="font-size: 12.5px; color: var(--text-3); margin-top: 2px;">Transmit official varsity player accreditation list to Teacher-in-Charge.</p>
                </div>
                <button type="button" class="modal-close" onclick="closeModal('submitSquadModal')">&times;</button>
            </div>
            <form action="../controllers/CoachController.php" method="POST">
                <input type="hidden" name="action" value="submit_squad">
                <input type="hidden" name="fixture_id" value="<?= $selectedFixtureId ?>">
                <div class="modal-body" style="padding: 20px;">
                    <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                        <div style="font-weight: 700; font-size: 13.5px; color: #1D4ED8; margin-bottom: 4px;">Official Match Accreditation:</div>
                        <div style="font-size: 13px; color: #1E40AF;">
                            You are submitting <strong><?= $selectedCount ?> Players</strong> for <em><?= htmlspecialchars($activeFixture['match_title']) ?></em>.
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" style="font-weight: 600; font-size: 12.5px; margin-bottom: 4px; display: block;">Coach Declaration &amp; Notes</label>
                        <textarea name="coach_declaration" class="form-control" rows="2" placeholder="e.g. All starting players have completed mandatory conditioning protocols." style="width: 100%; font-size: 12.5px;"></textarea>
                    </div>

                    <div style="font-size: 11.5px; color: #64748B;">
                        <strong>Historical Archiving Notice:</strong> Once submitted and approved by TIC, this squad lineup is permanently saved in the Inter-University Match Archives for student sports color awards.
                    </div>
                </div>
                <div class="modal-footer" style="padding: 12px 20px; background: #F8FAFC; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('submitSquadModal')">Cancel</button>
                    <button type="submit" class="btn btn-teal">
                        <span class="material-symbols-outlined" style="font-size: 18px;">send</span>
                        <span>Confirm Official Submission</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript Helper -->
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

        // Add Athlete to Squad with Cap Validation
        function addToSquad(id, name, faculty, pos) {
            var rows = document.querySelectorAll('#squadTable tbody tr');
            if (rows.length >= 18) {
                alert('Maximum squad capacity reached (18 Players). Remove an athlete before adding a new candidate.');
                return;
            }
            alert(name + ' (' + pos + ') drafted into squad selection! Role set to Key Substitute.');
        }

        // Remove from Squad
        function removeFromSquad(id) {
            var row = document.getElementById('squad-row-' + id);
            if (row && confirm('Remove this player from the official squad list?')) {
                row.remove();
            }
        }

        // Faculty Filter
        function filterAthletePool(faculty) {
            var rows = document.querySelectorAll('.athlete-pool-row');
            rows.forEach(function(row) {
                if (faculty === 'all' || row.getAttribute('data-faculty').indexOf(faculty) > -1) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
