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

$pageTitle = 'Training Schedule & Attendance Tracker';

// Top Stat Counters
$stats = [
    'total_sessions' => 24,
    'upcoming_sessions' => 5,
    'avg_attendance' => '88%',
    'medically_inactive' => 2
];

// Mock Data for Training Sessions
$trainingSessions = [
    [
        'id' => 'TRN-201',
        'datetime' => '2026-09-22 16:30',
        'display_date' => 'Today, Sep 22 • 04:30 PM',
        'sport' => 'Football',
        'sport_icon' => 'sports_soccer',
        'venue' => 'Main University Ground',
        'focus_area' => 'High-Press & Set Pieces',
        'attendance_present' => 22,
        'attendance_total' => 24,
        'attendance_ratio' => '22/24',
        'attendance_pct' => 92,
        'status' => 'Scheduled',
        'notes' => 'Match simulation with referee rehearsal before the SLUG regional qualifier.'
    ],
    [
        'id' => 'TRN-200',
        'datetime' => '2026-09-21 06:00',
        'display_date' => 'Yesterday, Sep 21 • 06:00 AM',
        'sport' => 'Football',
        'sport_icon' => 'sports_soccer',
        'venue' => 'Gym & Fitness Center',
        'focus_area' => 'Plyometrics & Conditioning',
        'attendance_present' => 24,
        'attendance_total' => 24,
        'attendance_ratio' => '24/24',
        'attendance_pct' => 100,
        'status' => 'Completed',
        'notes' => 'Early morning core mobility, resistance band drills, and squad weigh-in.'
    ],
    [
        'id' => 'TRN-199',
        'datetime' => '2026-09-23 15:30',
        'display_date' => 'Tomorrow, Sep 23 • 03:30 PM',
        'sport' => 'Basketball',
        'sport_icon' => 'sports_basketball',
        'venue' => 'Indoor Gymnasium Court 1',
        'focus_area' => 'Zone Defense & Fast Breaks',
        'attendance_present' => 14,
        'attendance_total' => 16,
        'attendance_ratio' => '14/16',
        'attendance_pct' => 88,
        'status' => 'Scheduled',
        'notes' => 'Full court press transition analysis and perimeter shooting drills.'
    ],
    [
        'id' => 'TRN-198',
        'datetime' => '2026-09-18 16:00',
        'display_date' => 'Sep 18, 2026 • 04:00 PM',
        'sport' => 'Cricket',
        'sport_icon' => 'sports_cricket',
        'venue' => 'Turf Nets Complex',
        'focus_area' => 'Spin Bowling & Death Overs',
        'attendance_present' => 16,
        'attendance_total' => 18,
        'attendance_ratio' => '16/18',
        'attendance_pct' => 89,
        'status' => 'Completed',
        'notes' => 'Target yorker practice and middle-order acceleration under game scenarios.'
    ],
    [
        'id' => 'TRN-197',
        'datetime' => '2026-09-16 17:00',
        'display_date' => 'Sep 16, 2026 • 05:00 PM',
        'sport' => 'Football',
        'sport_icon' => 'sports_soccer',
        'venue' => 'Main University Ground',
        'focus_area' => 'Counter-Attack & Crossing',
        'attendance_present' => 20,
        'attendance_total' => 24,
        'attendance_ratio' => '20/24',
        'attendance_pct' => 83,
        'status' => 'Completed',
        'notes' => 'Wing play combinations and finishing from edge-of-the-box cutbacks.'
    ],
    [
        'id' => 'TRN-196',
        'datetime' => '2026-09-14 07:00',
        'display_date' => 'Sep 14, 2026 • 07:00 AM',
        'sport' => 'Track & Field',
        'sport_icon' => 'timer',
        'venue' => 'Synthetic Athletic Track',
        'focus_area' => 'Speed Intervals & Block Starts',
        'attendance_present' => 12,
        'attendance_total' => 12,
        'attendance_ratio' => '12/12',
        'attendance_pct' => 100,
        'status' => 'Completed',
        'notes' => 'Sprint acceleration curves and reaction time tracking using electronic sensors.'
    ]
];

// Mock Athlete Roster for Attendance Sheet Modal
$mockAthletes = [
    ['id' => 'ATH-042', 'name' => 'Kavindu Perera', 'jersey' => '#10', 'faculty' => 'Science', 'position' => 'Midfielder', 'med_status' => 'Fit', 'default_attendance' => 'Present'],
    ['id' => 'ATH-018', 'name' => 'Nuwan Fernando', 'jersey' => '#07', 'faculty' => 'Management', 'position' => 'Forward', 'med_status' => 'Fit', 'default_attendance' => 'Present'],
    ['id' => 'ATH-089', 'name' => 'Chamara Silva', 'jersey' => '#01', 'faculty' => 'Arts', 'position' => 'Goalkeeper', 'med_status' => 'Fit', 'default_attendance' => 'Present'],
    ['id' => 'ATH-055', 'name' => 'Dineth Jayawardena', 'jersey' => '#04', 'faculty' => 'Engineering', 'position' => 'Centre Back', 'med_status' => 'Fit', 'default_attendance' => 'Present'],
    ['id' => 'ATH-063', 'name' => 'Sahan Wickramasinghe', 'jersey' => '#08', 'faculty' => 'Science', 'position' => 'Defensive Mid', 'med_status' => 'Fit', 'default_attendance' => 'Present'],
    ['id' => 'ATH-077', 'name' => 'Tharindu Alwis', 'jersey' => '#11', 'faculty' => 'Medicine', 'position' => 'Left Winger', 'med_status' => 'Under Review', 'default_attendance' => 'Excused'],
    ['id' => 'ATH-031', 'name' => 'Malith Bandara', 'jersey' => '#03', 'faculty' => 'Law', 'position' => 'Right Back', 'med_status' => 'Fit', 'default_attendance' => 'Present'],
    ['id' => 'ATH-094', 'name' => 'Roshan Kumara', 'jersey' => '#09', 'faculty' => 'Technology', 'position' => 'Striker', 'med_status' => 'Temporarily Unfit', 'default_attendance' => 'Absent']
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
        .badge-scheduled {
            background: #EFF6FF;
            color: #2563EB;
            border: 1px solid #BFDBFE;
        }
        .badge-completed {
            background: #F0FDF4;
            color: #16A34A;
            border: 1px solid #BBF7D0;
        }
        .badge-canceled {
            background: var(--danger-bg);
            color: var(--danger);
            border: 1px solid var(--danger-border);
        }
        .progress-bar-wrap {
            width: 90px;
            height: 6px;
            background: #E2E8F0;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 4px;
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 4px;
            background: var(--accent);
        }
        .attendance-radio-group {
            display: inline-flex;
            background: #F1F5F9;
            border-radius: 6px;
            padding: 2px;
            gap: 2px;
        }
        .attendance-radio-group label {
            padding: 3px 8px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 4px;
            color: var(--text-2);
            user-select: none;
            transition: all var(--ease);
        }
        .attendance-radio-group input[type="radio"] {
            display: none;
        }
        .attendance-radio-group input[type="radio"]:checked + span {
            font-weight: 700;
        }
        .attendance-radio-group label:has(input[value="Present"]:checked) {
            background: #DCFCE7;
            color: #15803D;
        }
        .attendance-radio-group label:has(input[value="Absent"]:checked) {
            background: #FEE2E2;
            color: #DC2626;
        }
        .attendance-radio-group label:has(input[value="Excused"]:checked) {
            background: #FEF3C7;
            color: #B45309;
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
                <h2 class="page-title">Training Schedule & Attendance Tracker</h2>
                <p style="font-size: 13px; color: var(--text-3); margin-top: 3px;">
                    Schedule team practices, log athlete physical attendance, and align tactical focus drills
                </p>
            </div>
            <div>
                <button type="button" class="btn btn-teal" onclick="openModal('newSessionModal')">
                    <span class="material-symbols-outlined" style="font-size: 18px;">event</span>
                    + Schedule Training Session
                </button>
            </div>
        </div>

        <!-- Top Stat Cards (Grid-4) -->
        <div class="grid-4">
            <div class="stat-card">
                <div>
                    <div class="stat-label">Total Sessions</div>
                    <div class="stat-value"><?= $stats['total_sessions'] ?></div>
                </div>
                <div class="stat-icon">
                    <span class="material-symbols-outlined">calendar_month</span>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Upcoming This Week</div>
                    <div class="stat-value" style="color: #2563EB;"><?= $stats['upcoming_sessions'] ?></div>
                </div>
                <div class="stat-icon" style="color: #2563EB; background: #EFF6FF;">
                    <span class="material-symbols-outlined">event_upcoming</span>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Avg Attendance Ratio</div>
                    <div class="stat-value" style="color: var(--accent);"><?= $stats['avg_attendance'] ?></div>
                </div>
                <div class="stat-icon" style="color: var(--accent); background: var(--accent-bg);">
                    <span class="material-symbols-outlined">co_present</span>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Medically Inactive</div>
                    <div class="stat-value" style="color: #DC2626;"><?= $stats['medically_inactive'] ?></div>
                </div>
                <div class="stat-icon" style="color: #DC2626; background: #FEF2F2;">
                    <span class="material-symbols-outlined">health_and_safety</span>
                </div>
            </div>
        </div>

        <!-- Sessions Data Table Card -->
        <div class="table-card">
            <div class="table-toolbar">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="table-title">Scheduled Training Sessions</span>
                    <span class="tab-counter"><?= count($trainingSessions) ?> sessions</span>
                </div>

                <div class="table-filters">
                    <div class="tab-group" id="sessionFilterPills">
                        <button type="button" class="tab-btn active" onclick="filterSessions('all', this)">All</button>
                        <button type="button" class="tab-btn" onclick="filterSessions('Scheduled', this)">Scheduled</button>
                        <button type="button" class="tab-btn" onclick="filterSessions('Completed', this)">Completed</button>
                    </div>

                    <div class="search-box">
                        <input type="text" class="search-input" id="searchSessionInput" placeholder="Search sessions, venue…" onkeyup="filterSessionTable()">
                        <span class="material-symbols-outlined">search</span>
                    </div>
                </div>
            </div>

            <div class="table-scroll">
                <table class="dash-table data-table" id="sessionsTable">
                    <thead>
                        <tr>
                            <th># ID</th>
                            <th>Date & Time</th>
                            <th>Sport</th>
                            <th>Venue</th>
                            <th>Focus Area</th>
                            <th>Attendance Ratio</th>
                            <th>Status</th>
                            <th class="text-right" style="text-align: right; padding-right: 18px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trainingSessions as $sess): 
                            $statusBadge = ($sess['status'] === 'Completed') ? 'badge-completed' : 'badge-scheduled';
                            $sessJson = htmlspecialchars(json_encode($sess), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr data-status="<?= htmlspecialchars($sess['status']) ?>">
                            <td>
                                <span class="cell-mono" style="font-weight: 700; color: var(--text);"><?= htmlspecialchars($sess['id']) ?></span>
                            </td>
                            <td>
                                <span class="cell-main"><?= htmlspecialchars($sess['display_date']) ?></span>
                            </td>
                            <td>
                                <div class="cell-flex">
                                    <span class="mini-icon">
                                        <span class="material-symbols-outlined"><?= htmlspecialchars($sess['sport_icon']) ?></span>
                                    </span>
                                    <span class="badge badge-info"><?= htmlspecialchars($sess['sport']) ?></span>
                                </div>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: var(--text-2); display: flex; align-items: center; gap: 4px;">
                                    <span class="material-symbols-outlined" style="font-size: 16px; color: var(--accent);">location_on</span>
                                    <?= htmlspecialchars($sess['venue']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="cell-main"><?= htmlspecialchars($sess['focus_area']) ?></span>
                                <span class="cell-sub"><?= htmlspecialchars($sess['notes']) ?></span>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 12.5px; font-weight: 700; color: var(--text);">
                                        <?= $sess['attendance_ratio'] ?>
                                    </span>
                                    <span style="font-size: 11px; color: var(--text-3); font-weight: 600;">
                                        (<?= $sess['attendance_pct'] ?>%)
                                    </span>
                                </div>
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar-fill" style="width: <?= $sess['attendance_pct'] ?>%;"></div>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= $statusBadge ?>">
                                    <?= htmlspecialchars($sess['status']) ?>
                                </span>
                            </td>
                            <td style="text-align: right; padding-right: 18px;">
                                <div class="cell-actions">
                                    <button type="button" class="btn btn-teal btn-sm" onclick='openAttendanceModal(<?= $sessJson ?>)'>
                                        <span class="material-symbols-outlined" style="font-size: 15px;">checklist</span>
                                        <?= ($sess['status'] === 'Completed') ? 'View Roster' : 'Log Attendance' ?>
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm" onclick="alert('Session details for <?= $sess['id'] ?> are currently locked to active roster.')">
                                        Edit
                                    </button>
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

<!-- MODAL 1: Schedule New Training Session Modal -->
<div class="modal-overlay" id="newSessionModal">
    <div class="modal" style="width: 520px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-outlined" style="color: var(--accent); font-size: 22px;">alarm_add</span>
                <span class="modal-title">Schedule New Training Session</span>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('newSessionModal')">&times;</button>
        </div>
        <form onsubmit="event.preventDefault(); alert('New training session scheduled successfully!'); closeModal('newSessionModal');">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Sport Discipline</label>
                        <select class="form-select" required>
                            <option value="">Select Sport…</option>
                            <option value="Football" selected>Football</option>
                            <option value="Basketball">Basketball</option>
                            <option value="Cricket">Cricket</option>
                            <option value="Badminton">Badminton</option>
                            <option value="Track & Field">Track & Field</option>
                            <option value="Swimming">Swimming</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Venue / Facility</label>
                        <select class="form-select" required>
                            <option value="">Select Venue…</option>
                            <option value="Main University Ground" selected>Main University Ground</option>
                            <option value="Indoor Gymnasium Court 1">Indoor Gymnasium Court 1</option>
                            <option value="Turf Nets Complex">Turf Nets Complex</option>
                            <option value="Gym & Fitness Center">Gym & Fitness Center</option>
                            <option value="Synthetic Athletic Track">Synthetic Athletic Track</option>
                            <option value="University Aquatic Complex">University Aquatic Complex</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Session Date</label>
                        <input type="date" class="form-input" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Start Time</label>
                        <input type="time" class="form-input" value="16:30" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tactical Focus Area</label>
                    <input type="text" class="form-input" placeholder="e.g. Set Pieces, Counter-Pressing, Zone Defense…" value="Defensive Transition & Set-Piece Drills" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Session Notes & Equipment Needs</label>
                    <textarea class="form-textarea" placeholder="Detailed coaching notes, required cones, bibs, water bottles…" rows="3">Bring full match kit, shin guards, and hydration fluids. Video tactical review starts 15 mins prior.</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('newSessionModal')">Cancel</button>
                <button type="submit" class="btn btn-teal">
                    <span class="material-symbols-outlined" style="font-size: 16px;">check</span>
                    Schedule Session
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: Athlete Attendance Sheet Modal -->
<div class="modal-overlay" id="attendanceModal">
    <div class="modal" style="width: 720px; max-width: 95%;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-outlined" style="color: var(--accent); font-size: 22px;">fact_check</span>
                <div>
                    <span class="modal-title" id="attModalTitle">Session Attendance Roster</span>
                    <span id="attModalSub" style="font-size: 12px; color: var(--text-3); display: block;">High-Press & Set Pieces • Main Ground</span>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('attendanceModal')">&times;</button>
        </div>
        <form onsubmit="event.preventDefault(); alert('Attendance logs updated and synchronized with sports registry!'); closeModal('attendanceModal');">
            <div class="modal-body" style="padding: 12px 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--r); padding: 8px 14px; margin-bottom: 12px;">
                    <div style="font-size: 12.5px; color: var(--text-2);">
                        Target Sport: <strong id="attSportLabel" style="color: var(--text);">Football</strong> &nbsp;|&nbsp; 
                        Session ID: <strong id="attSessionId" style="font-family: monospace; color: var(--accent-dark);">TRN-201</strong>
                    </div>
                    <div style="display: flex; gap: 6px;">
                        <button type="button" class="btn btn-outline btn-sm" onclick="markAllAttendance('Present')">Mark All Present</button>
                    </div>
                </div>

                <div style="max-height: 380px; overflow-y: auto; border: 1px solid var(--border); border-radius: 8px;">
                    <table class="dash-table data-table" style="font-size: 13px;">
                        <thead>
                            <tr>
                                <th>Jersey & Name</th>
                                <th>Faculty</th>
                                <th>Position</th>
                                <th>Med Status</th>
                                <th style="text-align: center;">Attendance Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mockAthletes as $i => $ath): 
                                $medClass = ($ath['med_status'] === 'Fit') ? 'badge-success' : (($ath['med_status'] === 'Under Review') ? 'badge-warning' : 'badge-danger');
                            ?>
                            <tr>
                                <td>
                                    <div class="cell-flex">
                                        <span class="mini-avatar" style="background: var(--side-bg); color: #fff; font-size: 11px;">
                                            <?= htmlspecialchars($ath['jersey']) ?>
                                        </span>
                                        <div>
                                            <span class="cell-main"><?= htmlspecialchars($ath['name']) ?></span>
                                            <span class="cell-sub"><?= htmlspecialchars($ath['id']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge badge-subtle"><?= htmlspecialchars($ath['faculty']) ?></span></td>
                                <td><span style="font-weight: 500;"><?= htmlspecialchars($ath['position']) ?></span></td>
                                <td>
                                    <span class="badge <?= $medClass ?>"><?= htmlspecialchars($ath['med_status']) ?></span>
                                </td>
                                <td style="text-align: center;">
                                    <div class="attendance-radio-group">
                                        <label>
                                            <input type="radio" name="att_status_<?= $i ?>" value="Present" <?= ($ath['default_attendance'] === 'Present') ? 'checked' : '' ?>>
                                            <span>Present</span>
                                        </label>
                                        <label>
                                            <input type="radio" name="att_status_<?= $i ?>" value="Absent" <?= ($ath['default_attendance'] === 'Absent') ? 'checked' : '' ?>>
                                            <span>Absent</span>
                                        </label>
                                        <label>
                                            <input type="radio" name="att_status_<?= $i ?>" value="Excused" <?= ($ath['default_attendance'] === 'Excused') ? 'checked' : '' ?>>
                                            <span>Excused</span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('attendanceModal')">Close</button>
                <button type="submit" class="btn btn-teal">
                    <span class="material-symbols-outlined" style="font-size: 16px;">save</span>
                    Save Attendance
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

// Open Attendance Modal with session payload
function openAttendanceModal(sess) {
    document.getElementById('attModalTitle').textContent = 'Session Attendance Roster (' + sess.id + ')';
    document.getElementById('attModalSub').textContent = sess.focus_area + ' • ' + sess.venue + ' (' + sess.display_date + ')';
    document.getElementById('attSessionId').textContent = sess.id;
    document.getElementById('attSportLabel').textContent = sess.sport;
    openModal('attendanceModal');
}

// Mark All Present shortcut
function markAllAttendance(status) {
    var radios = document.querySelectorAll('#attendanceModal input[type="radio"][value="' + status + '"]');
    radios.forEach(function(r) { r.checked = true; });
}

// Live Search Filter
function filterSessionTable() {
    var q = document.getElementById('searchSessionInput').value.toLowerCase();
    var rows = document.querySelectorAll('#sessionsTable tbody tr');
    rows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.indexOf(q) > -1 ? '' : 'none';
    });
}

// Status Filter Tabs
function filterSessions(status, btn) {
    var pills = document.querySelectorAll('#sessionFilterPills .tab-btn');
    pills.forEach(function(p) { p.classList.remove('active'); });
    btn.classList.add('active');

    var rows = document.querySelectorAll('#sessionsTable tbody tr');
    rows.forEach(function(row) {
        if (status === 'all') {
            row.style.display = '';
        } else {
            row.style.display = (row.dataset.status === status) ? '' : 'none';
        }
    });
}
</script>

</body>
</html>
