<?php
require_once __DIR__ . '/../config/auth_guard.php';
requireRole('Coach');

require_once __DIR__ . '/../models/Coach.php';
$coachModel = new Coach();

$stats = $coachModel->getStats();
$trainingSessions = $coachModel->getTrainingSessions();
$allSports = $coachModel->getAllSports();
$allFacilities = $coachModel->getAllFacilities();

$selectedSessionId = isset($_GET['session_id']) ? (int)$_GET['session_id'] : ($trainingSessions[0]['session_id'] ?? 1);
$attendanceRoster = $coachModel->getSessionAttendance($selectedSessionId);

$pageTitle = 'Training Schedule & Attendance Tracker';
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
            font-size: 13.5px;
        }
        .dash-table thead th {
            background: var(--side-bg);
            color: #CBD5E1;
            padding: 14px 16px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
            text-align: left;
            vertical-align: middle;
        }
        .dash-table tbody td {
            padding: 14px 16px;
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
            background: #F1F5F9;
            color: #475467;
            border-color: #94A3B8;
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

        <?php if (!empty($flashSuccess)): ?>
            <div style="background:#F0FDF4; border:1px solid #BBF7D0; color:#16A34A; padding:10px 14px; border-radius:8px; margin-bottom:14px; font-size:13.5px; display:flex; align-items:center; justify-content:space-between; gap:8px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span class="material-symbols-outlined" style="font-size:18px;">check_circle</span>
                    <span><?= htmlspecialchars($flashSuccess) ?></span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; cursor:pointer; font-size:16px; color:#16A34A; padding:0 4px;" aria-label="Close">✕</button>
            </div>
        <?php endif; ?>
        <?php if (!empty($flashError)): ?>
            <div style="background:#FFFBEB; border:1px solid #FDE68A; color:#B45309; padding:10px 14px; border-radius:8px; margin-bottom:14px; font-size:13.5px; display:flex; align-items:center; justify-content:space-between; gap:8px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span class="material-symbols-outlined" style="font-size:18px;">warning</span>
                    <span><?= htmlspecialchars($flashError) ?></span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; cursor:pointer; font-size:16px; color:#B45309; padding:0 4px;" aria-label="Close">✕</button>
            </div>
        <?php endif; ?>

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
                    <div class="stat-value" style="color: #D97706;"><?= $stats['medically_inactive'] ?></div>
                </div>
                <div class="stat-icon" style="color: #D97706; background: #FFFBEB;">
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
                        <?php if (empty($trainingSessions)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 48px 20px;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                    <div style="width: 56px; height: 56px; border-radius: 50%; background: #F1F5F9; color: var(--text-3); display: flex; align-items: center; justify-content: center; margin-bottom: 14px;">
                                        <span class="material-symbols-outlined" style="font-size: 28px;">calendar_today</span>
                                    </div>
                                    <h4 style="font-size: 16px; font-weight: 700; color: var(--text-1); margin-bottom: 6px;">No Training Sessions Scheduled</h4>
                                    <p style="font-size: 13px; color: var(--text-3); max-width: 420px; line-height: 1.5; margin-bottom: 16px;">
                                        There are currently no team practice sessions on the calendar. Use the button above to schedule your squad's next tactical drill or fitness session.
                                    </p>
                                    <button type="button" class="btn btn-teal" onclick="openModal('newSessionModal')">
                                        <span class="material-symbols-outlined" style="font-size: 18px;">event</span> Schedule First Session
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($trainingSessions as $sess): 
                            $statusBadge = ($sess['status'] === 'Completed') ? 'badge-completed' : 'badge-scheduled';
                            $sessJson = htmlspecialchars(json_encode($sess), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr data-status="<?= htmlspecialchars($sess['status']) ?>">
                            <td>
                                <span class="cell-mono" style="font-weight: 700; color: var(--text);"><?= htmlspecialchars($sess['id']) ?></span>
                            </td>
                            <td>
                                <span class="cell-main" style="font-weight: 600;"><?= htmlspecialchars($sess['display_date']) ?></span>
                            </td>
                            <td>
                                <div class="cell-flex">
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
                                <span class="cell-main" style="font-weight: 600;"><?= htmlspecialchars($sess['focus_area']) ?></span>
                                <?php if (!empty($sess['notes'])): ?>
                                    <span class="cell-sub" style="color: var(--text-3);"><?= htmlspecialchars($sess['notes']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 12.5px; font-weight: 700; color: var(--text);">
                                        <?= $sess['attendance_ratio'] ?>
                                    </span>
                                    <?php if ($sess['attendance_pct'] > 0): ?>
                                    <span style="font-size: 11px; color: var(--text-3); font-weight: 600;">
                                        (<?= $sess['attendance_pct'] ?>%)
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($sess['attendance_pct'] > 0): ?>
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar-fill" style="width: <?= $sess['attendance_pct'] ?>%;"></div>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $statusBadge ?>">
                                    <?= htmlspecialchars($sess['status']) ?>
                                </span>
                            </td>
                            <td style="text-align: right; padding-right: 18px;">
                                <div class="cell-actions" style="display: flex; justify-content: flex-end; gap: 6px; align-items: center;">
                                    <button type="button" class="btn btn-teal btn-sm" onclick='openAttendanceModal(<?= $sessJson ?>)' title="Log or View Attendance">
                                        <span class="material-symbols-outlined" style="font-size: 15px;">checklist</span>
                                        <?= ($sess['status'] === 'Completed') ? 'Roster' : 'Attendance' ?>
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm" onclick='openEditModal(<?= $sessJson ?>)' title="Edit Session Details">
                                        <span class="material-symbols-outlined" style="font-size: 14px;">edit</span> Edit
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick='openDeleteModal(<?= $sessJson ?>)' style="padding: 5px 8px;" title="Cancel / Delete Session">
                                        <span class="material-symbols-outlined" style="font-size: 15px;">delete</span>
                                    </button>
                                </div>
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
        <form action="../controllers/CoachController.php?action=schedule_session" method="POST" onsubmit="return validateNewSessionForm(event)">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Sport Discipline *</label>
                        <select name="sport_id" class="form-select" required>
                            <option value="" selected disabled>Select Sport Discipline…</option>
                            <?php foreach ($allSports as $sp): ?>
                                <option value="<?= $sp['sport_id'] ?>">
                                    <?= htmlspecialchars($sp['sport_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Venue / Facility *</label>
                        <select name="facility_id" class="form-select" required>
                            <option value="" selected disabled>Select Venue / Facility…</option>
                            <?php foreach ($allFacilities as $fac): ?>
                                <option value="<?= $fac['facility_id'] ?>">
                                    <?= htmlspecialchars($fac['facility_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Session Date *</label>
                        <input type="date" id="newSessionDate" name="session_date" class="form-input" 
                               value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required 
                               onchange="validateSessionDateTimeInputs('newSessionDate', 'newSessionTime')">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Start Time *</label>
                        <input type="time" id="newSessionTime" name="start_time" class="form-input" 
                               value="<?= date('H:00', strtotime('+1 hour')) ?>" required 
                               onchange="validateSessionDateTimeInputs('newSessionDate', 'newSessionTime')">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Duration *</label>
                        <select name="duration_minutes" class="form-select" required>
                            <option value="" selected disabled>Select Duration…</option>
                            <option value="01:00:00">1 Hour</option>
                            <option value="01:30:00">1.5 Hours (90 mins)</option>
                            <option value="02:00:00">2 Hours</option>
                            <option value="02:30:00">2.5 Hours</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Session Type *</label>
                        <select name="session_type" class="form-select" required>
                            <option value="" selected disabled>Select Session Type…</option>
                            <option value="Practice">Team Practice</option>
                            <option value="Tactical Drill">Tactical Drill</option>
                            <option value="Conditioning">Strength & Conditioning</option>
                            <option value="Trial Match">Internal Trial Match</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tactical Focus Area</label>
                    <input type="text" name="focus_area" class="form-input" placeholder="e.g. Set Pieces, Counter-Pressing, Zone Defense…" value="">
                </div>

                <div class="form-group">
                    <label class="form-label">Session Notes & Equipment Needs</label>
                    <textarea name="notes" class="form-textarea" placeholder="Detailed coaching notes, required cones, bibs, water bottles…" rows="3"></textarea>
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

<!-- MODAL: Edit Training Session Modal (UPDATE) -->
<div class="modal-overlay" id="editSessionModal">
    <div class="modal" style="width: 520px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-outlined" style="color: var(--accent); font-size: 22px;">edit_calendar</span>
                <span class="modal-title" id="editModalHeading">Edit Training Session</span>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('editSessionModal')">&times;</button>
        </div>
        <form action="../controllers/CoachController.php?action=update_session" method="POST" onsubmit="return validateEditSessionForm(event)">
            <input type="hidden" name="session_id" id="editSessionId">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Sport Discipline</label>
                        <select name="sport_id" id="editSportId" class="form-select" required>
                            <?php foreach ($allSports as $sp): ?>
                                <option value="<?= $sp['sport_id'] ?>">
                                    <?= htmlspecialchars($sp['sport_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Venue / Facility</label>
                        <select name="facility_id" id="editFacilityId" class="form-select" required>
                            <?php foreach ($allFacilities as $fac): ?>
                                <option value="<?= $fac['facility_id'] ?>">
                                    <?= htmlspecialchars($fac['facility_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Session Date</label>
                        <input type="date" name="session_date" id="editSessionDate" class="form-input" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" id="editStartTime" class="form-input" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Duration (HH:MM)</label>
                        <input type="text" name="duration_minutes" id="editDuration" class="form-input" value="01:30:00" placeholder="01:30:00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Session Type</label>
                        <select name="session_type" id="editSessionType" class="form-select">
                            <option value="Practice">Practice</option>
                            <option value="Conditioning">Conditioning</option>
                            <option value="Match Simulation">Match Simulation</option>
                            <option value="Recovery">Recovery</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tactical Focus Area</label>
                    <input type="text" name="focus_area" id="editFocusArea" class="form-input" placeholder="e.g. Set Pieces, Counter-Pressing…" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Session Notes & Equipment</label>
                    <textarea name="notes" id="editNotes" class="form-textarea" rows="3" placeholder="Coaching guidelines, required gear…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editSessionModal')">Cancel</button>
                <button type="submit" class="btn btn-teal">
                    <span class="material-symbols-outlined" style="font-size: 16px;">save</span>
                    Update Session
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Delete Session Confirmation Modal (DELETE) -->
<div class="modal-overlay" id="deleteSessionModal">
    <div class="modal" style="width: 440px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-outlined" style="color: #D97706; font-size: 22px;">warning</span>
                <span class="modal-title">Cancel Training Session</span>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('deleteSessionModal')">&times;</button>
        </div>
        <form action="../controllers/CoachController.php?action=delete_session" method="POST">
            <input type="hidden" name="session_id" id="deleteSessionId">
            <div class="modal-body">
                <p style="font-size: 14px; color: var(--text-1); line-height: 1.5; margin-bottom: 12px;">
                    Are you sure you want to permanently cancel and remove <strong id="deleteSessionLabel">this session</strong>?
                </p>
                <div style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 6px; padding: 10px 14px; font-size: 12.5px; color: #B45309; line-height: 1.4;">
                    <span class="material-symbols-outlined" style="font-size: 15px; vertical-align: middle;">info</span>
                    All recorded attendance logs for this session will also be deleted from the database.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('deleteSessionModal')">Keep Session</button>
                <button type="submit" class="btn btn-danger">
                    <span class="material-symbols-outlined" style="font-size: 16px;">delete_forever</span>
                    Confirm Cancellation
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
        <form action="../controllers/CoachController.php?action=save_attendance" method="POST">
            <input type="hidden" name="session_id" id="formAttSessionId" value="<?= $selectedSessionId ?>">
            <div class="modal-body" style="padding: 12px 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--r); padding: 8px 14px; margin-bottom: 12px;">
                    <div style="font-size: 12.5px; color: var(--text-2);">
                        Target Sport: <strong id="attSportLabel" style="color: var(--text);"><?= htmlspecialchars($trainingSessions[0]['sport'] ?? 'Football') ?></strong> &nbsp;|&nbsp; 
                        Session ID: <strong id="attSessionId" style="font-family: monospace; color: var(--accent-dark);"><?= htmlspecialchars($trainingSessions[0]['id'] ?? 'TRN-' . $selectedSessionId) ?></strong>
                    </div>
                    <div style="display: flex; gap: 6px;">
                        <button type="button" class="btn btn-outline btn-sm" onclick="markAllAttendance('Present')">Mark All Present</button>
                    </div>
                </div>

                <div style="max-height: 380px; overflow-y: auto; border: 1px solid var(--border); border-radius: 8px;">
                    <table class="dash-table data-table" style="font-size: 13px;">
                        <thead>
                            <tr>
                                <th>Athlete & Reg No</th>
                                <th>Faculty</th>
                                <th>Medical Status</th>
                                <th style="text-align: center;">Attendance Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendanceRoster as $ath): 
                                $medClass = ($ath['medical_status'] === 'Fit to Compete') ? 'badge-success' : (($ath['medical_status'] === 'Conditional Clearance') ? 'badge-warning' : 'badge-danger');
                                $currentStatus = $ath['status'] ?? 'Present';
                            ?>
                            <tr>
                                <td>
                                    <div class="cell-flex">
                                        <span class="mini-avatar" style="background: var(--side-bg); color: #fff; font-size: 11px;">
                                            <?= htmlspecialchars(substr($ath['full_name'], 0, 2)) ?>
                                        </span>
                                        <div>
                                            <span class="cell-main"><?= htmlspecialchars($ath['full_name']) ?></span>
                                            <span class="cell-sub"><?= htmlspecialchars($ath['index_number']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge badge-subtle"><?= htmlspecialchars($ath['faculty']) ?></span></td>
                                <td>
                                    <span class="badge <?= $medClass ?>"><?= htmlspecialchars($ath['medical_status']) ?></span>
                                </td>
                                <td style="text-align: center;">
                                    <div class="attendance-radio-group">
                                        <label>
                                            <input type="radio" name="attendance[<?= $ath['athlete_id'] ?>]" value="Present" <?= ($currentStatus === 'Present') ? 'checked' : '' ?>>
                                            <span>Present</span>
                                        </label>
                                        <label>
                                            <input type="radio" name="attendance[<?= $ath['athlete_id'] ?>]" value="Absent" <?= ($currentStatus === 'Absent') ? 'checked' : '' ?>>
                                            <span>Absent</span>
                                        </label>
                                        <label>
                                            <input type="radio" name="attendance[<?= $ath['athlete_id'] ?>]" value="Excused" <?= ($currentStatus === 'Excused') ? 'checked' : '' ?>>
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

// Open Edit Modal with session payload (CRUD: UPDATE)
function openEditModal(sess) {
    document.getElementById('editSessionId').value = sess.session_id || '';
    document.getElementById('editModalHeading').textContent = 'Edit Session (' + (sess.id || ('TRN-' + sess.session_id)) + ')';
    if (sess.sport_id) document.getElementById('editSportId').value = sess.sport_id;
    if (sess.facility_id) document.getElementById('editFacilityId').value = sess.facility_id;
    if (sess.session_date) document.getElementById('editSessionDate').value = sess.session_date;
    if (sess.start_time) document.getElementById('editStartTime').value = sess.start_time;
    if (sess.duration_minutes) document.getElementById('editDuration').value = sess.duration_minutes;
    if (sess.session_type) document.getElementById('editSessionType').value = sess.session_type;
    document.getElementById('editFocusArea').value = sess.focus_area || '';
    document.getElementById('editNotes').value = sess.notes || '';
    openModal('editSessionModal');
}

// Open Delete Modal with session payload (CRUD: DELETE)
function openDeleteModal(sess) {
    document.getElementById('deleteSessionId').value = sess.session_id || '';
    document.getElementById('deleteSessionLabel').textContent = (sess.id || ('TRN-' + sess.session_id)) + ' on ' + (sess.display_date || sess.session_date);
    openModal('deleteSessionModal');
}

// Open Attendance Modal with session payload
function openAttendanceModal(sess) {
    if (sess.session_id && <?= (int)$selectedSessionId ?> !== parseInt(sess.session_id)) {
        window.location.href = 'coach_training_sessions.php?session_id=' + sess.session_id + '#attendance';
        return;
    }
    document.getElementById('attModalTitle').textContent = 'Session Attendance Roster (' + (sess.id || ('TRN-' + sess.session_id)) + ')';
    document.getElementById('attModalSub').textContent = (sess.focus_area || '') + ' • ' + (sess.venue || '') + ' (' + (sess.display_date || '') + ')';
    document.getElementById('attSessionId').textContent = sess.id || ('TRN-' + sess.session_id);
    document.getElementById('attSportLabel').textContent = sess.sport || 'Sports Team';
    var formIdInput = document.getElementById('formAttSessionId');
    if (formIdInput) {
        formIdInput.value = sess.session_id;
    }
    openModal('attendanceModal');
}

window.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash === '#attendance') {
        openModal('attendanceModal');
    }
});

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

function validateSessionDateTimeInputs(dateFieldId, timeFieldId) {
    var dInput = document.getElementById(dateFieldId);
    var tInput = document.getElementById(timeFieldId);
    if (!dInput || !tInput) return true;

    var now = new Date();
    var year = now.getFullYear();
    var month = String(now.getMonth() + 1).padStart(2, '0');
    var day = String(now.getDate()).padStart(2, '0');
    var todayStr = year + '-' + month + '-' + day;

    if (dInput.value < todayStr) {
        showToast('Cannot select a past date for a training session.', 'warning', 'Invalid Date');
        dInput.value = todayStr;
        return false;
    }

    if (dInput.value === todayStr) {
        var currentHours = String(now.getHours()).padStart(2, '0');
        var currentMinutes = String(now.getMinutes()).padStart(2, '0');
        var currentTimeStr = currentHours + ':' + currentMinutes;
        if (tInput.value < currentTimeStr) {
            showToast('Session start time cannot be earlier than current time for today.', 'warning', 'Invalid Time');
            now.setHours(now.getHours() + 1);
            var nextH = String(now.getHours()).padStart(2, '0');
            tInput.value = nextH + ':00';
            return false;
        }
    }
    return true;
}

function validateNewSessionForm(e) {
    if (!validateSessionDateTimeInputs('newSessionDate', 'newSessionTime')) {
        e.preventDefault();
        return false;
    }
    return true;
}

function validateEditSessionForm(e) {
    if (!validateSessionDateTimeInputs('editSessionDate', 'editStartTime')) {
        e.preventDefault();
        return false;
    }
    return true;
}
</script>
<script src="../assets/js/dashboard.js"></script>
</body>
</html>
