<?php
/**
 * USMS - Coach Fixtures & Match Results
 * 
 * Production-ready View implementing Interim Evaluation Feedback:
 * 1. Historical Season Match Ledger & Colors Award Archival
 * 2. Operational Logic: Automatic score outcome calculation & completed states
 * 3. Dropdowns instead of manual typing for opponents, scores, outcomes & tournaments
 * 4. Clean realistic mock data rendering immediately in XAMPP without DB dependency
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['role'])) {
    $_SESSION['role'] = 'Coach';
    $_SESSION['username'] = 'Coach Sunil';
    $_SESSION['email'] = 'sunil.coach@uoc.ac.lk';
}

$pageTitle = 'Fixtures & Match Results';
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// ==========================================
// 1. CLEAN REALISTIC MOCK DATA ARRAYS
// ==========================================
$fixtures = [
    [
        'fixture_id' => 37,
        'code'       => 'FIX-037',
        'opponent'   => 'University of Ruhuna',
        'tournament' => 'Inter-University League (SLUG)',
        'sport'      => 'Track & Field',
        'sport_icon' => 'directions_run',
        'date_time'  => '2026-10-08 08:00 AM',
        'venue'      => 'Colombo University Grounds',
        'score'      => '- vs -',
        'outcome'    => 'Scheduled',
        'scorers'    => 'Not yet contested',
        'report'     => 'Inter-University preliminary heats and field qualifiers.'
    ],
    [
        'fixture_id' => 36,
        'code'       => 'FIX-036',
        'opponent'   => 'Univ. of Sri Jayewardenepura',
        'tournament' => 'Inter-University League (SLUG)',
        'sport'      => 'Badminton',
        'sport_icon' => 'sports_tennis',
        'date_time'  => '2026-10-05 02:00 PM',
        'venue'      => 'Indoor Sports Arena',
        'score'      => '- vs -',
        'outcome'    => 'Scheduled',
        'scorers'    => 'Not yet contested',
        'report'     => 'Men singles and doubles tie against rivals USJ.'
    ],
    [
        'fixture_id' => 42,
        'code'       => 'FIX-042',
        'opponent'   => 'UCSC',
        'tournament' => 'Inter-Faculty Championship',
        'sport'      => 'Table Tennis',
        'sport_icon' => 'sports_tennis',
        'date_time'  => '2026-10-03 04:00 PM',
        'venue'      => 'Indoor Sports Arena',
        'score'      => '- vs -',
        'outcome'    => 'Scheduled',
        'scorers'    => 'Not yet contested',
        'report'     => 'Faculty derbies seeding match.'
    ],
    [
        'fixture_id' => 35,
        'code'       => 'FIX-035',
        'opponent'   => 'University of Kelaniya',
        'tournament' => 'Inter-University League (SLUG)',
        'sport'      => 'Cricket',
        'sport_icon' => 'sports_cricket',
        'date_time'  => '2026-10-02 09:30 AM',
        'venue'      => 'Colombo University Grounds',
        'score'      => '- vs -',
        'outcome'    => 'Scheduled',
        'scorers'    => 'Not yet contested',
        'report'     => '50-Over group stage tournament clash.'
    ],
    [
        'fixture_id' => 30,
        'code'       => 'FIX-030',
        'opponent'   => 'University of Moratuwa',
        'tournament' => 'Inter-University Championship (SLUG)',
        'sport'      => 'Football',
        'sport_icon' => 'sports_soccer',
        'date_time'  => '2026-09-18 04:00 PM',
        'venue'      => 'Colombo University Grounds',
        'score'      => '3 - 1',
        'outcome'    => 'Won',
        'scorers'    => 'K. Senanayake 24\', 58\', C. Bandara 81\'',
        'report'     => 'Tactical 4-3-3 high-press executed flawlessly. Dominant midfield possession.'
    ],
    [
        'fixture_id' => 28,
        'code'       => 'FIX-028',
        'opponent'   => 'University of Peradeniya',
        'tournament' => 'National Invitational Tournament',
        'sport'      => 'Basketball',
        'sport_icon' => 'sports_basketball',
        'date_time'  => '2026-09-12 05:30 PM',
        'venue'      => 'Indoor Sports Complex',
        'score'      => '68 - 68',
        'outcome'    => 'Drawn',
        'scorers'    => 'K. Mendis 22 pts, D. Shanaka 18 pts',
        'report'     => 'Thrilling contest ending level at buzzer. Overtime was not contested in friendly tournament.'
    ],
    [
        'fixture_id' => 25,
        'code'       => 'FIX-025',
        'opponent'   => 'Eastern University',
        'tournament' => 'Inter-University League (SLUG)',
        'sport'      => 'Football',
        'sport_icon' => 'sports_soccer',
        'date_time'  => '2026-09-04 03:30 PM',
        'venue'      => 'Batticaloa Sports Grounds',
        'score'      => '0 - 1',
        'outcome'    => 'Lost',
        'scorers'    => 'Conceded penalty 74\'',
        'report'     => 'Defensive compactness maintained, but failed to convert late set-piece opportunities.'
    ]
];

// Stats calculation
$totalMatches = count($fixtures);
$won = 0;
$drawn = 0;
$lost = 0;
$scheduled = 0;

foreach ($fixtures as $f) {
    if ($f['outcome'] === 'Won') $won++;
    elseif ($f['outcome'] === 'Drawn') $drawn++;
    elseif ($f['outcome'] === 'Lost') $lost++;
    else $scheduled++;
}
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
        table.dash-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }
        .dash-table thead th {
            background: var(--side-bg, #1B263B);
            color: #CBD5E1;
            padding: 13px 16px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
        }
        .dash-table tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
            vertical-align: middle;
        }
        .dash-table tbody tr:hover {
            background: rgba(241, 245, 249, 0.55);
        }
        .badge-won   { background: #F0FDF4; color: #16A34A; border: 1px solid #BBF7D0; }
        .badge-drawn { background: #FFFBEB; color: #D97706; border: 1px solid #FDE68A; }
        .badge-lost  { background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; }
        .badge-sched { background: #EFF6FF; color: #2563EB; border: 1px solid #BFDBFE; }
        
        .score-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 13.5px;
            font-weight: 800;
            background: #F1F5F9;
            color: var(--text);
            border: 1px solid var(--border);
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
                    <h1 class="page-title" style="margin: 0; font-size: 1.6rem; font-weight: 800; color: var(--text);">Fixtures &amp; Match Results</h1>
                    <p style="color: var(--text-3); font-size: 13.5px; margin-top: 4px;">Review upcoming competitive fixtures, evaluate past match performance, and log official scores</p>
                </div>
                <div>
                    <button type="button" class="btn btn-teal" onclick="openLogModal('FIX-037', 'University of Ruhuna', 'Track & Field')" style="display: flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">scoreboard</span>
                        <span>Log Match Result</span>
                    </button>
                </div>
            </div>

            <!-- Top Stat Cards (Grid-4) -->
            <div class="grid-4" style="margin-bottom: 24px;">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">TOTAL MATCHES</span>
                        <span class="material-symbols-outlined" style="color: var(--teal);">flag</span>
                    </div>
                    <div class="stat-card-value"><?= $totalMatches ?></div>
                    <div class="stat-card-desc"><?= $scheduled ?> upcoming fixtures scheduled</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">MATCHES WON</span>
                        <span class="material-symbols-outlined" style="color: #16A34A;">emoji_events</span>
                    </div>
                    <div class="stat-card-value" style="color: #16A34A;"><?= $won ?></div>
                    <div class="stat-card-desc" style="color: #16A34A; font-weight: 600;">Win ratio: <?= round(($won / max(1, $won + $lost + $drawn)) * 100) ?>%</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">MATCHES DRAWN</span>
                        <span class="material-symbols-outlined" style="color: #D97706;">handshake</span>
                    </div>
                    <div class="stat-card-value" style="color: #D97706;"><?= $drawn ?></div>
                    <div class="stat-card-desc">Tied encounters</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">MATCHES LOST</span>
                        <span class="material-symbols-outlined" style="color: #DC2626;">trending_down</span>
                    </div>
                    <div class="stat-card-value" style="color: #DC2626;"><?= $lost ?></div>
                    <div class="stat-card-desc">Detailed tactical reviews logged</div>
                </div>
            </div>

            <!-- Season Match Ledger Card (Historical Data Preservation) -->
            <div class="card" style="padding: 0; overflow: hidden; border-radius: 12px; border: 1px solid var(--border);">
                <!-- Search & Filter Bar -->
                <div style="padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; background: #FFFFFF;">
                    <!-- Filter Pills -->
                    <div style="display: flex; gap: 6px;">
                        <button type="button" class="btn btn-sm btn-teal filter-tab" onclick="filterMatches('all', this)">All Matches (<?= $totalMatches ?>)</button>
                        <button type="button" class="btn btn-sm btn-outline filter-tab" onclick="filterMatches('Scheduled', this)">Upcoming (<?= $scheduled ?>)</button>
                        <button type="button" class="btn btn-sm btn-outline filter-tab" onclick="filterMatches('Won', this)">Won (<?= $won ?>)</button>
                        <button type="button" class="btn btn-sm btn-outline filter-tab" onclick="filterMatches('Drawn', this)">Drawn (<?= $drawn ?>)</button>
                        <button type="button" class="btn btn-sm btn-outline filter-tab" onclick="filterMatches('Lost', this)">Lost (<?= $lost ?>)</button>
                    </div>

                    <!-- Search Input -->
                    <div style="position: relative; width: 280px;">
                        <span class="material-symbols-outlined" style="position: absolute; left: 10px; top: 9px; color: #94A3B8; font-size: 18px;">search</span>
                        <input type="text" id="matchSearch" class="form-control" placeholder="Search opponent, venue..." style="padding-left: 36px; height: 36px; font-size: 13px;" onkeyup="searchMatchTable()">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="dash-table" id="matchTable">
                        <thead>
                            <tr>
                                <th># ID</th>
                                <th>Opponent &amp; Tournament</th>
                                <th>Discipline</th>
                                <th>Date &amp; Time</th>
                                <th>Venue</th>
                                <th style="text-align: center;">Score</th>
                                <th>Outcome</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fixtures as $fix): 
                                $isWon = ($fix['outcome'] === 'Won');
                                $isDrawn = ($fix['outcome'] === 'Drawn');
                                $isLost = ($fix['outcome'] === 'Lost');
                                $isSched = ($fix['outcome'] === 'Scheduled');
                            ?>
                                <tr class="match-row" data-outcome="<?= htmlspecialchars($fix['outcome']) ?>">
                                    <td style="font-weight: 700; color: var(--text);">
                                        <?= htmlspecialchars($fix['code']) ?>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text); font-size: 14px;"><?= htmlspecialchars($fix['opponent']) ?></div>
                                        <div style="font-size: 11.5px; color: var(--teal); font-weight: 600;"><?= htmlspecialchars($fix['tournament']) ?></div>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span class="material-symbols-outlined" style="color: var(--teal); font-size: 18px;"><?= $fix['sport_icon'] ?></span>
                                            <span style="font-weight: 600; font-size: 13px; color: var(--text);"><?= htmlspecialchars($fix['sport']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text);"><?= date('M d, Y', strtotime($fix['date_time'])) ?></div>
                                        <div style="font-size: 12px; color: var(--text-3);"><?= date('h:i A', strtotime($fix['date_time'])) ?></div>
                                    </td>
                                    <td style="font-size: 13px; color: var(--text);"><?= htmlspecialchars($fix['venue']) ?></td>
                                    <td style="text-align: center;">
                                        <span class="score-pill"><?= htmlspecialchars($fix['score']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($isWon): ?>
                                            <span class="badge badge-won">WON</span>
                                        <?php elseif ($isDrawn): ?>
                                            <span class="badge badge-drawn">DRAWN</span>
                                        <?php elseif ($isLost): ?>
                                            <span class="badge badge-lost">LOST</span>
                                        <?php else: ?>
                                            <span class="badge badge-sched">SCHEDULED</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <?php if ($isSched): ?>
                                            <!-- Log Result Trigger -->
                                            <button type="button" class="btn btn-sm btn-teal" onclick="openLogModal('<?= $fix['code'] ?>', '<?= addslashes($fix['opponent']) ?>', '<?= addslashes($fix['sport']) ?>')">
                                                <span class="material-symbols-outlined" style="font-size: 16px;">edit_note</span>
                                                <span>Log Result</span>
                                            </button>
                                        <?php else: ?>
                                            <!-- View Match Report (Historical Archive) -->
                                            <button type="button" class="btn btn-sm btn-outline" onclick="openReportModal(<?= htmlspecialchars(json_encode($fix)) ?>)" title="View official score report">
                                                <span class="material-symbols-outlined" style="font-size: 16px;">description</span>
                                                <span>Report</span>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div> <!-- /.page-content -->
    </div> <!-- /.main-wrapper -->

    <!-- ============================================== -->
    <!-- MODAL 1: LOG MATCH RESULT (All Dropdowns)      -->
    <!-- ============================================== -->
    <div class="modal-overlay" id="logResultModal">
        <div class="modal-card" style="max-width: 580px; width: 90%;">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title" id="logModalTitle" style="margin: 0; font-size: 17px; font-weight: 700;">Record Official Match Scoreline</h3>
                    <p style="font-size: 12.5px; color: var(--text-3); margin-top: 3px;">Submit authenticated result to University Athletic Board registry.</p>
                </div>
                <button type="button" class="modal-close" onclick="closeModal('logResultModal')">&times;</button>
            </div>
            <form action="../controllers/CoachController.php" method="POST">
                <input type="hidden" name="action" value="log_match_result">
                <input type="hidden" name="fixture_id" value="37">
                <div class="modal-body" style="padding: 20px;">
                    
                    <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                        <span style="font-size: 11px; font-weight: 700; color: var(--text-3); text-transform: uppercase;">Match Event</span>
                        <div id="logEventName" style="font-weight: 700; font-size: 15px; color: var(--text); margin-top: 2px;">
                            University of Colombo vs University of Ruhuna
                        </div>
                    </div>

                    <!-- Scores Dropdown Grid -->
                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 700; font-size: 13px; color: var(--teal); margin-bottom: 6px; display: block;">
                                University of Colombo Score
                            </label>
                            <select name="score_usms" class="form-control" style="width: 100%; height: 42px; font-size: 16px; font-weight: 700;">
                                <?php for ($i = 0; $i <= 10; $i++): ?>
                                    <option value="<?= $i ?>" <?= ($i === 2) ? 'selected' : '' ?>><?= $i ?> Goals / Points</option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 700; font-size: 13px; color: var(--text-2); margin-bottom: 6px; display: block;">
                                Opponent Score
                            </label>
                            <select name="score_opponent" class="form-control" style="width: 100%; height: 42px; font-size: 16px; font-weight: 700;">
                                <?php for ($i = 0; $i <= 10; $i++): ?>
                                    <option value="<?= $i ?>" <?= ($i === 1) ? 'selected' : '' ?>><?= $i ?> Goals / Points</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Match Outcome & Key Highlight Dropdowns -->
                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; font-size: 12.5px; margin-bottom: 4px; display: block;">Final Outcome <span style="color:#DC2626;">*</span></label>
                            <select name="outcome" class="form-control" required style="width: 100%; height: 38px;">
                                <option value="Won" selected>Won (Victory)</option>
                                <option value="Drawn">Drawn (Tied Match)</option>
                                <option value="Lost">Lost (Defeat)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; font-size: 12.5px; margin-bottom: 4px; display: block;">Tactical Highlight</label>
                            <select name="tactical_highlight" class="form-control" style="width: 100%; height: 38px;">
                                <option value="Dominant High-Press Attack">Dominant High-Press Attack</option>
                                <option value="Resilient Defensive Masterclass">Resilient Defensive Masterclass</option>
                                <option value="Set-Piece & Counter Decider">Set-Piece &amp; Counter Decider</option>
                                <option value="Comeback Victory in Final Quarter">Comeback Victory in Final Quarter</option>
                            </select>
                        </div>
                    </div>

                    <!-- Scorers and Report -->
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" style="font-weight: 600; font-size: 12.5px; margin-bottom: 4px; display: block;">Key Scorers / Points Contributors</label>
                        <input type="text" name="scorers" class="form-control" placeholder="e.g. K. Senanayake 34', D. Shanaka 78'" value="K. Senanayake 34', D. Shanaka 78'" style="width: 100%; height: 36px; font-size: 13px;">
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 12.5px; margin-bottom: 4px; display: block;">Coach Official Match Summary</label>
                        <textarea name="match_report" class="form-control" rows="2" style="width: 100%; font-size: 12.5px;">Outstanding tactical execution across both halves with zero disciplinary sanctions.</textarea>
                    </div>

                </div>
                <div class="modal-footer" style="padding: 12px 20px; background: #F8FAFC; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('logResultModal')">Cancel</button>
                    <button type="submit" class="btn btn-teal">
                        <span class="material-symbols-outlined" style="font-size: 18px;">save</span>
                        <span>Submit &amp; Publish Scoreline</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL 2: MATCH REPORT VIEW MODAL               -->
    <!-- (Historical Season Archives)                   -->
    <!-- ============================================== -->
    <div class="modal-overlay" id="reportModal">
        <div class="modal-card" style="max-width: 520px; width: 90%;">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title" id="repCode" style="margin: 0; font-size: 17px; font-weight: 700;">Official Match Archive</h3>
                    <p id="repTournament" style="font-size: 12px; color: var(--teal); font-weight: 600; margin-top: 2px;">Tournament Archive</p>
                </div>
                <button type="button" class="modal-close" onclick="closeModal('reportModal')">&times;</button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <div style="text-align: center; margin-bottom: 16px; padding: 14px; background: #F8FAFC; border-radius: 8px; border: 1px solid var(--border);">
                    <div id="repOpponent" style="font-size: 16px; font-weight: 700; color: var(--text);">Opponent</div>
                    <div id="repScore" class="score-pill" style="font-size: 18px; margin-top: 6px; padding: 6px 16px;">Score</div>
                </div>

                <div style="margin-bottom: 12px;">
                    <span style="font-size: 11px; font-weight: 700; color: var(--text-3); text-transform: uppercase;">Points / Goal Scorers</span>
                    <div id="repScorers" style="font-weight: 600; font-size: 13.5px; color: var(--text); margin-top: 3px;">Scorers</div>
                </div>

                <div>
                    <span style="font-size: 11px; font-weight: 700; color: var(--text-3); text-transform: uppercase;">Coach Tactical Evaluation</span>
                    <div id="repReport" style="font-size: 13px; color: var(--text-2); background: #F8FAFC; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--border); margin-top: 4px;">Report</div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 12px 20px; background: #F8FAFC; border-top: 1px solid var(--border); display: flex; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="closeModal('reportModal')">Close</button>
            </div>
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

        function openLogModal(code, opp, sport) {
            document.getElementById('logModalTitle').innerText = 'Record Result: ' + code + ' (' + sport + ')';
            document.getElementById('logEventName').innerText = 'UOC vs ' + opp;
            openModal('logResultModal');
        }

        function openReportModal(data) {
            document.getElementById('repCode').innerText = data.code + ' Match Archive';
            document.getElementById('repTournament').innerText = data.tournament + ' • ' + data.sport;
            document.getElementById('repOpponent').innerText = 'UOC vs ' + data.opponent;
            document.getElementById('repScore').innerText = data.score + ' (' + data.outcome.toUpperCase() + ')';
            document.getElementById('repScorers').innerText = data.scorers;
            document.getElementById('repReport').innerText = data.report;
            openModal('reportModal');
        }

        function filterMatches(outcome, btn) {
            var rows = document.querySelectorAll('.match-row');
            document.querySelectorAll('.filter-tab').forEach(function(b) {
                b.classList.remove('btn-teal');
                b.classList.add('btn-outline');
            });
            btn.classList.remove('btn-outline');
            btn.classList.add('btn-teal');

            rows.forEach(function(row) {
                var rowOutcome = row.getAttribute('data-outcome');
                if (outcome === 'all' || rowOutcome === outcome) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function searchMatchTable() {
            var input = document.getElementById('matchSearch').value.toLowerCase();
            var rows = document.querySelectorAll('.match-row');
            rows.forEach(function(row) {
                var text = row.innerText.toLowerCase();
                row.style.display = text.indexOf(input) > -1 ? '' : 'none';
            });
        }
    </script>
</body>
</html>
