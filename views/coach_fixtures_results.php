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

$pageTitle = 'Fixtures & Match Results';

// Stat Cards (Grid-4)
$stats = [
    'total_matches' => 12,
    'won' => 8,
    'drawn' => 2,
    'lost' => 2
];

// Realistic Mock Data for Fixtures & Results
$fixtures = [
    [
        'id' => 'FIX-104',
        'opponent' => 'University of Peradeniya',
        'sport' => 'Men\'s Football',
        'sport_icon' => 'sports_soccer',
        'tournament' => 'SLUG Championship 2026 (Semi Final)',
        'date' => '2026-10-04 15:30',
        'display_date' => 'Oct 04, 2026 • 03:30 PM',
        'venue' => 'Bogambara Stadium, Kandy',
        'status' => 'Upcoming',
        'score' => '- vs -',
        'outcome' => 'Scheduled',
        'scorers' => '',
        'remarks' => 'Pre-match tactical conference scheduled at 01:30 PM in team room.'
    ],
    [
        'id' => 'FIX-103',
        'opponent' => 'Faculty of Science (Derby)',
        'sport' => 'Men\'s Football',
        'sport_icon' => 'sports_soccer',
        'tournament' => 'Inter-Faculty Championship',
        'date' => '2026-09-28 16:00',
        'display_date' => 'Sep 28, 2026 • 04:00 PM',
        'venue' => 'Main University Ground',
        'status' => 'Upcoming',
        'score' => '- vs -',
        'outcome' => 'Scheduled',
        'scorers' => '',
        'remarks' => 'Annual varsity derby. High attendance expected.'
    ],
    [
        'id' => 'FIX-102',
        'opponent' => 'University of Moratuwa',
        'sport' => 'Men\'s Football',
        'sport_icon' => 'sports_soccer',
        'tournament' => 'SLUG Championship 2026 (Quarter Final)',
        'date' => '2026-09-20 16:00',
        'display_date' => 'Sep 20, 2026 • 04:00 PM',
        'venue' => 'Main University Ground',
        'status' => 'Completed',
        'score' => '3 - 1',
        'outcome' => 'Won',
        'scorers' => 'Kavindu Perera (24\', 68\'), Nuwan Fernando (81\')',
        'remarks' => 'Superb counter-attacking masterclass. Team maintained 62% possession and disciplined defensive shape.'
    ],
    [
        'id' => 'FIX-101',
        'opponent' => 'University of Kelaniya',
        'sport' => 'Men\'s Football',
        'sport_icon' => 'sports_soccer',
        'tournament' => 'Western Province Varsity League',
        'date' => '2026-09-15 15:30',
        'display_date' => 'Sep 15, 2026 • 03:30 PM',
        'venue' => 'Kelaniya University Grounds',
        'status' => 'Completed',
        'score' => '1 - 1',
        'outcome' => 'Drawn',
        'scorers' => 'Sadeera Samarawickrama (53\')',
        'remarks' => 'Challenging turf surface due to rain. Equalizer scored via corner set-piece.'
    ],
    [
        'id' => 'FIX-100',
        'opponent' => 'University of Sri Jayewardenepura',
        'sport' => 'Men\'s Football',
        'sport_icon' => 'sports_soccer',
        'tournament' => 'Invitational President\'s Trophy',
        'date' => '2026-09-08 16:00',
        'display_date' => 'Sep 08, 2026 • 04:00 PM',
        'venue' => 'J\'pura Sports Complex',
        'status' => 'Completed',
        'score' => '2 - 0',
        'outcome' => 'Won',
        'scorers' => 'Dineth Jayawardena (39\'), Kavindu Perera (74\')',
        'remarks' => 'Clean sheet preserved by Chamara Silva in goal with three critical saves.'
    ],
    [
        'id' => 'FIX-099',
        'opponent' => 'Sabaragamuwa University',
        'sport' => 'Men\'s Football',
        'sport_icon' => 'sports_soccer',
        'tournament' => 'Western Province Varsity League',
        'date' => '2026-08-30 15:00',
        'display_date' => 'Aug 30, 2026 • 03:00 PM',
        'venue' => 'Belihuloya Sports Ground',
        'status' => 'Completed',
        'score' => '1 - 2',
        'outcome' => 'Lost',
        'scorers' => 'Nuwan Fernando (18\')',
        'remarks' => 'High altitude fatigue observed in the final 20 minutes. Penalty conceded on 86th minute.'
    ],
    [
        'id' => 'FIX-098',
        'opponent' => 'Eastern University of Sri Lanka',
        'sport' => 'Men\'s Football',
        'sport_icon' => 'sports_soccer',
        'tournament' => 'National Inter-University Trials',
        'date' => '2026-08-22 09:30',
        'display_date' => 'Aug 22, 2026 • 09:30 AM',
        'venue' => 'Sugathadasa Outdoor Stadium',
        'status' => 'Completed',
        'score' => '4 - 0',
        'outcome' => 'Won',
        'scorers' => 'Sahan Wickramasinghe (12\'), Kavindu Perera (31\', 44\'), Asalanka (70\')',
        'remarks' => 'Dominant attacking display with quick wing progression.'
    ]
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
            font-size: 13.5px;
        }
        .dash-table thead th {
            background: var(--side-bg);
            color: #CBD5E1;
            padding: 11px 12px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
            text-align: left;
            vertical-align: middle;
        }
        .dash-table tbody td {
            padding: 11px 12px;
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
        .badge-drawn {
            background: #FFFBEB;
            color: #B45309;
            border: 1px solid #FDE68A;
        }
        .badge-scheduled {
            background: #EFF6FF;
            color: #2563EB;
            border: 1px solid #BFDBFE;
        }
        .score-pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 6px;
            font-family: 'Cascadia Code', 'Consolas', monospace;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.04em;
        }
        .score-pill.won {
            background: #DCFCE7;
            color: #15803D;
            border: 1px solid #BBF7D0;
        }
        .score-pill.drawn {
            background: #FEF3C7;
            color: #B45309;
            border: 1px solid #FDE68A;
        }
        .score-pill.lost {
            background: #FEE2E2;
            color: #DC2626;
            border: 1px solid #FECACA;
        }
        .score-pill.upcoming {
            background: #F1F5F9;
            color: #64748B;
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
                <h2 class="page-title">Fixtures & Match Results</h2>
                <p style="font-size: 13px; color: var(--text-3); margin-top: 3px;">
                    Review upcoming competitive fixtures, evaluate past match performance, and log official scores
                </p>
            </div>
            <div>
                <button type="button" class="btn btn-teal" onclick="openLogResultModal(null)">
                    <span class="material-symbols-outlined" style="font-size: 18px;">scoreboard</span>
                    Log Match Result
                </button>
            </div>
        </div>

        <!-- Top Stat Cards (Grid-4) -->
        <div class="grid-4">
            <div class="stat-card">
                <div>
                    <div class="stat-label">Total Matches</div>
                    <div class="stat-value"><?= $stats['total_matches'] ?></div>
                </div>
                <div class="stat-icon">
                    <span class="material-symbols-outlined">sports_score</span>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Matches Won</div>
                    <div class="stat-value" style="color: #16A34A;"><?= $stats['won'] ?></div>
                </div>
                <div class="stat-icon" style="color: #16A34A; background: #F0FDF4;">
                    <span class="material-symbols-outlined">emoji_events</span>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Matches Drawn</div>
                    <div class="stat-value" style="color: #D97706;"><?= $stats['drawn'] ?></div>
                </div>
                <div class="stat-icon" style="color: #D97706; background: #FFFBEB;">
                    <span class="material-symbols-outlined">handshake</span>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Matches Lost</div>
                    <div class="stat-value" style="color: #DC2626;"><?= $stats['lost'] ?></div>
                </div>
                <div class="stat-icon" style="color: #DC2626; background: #FEF2F2;">
                    <span class="material-symbols-outlined">trending_down</span>
                </div>
            </div>
        </div>

        <!-- Data Table Card -->
        <div class="table-card">
            <div class="table-toolbar">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="table-title">Season Match Ledger</span>
                    <span class="tab-counter"><?= count($fixtures) ?> logged</span>
                </div>

                <div class="table-filters">
                    <div class="tab-group" id="fixtureFilterPills">
                        <button type="button" class="tab-btn active" onclick="filterFixtures('all', this)">All Matches</button>
                        <button type="button" class="tab-btn" onclick="filterFixtures('Upcoming', this)">Upcoming</button>
                        <button type="button" class="tab-btn" onclick="filterFixtures('Won', this)">Won</button>
                        <button type="button" class="tab-btn" onclick="filterFixtures('Drawn', this)">Drawn</button>
                        <button type="button" class="tab-btn" onclick="filterFixtures('Lost', this)">Lost</button>
                    </div>

                    <div class="search-box">
                        <input type="text" class="search-input" id="searchFixtureInput" placeholder="Search opponent, venue…" onkeyup="filterFixtureTable()">
                        <span class="material-symbols-outlined">search</span>
                    </div>
                </div>
            </div>

            <div class="table-scroll">
                <table class="dash-table data-table" id="fixturesTable">
                    <thead>
                        <tr>
                            <th># ID</th>
                            <th>Opponent & Tournament</th>
                            <th>Discipline</th>
                            <th>Date & Time</th>
                            <th>Venue</th>
                            <th style="text-align: center;">Score</th>
                            <th>Outcome</th>
                            <th class="text-right" style="text-align: right; padding-right: 18px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fixtures as $fix): 
                            $outcomeBadge = 'badge-scheduled';
                            $scoreClass = 'upcoming';
                            if ($fix['outcome'] === 'Won') {
                                $outcomeBadge = 'badge-success';
                                $scoreClass = 'won';
                            } elseif ($fix['outcome'] === 'Drawn') {
                                $outcomeBadge = 'badge-drawn';
                                $scoreClass = 'drawn';
                            } elseif ($fix['outcome'] === 'Lost') {
                                $outcomeBadge = 'badge-danger';
                                $scoreClass = 'lost';
                            }

                            $fixJson = htmlspecialchars(json_encode($fix), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr data-outcome="<?= htmlspecialchars($fix['outcome']) ?>" data-status="<?= htmlspecialchars($fix['status']) ?>">
                            <td>
                                <span class="cell-mono" style="font-weight: 700; color: var(--text);"><?= htmlspecialchars($fix['id']) ?></span>
                            </td>
                            <td>
                                <span class="cell-main" style="font-size: 14px; font-weight: 700;"><?= htmlspecialchars($fix['opponent']) ?></span>
                                <span class="cell-sub" style="color: var(--accent-dark); font-weight: 600;">
                                    <?= htmlspecialchars($fix['tournament']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="cell-flex">
                                    <span class="mini-icon">
                                        <span class="material-symbols-outlined"><?= htmlspecialchars($fix['sport_icon']) ?></span>
                                    </span>
                                    <span class="badge badge-info"><?= htmlspecialchars($fix['sport']) ?></span>
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 13px; font-weight: 600;"><?= htmlspecialchars($fix['display_date']) ?></span>
                            </td>
                            <td>
                                <span style="font-size: 13px; color: var(--text-2); display: flex; align-items: center; gap: 4px;">
                                    <span class="material-symbols-outlined" style="font-size: 15px; color: var(--accent);">location_on</span>
                                    <?= htmlspecialchars($fix['venue']) ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span class="score-pill <?= $scoreClass ?>">
                                    <?= htmlspecialchars($fix['score']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $outcomeBadge ?>">
                                    <?= htmlspecialchars($fix['outcome']) ?>
                                </span>
                            </td>
                            <td style="text-align: right; padding-right: 18px;">
                                <div class="cell-actions">
                                    <?php if ($fix['status'] === 'Upcoming'): ?>
                                        <button type="button" class="btn btn-teal btn-sm" onclick='openLogResultModal(<?= $fixJson ?>)'>
                                            <span class="material-symbols-outlined" style="font-size: 15px;">edit_note</span>
                                            Log Result
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-outline btn-sm" onclick='viewMatchReport(<?= $fixJson ?>)'>
                                            <span class="material-symbols-outlined" style="font-size: 15px;">description</span>
                                            View Report
                                        </button>
                                        <button type="button" class="btn btn-outline btn-sm" onclick='openLogResultModal(<?= $fixJson ?>)'>
                                            Edit
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- MODAL 1: Log Match Result Modal -->
<div class="modal-overlay" id="logResultModal">
    <div class="modal" style="width: 540px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-outlined" style="color: var(--accent); font-size: 22px;">scoreboard</span>
                <div>
                    <span class="modal-title">Log Match Result</span>
                    <span id="logModalSub" style="font-size: 12px; color: var(--text-3); display: block;">Record final score, goal scorers, and post-match notes</span>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('logResultModal')">&times;</button>
        </div>
        <form onsubmit="event.preventDefault(); alert('Match result and official scoreline recorded in USMS registry!'); closeModal('logResultModal');">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Match Fixture</label>
                    <input type="text" class="form-input" id="logFixtureTitle" value="vs University of Peradeniya (SLUG Semi Final)" readonly style="background: var(--bg); font-weight: 700;">
                </div>

                <div class="form-row" style="background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--r); padding: 12px; margin-bottom: 14px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="color: var(--accent-dark);">Our Score (UoC)</label>
                        <input type="number" class="form-input" id="logOurScore" min="0" max="99" value="3" required style="font-size: 18px; font-weight: 800; text-align: center;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="color: var(--text-2);">Opponent Score</label>
                        <input type="number" class="form-input" id="logOpponentScore" min="0" max="99" value="1" required style="font-size: 18px; font-weight: 800; text-align: center;" onchange="autoDetermineOutcome()">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Match Outcome</label>
                    <select class="form-select" id="logOutcome" required>
                        <option value="Won" selected>Won (Victory)</option>
                        <option value="Drawn">Drawn (Tie)</option>
                        <option value="Lost">Lost (Defeat)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Goal Scorers / Key Performers</label>
                    <input type="text" class="form-input" id="logScorers" placeholder="e.g. Kavindu Perera (24', 68'), Nuwan Fernando (81')" value="Kavindu Perera (24', 68'), Nuwan Fernando (81')">
                </div>

                <div class="form-group">
                    <label class="form-label">Coach Tactical Remarks & Highlights</label>
                    <textarea class="form-textarea" id="logRemarks" placeholder="Tactical notes, player discipline, referee notes, injury occurrences during match…" rows="3">Outstanding execution of high-press defensive structure. 62% ball possession maintained in second half.</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('logResultModal')">Cancel</button>
                <button type="submit" class="btn btn-teal">
                    <span class="material-symbols-outlined" style="font-size: 16px;">save</span>
                    Record Official Result
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: View Match Report Modal -->
<div class="modal-overlay" id="matchReportModal">
    <div class="modal" style="width: 520px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-outlined" style="color: var(--accent); font-size: 22px;">assessment</span>
                <span class="modal-title">Official Match Report</span>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('matchReportModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div style="text-align: center; padding: 14px 16px; background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--r); margin-bottom: 14px;">
                <span id="rptTournament" style="font-size: 11.5px; font-weight: 700; color: var(--accent-dark); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 4px;">SLUG Championship 2026</span>
                <h3 id="rptOpponent" style="font-size: 17px; font-weight: 800; color: var(--text); margin-bottom: 6px;">University of Colombo vs University of Moratuwa</h3>
                <div id="rptScorePill" class="score-pill won" style="font-size: 18px; padding: 4px 16px;">3 - 1 (Won)</div>
                <div id="rptDateTime" style="font-size: 12.5px; color: var(--text-3); margin-top: 6px;">Sep 20, 2026 • Main University Ground</div>
            </div>

            <div style="margin-bottom: 14px;">
                <label class="form-label" style="font-size: 12px; color: var(--text-3); text-transform: uppercase;">Scorers & Points Contributed</label>
                <div id="rptScorers" style="font-size: 13.5px; font-weight: 600; color: var(--text); background: var(--bg); padding: 10px 12px; border-radius: 6px; border: 1px solid var(--border);">
                    -
                </div>
            </div>

            <div>
                <label class="form-label" style="font-size: 12px; color: var(--text-3); text-transform: uppercase;">Coach Tactical Review & Post-Match Notes</label>
                <div id="rptRemarks" style="font-size: 13px; color: var(--text-2); background: var(--bg); padding: 10px 12px; border-radius: 6px; border: 1px solid var(--border); line-height: 1.5;">
                    -
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('matchReportModal')">Close</button>
        </div>
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

// Open Log Result Modal with fixture pre-filled if available
function openLogResultModal(fix) {
    if (fix) {
        document.getElementById('logFixtureTitle').value = fix.id + ': vs ' + fix.opponent + ' (' + fix.tournament + ')';
        if (fix.score && fix.score.indexOf('-') > -1 && fix.score !== '- vs -') {
            var parts = fix.score.split('-');
            document.getElementById('logOurScore').value = parseInt(parts[0].trim()) || 0;
            document.getElementById('logOpponentScore').value = parseInt(parts[1].trim()) || 0;
        } else {
            document.getElementById('logOurScore').value = '0';
            document.getElementById('logOpponentScore').value = '0';
        }
        if (fix.outcome && fix.outcome !== 'Scheduled') {
            document.getElementById('logOutcome').value = fix.outcome;
        }
        document.getElementById('logScorers').value = fix.scorers || '';
        document.getElementById('logRemarks').value = fix.remarks || '';
    } else {
        document.getElementById('logFixtureTitle').value = 'FIX-104: vs University of Peradeniya (SLUG Championship 2026)';
    }
    openModal('logResultModal');
}

// Auto determine outcome based on score
function autoDetermineOutcome() {
    var our = parseInt(document.getElementById('logOurScore').value) || 0;
    var opp = parseInt(document.getElementById('logOpponentScore').value) || 0;
    var sel = document.getElementById('logOutcome');
    if (our > opp) sel.value = 'Won';
    else if (our === opp) sel.value = 'Drawn';
    else sel.value = 'Lost';
}

// View Match Report Modal
function viewMatchReport(fix) {
    document.getElementById('rptTournament').textContent = fix.tournament;
    document.getElementById('rptOpponent').textContent = 'UoC vs ' + fix.opponent;
    document.getElementById('rptScorePill').textContent = fix.score + ' (' + fix.outcome + ')';
    document.getElementById('rptScorePill').className = 'score-pill ' + fix.outcome.toLowerCase();
    document.getElementById('rptDateTime').textContent = fix.display_date + ' • ' + fix.venue;
    document.getElementById('rptScorers').textContent = fix.scorers || 'No scorers logged';
    document.getElementById('rptRemarks').textContent = fix.remarks || 'No tactical remarks filed';

    openModal('matchReportModal');
}

// Live Search Filter
function filterFixtureTable() {
    var q = document.getElementById('searchFixtureInput').value.toLowerCase();
    var rows = document.querySelectorAll('#fixturesTable tbody tr');
    rows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.indexOf(q) > -1 ? '' : 'none';
    });
}

// Status Filter Tabs
function filterFixtures(filter, btn) {
    var pills = document.querySelectorAll('#fixtureFilterPills .tab-btn');
    pills.forEach(function(p) { p.classList.remove('active'); });
    btn.classList.add('active');

    var rows = document.querySelectorAll('#fixturesTable tbody tr');
    rows.forEach(function(row) {
        if (filter === 'all') {
            row.style.display = '';
        } else if (filter === 'Upcoming') {
            row.style.display = (row.dataset.status === 'Upcoming') ? '' : 'none';
        } else {
            row.style.display = (row.dataset.outcome === filter) ? '' : 'none';
        }
    });
}
</script>

</body>
</html>
