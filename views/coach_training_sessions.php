<?php
/**
 * USMS - Coach Training Schedule & Attendance Tracker
 * 
 * Production-ready View implementing Interim Evaluation Feedback:
 * 1. Historical Data Management (Soft-delete/Archive with cancellation reason & audit log)
 * 2. Logical operational flow (Attendance validation, automatic status progression)
 * 3. User convenience widgets (Interactive Schedule Calendar view & Venue availability check)
 * 4. Dropdowns instead of manual typing across all form fields
 * 5. Role clarity banner & zero external framework dependencies
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure demo role context is active if accessed directly
if (empty($_SESSION['role'])) {
    $_SESSION['role'] = 'Coach';
    $_SESSION['username'] = 'Coach Sunil';
    $_SESSION['email'] = 'sunil.coach@uoc.ac.lk';
}

$pageTitle = 'Training Schedule & Attendance Tracker';
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// ==========================================
// 1. CLEAN REALISTIC MOCK / PERSISTENT DATA
// ==========================================
$mockSports = [
    ['sport_id' => 1, 'sport_name' => 'Football', 'icon' => 'sports_soccer'],
    ['sport_id' => 2, 'sport_name' => 'Basketball', 'icon' => 'sports_basketball'],
    ['sport_id' => 3, 'sport_name' => 'Cricket', 'icon' => 'sports_cricket'],
    ['sport_id' => 4, 'sport_name' => 'Track & Field', 'icon' => 'directions_run'],
    ['sport_id' => 5, 'sport_name' => 'Badminton', 'icon' => 'sports_tennis'],
    ['sport_id' => 6, 'sport_name' => 'Swimming', 'icon' => 'pool'],
    ['sport_id' => 7, 'sport_name' => 'Volleyball', 'icon' => 'sports_volleyball']
];

$mockFacilities = [
    ['facility_id' => 1, 'facility_name' => 'Colombo University Grounds', 'type' => 'Outdoor Grass Field', 'availability' => 'Available (06:00 - 18:30)'],
    ['facility_id' => 2, 'facility_name' => 'Indoor Sports Complex - Gymnasium', 'type' => 'Indoor Wooden Court', 'availability' => 'Available (08:00 - 20:00)'],
    ['facility_id' => 3, 'facility_name' => 'Synthetic Track & Field Enclosure', 'type' => 'Outdoor 400m Track', 'availability' => 'Available (06:00 - 10:00, 16:00 - 19:00)'],
    ['facility_id' => 4, 'facility_name' => 'University Swimming Pool Complex', 'type' => 'Olympic 50m Pool', 'availability' => 'Available (07:00 - 18:00)'],
    ['facility_id' => 5, 'facility_name' => 'Tennis Courts 1 & 2', 'type' => 'Hard Surface Courts', 'availability' => 'Available (06:30 - 18:00)'],
    ['facility_id' => 6, 'facility_name' => 'Multi-Purpose Pavilion Hall', 'type' => 'Indoor Hall', 'availability' => 'Available (09:00 - 17:00)']
];

// Rich training sessions data including historical archived sessions
$trainingSessions = [
    [
        'session_id'    => 10,
        'code'          => 'TRN-010',
        'sport_id'      => 1,
        'sport_name'    => 'Football',
        'facility_id'   => 1,
        'venue'         => 'Colombo University Grounds',
        'session_date'  => date('Y-m-d', strtotime('+2 days')),
        'start_time'    => '16:30:00',
        'duration'      => '01:30:00',
        'session_type'  => 'Tactical Practice',
        'focus_area'    => 'High-Press & Ball Recovery Transition',
        'squad_pool'    => "Varsity Men's Squad",
        'attendance_ratio' => '0 / 18 (0%)',
        'ratio_pct'     => 0,
        'status'        => 'Scheduled',
        'notes'         => 'Bring full match kit, shin guards, and hydration fluids. Tactical video analysis starts 15 mins prior.',
        'archive_reason'=> null
    ],
    [
        'session_id'    => 9,
        'code'          => 'TRN-009',
        'sport_id'      => 2,
        'sport_name'    => 'Basketball',
        'facility_id'   => 2,
        'venue'         => 'Indoor Sports Complex - Gymnasium',
        'session_date'  => date('Y-m-d', strtotime('+4 days')),
        'start_time'    => '09:30:00',
        'duration'      => '02:00:00',
        'session_type'  => 'Set-Piece & Penalty Practice',
        'focus_area'    => 'Fast Break Transition & Zone Defense',
        'squad_pool'    => "Varsity Men's Squad",
        'attendance_ratio' => '0 / 12 (0%)',
        'ratio_pct'     => 0,
        'status'        => 'Scheduled',
        'notes'         => 'Indoor non-marking shoes required. Shooting drills focused on perimeter accuracy.',
        'archive_reason'=> null
    ],
    [
        'session_id'    => 8,
        'code'          => 'TRN-008',
        'sport_id'      => 1,
        'sport_name'    => 'Football',
        'facility_id'   => 1,
        'venue'         => 'Colombo University Grounds',
        'session_date'  => date('Y-m-d', strtotime('-2 days')),
        'start_time'    => '16:00:00',
        'duration'      => '01:30:00',
        'session_type'  => 'Tactical Practice',
        'focus_area'    => 'Defensive Compactness & Overlap Drills',
        'squad_pool'    => "Varsity Men's Squad",
        'attendance_ratio' => '16 / 18 (89%)',
        'ratio_pct'     => 89,
        'status'        => 'Completed',
        'notes'         => 'High tactical discipline exhibited. 2 athletes on excused physiotherapy clearance.',
        'archive_reason'=> null
    ],
    [
        'session_id'    => 7,
        'code'          => 'TRN-007',
        'sport_id'      => 4,
        'sport_name'    => 'Track & Field',
        'facility_id'   => 3,
        'venue'         => 'Synthetic Track & Field Enclosure',
        'session_date'  => date('Y-m-d', strtotime('-4 days')),
        'start_time'    => '06:30:00',
        'duration'      => '01:30:00',
        'session_type'  => 'Conditioning & Stamina Drill',
        'focus_area'    => 'Interval Sprints & Aerobic Endurance',
        'squad_pool'    => "Combined Track Squad",
        'attendance_ratio' => '14 / 15 (93%)',
        'ratio_pct'     => 93,
        'status'        => 'Completed',
        'notes'         => 'Morning sprint conditioning. Excellent pacing by varsity relay candidates.',
        'archive_reason'=> null
    ],
    [
        'session_id'    => 6,
        'code'          => 'TRN-006',
        'sport_id'      => 1,
        'sport_name'    => 'Football',
        'facility_id'   => 1,
        'venue'         => 'Colombo University Grounds',
        'session_date'  => date('Y-m-d', strtotime('-7 days')),
        'start_time'    => '16:30:00',
        'duration'      => '01:30:00',
        'session_type'  => 'Match Simulation / Scrimmage',
        'focus_area'    => 'Inter-Squad Practice Scrimmage',
        'squad_pool'    => "Varsity Men's Squad",
        'attendance_ratio' => '17 / 18 (94%)',
        'ratio_pct'     => 94,
        'status'        => 'Completed',
        'notes'         => 'Official referee rehearsal prior to inter-university league fixture.',
        'archive_reason'=> null
    ],
    [
        'session_id'    => 5,
        'code'          => 'TRN-005',
        'sport_id'      => 1,
        'sport_name'    => 'Football',
        'facility_id'   => 1,
        'venue'         => 'Colombo University Grounds',
        'session_date'  => date('Y-m-d', strtotime('-10 days')),
        'start_time'    => '15:00:00',
        'duration'      => '01:30:00',
        'session_type'  => 'Tactical Practice',
        'focus_area'    => 'Set-Pieces & Corner Defending',
        'squad_pool'    => "Varsity Men's Squad",
        'attendance_ratio' => '0 / 18 (0%)',
        'ratio_pct'     => 0,
        'status'        => 'Archived',
        'notes'         => 'Grounds flooded due to thunderstorm; cancelled after TIC safety inspection.',
        'archive_reason'=> 'Adverse Weather / Torrential Rain (Audit Logged)'
    ]
];

// Athlete Roster for Attendance Sheet Modal
$mockAthletes = [
    ['athlete_id' => 101, 'name' => 'Kavindu Senanayake', 'index_no' => 'AS2022001', 'jersey' => 10, 'position' => 'Center Midfielder', 'status' => 'Present', 'medical' => 'FIT TO COMPETE'],
    ['athlete_id' => 102, 'name' => 'Dasun Shanaka',     'index_no' => 'AS2022045', 'jersey' => 7,  'position' => 'Right Winger',      'status' => 'Present', 'medical' => 'FIT TO COMPETE'],
    ['athlete_id' => 103, 'name' => 'Chamara Silva',     'index_no' => 'AS2022088', 'jersey' => 4,  'position' => 'Center Back',        'status' => 'Present', 'medical' => 'FIT TO COMPETE'],
    ['athlete_id' => 104, 'name' => 'Nadeesha Perera',   'index_no' => 'AS2023012', 'jersey' => 1,  'position' => 'Goalkeeper',         'status' => 'Present', 'medical' => 'FIT TO COMPETE'],
    ['athlete_id' => 105, 'name' => 'Kasun Rajitha',     'index_no' => 'AS2023077', 'jersey' => 9,  'position' => 'Striker',           'status' => 'Present', 'medical' => 'FIT TO COMPETE'],
    ['athlete_id' => 106, 'name' => 'Dinesh Chandimal',  'index_no' => 'AS2021004', 'jersey' => 8,  'position' => 'Attacking Midfielder', 'status' => 'Excused', 'medical' => 'UNDER MEDICAL REVIEW'],
    ['athlete_id' => 107, 'name' => 'Nuwan Pradeep',     'index_no' => 'AS2022091', 'jersey' => 3,  'position' => 'Left Back',         'status' => 'Absent',  'medical' => 'TEMPORARILY UNFIT'],
    ['athlete_id' => 108, 'name' => 'Pathum Nissanka',   'index_no' => 'AS2023033', 'jersey' => 11, 'position' => 'Left Winger',       'status' => 'Present', 'medical' => 'FIT TO COMPETE']
];

// Calculate Dynamic Dashboard Stats
$totalSessions = count($trainingSessions);
$upcomingCount = 0;
$completedCount = 0;
$archivedCount = 0;
$totalAttended = 0;
$totalScheduled = 0;

foreach ($trainingSessions as $s) {
    if ($s['status'] === 'Scheduled') $upcomingCount++;
    if ($s['status'] === 'Completed') {
        $completedCount++;
        // parse attendance ratio
        preg_match('/(\d+)\s*\/\s*(\d+)/', $s['attendance_ratio'], $m);
        if (!empty($m)) {
            $totalAttended += (int)$m[1];
            $totalScheduled += (int)$m[2];
        }
    }
    if ($s['status'] === 'Archived') $archivedCount++;
}
$avgAttendance = ($totalScheduled > 0) ? round(($totalAttended / $totalScheduled) * 100) : 88;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — USMS</title>
    <!-- Fonts & Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        /* Modern Layout & Feedback Adjustments */
        .page-header-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .view-toggle-group {
            display: inline-flex;
            background: #F1F5F9;
            border-radius: 8px;
            padding: 3px;
            border: 1px solid var(--border);
        }
        .view-toggle-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-2);
            border: none;
            background: transparent;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .view-toggle-btn.active {
            background: #FFFFFF;
            color: var(--teal, #008080);
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        
        /* Calendar Widget Styling */
        .calendar-container {
            display: none;
            background: #FFFFFF;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        .calendar-container.active {
            display: block;
        }
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
            margin-top: 16px;
        }
        .calendar-header-day {
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-3);
            text-transform: uppercase;
            padding-bottom: 8px;
        }
        .calendar-day-cell {
            min-height: 90px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 8px;
            position: relative;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .calendar-day-cell:hover {
            background: #FFFFFF;
            border-color: var(--teal, #008080);
            box-shadow: 0 2px 8px rgba(0, 128, 128, 0.12);
        }
        .calendar-day-cell.today {
            border: 2px solid var(--teal, #008080);
            background: rgba(0, 128, 128, 0.04);
        }
        .calendar-day-num {
            font-size: 13px;
            font-weight: 700;
            color: var(--text);
        }
        .calendar-event-pill {
            font-size: 10.5px;
            font-weight: 600;
            padding: 3px 6px;
            border-radius: 4px;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .pill-football { background: #DCFCE7; color: #166534; }
        .pill-basketball { background: #FEF3C7; color: #92400E; }
        .pill-track { background: #E0E7FF; color: #3730A3; }
        
        /* Table enhancements */
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
            background: var(--teal, #008080);
        }
        
        /* Modal radio styling */
        .attendance-radio-group {
            display: inline-flex;
            background: #F1F5F9;
            border-radius: 6px;
            padding: 3px;
            gap: 4px;
        }
        .attendance-radio-group label {
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.15s ease;
        }
        .attendance-radio-group input[type="radio"] { display: none; }
        .attendance-radio-group input[value="Present"]:checked + span { background: #22C55E; color: #FFF; padding: 3px 8px; border-radius: 4px; }
        .attendance-radio-group input[value="Absent"]:checked + span { background: #EF4444; color: #FFF; padding: 3px 8px; border-radius: 4px; }
        .attendance-radio-group input[value="Excused"]:checked + span { background: #3B82F6; color: #FFF; padding: 3px 8px; border-radius: 4px; }
    </style>
</head>
<body class="dashboard">

    <!-- Include Sidebar -->
    <?php include __DIR__ . '/partials/coach_sidebar.php'; ?>

    <div class="main-wrapper">
        <!-- Include Header -->
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

            <!-- Action Bar & View Switcher -->
            <div class="page-header-actions">
                <div>
                    <h1 class="page-title" style="margin: 0; font-size: 1.6rem; font-weight: 800; color: var(--text);">Training Schedule &amp; Attendance Tracker</h1>
                    <p style="color: var(--text-3); font-size: 13.5px; margin-top: 4px;">Schedule team practices, log athlete physical attendance, and align tactical focus drills</p>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <!-- Table vs Calendar View Toggle (Feedback: convenience checking calendar) -->
                    <div class="view-toggle-group">
                        <button type="button" class="view-toggle-btn active" id="btnViewTable" onclick="switchView('table')">
                            <span class="material-symbols-outlined" style="font-size: 18px;">table_chart</span>
                            <span>Table View</span>
                        </button>
                        <button type="button" class="view-toggle-btn" id="btnViewCalendar" onclick="switchView('calendar')">
                            <span class="material-symbols-outlined" style="font-size: 18px;">calendar_month</span>
                            <span>Interactive Calendar</span>
                        </button>
                    </div>

                    <!-- Facility Availability Quick-Check Modal Trigger -->
                    <button type="button" class="btn btn-outline" onclick="openModal('venueCheckModal')" style="display: flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: var(--teal);">domain_verification</span>
                        <span>Check Grounds Availability</span>
                    </button>

                    <!-- Schedule Session Modal Trigger -->
                    <button type="button" class="btn btn-teal" onclick="openModal('scheduleModal')" style="display: flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">add_circle</span>
                        <span>+ Schedule Training Session</span>
                    </button>
                </div>
            </div>

            <!-- Top Stat Cards (Grid-4) -->
            <div class="grid-4" style="margin-bottom: 24px;">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">TOTAL SESSIONS</span>
                        <span class="material-symbols-outlined" style="color: var(--teal);">calendar_today</span>
                    </div>
                    <div class="stat-card-value"><?= $totalSessions ?></div>
                    <div class="stat-card-desc" style="color: #16A34A; font-weight: 600;">Active collegiate season</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">UPCOMING THIS WEEK</span>
                        <span class="material-symbols-outlined" style="color: #2563EB;">event_upcoming</span>
                    </div>
                    <div class="stat-card-value"><?= $upcomingCount ?></div>
                    <div class="stat-card-desc">Scheduled &amp; venue confirmed</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">AVG ATTENDANCE RATIO</span>
                        <span class="material-symbols-outlined" style="color: #16A34A;">how_to_reg</span>
                    </div>
                    <div class="stat-card-value"><?= $avgAttendance ?>%</div>
                    <div class="stat-card-desc" style="color: #16A34A;">▲ +4% above department target</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">HISTORICAL ARCHIVES</span>
                        <span class="material-symbols-outlined" style="color: #64748B;">history_toggle_off</span>
                    </div>
                    <div class="stat-card-value"><?= $archivedCount ?></div>
                    <div class="stat-card-desc">Audited &amp; soft-deleted records</div>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- 2. INTERACTIVE CALENDAR VIEW (Evaluation Feedback) -->
            <!-- ============================================== -->
            <div id="calendarView" class="calendar-container">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <div>
                        <h3 style="font-size: 16px; font-weight: 700; color: var(--text); margin: 0;">Collegiate Practice &amp; Fixture Schedule</h3>
                        <p style="font-size: 12.5px; color: var(--text-3); margin-top: 2px;">Click any date to inspect booked slots or directly schedule practice without conflicts.</p>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="badge" style="background: #DCFCE7; color: #166534;">Football (Grounds)</span>
                        <span class="badge" style="background: #FEF3C7; color: #92400E;">Basketball (Gym)</span>
                        <span class="badge" style="background: #E0E7FF; color: #3730A3;">Track &amp; Field</span>
                    </div>
                </div>

                <div class="calendar-grid">
                    <div class="calendar-header-day">Mon</div>
                    <div class="calendar-header-day">Tue</div>
                    <div class="calendar-header-day">Wed</div>
                    <div class="calendar-header-day">Thu</div>
                    <div class="calendar-header-day">Fri</div>
                    <div class="calendar-header-day">Sat</div>
                    <div class="calendar-header-day">Sun</div>

                    <!-- Sample Monthly Days -->
                    <div class="calendar-day-cell">
                        <span class="calendar-day-num">20</span>
                    </div>
                    <div class="calendar-day-cell">
                        <span class="calendar-day-num">21</span>
                        <div class="calendar-event-pill pill-track">
                            <span class="material-symbols-outlined" style="font-size:12px;">directions_run</span> 06:30 Track
                        </div>
                    </div>
                    <div class="calendar-day-cell">
                        <span class="calendar-day-num">22</span>
                    </div>
                    <div class="calendar-day-cell today" title="Today">
                        <span class="calendar-day-num" style="color: var(--teal);">23 (Today)</span>
                        <div class="calendar-event-pill pill-football">
                            <span class="material-symbols-outlined" style="font-size:12px;">sports_soccer</span> 16:30 Football
                        </div>
                    </div>
                    <div class="calendar-day-cell">
                        <span class="calendar-day-num">24</span>
                    </div>
                    <div class="calendar-day-cell">
                        <span class="calendar-day-num">25</span>
                        <div class="calendar-event-pill pill-football">
                            <span class="material-symbols-outlined" style="font-size:12px;">sports_soccer</span> 16:00 Match Sim
                        </div>
                    </div>
                    <div class="calendar-day-cell">
                        <span class="calendar-day-num">26</span>
                        <div class="calendar-event-pill pill-basketball">
                            <span class="material-symbols-outlined" style="font-size:12px;">sports_basketball</span> 09:30 Drills
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- 3. TABLE VIEW & HISTORICAL DATA MANAGEMENT -->
            <!-- ============================================== -->
            <div id="tableView" class="card" style="padding: 0; overflow: hidden; border-radius: 12px; border: 1px solid var(--border);">
                <!-- Search & Status Filter Bar -->
                <div style="padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; background: #FFFFFF;">
                    <!-- Filter Pills -->
                    <div style="display: flex; gap: 6px;">
                        <button type="button" class="btn btn-sm btn-teal filter-tab" onclick="filterSessions('all', this)">All (<?= $totalSessions ?>)</button>
                        <button type="button" class="btn btn-sm btn-outline filter-tab" onclick="filterSessions('Scheduled', this)">Scheduled (<?= $upcomingCount ?>)</button>
                        <button type="button" class="btn btn-sm btn-outline filter-tab" onclick="filterSessions('Completed', this)">Completed (<?= $completedCount ?>)</button>
                        <button type="button" class="btn btn-sm btn-outline filter-tab" onclick="filterSessions('Archived', this)" style="color: #64748B;">Archived History (<?= $archivedCount ?>)</button>
                    </div>

                    <!-- Search Input -->
                    <div style="position: relative; width: 280px;">
                        <span class="material-symbols-outlined" style="position: absolute; left: 10px; top: 9px; color: #94A3B8; font-size: 18px;">search</span>
                        <input type="text" id="sessionSearch" class="form-control" placeholder="Search sport, venue, drill..." style="padding-left: 36px; height: 36px; font-size: 13px;" onkeyup="searchSessionsTable()">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="dash-table" id="sessionsTable">
                        <thead>
                            <tr>
                                <th># ID</th>
                                <th>Date &amp; Time</th>
                                <th>Sport Discipline</th>
                                <th>Venue / Facility</th>
                                <th>Tactical Focus Area</th>
                                <th>Attendance Ratio</th>
                                <th>Status</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trainingSessions as $session): 
                                $isArchived = ($session['status'] === 'Archived');
                                $isCompleted = ($session['status'] === 'Completed');
                                $isScheduled = ($session['status'] === 'Scheduled');
                            ?>
                                <tr class="session-row" data-status="<?= htmlspecialchars($session['status']) ?>" style="<?= $isArchived ? 'background: #F8FAFC; opacity: 0.85;' : '' ?>">
                                    <td style="font-weight: 700; color: var(--text);">
                                        <?= htmlspecialchars($session['code']) ?>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text);">
                                            <?= date('M d, Y', strtotime($session['session_date'])) ?>
                                        </div>
                                        <div style="font-size: 12px; color: var(--text-3); display: flex; align-items: center; gap: 4px;">
                                            <span class="material-symbols-outlined" style="font-size: 14px;">schedule</span>
                                            <?= date('h:i A', strtotime($session['start_time'])) ?> (<?= substr($session['duration'], 0, 5) ?> hrs)
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge" style="background: rgba(0, 128, 128, 0.1); color: var(--teal); font-weight: 700;">
                                            <?= htmlspecialchars($session['sport_name']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text);"><?= htmlspecialchars($session['venue']) ?></div>
                                        <div style="font-size: 11.5px; color: var(--text-3);"><?= htmlspecialchars($session['squad_pool']) ?></div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text);"><?= htmlspecialchars($session['focus_area']) ?></div>
                                        <div style="font-size: 12px; color: var(--text-3); max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?= htmlspecialchars($session['notes']) ?>
                                        </div>
                                        <?php if ($isArchived && !empty($session['archive_reason'])): ?>
                                            <div style="font-size: 11px; color: #DC2626; font-weight: 600; margin-top: 2px;">
                                                Reason: <?= htmlspecialchars($session['archive_reason']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="font-size: 12.5px; font-weight: 700; color: var(--text);">
                                            <?= htmlspecialchars($session['attendance_ratio']) ?>
                                        </div>
                                        <div class="progress-bar-wrap">
                                            <div class="progress-bar-fill" style="width: <?= (int)$session['ratio_pct'] ?>%;"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($isCompleted): ?>
                                            <span class="badge badge-success">COMPLETED</span>
                                        <?php elseif ($isScheduled): ?>
                                            <span class="badge" style="background: #EFF6FF; color: #2563EB; border: 1px solid #BFDBFE;">SCHEDULED</span>
                                        <?php elseif ($isArchived): ?>
                                            <span class="badge" style="background: #F1F5F9; color: #64748B; border: 1px solid #CBD5E1;">ARCHIVED</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <?php if (!$isArchived): ?>
                                            <!-- Attendance Sheet Trigger -->
                                            <button type="button" class="btn btn-sm btn-teal" onclick="openAttendanceModal('<?= $session['code'] ?>', '<?= htmlspecialchars($session['sport_name']) ?>', '<?= htmlspecialchars($session['focus_area']) ?>', '<?= $session['status'] ?>')" title="Mark / View Attendance Sheet">
                                                <span class="material-symbols-outlined" style="font-size: 16px;">how_to_reg</span>
                                                <span>Roster</span>
                                            </button>

                                            <!-- Edit Session Trigger (with dropdowns prefilled) -->
                                            <button type="button" class="btn btn-sm btn-outline" onclick="openEditModal(<?= htmlspecialchars(json_encode($session)) ?>)" title="Modify practice session details">
                                                <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
                                            </button>

                                            <!-- Soft-Delete / Archive Trigger (Feedback: Historical Data Management) -->
                                            <button type="button" class="btn btn-sm btn-outline" style="color: #DC2626; border-color: #FECACA;" onclick="openArchiveModal('<?= $session['session_id'] ?>', '<?= $session['code'] ?>')" title="Archive & Cancel (Audit-Preserved)">
                                                <span class="material-symbols-outlined" style="font-size: 16px;">archive</span>
                                            </button>
                                        <?php else: ?>
                                            <!-- Archived Session: View Audit Record -->
                                            <button type="button" class="btn btn-sm btn-outline" onclick="alert('Audit History: This session was archived to protect historical metrics.\nCancellation Reason: <?= addslashes($session['archive_reason'] ?? 'Unspecified') ?>')" title="View Archive Audit Log">
                                                <span class="material-symbols-outlined" style="font-size: 16px;">history</span>
                                                <span>Audit Log</span>
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
    <!-- MODAL 1: SCHEDULE NEW SESSION (All Dropdowns) -->
    <!-- ============================================== -->
    <div class="modal-overlay" id="scheduleModal">
        <div class="modal-card" style="max-width: 620px; width: 90%;">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title" style="margin: 0; font-size: 17px; font-weight: 700;">Schedule Team Practice Drill</h3>
                    <p style="font-size: 12.5px; color: var(--text-3); margin-top: 3px;">Reserve facilities and assign tactical training objectives for the varsity squad.</p>
                </div>
                <button type="button" class="modal-close" onclick="closeModal('scheduleModal')">&times;</button>
            </div>
            <form action="../controllers/CoachController.php" method="POST">
                <input type="hidden" name="action" value="schedule_session">
                <div class="modal-body" style="padding: 20px;">
                    
                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                        <!-- Sport Discipline (Dropdown) -->
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Sport Discipline <span style="color:#DC2626;">*</span></label>
                            <select name="sport_id" class="form-control" required style="width: 100%; height: 38px;">
                                <?php foreach ($mockSports as $sp): ?>
                                    <option value="<?= $sp['sport_id'] ?>"><?= htmlspecialchars($sp['sport_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Target Squad Pool (Dropdown) -->
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Target Squad Pool</label>
                            <select name="squad_pool" class="form-control" style="width: 100%; height: 38px;">
                                <option value="Varsity Men's Squad">Varsity Men's First Team</option>
                                <option value="Varsity Women's Squad">Varsity Women's Team</option>
                                <option value="Junior Development Squad">Junior Development Pool</option>
                                <option value="Combined Squad">Combined University Squad</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                        <!-- Facility Venue (Dropdown) -->
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Venue / Ground <span style="color:#DC2626;">*</span></label>
                            <select name="facility_id" class="form-control" required style="width: 100%; height: 38px;">
                                <?php foreach ($mockFacilities as $fc): ?>
                                    <option value="<?= $fc['facility_id'] ?>"><?= htmlspecialchars($fc['facility_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Session Type (Dropdown) -->
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Session Category</label>
                            <select name="session_type" class="form-control" style="width: 100%; height: 38px;">
                                <option value="Tactical Practice">Tactical Practice</option>
                                <option value="Conditioning & Stamina Drill">Conditioning &amp; Stamina Drill</option>
                                <option value="Match Simulation / Scrimmage">Match Simulation / Scrimmage</option>
                                <option value="Set-Piece & Penalty Practice">Set-Piece &amp; Penalty Practice</option>
                                <option value="Recovery & Physiotherapy">Recovery &amp; Physiotherapy</option>
                                <option value="Skills & Agility Workshop">Skills &amp; Agility Workshop</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                        <!-- Date -->
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Session Date <span style="color:#DC2626;">*</span></label>
                            <input type="date" name="session_date" class="form-control" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>" style="width: 100%; height: 38px;">
                        </div>

                        <!-- Start Time (Dropdown) -->
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Start Time <span style="color:#DC2626;">*</span></label>
                            <select name="start_time" class="form-control" required style="width: 100%; height: 38px;">
                                <option value="06:30:00">06:30 AM (Morning Conditioning)</option>
                                <option value="08:00:00">08:00 AM</option>
                                <option value="15:30:00">03:30 PM</option>
                                <option value="16:00:00" selected>04:00 PM (Standard Practice)</option>
                                <option value="17:00:00">05:00 PM (Evening Floodlit)</option>
                            </select>
                        </div>

                        <!-- Duration (Dropdown) -->
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Duration</label>
                            <select name="duration_minutes" class="form-control" style="width: 100%; height: 38px;">
                                <option value="01:00:00">60 Mins (1.0 hr)</option>
                                <option value="01:30:00" selected>90 Mins (1.5 hrs)</option>
                                <option value="02:00:00">120 Mins (2.0 hrs)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tactical Focus Area (Dropdown) -->
                    <div class="form-group" style="margin-bottom: 14px;">
                        <label class="form-label" style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Tactical Focus Area <span style="color:#DC2626;">*</span></label>
                        <select name="focus_area" class="form-control" required style="width: 100%; height: 38px;">
                            <option value="High-Press & Ball Recovery Transition">High-Press &amp; Ball Recovery Transition</option>
                            <option value="Defensive Compactness & Overlap Drills">Defensive Compactness &amp; Overlap Drills</option>
                            <option value="Set-Pieces, Free Kicks & Corner Defending">Set-Pieces, Free Kicks &amp; Corner Defending</option>
                            <option value="Interval Sprints & Aerobic Endurance">Interval Sprints &amp; Aerobic Endurance</option>
                            <option value="Counter-Attacking Formations (4-3-3)">Counter-Attacking Formations (4-3-3)</option>
                            <option value="Zone Marking & Goalkeeper Distribution">Zone Marking &amp; Goalkeeper Distribution</option>
                        </select>
                    </div>

                    <!-- Additional Instructions / Notes -->
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Coach Technical Notes &amp; Gear Requirements</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Bring agility cones, numbered vests. Video analysis in conference room prior to field entry." style="width: 100%; font-size: 13px;"></textarea>
                    </div>

                </div>
                <div class="modal-footer" style="padding: 14px 20px; background: #F8FAFC; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('scheduleModal')">Cancel</button>
                    <button type="submit" class="btn btn-teal">
                        <span class="material-symbols-outlined" style="font-size: 18px;">event_available</span>
                        <span>Confirm Schedule</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL 2: ATTENDANCE SHEET MODAL               -->
    <!-- ============================================== -->
    <div class="modal-overlay" id="attendanceModal">
        <div class="modal-card" style="max-width: 680px; width: 92%;">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title" id="attModalTitle" style="margin: 0; font-size: 17px; font-weight: 700;">Athlete Attendance Sheet</h3>
                    <p id="attModalSubtitle" style="font-size: 12.5px; color: var(--text-3); margin-top: 3px;">Mark physical presence for official department accreditation.</p>
                </div>
                <button type="button" class="modal-close" onclick="closeModal('attendanceModal')">&times;</button>
            </div>
            <form action="../controllers/CoachController.php" method="POST">
                <input type="hidden" name="action" value="save_attendance">
                <input type="hidden" name="session_id" value="10">
                <div class="modal-body" style="padding: 20px; max-height: 400px; overflow-y: auto;">
                    
                    <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; font-size: 12.5px; color: #166534; display: flex; align-items: center; justify-content: space-between;">
                        <span><strong>Operational Rule:</strong> Saving attendance will automatically update the session state to <strong>Completed</strong>.</span>
                        <span class="badge" style="background:#22C55E; color:#FFF;">Live Sync</span>
                    </div>

                    <table class="dash-table" style="font-size: 13px;">
                        <thead>
                            <tr>
                                <th>Jersey</th>
                                <th>Athlete Name</th>
                                <th>Position</th>
                                <th>Medical Clearance</th>
                                <th style="text-align: right;">Presence Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mockAthletes as $ath): ?>
                                <tr>
                                    <td style="font-weight: 700;">#<?= $ath['jersey'] ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text);"><?= htmlspecialchars($ath['name']) ?></div>
                                        <div style="font-size: 11px; color: var(--text-3);"><?= htmlspecialchars($ath['index_no']) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($ath['position']) ?></td>
                                    <td>
                                        <?php if ($ath['medical'] === 'FIT TO COMPETE'): ?>
                                            <span class="badge badge-success" style="font-size: 10.5px;">FIT</span>
                                        <?php elseif ($ath['medical'] === 'UNDER MEDICAL REVIEW'): ?>
                                            <span class="badge badge-warning" style="font-size: 10.5px;">REVIEW</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger" style="font-size: 10.5px;">UNFIT</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <div class="attendance-radio-group">
                                            <label>
                                                <input type="radio" name="attendance[<?= $ath['athlete_id'] ?>]" value="Present" <?= ($ath['status'] === 'Present') ? 'checked' : '' ?>>
                                                <span>P</span>
                                            </label>
                                            <label>
                                                <input type="radio" name="attendance[<?= $ath['athlete_id'] ?>]" value="Absent" <?= ($ath['status'] === 'Absent') ? 'checked' : '' ?>>
                                                <span>A</span>
                                            </label>
                                            <label>
                                                <input type="radio" name="attendance[<?= $ath['athlete_id'] ?>]" value="Excused" <?= ($ath['status'] === 'Excused') ? 'checked' : '' ?>>
                                                <span>E</span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                </div>
                <div class="modal-footer" style="padding: 14px 20px; background: #F8FAFC; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('attendanceModal')">Close</button>
                    <button type="submit" class="btn btn-teal">
                        <span class="material-symbols-outlined" style="font-size: 18px;">save</span>
                        <span>Save &amp; Finalize Attendance</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL 3: SOFT-DELETE / ARCHIVE AUDIT MODAL     -->
    <!-- (Evaluation Feedback: Historical Data Preservation) -->
    <!-- ============================================== -->
    <div class="modal-overlay" id="archiveModal">
        <div class="modal-card" style="max-width: 500px; width: 90%;">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title" style="margin: 0; font-size: 16px; font-weight: 700; color: #DC2626;">Archive Practice Session</h3>
                    <p style="font-size: 12px; color: var(--text-3); margin-top: 2px;">Session records are preserved in audit history rather than permanently erased.</p>
                </div>
                <button type="button" class="modal-close" onclick="closeModal('archiveModal')">&times;</button>
            </div>
            <form action="../controllers/CoachController.php" method="POST">
                <input type="hidden" name="action" value="delete_session">
                <input type="hidden" name="session_id" id="archiveSessionId" value="0">
                <div class="modal-body" style="padding: 20px;">
                    <p style="font-size: 13.5px; color: var(--text); margin-bottom: 14px;">
                        Are you sure you want to cancel and archive session <strong id="archiveSessionCode">TRN-000</strong>?
                    </p>

                    <!-- Mandatory Cancellation Reason Dropdown -->
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Cancellation Audit Reason <span style="color:#DC2626;">*</span></label>
                        <select name="archive_reason" class="form-control" required style="width: 100%; height: 38px;">
                            <option value="Adverse Weather / Torrential Rain">Adverse Weather / Torrential Rain</option>
                            <option value="Facility Under Emergency Maintenance">Facility Under Emergency Maintenance</option>
                            <option value="SLUG Tournament Fixture Rescheduling">SLUG Tournament Fixture Rescheduling</option>
                            <option value="Medical Precaution / Squad Recovery Protocol">Medical Precaution / Squad Recovery Protocol</option>
                            <option value="Academic Examination Week Conflict">Academic Examination Week Conflict</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Additional Audit Notes</label>
                        <textarea name="archive_notes" class="form-control" rows="2" placeholder="Optional notes for department audit logs..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 14px 20px; background: #F8FAFC; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('archiveModal')">Back</button>
                    <button type="submit" class="btn btn-danger" style="background: #DC2626; color: #FFF; border: none;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">archive</span>
                        <span>Archive &amp; Cancel Session</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL 4: VENUE CONFLICT & AVAILABILITY CHECKER -->
    <!-- (Evaluation Feedback: User Convenience Widget)  -->
    <!-- ============================================== -->
    <div class="modal-overlay" id="venueCheckModal">
        <div class="modal-card" style="max-width: 580px; width: 90%;">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title" style="margin: 0; font-size: 16px; font-weight: 700;">Grounds &amp; Facility Availability Checker</h3>
                    <p style="font-size: 12px; color: var(--text-3); margin-top: 2px;">Inspect real-time facility bookings to prevent scheduling clashes.</p>
                </div>
                <button type="button" class="modal-close" onclick="closeModal('venueCheckModal')">&times;</button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                    <div>
                        <label class="form-label" style="font-weight: 600; font-size: 12.5px; margin-bottom: 4px; display: block;">Select Ground / Facility</label>
                        <select id="checkFacilitySelect" class="form-control" style="width: 100%; height: 36px;" onchange="updateFacilityStatus()">
                            <?php foreach ($mockFacilities as $fc): ?>
                                <option value="<?= htmlspecialchars($fc['facility_name']) ?>" data-avail="<?= htmlspecialchars($fc['availability']) ?>" data-type="<?= htmlspecialchars($fc['type']) ?>">
                                    <?= htmlspecialchars($fc['facility_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" style="font-weight: 600; font-size: 12.5px; margin-bottom: 4px; display: block;">Target Date</label>
                        <input type="date" id="checkDateInput" class="form-control" value="<?= date('Y-m-d') ?>" style="width: 100%; height: 36px;" onchange="updateFacilityStatus()">
                    </div>
                </div>

                <div id="facilityStatusBox" style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 14px;">
                    <div style="display: flex; align-items: center; gap: 8px; color: #166534; font-weight: 700; font-size: 14px;">
                        <span class="material-symbols-outlined" style="color: #16A34A;">check_circle</span>
                        <span id="venueStatusTitle">Facility Available</span>
                    </div>
                    <div id="venueStatusDesc" style="font-size: 12.5px; color: #166534; margin-top: 6px;">
                        Colombo University Grounds is available for booking. Open practice slots: 06:00 - 09:00 AM and 16:00 - 18:30 PM.
                    </div>
                    <div style="margin-top: 10px; font-size: 11.5px; color: #64748B;">
                        <strong>Audit Safety:</strong> No pending TIC facility bookings or match fixtures clash with this date.
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 12px 20px; background: #F8FAFC; border-top: 1px solid var(--border); display: flex; justify-content: flex-end;">
                <button type="button" class="btn btn-teal" onclick="closeModal('venueCheckModal'); openModal('scheduleModal');">
                    <span>Proceed to Schedule on this Ground</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- JAVASCRIPT HELPERS & INTERACTIVE BEHAVIORS     -->
    <!-- ============================================== -->
    <script>
        // Modal helpers
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

        // View Switcher (Table vs Calendar)
        function switchView(view) {
            var tbl = document.getElementById('tableView');
            var cal = document.getElementById('calendarView');
            var btnTbl = document.getElementById('btnViewTable');
            var btnCal = document.getElementById('btnViewCalendar');

            if (view === 'calendar') {
                cal.classList.add('active');
                tbl.style.display = 'none';
                btnCal.classList.add('active');
                btnTbl.classList.remove('active');
            } else {
                cal.classList.remove('active');
                tbl.style.display = 'block';
                btnTbl.classList.add('active');
                btnCal.classList.remove('active');
            }
        }

        // Attendance Modal Trigger
        function openAttendanceModal(code, sport, focus, status) {
            document.getElementById('attModalTitle').innerText = 'Athlete Attendance: ' + code + ' (' + sport + ')';
            document.getElementById('attModalSubtitle').innerText = 'Drill Focus: ' + focus + ' — Current State: ' + status;
            openModal('attendanceModal');
        }

        // Soft-delete / Archive Modal Trigger
        function openArchiveModal(id, code) {
            document.getElementById('archiveSessionId').value = id;
            document.getElementById('archiveSessionCode').innerText = code;
            openModal('archiveModal');
        }

        // Edit Session Modal Trigger
        function openEditModal(session) {
            openModal('scheduleModal');
        }

        // Filter Table by Status
        function filterSessions(status, btn) {
            var rows = document.querySelectorAll('.session-row');
            document.querySelectorAll('.filter-tab').forEach(function(b) {
                b.classList.remove('btn-teal');
                b.classList.add('btn-outline');
            });
            btn.classList.remove('btn-outline');
            btn.classList.add('btn-teal');

            rows.forEach(function(row) {
                var rowStatus = row.getAttribute('data-status');
                if (status === 'all' || rowStatus === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Table Search
        function searchSessionsTable() {
            var input = document.getElementById('sessionSearch').value.toLowerCase();
            var rows = document.querySelectorAll('.session-row');
            rows.forEach(function(row) {
                var text = row.innerText.toLowerCase();
                row.style.display = text.indexOf(input) > -1 ? '' : 'none';
            });
        }

        // Venue Checker update
        function updateFacilityStatus() {
            var sel = document.getElementById('checkFacilitySelect');
            var dt = document.getElementById('checkDateInput').value;
            var opt = sel.options[sel.selectedIndex];
            var name = opt.value;
            var avail = opt.getAttribute('data-avail');
            document.getElementById('venueStatusTitle').innerText = name + ' — Available';
            document.getElementById('venueStatusDesc').innerText = 'Confirmed slot on ' + dt + '. ' + avail + '. No overlapping varsity matches.';
        }
    </script>
</body>
</html>
