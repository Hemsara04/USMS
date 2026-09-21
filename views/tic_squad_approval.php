<?php
require_once '../config/auth_guard.php';
requireRole('TIC');

require_once '../models/Squad.php';
$squadModel         = new Squad();
$pendingSquadGroups = $squadModel->getPendingSquadGroups();
$upcomingMatches    = $squadModel->getUpcomingMatches();
$stats              = $squadModel->getStats();

$totalPendingPlayers = $stats['total_pending'];
$totalMatches        = $stats['total_matches'];

$pdo = Database::connect();
$allSports      = $pdo->query("SELECT sport_id, sport_name FROM sports ORDER BY sport_name ASC")->fetchAll();
$allFacilities  = $pdo->query("SELECT facility_id, facility_name FROM facilities ORDER BY facility_name ASC")->fetchAll();
$allAthletes    = $pdo->query("SELECT athlete_id, full_name, index_number, faculty FROM Athletic ORDER BY full_name ASC")->fetchAll();
$allFixtures    = $pdo->query("SELECT f.fixture_id, f.opponent, f.home_team, f.fixture_type, s.sport_name, f.fixture_date FROM fixtures f JOIN sports s ON f.sport_id = s.sport_id WHERE f.fixture_date >= CURDATE() ORDER BY f.fixture_date ASC")->fetchAll();

// Pre-load rosters for pending fixture groups to support the roster preview modal
$fixtureRosters = [];
foreach ($pendingSquadGroups as $grp) {
    $fixtureRosters[$grp['fixture_id']] = $squadModel->getFixtureRoster($grp['fixture_id']);
}

$pageTitle = 'Squad Approvals';
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
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

<?php include 'partials/tic_sidebar.php'; ?>

<div class="main-wrapper">
    <?php include 'partials/tic_header.php'; ?>

    <div class="page-content">

        <!-- Flash messages -->
        <?php if ($flashSuccess): ?>
            <div style="background: var(--ok-bg); color: var(--ok); border: 1px solid var(--ok-border); padding: 10px 16px; border-radius: var(--r); margin-bottom: 16px; font-weight: 600; font-size: 13.5px; display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-outlined" style="font-size: 20px;">check_circle</span>
                <span><?= htmlspecialchars($flashSuccess) ?></span>
            </div>
        <?php endif; ?>
        <?php if ($flashError): ?>
            <div style="background: var(--danger-bg); color: var(--danger); border: 1px solid var(--danger-border); padding: 10px 16px; border-radius: var(--r); margin-bottom: 16px; font-weight: 600; font-size: 13.5px; display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-outlined" style="font-size: 20px;">error</span>
                <span><?= htmlspecialchars($flashError) ?></span>
            </div>
        <?php endif; ?>

        <!-- Page Title -->
        <div class="page-title-row">
            <h2 class="page-title">Squad Approvals & Team Rosters</h2>
        </div>

        <!-- Stats -->
        <div class="grid-2">
            <div class="stat-card">
                <div>
                    <div class="stat-label">Pending Squad Reviews</div>
                    <div class="stat-value"><?= count($pendingSquadGroups) ?> <span style="font-size: 14px; font-weight: 500; color: var(--text-3);">(<?= $totalPendingPlayers ?> athletes)</span></div>
                </div>
                <div class="stat-icon">
                    <span class="material-symbols-outlined">assignment_turned_in</span>
                </div>
            </div>
            <div class="stat-card">
                <div>
                    <div class="stat-label">Upcoming Fixtures</div>
                    <div class="stat-value"><?= $totalMatches ?></div>
                </div>
                <div class="stat-icon" style="color:#3B82F6;">
                    <span class="material-symbols-outlined">calendar_month</span>
                </div>
            </div>
        </div>

        <!-- Toolbar with tabs + actions -->
        <div class="toolbar">
            <div class="tab-group">
                <button class="tab-btn active" onclick="switchTab(this,'squadTabs','tab-requests')">Squad Requests</button>
                <button class="tab-btn" onclick="switchTab(this,'squadTabs','tab-matches')">Upcoming Matches</button>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button class="btn btn-outline" onclick="openModal('nominateSquadModal')">
                    <span class="material-symbols-outlined" style="font-size:16px">group_add</span> Nominate Team Squad
                </button>
                <button class="btn btn-primary" onclick="openModal('addFixtureModal')">
                    <span class="material-symbols-outlined" style="font-size:16px">add_circle</span> Add Match Fixture
                </button>
            </div>
        </div>

        <!-- TAB 1: Squad Requests (Grouped by Team / Fixture) -->
        <div class="tab-content active" id="tab-requests" data-tab-group="squadTabs">
            <div class="table-card">
                <div class="table-toolbar">
                    <span class="table-title">Team Squad Approval Queue (By Match Fixture)</span>
                    <div class="table-filters">
                        <div class="search-box">
                            <span class="material-symbols-outlined">search</span>
                            <input type="text" class="search-input" id="searchSquad" placeholder="Search fixtures…">
                        </div>
                    </div>
                </div>
                <div class="table-scroll">
                    <table class="data-table" id="squadTable">
                        <thead>
                            <tr>
                                <th style="min-width: 220px;">Match Event & Venue</th>
                                <th style="min-width: 140px;">Sport / Type</th>
                                <th style="min-width: 135px;">Match Date & Time</th>
                                <th style="min-width: 155px;">Nominated Squad</th>
                                <th style="min-width: 165px;">Roster Details</th>
                                <th class="text-right" style="min-width: 245px; text-align: right;">Team Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendingSquadGroups)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--text-3); padding: 36px;">
                                        <span class="material-symbols-outlined" style="font-size: 32px; display: block; margin-bottom: 6px; color:#22C55E;">check_circle</span>
                                        No pending team squads requiring approval at this time.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pendingSquadGroups as $sq): ?>
                                <tr>
                                    <td>
                                        <span class="cell-main" style="font-size: 13.5px; font-weight: 700;">
                                            <?php if (($sq['fixture_type'] ?? '') === 'Inter-Faculty'): ?>
                                                <?= htmlspecialchars($sq['home_team'] ?? 'Faculty') ?> vs <?= htmlspecialchars($sq['opponent']) ?>
                                            <?php else: ?>
                                                UOC vs <?= htmlspecialchars($sq['opponent']) ?>
                                            <?php endif; ?>
                                        </span>
                                        <span class="cell-sub" style="font-size: 12.5px; margin-top: 3px;"><?= htmlspecialchars($sq['facility_name']) ?></span>
                                    </td>
                                    <td>
                                        <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                            <span class="badge badge-info" style="font-size: 11px; padding: 2.5px 8px; border-radius: 4px; font-weight: 700;"><?= htmlspecialchars($sq['sport_name']) ?></span>
                                            <span class="badge <?= ($sq['fixture_type'] === 'Inter-Faculty') ? 'badge-ok' : 'badge-subtle' ?>" style="font-size: 10px; padding: 2px 7px; border-radius: 4px; font-weight: 600; text-transform: uppercase;">
                                                <?= htmlspecialchars($sq['fixture_type']) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="cell-main" style="font-size: 13px; font-weight: 600;"><?= date('M d, Y', strtotime($sq['fixture_date'])) ?></span>
                                        <span class="cell-sub" style="font-size: 12px; margin-top: 2px;"><?= date('h:i A', strtotime($sq['fixture_time'])) ?></span>
                                    </td>
                                    <td>
                                        <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                            <span style="font-weight: 700; font-size: 13.5px; color: var(--text);"><?= $sq['total_players'] ?> Athletes</span>
                                            <span class="badge badge-warn" style="font-size: 10.5px; padding: 2px 8px; border-radius: 4px; font-weight: 700;">
                                                <?= $sq['pending_players'] ?> Pending Approval
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-outline btn-sm" onclick="showRosterModal(<?= $sq['fixture_id'] ?>, '<?= htmlspecialchars(addslashes($sq['opponent'])) ?>', '<?= htmlspecialchars(addslashes($sq['sport_name'])) ?>')">
                                            <span class="material-symbols-outlined" style="font-size:16px;">list_alt</span> View Squad Roster
                                        </button>
                                    </td>
                                    <td class="text-right">
                                        <div class="cell-actions" style="display: flex; gap: 8px; justify-content: flex-end; align-items: center; white-space: nowrap;">
                                            <form action="../controllers/TicController.php?action=approve_fixture_squad" method="POST" style="margin: 0; display: inline;">
                                                <input type="hidden" name="fixture_id" value="<?= $sq['fixture_id'] ?>">
                                                <button type="submit" class="btn btn-success btn-sm" onclick="return USMS.confirmForm(event, this, 'Approve the entire nominated squad for this match?')">
                                                    <span class="material-symbols-outlined" style="font-size:15px;">check</span> Approve Squad
                                                </button>
                                            </form>
                                            <form action="../controllers/TicController.php?action=reject_fixture_squad" method="POST" style="margin: 0; display: inline;">
                                                <input type="hidden" name="fixture_id" value="<?= $sq['fixture_id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return USMS.confirmForm(event, this, 'Reject the pending squad for this match?')">
                                                    <span class="material-symbols-outlined" style="font-size:15px;">close</span> Reject Squad
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
                    <span>Showing <?= count($pendingSquadGroups) ?> fixtures</span>
                    <div class="page-btns"></div>
                </div>
            </div>
        </div>

        <!-- TAB 2: Upcoming Matches -->
        <div class="tab-content" id="tab-matches" data-tab-group="squadTabs">
            <div class="table-card">
                <div class="table-toolbar">
                    <span class="table-title">Upcoming Fixtures & Squad Readiness</span>
                </div>
                <div class="table-scroll">
                    <table class="data-table" id="matchesTable">
                        <thead>
                            <tr>
                                <th style="min-width: 220px;">Match Opponent</th>
                                <th style="min-width: 130px;">Sport</th>
                                <th style="min-width: 170px;">Venue</th>
                                <th style="min-width: 135px;">Date & Time</th>
                                <th style="min-width: 170px;">Squad Status</th>
                                <th class="text-right" style="min-width: 160px; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($upcomingMatches)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--text-3); padding: 30px;">No upcoming fixtures scheduled.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($upcomingMatches as $m): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                            <span class="cell-main" style="font-size: 13.5px; font-weight: 700;">
                                                <?php if (($m['fixture_type'] ?? '') === 'Inter-Faculty'): ?>
                                                    <?= htmlspecialchars($m['home_team'] ?? 'Faculty') ?> vs <?= htmlspecialchars($m['opponent']) ?>
                                                <?php else: ?>
                                                    UOC vs <?= htmlspecialchars($m['opponent']) ?>
                                                <?php endif; ?>
                                            </span>
                                            <span class="badge <?= ($m['fixture_type'] === 'Inter-Faculty') ? 'badge-ok' : 'badge-subtle' ?>" style="font-size: 10px; padding: 2px 7px; border-radius: 4px; font-weight: 600; text-transform: uppercase;">
                                                <?= htmlspecialchars($m['fixture_type']) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-info" style="font-size: 11px; padding: 2.5px 8px; border-radius: 4px; font-weight: 700;"><?= htmlspecialchars($m['sport_name']) ?></span></td>
                                    <td><span class="cell-main" style="font-size: 13px;"><?= htmlspecialchars($m['facility_name']) ?></span></td>
                                    <td>
                                        <span class="cell-main" style="font-size: 13px; font-weight: 600;"><?= date('M d, Y', strtotime($m['fixture_date'])) ?></span>
                                        <span class="cell-sub" style="font-size: 12px; margin-top: 2px;"><?= date('h:i A', strtotime($m['fixture_time'])) ?></span>
                                    </td>
                                    <td>
                                        <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                            <span style="font-weight: 600; font-size: 13px; color: var(--text);"><?= $m['approved_players'] ?> / <?= $m['total_players'] ?> Approved</span>
                                            <?php if ($m['pending_players'] > 0): ?>
                                                <span class="badge badge-warn" style="font-size: 10px; padding: 2px 7px;"><?= $m['pending_players'] ?> Pending</span>
                                            <?php elseif ($m['total_players'] > 0): ?>
                                                <span class="badge badge-ok" style="font-size: 10px; padding: 2px 7px;">Squad Ready</span>
                                            <?php else: ?>
                                                <span class="badge badge-subtle" style="font-size: 10px; padding: 2px 7px;">No Squad Nominated</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <div class="cell-actions" style="display: flex; gap: 8px; justify-content: flex-end; align-items: center; white-space: nowrap;">
                                            <?php if ($m['pending_players'] > 0): ?>
                                                <form action="../controllers/TicController.php?action=approve_fixture_squad" method="POST" style="margin: 0; display: inline;">
                                                    <input type="hidden" name="fixture_id" value="<?= $m['fixture_id'] ?>">
                                                    <button type="submit" class="btn btn-primary btn-sm" onclick="return USMS.confirmForm(event, this, 'Approve all pending players for this match?')">
                                                        <span class="material-symbols-outlined" style="font-size: 15px;">check_circle</span> Approve All
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="badge badge-ok" style="font-size: 10px; padding: 3px 8px;">Verified</span>
                                            <?php endif; ?>
                                            <form action="../controllers/TicController.php?action=delete_fixture" method="POST" style="margin: 0; display: inline;" onsubmit="return USMS.confirmForm(event, this, 'Are you sure you want to cancel and delete this match fixture and any squad assigned?');">
                                                <input type="hidden" name="fixture_id" value="<?= $m['fixture_id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete Fixture" style="padding: 0 8px;">
                                                    <span class="material-symbols-outlined" style="font-size: 15px;">delete</span>
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
                    <span>Showing <?= count($upcomingMatches) ?> fixtures</span>
                    <div class="page-btns"></div>
                </div>
            </div>
        </div>

    </div><!-- .page-content -->
</div><!-- .main-wrapper -->

<!-- ===== MODAL 1: ADD FIXTURE MODAL ===== -->
<div class="modal-overlay" id="addFixtureModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Schedule New Match Fixture</span>
            <button class="modal-close" onclick="closeModal('addFixtureModal')">&times;</button>
        </div>
        <form action="../controllers/TicController.php?action=add_fixture" method="POST">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Tournament / Competition Category</label>
                    <select class="form-select" name="fixture_type" id="fixtureTypeSelect" onchange="toggleFixtureTypeFields(this.value)" required>
                        <option value="Inter-University" selected>Inter-University Championship (SLUG / Inter-Varsity)</option>
                        <option value="Inter-Faculty">Inter-Faculty Championship (Internal Faculty vs Faculty)</option>
                        <option value="Internal">Internal University Practice / Trial Match</option>
                    </select>
                </div>

                <!-- Inter-University Fields -->
                <div id="interUniFields">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Representing University</label>
                            <input type="text" class="form-input" name="home_team" value="University of Colombo (Varsity Squad)" readonly style="background:#F1F5F9; color:var(--text-2);">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Opponent University</label>
                            <input type="text" class="form-input" name="opponent" id="opponentUniInput" placeholder="e.g. University of Peradeniya" required>
                        </div>
                    </div>
                </div>

                <!-- Inter-Faculty Fields (Toggled when Inter-Faculty is selected) -->
                <div id="interFacultyFields" style="display:none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Home Faculty Team</label>
                            <select class="form-select" name="home_faculty" id="homeFacultySelect">
                                <option value="Faculty of Science" selected>Faculty of Science</option>
                                <option value="Faculty of Arts">Faculty of Arts</option>
                                <option value="Faculty of Medicine">Faculty of Medicine</option>
                                <option value="Faculty of Management">Faculty of Management & Finance</option>
                                <option value="Faculty of Technology">Faculty of Technology</option>
                                <option value="Faculty of Law">Faculty of Law</option>
                                <option value="UCSC">UCSC (School of Computing)</option>
                                <option value="Faculty of Education">Faculty of Education</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Opponent Faculty Team</label>
                            <select class="form-select" name="opponent_faculty" id="opponentFacultySelect">
                                <option value="Faculty of Arts" selected>Faculty of Arts</option>
                                <option value="Faculty of Science">Faculty of Science</option>
                                <option value="Faculty of Medicine">Faculty of Medicine</option>
                                <option value="Faculty of Management">Faculty of Management & Finance</option>
                                <option value="Faculty of Technology">Faculty of Technology</option>
                                <option value="Faculty of Law">Faculty of Law</option>
                                <option value="UCSC">UCSC (School of Computing)</option>
                                <option value="Faculty of Education">Faculty of Education</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Sport</label>
                        <select class="form-select searchable" name="sport_id" data-placeholder="Select sport…" required>
                            <option value="" disabled selected>Select sport…</option>
                            <?php foreach ($allSports as $sp): ?>
                                <option value="<?= $sp['sport_id'] ?>"><?= htmlspecialchars($sp['sport_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Match Venue / Facility</label>
                        <select class="form-select searchable" name="facility_id" data-placeholder="Select venue…" required>
                            <option value="" disabled selected>Select venue…</option>
                            <?php foreach ($allFacilities as $fac): ?>
                                <option value="<?= $fac['facility_id'] ?>"><?= htmlspecialchars($fac['facility_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Fixture Date</label>
                        <input type="date" class="form-input" name="fixture_date" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Match Time</label>
                        <input type="time" class="form-input" name="fixture_time" value="15:30" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addFixtureModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Schedule Fixture</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL 2: NOMINATE TEAM SQUAD MODAL ===== -->
<div class="modal-overlay" id="nominateSquadModal">
    <div class="modal" style="max-width: 620px;">
        <div class="modal-header">
            <span class="modal-title">Nominate Team Squad to Fixture</span>
            <button class="modal-close" onclick="closeModal('nominateSquadModal')">&times;</button>
        </div>
        <form action="../controllers/TicController.php?action=nominate_squad" method="POST">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Select Match Fixture</label>
                    <select class="form-select searchable" name="fixture_id" data-placeholder="Choose upcoming match…" required>
                        <option value="" disabled selected>Choose upcoming match…</option>
                        <?php foreach ($allFixtures as $fx): ?>
                            <option value="<?= $fx['fixture_id'] ?>">
                                <?= htmlspecialchars($fx['sport_name']) ?> — <?= (($fx['fixture_type'] ?? '') === 'Inter-Faculty') ? htmlspecialchars(($fx['home_team'] ?? 'Faculty') . ' vs ' . $fx['opponent']) : ('UOC vs ' . htmlspecialchars($fx['opponent'])) ?> (<?= date('M d', strtotime($fx['fixture_date'])) ?>) [<?= htmlspecialchars($fx['fixture_type'] ?? '') ?>]
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label class="form-label" style="margin-bottom:0;">Select Athletes for Squad</label>
                        <div style="display:flex; gap:6px;">
                            <button type="button" class="btn btn-outline btn-sm" style="padding:2px 10px; font-size:11.5px;" onclick="selectAllNominate(true)">Select All</button>
                            <button type="button" class="btn btn-outline btn-sm" style="padding:2px 10px; font-size:11.5px;" onclick="selectAllNominate(false)">Clear</button>
                        </div>
                    </div>

                    <!-- Faculty Quick-Filter Pills for Inter-Faculty/Inter-University Selection -->
                    <div style="display:flex; gap:6px; margin-bottom:8px; overflow-x:auto; padding-bottom:4px;" id="squadFacultyFilterChips">
                        <button type="button" class="btn btn-primary btn-sm fac-pill-btn" onclick="filterByFaculty('')">All</button>
                        <button type="button" class="btn btn-outline btn-sm fac-pill-btn" onclick="filterByFaculty('Science')">Science</button>
                        <button type="button" class="btn btn-outline btn-sm fac-pill-btn" onclick="filterByFaculty('Arts')">Arts</button>
                        <button type="button" class="btn btn-outline btn-sm fac-pill-btn" onclick="filterByFaculty('Management')">Management</button>
                        <button type="button" class="btn btn-outline btn-sm fac-pill-btn" onclick="filterByFaculty('Medicine')">Medicine</button>
                        <button type="button" class="btn btn-outline btn-sm fac-pill-btn" onclick="filterByFaculty('Technology')">Technology</button>
                        <button type="button" class="btn btn-outline btn-sm fac-pill-btn" onclick="filterByFaculty('Computing')">UCSC</button>
                    </div>

                    <!-- Live search filter box for squad modal -->
                    <div style="position:relative; margin-bottom:8px;">
                        <input type="text" class="form-input" id="searchNominateAth" placeholder="Search athlete by name, index, or faculty…" oninput="filterNominateList(this.value)" style="padding-left:34px; font-size:13px;">
                        <span class="material-symbols-outlined" style="position:absolute; left:8px; top:7px; font-size:18px; color:var(--text-3);">search</span>
                    </div>

                    <div id="nominateAthList" style="max-height: 230px; overflow-y: auto; border: 1px solid var(--border); border-radius: var(--r); padding: 8px 12px; background: var(--card);">
                        <?php foreach ($allAthletes as $ath): ?>
                            <label class="nominate-item" data-faculty="<?= htmlspecialchars(strtolower($ath['faculty'])) ?>" style="display:flex; align-items:center; gap:10px; padding:6px 0; border-bottom:1px solid #f1f5f9; cursor:pointer; font-size:13.5px;">
                                <input type="checkbox" class="nominate-cb" name="athlete_ids[]" value="<?= $ath['athlete_id'] ?>" style="width:16px; height:16px; accent-color: var(--accent-dark);" onchange="updateNominateCount()">
                                <div>
                                    <span class="ath-name" style="font-weight:600; color:var(--text);"><?= htmlspecialchars($ath['full_name']) ?></span>
                                    <span class="ath-meta" style="font-size:12px; color:var(--text-3); margin-left:6px;"><?= htmlspecialchars($ath['index_number']) ?> (<?= htmlspecialchars($ath['faculty']) ?>)</span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div style="font-size:11.5px; color:var(--text-3); margin-top:5px;" id="nominateCountBadge">0 athletes selected for squad</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('nominateSquadModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Nominate Selected Squad</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL 3: VIEW SQUAD ROSTER MODAL ===== -->
<div class="modal-overlay" id="squadRosterModal">
    <div class="modal" style="max-width: 820px; width: 95%;">
        <div class="modal-header">
            <span class="modal-title" id="rosterModalTitle">Match Squad Roster</span>
            <button class="modal-close" onclick="closeModal('squadRosterModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div style="margin-bottom: 12px; font-size: 13.5px; color: var(--text-2);" id="rosterSubtitle">
                Review athlete eligibility and clearances for this fixture.
            </div>
            <div class="table-scroll" style="max-height: 380px; overflow-y: auto; border: 1px solid var(--border); border-radius: var(--r);">
                <table class="data-table" style="font-size: 13px; width: 100%;">
                    <thead>
                        <tr>
                            <th style="min-width: 170px;">Athlete Name</th>
                            <th style="min-width: 120px;">Reg / Index</th>
                            <th style="min-width: 190px;">Faculty</th>
                            <th style="min-width: 100px; text-align: center;">Status</th>
                            <th class="text-right" style="min-width: 170px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="rosterTableBody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" onclick="closeModal('squadRosterModal')">Done</button>
        </div>
    </div>
</div>

<script src="../assets/js/dashboard.js"></script>
<script>
var fixtureRostersData = <?= json_encode($fixtureRosters) ?>;

function showRosterModal(fixtureId, opponent, sportName) {
    document.getElementById('rosterModalTitle').textContent = sportName + ' Squad vs ' + opponent;
    var tbody = document.getElementById('rosterTableBody');
    tbody.replaceChildren();

    var players = fixtureRostersData[fixtureId] || [];
    if (players.length === 0) {
        var tr = document.createElement('tr');
        var td = document.createElement('td');
        td.colSpan = 5;
        td.style.textAlign = 'center';
        td.style.padding = '20px';
        td.style.color = 'var(--text-3)';
        td.textContent = 'No athletes nominated in this squad roster yet.';
        tr.appendChild(td);
        tbody.appendChild(tr);
    } else {
        players.forEach(function(p) {
            var tr = document.createElement('tr');

            var tdName = document.createElement('td');
            tdName.innerHTML = '<span class="cell-main" style="font-weight:600; font-size:13.5px;">' + escapeHtml(p.athlete_name) + '</span>';

            var tdIndex = document.createElement('td');
            tdIndex.innerHTML = '<span class="cell-mono" style="font-size:12.5px; white-space:nowrap;">' + escapeHtml(p.index_number) + '</span>';

            var tdFac = document.createElement('td');
            tdFac.innerHTML = '<span style="white-space:nowrap; color:var(--text-2); font-size:12.5px; font-weight:500;">' + escapeHtml(p.faculty) + '</span>';

            var tdStatus = document.createElement('td');
            tdStatus.style.textAlign = 'center';
            var badgeClass = p.tic_approval_status === 'Approved' ? 'badge-ok' : (p.tic_approval_status === 'Rejected' ? 'badge-danger' : 'badge-warn');
            tdStatus.innerHTML = '<span class="badge ' + badgeClass + '" style="font-size:10.5px; padding:2.5px 8px;">' + escapeHtml(p.tic_approval_status) + '</span>';

            var tdAction = document.createElement('td');
            tdAction.className = 'text-right';
            tdAction.style.textAlign = 'right';
            if (p.tic_approval_status === 'Pending') {
                tdAction.innerHTML = '<div style="display:inline-flex; gap:6px; justify-content:flex-end; align-items:center;">' +
                    '<form action="../controllers/TicController.php?action=approve_squad" method="POST" style="margin:0; display:inline;">' +
                    '<input type="hidden" name="squad_id" value="' + p.squad_id + '">' +
                    '<button type="submit" class="btn btn-success btn-sm" style="height:28px; padding:0 10px; font-size:11.5px; border-radius:5px;" onclick="return USMS.confirmForm(event, this, \'Approve ' + escapeHtml(p.athlete_name) + ' for this match squad?\')">' +
                    '<span class="material-symbols-outlined" style="font-size:14px;">check</span> Approve</button>' +
                    '</form>' +
                    '<form action="../controllers/TicController.php?action=reject_squad" method="POST" style="margin:0; display:inline;">' +
                    '<input type="hidden" name="squad_id" value="' + p.squad_id + '">' +
                    '<button type="submit" class="btn btn-danger btn-sm" style="height:28px; padding:0 10px; font-size:11.5px; border-radius:5px;" onclick="return USMS.confirmForm(event, this, \'Exclude ' + escapeHtml(p.athlete_name) + ' from this match squad?\')">' +
                    '<span class="material-symbols-outlined" style="font-size:14px;">close</span> Exclude</button>' +
                    '</form>' +
                    '</div>';
            } else {
                tdAction.innerHTML = '<span style="font-size:12px; font-weight:600; color:var(--text-3);">' + p.tic_approval_status + '</span>';
            }

            tr.appendChild(tdName);
            tr.appendChild(tdIndex);
            tr.appendChild(tdFac);
            tr.appendChild(tdStatus);
            tr.appendChild(tdAction);
            tbody.appendChild(tr);
        });
    }
    openModal('squadRosterModal');
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function toggleFixtureTypeFields(type) {
    var uniFields = document.getElementById('interUniFields');
    var facFields = document.getElementById('interFacultyFields');
    var oppInput = document.getElementById('opponentUniInput');

    if (type === 'Inter-Faculty') {
        if (uniFields) uniFields.style.display = 'none';
        if (facFields) facFields.style.display = 'block';
        if (oppInput) oppInput.removeAttribute('required');
    } else {
        if (uniFields) uniFields.style.display = 'block';
        if (facFields) facFields.style.display = 'none';
        if (oppInput) oppInput.setAttribute('required', 'required');
    }
}

var currentFacultyFilter = '';
function filterByFaculty(faculty) {
    currentFacultyFilter = faculty.toLowerCase();
    document.querySelectorAll('.fac-pill-btn').forEach(function(b) {
        var match = (faculty === '' && b.textContent === 'All') || (faculty !== '' && b.textContent.toLowerCase().includes(currentFacultyFilter));
        if (match) {
            b.className = 'btn btn-primary btn-sm fac-pill-btn';
        } else {
            b.className = 'btn btn-outline btn-sm fac-pill-btn';
        }
    });
    var searchInput = document.getElementById('searchNominateAth');
    filterNominateList(searchInput ? searchInput.value : '');
}

function filterNominateList(query) {
    var q = query.toLowerCase().trim();
    var items = document.querySelectorAll('#nominateAthList .nominate-item');
    items.forEach(function(item) {
        var text = item.textContent.toLowerCase();
        var fac = item.dataset.faculty || '';
        var matchText = (q === '' || text.indexOf(q) > -1);
        var matchFac = (currentFacultyFilter === '' || fac.indexOf(currentFacultyFilter) > -1);
        if (matchText && matchFac) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

function selectAllNominate(check) {
    var cbs = document.querySelectorAll('#nominateAthList .nominate-item:not([style*="display: none"]) .nominate-cb');
    cbs.forEach(function(cb) {
        cb.checked = check;
    });
    updateNominateCount();
}

function updateNominateCount() {
    var checked = document.querySelectorAll('#nominateAthList .nominate-cb:checked').length;
    var badge = document.getElementById('nominateCountBadge');
    if (badge) {
        badge.textContent = checked + ' athlete' + (checked === 1 ? '' : 's') + ' selected for squad';
    }
}

setupTablePagination('squadTable', 8, 'searchSquad');
setupTablePagination('matchesTable', 8);
</script>
</body>
</html>

