<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Athlete') {
    header('Location: login.php');
    exit;
}

date_default_timezone_set('Asia/Colombo');
$db = Database::connect();
require_once __DIR__ . '/../models/AthletePortal.php';
$portal = new AthletePortal($db);
$_SESSION['athlete_csrf'] = $_SESSION['athlete_csrf'] ?? bin2hex(random_bytes(32));
function athleteEscape($value) { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8'); }
$stmt = $db->prepare("SELECT a.*, u.email FROM Athletic a JOIN user u ON a.user_id = u.user_id WHERE a.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$athlete = $stmt->fetch();

if (!$athlete) {
    die("Athlete profile not found. Please contact administration.");
}

$fullName = htmlspecialchars($athlete['full_name']);
$nameParts = explode(' ', $athlete['full_name']);
$firstName = htmlspecialchars($nameParts[0]);
$lastName = htmlspecialchars(isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : '');

$indexNumber = htmlspecialchars($athlete['index_number']);
$faculty = htmlspecialchars($athlete['faculty']);
$height = athleteEscape($athlete['height'] ?: 'Not provided');
$weight = athleteEscape($athlete['weight'] ?: 'Not provided');
$academicYear = htmlspecialchars($athlete['academic_year']);
$emergencyContact = htmlspecialchars($athlete['emergency_Contact']);
$email = htmlspecialchars($athlete['email'] ?? '');

$athleteId = $athlete['athlete_id'];

// 1. Training logged
$stmtHours = $db->prepare("
    SELECT SUM(TIME_TO_SEC(ts.duration_minutes)) / 3600 AS total_hours 
    FROM training_attendance ta 
    JOIN training_sessions ts ON ta.session_id = ts.session_id 
    WHERE ta.athlete_id = ? AND ta.status = 'Present'
      AND ts.session_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
      AND TIMESTAMP(ts.session_date, ts.start_time) <= NOW()
");
$stmtHours->execute([$athleteId]);
$trainingHours = round($stmtHours->fetchColumn() ?: 0, 1);

// 2. Pending/Active applications
$stmtApps = $db->prepare("
    SELECT COUNT(*) FROM sport_application 
    WHERE athletic_id = ? AND status = 'Pending'
");
$stmtApps->execute([$athleteId]);
$activeApps = $stmtApps->fetchColumn();

// All dashboard and calendar events use the same athlete-scoped queries.
$now = new DateTimeImmutable();
$upcoming = $portal->schedule($athleteId, $now->format('Y-m-d H:i:s'));
$nextFixture = $upcoming['matches'][0] ?? null;
$upcomingTrainings = array_slice($upcoming['training'], 0, 5);
$weekAhead = $now->modify('+7 days')->format('Y-m-d H:i:s');
$upcomingEvents = count(array_filter(array_merge($upcoming['training'], $upcoming['matches']), function ($event) use ($weekAhead) {
    return $event['event_date'] . ' ' . $event['event_time'] < $weekAhead;
}));
[$weekStart, $weekEnd] = AthletePortal::calendarWeek($_GET['week'] ?? '', $now);
$calendar = $portal->schedule($athleteId, $weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d'));
$calendarDays = [];
foreach ($calendar as $type => $events) {
    foreach ($events as $event) {
        $event['kind'] = $type === 'training' ? 'training' : 'match';
        $calendarDays[$event['event_date']][] = $event;
    }
}
foreach ($calendarDays as &$events) {
    usort($events, function ($a, $b) { return strcmp($a['event_time'], $b['event_time']); });
}
unset($events);
$stmtAwards = $db->prepare('SELECT uc.*, s.sport_name, se.season_year FROM university_colors uc JOIN sports s ON s.sport_id = uc.sport_id JOIN seasons se ON se.season_id = uc.season_id WHERE uc.athelet_id = ? ORDER BY se.season_year DESC, uc.award_id DESC');
$stmtAwards->execute([$athleteId]);
$athleteAwards = $stmtAwards->fetchAll();

// 5. Medical Clearance
$stmtMedical = $db->prepare("
    SELECT mc.*, s.sport_name 
    FROM medical_clearances mc
    LEFT JOIN sports s ON mc.sport_id = s.sport_id
    WHERE mc.athletic_id = ?
    ORDER BY mc.expiry_date DESC, mc.clearance_id DESC
    LIMIT 1
");
$stmtMedical->execute([$athleteId]);
$medicalClearance = $stmtMedical->fetch();

// 7. Sport Application data
require_once __DIR__ . '/../models/SportApplication.php';
$sportAppModel = new SportApplication($db);
$availableSports = $sportAppModel->getAllSports();
$availableSeasons = $sportAppModel->getAllSeasons();
$myApplications = $sportAppModel->getAthleteApplications($athleteId);
$appStats = $sportAppModel->getApplicationStats($athleteId);

// 8. Real Announcements & Notifications
require_once __DIR__ . '/../models/Announcement.php';
$athleteAnnouncements = [];
try {
    $annModel = new Announcement();
    $athleteAnnouncements = $annModel->getActiveForNotifications(6, 'Athlete');
} catch (Exception $e) {
    $athleteAnnouncements = [];
}

// Compute total actionable notifications
$totalAthleteNotifs = count($athleteAnnouncements)
    + ($nextFixture ? 1 : 0)
    + ($medicalClearance ? 1 : 0)
    + ($activeApps > 0 ? 1 : 0);

$currentView = $_GET['view'] ?? 'dashboard-view';
if (!in_array($currentView, ['dashboard-view', 'apply-view', 'applications-view', 'submission-view', 'application-details-view', 'calendar-view', 'profile-view', 'edit-profile-view'], true)) $currentView = 'dashboard-view';
if (!empty($_SESSION['flash_error'])) {
    $currentView = 'apply-view';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Sports Management Dashboard</title>
    <link rel="stylesheet" href="../assets/css/athletic_regestration.css?v=<?= filemtime(__DIR__ . '/../assets/css/athletic_regestration.css') ?>">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web" async></script>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="ph-fill ph-activity logo-icon"></i>
                <div class="logo-text">
                    <h1>USMS</h1>
                    <p>ATHLETE PORTAL</p>
                </div>
            </div>
            <nav class="sidebar-nav">
                <a href="#" class="nav-item <?= $currentView === 'dashboard-view' ? 'active' : '' ?>" data-target="dashboard-view" id="nav-dashboard"><i class="ph ph-squares-four"></i> Dashboard</a>
                <a href="#" class="nav-item <?= in_array($currentView, ['apply-view', 'submission-view']) ? 'active' : '' ?>" data-target="apply-view" id="nav-apply"><i class="ph ph-file-text"></i> Apply for Sport</a>
                <a href="#" class="nav-item <?= in_array($currentView, ['applications-view', 'application-details-view']) ? 'active' : '' ?>" data-target="applications-view" id="nav-applications"><i class="ph ph-list-dashes"></i> My Applications</a>
                <a href="?view=calendar-view" class="nav-item <?= $currentView === 'calendar-view' ? 'active' : '' ?>" data-target="calendar-view" id="nav-calendar"><i class="ph ph-calendar-blank"></i> Training Calendar</a>
                <a href="#" class="nav-item <?= in_array($currentView, ['profile-view', 'edit-profile-view']) ? 'active' : '' ?>" data-target="profile-view" id="nav-profile"><i class="ph ph-user"></i> Profile</a>
            </nav>
            <div class="sidebar-footer">
                <a href="../controllers/AuthController.php?action=logout" class="nav-item"><i class="ph ph-sign-out"></i> Sign Out</a>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <main class="main-content">
            <!-- Header -->
            <header class="top-header">
                <div class="header-title">University Sports Management</div>
                <div class="header-actions">
                    <!-- Notification Bell with Real Dropdown -->
                    <div class="athlete-notif-wrap" style="position: relative;">
                        <button type="button" class="notification-btn" id="athleteNotifBtn" title="System Notifications &amp; Alerts" onclick="toggleAthleteNotif(event)" style="position: relative;">
                            <i class="ph ph-bell"></i>
                            <?php if ($totalAthleteNotifs > 0): ?>
                            <span class="notification-dot" style="position: absolute; top: 0; right: 2px; width: 8px; height: 8px; background: #0099ab; border-radius: 50%; box-shadow: 0 0 0 2px #fff;"></span>
                            <?php endif; ?>
                        </button>

                        <div id="athleteNotifDropdown" class="athlete-notif-dropdown" style="display: none; position: absolute; right: 0; top: calc(100% + 10px); width: 340px; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; z-index: 1000; overflow: hidden; text-align: left;">
                            <div style="padding: 12px 16px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                                <div style="font-weight: 700; font-size: 13px; color: #041427;">Notifications &amp; Alerts</div>
                                <span style="font-size: 11px; padding: 2px 8px; border-radius: 12px; background: #e0f2fe; color: #0284c7; font-weight: 600;"><?= $totalAthleteNotifs ?> Active</span>
                            </div>
                            <div style="max-height: 320px; overflow-y: auto;">
                                <?php if ($totalAthleteNotifs === 0): ?>
                                    <div style="padding: 24px 16px; text-align: center; color: #94a3b8; font-size: 13px;">
                                        <i class="ph ph-bell-slash" style="font-size: 24px; display: block; margin-bottom: 6px; opacity: 0.6;"></i>
                                        All caught up! No new notifications.
                                    </div>
                                <?php else: ?>
                                    <!-- Official System Announcements -->
                                    <?php foreach ($athleteAnnouncements as $ann): ?>
                                    <div class="notif-dropdown-item" style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; display: flex; gap: 10px; align-items: flex-start;">
                                        <div style="width: 30px; height: 30px; border-radius: 6px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px;">
                                            <i class="ph ph-megaphone-simple" style="font-size: 16px;"></i>
                                        </div>
                                        <div style="flex: 1; min-width: 0;">
                                            <div style="font-size: 12.5px; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($ann['title']) ?></div>
                                            <div style="font-size: 11.5px; color: #64748b; line-height: 1.35; margin-top: 2px;"><?= htmlspecialchars(strlen($ann['content'] ?? '') > 75 ? substr($ann['content'], 0, 72) . '...' : ($ann['content'] ?? '')) ?></div>
                                            <div style="font-size: 10px; color: #94a3b8; margin-top: 4px;"><?= date('M d, Y', strtotime($ann['created_at'])) ?></div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>

                                    <!-- Next Match Alert -->
                                    <?php if ($nextFixture): ?>
                                    <div class="notif-dropdown-item" style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; cursor: pointer; display: flex; gap: 10px; align-items: center;" onclick="switchView('dashboard-view'); toggleAthleteNotif(false);">
                                        <div style="width: 30px; height: 30px; border-radius: 6px; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <i class="ph ph-calendar-blank" style="font-size: 16px;"></i>
                                        </div>
                                        <div style="flex: 1; min-width: 0;">
                                            <div style="font-size: 12.5px; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Upcoming Fixture vs <?= htmlspecialchars($nextFixture['opponent']) ?></div>
                                            <div style="font-size: 11px; color: #64748b;"><?= htmlspecialchars($nextFixture['sport_name']) ?> &bull; <?= date('M d, H:i', strtotime($nextFixture['fixture_date'] . ' ' . $nextFixture['fixture_time'])) ?></div>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Medical Clearance Status -->
                                    <?php if ($medicalClearance): ?>
                                    <div class="notif-dropdown-item" style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; cursor: pointer; display: flex; gap: 10px; align-items: center;" onclick="switchView('profile-view'); toggleAthleteNotif(false);">
                                        <div style="width: 30px; height: 30px; border-radius: 6px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <i class="ph ph-first-aid" style="font-size: 16px;"></i>
                                        </div>
                                        <div style="flex: 1; min-width: 0;">
                                            <div style="font-size: 12.5px; font-weight: 600; color: #1e293b;">Medical Clearance Active</div>
                                            <div style="font-size: 11px; color: #64748b;">Valid until <?= htmlspecialchars($medicalClearance['expiry_date']) ?></div>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Active Sport Applications -->
                                    <?php if ($activeApps > 0): ?>
                                    <div class="notif-dropdown-item" style="padding: 10px 14px; cursor: pointer; display: flex; gap: 10px; align-items: center;" onclick="switchView('applications-view'); toggleAthleteNotif(false);">
                                        <div style="width: 30px; height: 30px; border-radius: 6px; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <i class="ph ph-file-text" style="font-size: 16px;"></i>
                                        </div>
                                        <div style="flex: 1; min-width: 0;">
                                            <div style="font-size: 12.5px; font-weight: 600; color: #1e293b;"><?= $activeApps ?> Application(s) Pending</div>
                                            <div style="font-size: 11px; color: #64748b;">Trial evaluation in progress</div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div style="padding: 8px 14px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 11px; color: #94a3b8;">USMS Athlete Hub</span>
                                <a href="#" onclick="switchView('profile-view'); toggleAthleteNotif(false); return false;" style="font-size: 11.5px; color: #0099ab; font-weight: 600; text-decoration: none;">Profile Details &rarr;</a>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile Pill (Navigates to Profile View) -->
                    <div class="user-profile" id="topbar-user-profile" role="button" tabindex="0" title="View &amp; Edit Profile (Click to navigate)" style="cursor: pointer;" onclick="switchView('profile-view')">
                        <div class="user-info">
                            <span class="user-name"><?= $fullName ?></span>
                            <span class="user-role">Varsity Athlete</span>
                        </div>
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($fullName) ?>&background=0099ab&color=fff&size=40" alt="User Avatar" class="avatar">
                    </div>
                </div>
            </header>

            <!-- Views -->
            <div class="views-container">
                
                <!-- Dashboard View -->
                <section id="dashboard-view" class="view-section <?= $currentView === 'dashboard-view' ? 'active' : '' ?>">
                    <div class="dashboard-header">
                        <h2>Welcome back,<br><?= $fullName ?></h2>
                        <button class="btn btn-secondary"><i class="ph ph-squares-four"></i> Full Dashboard View</button>
                    </div>
                    
                    <div class="dashboard-stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="ph ph-clock"></i></div>
                            <div class="stat-content">
                                <h3><?= $trainingHours ?><small>hrs</small></h3>
                                <p>Training logged this month</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon bg-blue-light text-blue"><i class="ph ph-file-text"></i></div>
                            <div class="stat-content">
                                <h3><?= $activeApps ?></h3>
                                <p>Active sport applications</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon bg-purple-light text-purple"><i class="ph ph-calendar-blank"></i></div>
                            <div class="stat-content">
                                <h3><?= $upcomingEvents ?></h3>
                                <p>Events in the next 7 days</p>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-main-grid">
                        <div class="dash-col-left">
                            <div class="card next-match-card">
                                <div class="card-header-dark">
                                    <span class="badge badge-primary"><i class="ph ph-calendar-blank"></i> NEXT FIXTURE</span>
                                    <button class="btn-icon text-white"><i class="ph ph-dots-three"></i></button>
                                </div>
                                <?php if ($nextFixture): ?>
                                <div class="match-teams">
                                    <div class="team">
                                        <div class="team-logo bg-white text-dark"><i class="ph-fill ph-shield-chevron"></i></div>
                                        <p><?= htmlspecialchars($nextFixture['home_team']) ?></p>
                                    </div>
                                    <div class="match-vs">
                                        <h3>VS</h3>
                                        <p><?= date('H:i', strtotime($nextFixture['fixture_time'])) ?></p>
                                        <span class="badge badge-red" style="font-size: 0.7rem; padding: 4px 8px;"><?= date('M d', strtotime($nextFixture['fixture_date'])) ?></span>
                                    </div>
                                    <div class="team">
                                        <div class="team-logo bg-dark text-white"><i class="ph-fill ph-shield"></i></div>
                                        <p><?= htmlspecialchars($nextFixture['opponent']) ?></p>
                                    </div>
                                </div>
                                <div class="match-footer">
                                    <p><i class="ph ph-map-pin"></i> <?= athleteEscape($nextFixture['facility_name'] ?: 'Venue to be confirmed') ?> (<?= htmlspecialchars($nextFixture['sport_name']) ?>)</p>
                                    <a class="btn btn-primary btn-sm" href="?view=calendar-view&amp;week=<?= athleteEscape($nextFixture['fixture_date']) ?>">View Details</a>
                                </div>
                                <?php else: ?>
                                <div class="match-teams" style="justify-content: center; padding: 2rem;">
                                    <p style="color: #a0aec0;">No fixtures scheduled</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="dash-col-right">
                            <div class="card clearance-card">
                                <div class="card-header">
                                    <h3>Medical Clearance</h3>
                                    <button class="btn-icon"><i class="ph ph-funnel"></i></button>
                                </div>
                                <div class="clearance-status">
                                    <?php if ($medicalClearance): ?>
                                        <?php 
                                            $mcStatus = $medicalClearance['status'];
                                            $isCleared = ($mcStatus === 'Cleared');
                                            $isExpired = ($mcStatus === 'Expired' || strtotime($medicalClearance['expiry_date']) < time());
                                            $isBlocked = ($mcStatus === 'Blocked');

                                            if ($isCleared && !$isExpired) {
                                                $circleClass = 'success';
                                                $iconHtml = '<i class="ph-fill ph-check-circle text-teal" style="font-size: 32px;"></i>';
                                                $statusTitle = 'Cleared to Play';
                                                $statusSub = 'Status valid until ' . date('M Y', strtotime($medicalClearance['expiry_date']));
                                            } elseif ($isExpired) {
                                                $circleClass = 'warning';
                                                $iconHtml = '<i class="ph-fill ph-warning-circle text-warning" style="font-size: 32px; color: #ed8936;"></i>';
                                                $statusTitle = 'Medical Expired';
                                                $statusSub = 'Expired on ' . date('M d, Y', strtotime($medicalClearance['expiry_date']));
                                            } else {
                                                $circleClass = 'danger';
                                                $iconHtml = '<i class="ph-fill ph-x-circle text-danger" style="font-size: 32px; color: #e53e3e;"></i>';
                                                $statusTitle = 'Not Cleared (Blocked)';
                                                $statusSub = htmlspecialchars($medicalClearance['notes'] ?? 'Medical review required');
                                            }
                                        ?>
                                        <div class="circular-progress lg <?= $circleClass ?>">
                                            <svg viewBox="0 0 36 36" class="circular-chart">
                                                <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                <path class="circle" stroke-dasharray="100, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                            </svg>
                                            <span><?= $iconHtml ?></span>
                                        </div>
                                        <h4><?= $statusTitle ?></h4>
                                        <p class="text-muted"><?= $statusSub ?></p>
                                    <?php else: ?>
                                        <div class="circular-progress lg warning">
                                            <svg viewBox="0 0 36 36" class="circular-chart">
                                                <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                <path class="circle" stroke-dasharray="0, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                            </svg>
                                            <span><i class="ph-fill ph-question" style="font-size: 32px; color: #a0aec0;"></i></span>
                                        </div>
                                        <h4>No Medical Record</h4>
                                        <p class="text-muted">Please submit medical clearance</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="card upcoming-training-card">
                                <div class="card-header">
                                    <h3>Upcoming Training</h3>
                                    <button class="btn-icon"><i class="ph ph-dots-three"></i></button>
                                </div>
                                <div class="training-list">
                                    <?php if (!empty($upcomingTrainings)): ?>
                                        <?php foreach ($upcomingTrainings as $tSession): ?>
                                            <?php
                                                $sessionDate = strtotime($tSession['session_date']);
                                                $dayStr = date('d', $sessionDate);
                                                $monthStr = date('M', $sessionDate);
                                                $startTimeStr = date('H:i', strtotime($tSession['start_time']));
                                                $isToday = (date('Y-m-d', $sessionDate) === date('Y-m-d'));
                                                
                                                $title = htmlspecialchars($tSession['session_type'] . ' - ' . $tSession['sport_name']);
                                                $location = htmlspecialchars($tSession['facility_name'] ?: 'Venue to be confirmed');
                                            ?>
                                            <div class="training-item">
                                                <div class="training-date">
                                                    <span class="day"><?= $dayStr ?></span>
                                                    <span class="month"><?= $monthStr ?></span>
                                                </div>
                                                <div class="training-details">
                                                    <h4><?= $title ?></h4>
                                                    <p><i class="ph ph-clock"></i> <?= $startTimeStr ?> - <?= $location ?></p>
                                                </div>
                                                <?php if ($isToday): ?>
                                                    <span class="badge badge-teal">Today</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div style="padding: 1.5rem; text-align: center; color: #a0aec0;">
                                            <p>No upcoming training scheduled</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <a class="btn-text full-width" href="?view=calendar-view" style="margin-top: 16px; justify-content: center;">View Full Schedule</a>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Apply for Sport View -->
                <section id="apply-view" class="view-section <?= $currentView === 'apply-view' ? 'active' : '' ?>">
                    <div class="apply-container">
                        <div class="apply-header">
                            <div class="apply-header-text">
                                <h2>Apply</h2>
                                <p>Submit your application to join a university sports program. Ensure your medical clearance is up to date before proceeding.</p>
                                
                                <div class="eligibility-cards">
                                    <div class="elig-card success">
                                        <div class="elig-icon"><i class="ph-fill ph-check-circle"></i></div>
                                        <div class="elig-details">
                                            <h4>Medical Clearance</h4>
                                            <p>Status: <strong>Cleared</strong> (valid until Aug 2024)</p>
                                        </div>
                                    </div>
                                    <div class="elig-card success">
                                        <div class="elig-icon"><i class="ph-fill ph-check-circle"></i></div>
                                        <div class="elig-details">
                                            <h4>Academic Eligibility</h4>
                                            <p>Status: <strong>Eligible</strong> (GPA: 3.5)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <form id="sport-application-form" class="card form-card apply-form" action="../controllers/AthleteController.php?action=submit_application" method="POST">
                                <?php if (!empty($_SESSION['flash_error'])): ?>
                                    <div style="color: #dc3545; background: #ffe6e6; border: 1px solid #f5c2c7; padding: 10px 14px; border-radius: 6px; margin-bottom: 15px; font-size: 0.9rem;">
                                        <?= htmlspecialchars($_SESSION['flash_error']) ?>
                                    </div>
                                    <?php unset($_SESSION['flash_error']); ?>
                                <?php endif; ?>

                                <h3>Program Selection</h3>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="apply-sport-id">SELECT SPORT</label>
                                        <select id="apply-sport-id" name="sport_id" required>
                                            <option value="">Choose a sport program...</option>
                                            <?php foreach ($availableSports as $s): ?>
                                                <option value="<?= htmlspecialchars($s['sport_id']) ?>">
                                                    <?= htmlspecialchars($s['sport_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="apply-season-id">TARGET SEASON</label>
                                        <select id="apply-season-id" name="season_id" required>
                                            <option value="">Choose a season...</option>
                                            <?php foreach ($availableSeasons as $se): ?>
                                                <option value="<?= htmlspecialchars($se['season_id']) ?>">
                                                    Season ID: <?= htmlspecialchars($se['season_id']) ?> (Year: <?= htmlspecialchars($se['season_year']) ?><?= !empty($se['status']) ? ' - ' . htmlspecialchars($se['status']) : '' ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="divider"></div>
                                
                                <h3>Athlete Details</h3>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="apply-athlete-id">ATHLETE ID</label>
                                        <input type="text" id="apply-athlete-id" name="athlete_id" value="<?= htmlspecialchars($athleteId) ?>" readonly style="background-color: var(--card-bg, #f8f9fa); cursor: not-allowed;">
                                        <small class="form-hint" style="color: var(--text-muted); font-size: 0.75rem; margin-top: 4px; display: block;">Linked to Student ID: <?= $indexNumber ?> (<?= $fullName ?>)</small>
                                    </div>
                                </div>
                                
                                <div class="form-group full-width">
                                    <label for="apply-statement">BRIEF STATEMENT OF INTEREST</label>
                                    <textarea id="apply-statement" name="statement_of_interest" rows="4" placeholder="Why do you want to join this program? Describe your sporting background and motivation..."></textarea>
                                </div>
                                
                                <div class="form-group checkbox-group">
                                    <input type="checkbox" id="terms-agree" name="terms_agree" value="1">
                                    <label for="terms-agree">I acknowledge that I have read and agree to the <a href="#">Athlete Code of Conduct</a> and <a href="#">Compliance Guidelines</a>.<br><span class="text-muted">By submitting, I confirm all provided medical and academic records are accurate.</span></label>
                                </div>

                                <div id="apply-error-msg" style="display: none; color: #dc3545; background: #ffe6e6; border: 1px solid #f5c2c7; padding: 10px 14px; border-radius: 6px; margin-bottom: 15px; font-size: 0.9rem;"></div>
                                
                                <div class="form-actions right">
                                    <button class="btn btn-secondary" id="cancel-apply-btn" type="button">Cancel</button>
                                    <button class="btn btn-primary" id="submit-application-btn" type="submit">Submit Application</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>

                <!-- Submission Confirmed View -->
                <section id="submission-view" class="view-section <?= $currentView === 'submission-view' ? 'active' : '' ?>">
                    <div class="submission-card-container">
                        <div class="card submission-card">
                            <div class="success-icon-large">
                                <i class="ph-fill ph-check-circle text-teal"></i>
                            </div>
                            <h2>Application Submitted</h2>
                            <p id="submission-confirm-text">Your application for <strong id="submission-program-title"><?= htmlspecialchars(($_SESSION['last_application']['sport_name'] ?? 'Sport Program') . (!empty($_SESSION['last_application']['season_id']) ? ' - Season ID: ' . $_SESSION['last_application']['season_id'] . ' (' . ($_SESSION['last_application']['season_year'] ?? '') . ')' : '')) ?></strong> has been received and is currently under review by the Sports Council.</p>
                            
                            <div class="reference-block">
                                <p class="label">REFERENCE NUMBER</p>
                                <p class="ref-number" id="submission-ref-code"><?= htmlspecialchars($_SESSION['last_application']['reference_number'] ?? 'APP-2026-0001') ?></p>
                            </div>
                            
                            <div class="submission-actions">
                                <button class="btn btn-primary" id="view-my-apps-btn" type="button">View My Applications</button>
                                <button class="btn btn-secondary" id="back-dash-btn" type="button">Back to Dashboard</button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- My Applications View -->
                <section id="applications-view" data-csrf="<?= athleteEscape($_SESSION['athlete_csrf']) ?>" class="view-section <?= $currentView === 'applications-view' ? 'active' : '' ?>">
                    <div class="page-header-card compact">
                        <div class="header-text">
                            <p class="breadcrumb">ATHLETE PORTAL</p>
                            <h2>My Applications</h2>
                            <p class="text-muted">Review and manage your submitted sport applications. Track your evaluation status and upcoming trial decisions.</p>
                        </div>
                        <button class="btn btn-primary" id="new-app-btn"><i class="ph ph-plus"></i> New Application</button>
                    </div>
                    
                    <div class="app-stats-row">
                        <div class="app-stat-card">
                            <div class="stat-val" id="stat-total-apps"><?= $appStats['total'] ?></div>
                            <div class="stat-label"><span class="dot bg-dark"></span> Total Applications</div>
                        </div>
                        <div class="app-stat-card">
                            <div class="stat-val" id="stat-under-review"><?= $appStats['under_review'] ?></div>
                            <div class="stat-label"><span class="dot bg-teal"></span> Under Review</div>
                        </div>
                        <div class="app-stat-card">
                            <div class="stat-val" id="stat-action-req"><?= $appStats['action_required'] ?></div>
                            <div class="stat-label"><span class="dot bg-red"></span> Action Required</div>
                        </div>
                        <div class="app-stat-card">
                            <div class="stat-val" id="stat-accepted"><?= $appStats['accepted'] ?></div>
                            <div class="stat-label"><span class="dot bg-purple"></span> Accepted/Waitlisted</div>
                        </div>
                    </div>
                    
                    <div class="card table-card">
                        <div class="card-header">
                            <h3>Application History</h3>
                            <div class="search-box">
                                <i class="ph ph-magnifying-glass"></i>
                                <input type="text" id="app-search-input" placeholder="Search applications...">
                            </div>
                        </div>
                        <table class="data-table" id="my-apps-table">
                            <thead>
                                <tr>
                                    <th>SPORT PROGRAM</th>
                                    <th>TARGET SEASON</th>
                                    <th>DATE APPLIED</th>
                                    <th>APPLICATION STATUS</th>
                                    <th>ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($myApplications)): ?>
                                    <tr id="empty-apps-row">
                                        <td colspan="5" style="text-align: center; padding: 2.5rem; color: var(--text-muted, #64748b);">
                                            <i class="ph ph-clipboard-text" style="font-size: 2rem; display: block; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                                            No sport applications submitted yet. Click <strong>"New Application"</strong> to apply for a sport.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($myApplications as $app): 
                                        $badgeClass = 'dark';
                                        $statusUpper = strtoupper($app['status']);
                                        if ($app['status'] === 'Approved') $badgeClass = 'purple';
                                        elseif ($app['status'] === 'Shortlisted') $badgeClass = 'teal';
                                        elseif ($app['status'] === 'Rejected') $badgeClass = 'red';
                                        
                                        $iconClass = 'ph-trophy';
                                        $sportNameLower = strtolower($app['sport_name']);
                                        if (strpos($sportNameLower, 'basket') !== false) $iconClass = 'ph-basketball';
                                        elseif (strpos($sportNameLower, 'swim') !== false) $iconClass = 'ph-swimming-pool';
                                        elseif (strpos($sportNameLower, 'track') !== false) $iconClass = 'ph-person-simple-run';
                                        elseif (strpos($sportNameLower, 'foot') !== false || strpos($sportNameLower, 'soccer') !== false) $iconClass = 'ph-soccer-ball';
                                        elseif (strpos($sportNameLower, 'volley') !== false) $iconClass = 'ph-volleyball';
                                        elseif (strpos($sportNameLower, 'tennis') !== false) $iconClass = 'ph-tennis-ball';
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="program-cell">
                                                    <div class="icon-circle"><i class="ph-fill <?= $iconClass ?>"></i></div>
                                                    <div>
                                                        <strong><?= htmlspecialchars($app['sport_name']) ?></strong>
                                                        <p class="text-muted">App #<?= str_pad($app['application_id'], 4, '0', STR_PAD_LEFT) ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Season ID: <?= htmlspecialchars($app['season_id']) ?> (Year <?= htmlspecialchars($app['season_year']) ?>)</td>
                                            <td><?= date('Y-m-d', strtotime($app['applied_date'])) ?></td>
                                            <td><span class="status-badge <?= $badgeClass ?>"><?= $statusUpper ?></span></td>
                                            <td><button class="btn-text view-details-btn" data-appid="<?= $app['application_id'] ?>" data-sport-id="<?= (int)$app['sport_id'] ?>" data-season-id="<?= (int)$app['season_id'] ?>" data-sport="<?= htmlspecialchars($app['sport_name']) ?>" data-season="Season ID: <?= htmlspecialchars($app['season_id']) ?> (Year <?= htmlspecialchars($app['season_year']) ?>)" data-status="<?= htmlspecialchars($app['status']) ?>" data-date="<?= date('Y-m-d', strtotime($app['applied_date'])) ?>" data-rejection-reason="<?= athleteEscape($app['rejection_reason']) ?>" data-statement="<?= athleteEscape($app['statement_of_interest']) ?>">View Details</button> <button type="button" class="btn-text edit-application-btn" data-appid="<?= (int)$app['application_id'] ?>">Edit</button> <button type="button" class="btn-text text-red delete-application-btn" data-appid="<?= (int)$app['application_id'] ?>">Delete</button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <div class="table-footer">
                            <p class="text-muted" id="table-footer-text">Showing <?= count($myApplications) ?> application(s)</p>
                        </div>
                    </div>
                </section>

                <!-- Application Details View -->
                <section id="application-details-view" class="view-section">
                    <div class="details-header">
                        <button class="btn-text" id="back-to-apps-btn"><i class="ph ph-arrow-left"></i> MY APPLICATIONS</button>
                        <div class="title-row">
                            <h2>Application Details</h2>
                            <span class="status-badge dark"></span>
                        </div>
                        <p class="text-muted"></p>
                    </div>
                    
                    <div class="details-grid">
                        <div class="details-main">
                            <div class="alert-box error" id="application-rejection" style="display: none;">
                                <div class="alert-icon"><i class="ph-fill ph-warning-circle"></i></div>
                                <div class="alert-content">
                                    <h4>Reason for Rejection</h4>
                                    <p id="application-rejection-reason"></p>
                                </div>
                            </div>
                            <div class="card documents-card">
                                <h3>Statement of Interest</h3>
                                <p id="application-statement" style="white-space: pre-wrap;"></p>
                            </div>
                            <button type="button" class="btn btn-primary edit-application-btn" id="edit-detail-application">Edit Application</button>
                            <button type="button" class="btn btn-secondary text-red delete-application-btn" id="delete-detail-application">Delete Application</button>
                        </div>
                        
                        <div class="details-sidebar">
                            <div class="card summary-card">
                                <h3>Application Data Summary</h3>
                                <div class="summary-row">
                                    <p class="label">SPORT</p>
                                    <p class="value">Soccer</p>
                                </div>
                                <div class="summary-row">
                                    <p class="label">TARGET SEASON</p>
                                    <p class="value">Fall 2024</p>
                                </div>
                                <div class="summary-row">
                                    <p class="label">SUBMISSION DATE</p>
                                    <p class="value">May 12, 2024</p>
                                </div>
                                <div class="summary-row">
                                    <p class="label">APPLICATION REFERENCE</p>
                                    <p class="value font-mono">APP-2024-0002</p>
                                </div>
                            </div>
                            
                            <div class="image-card">
                                <img src="https://images.unsplash.com/photo-1518605368461-1e1e11414442?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Stadium" class="facility-img">
                                <div class="img-overlay">
                                    <p><i class="ph ph-map-pin"></i> University Stadium</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                
                <section id="edit-application-view" class="view-section">
                    <h2>Edit Application</h2>
                    <p class="text-muted">Saving changes returns this application to Pending for review.</p>
                    <form id="edit-application-form" class="card form-card apply-form" action="../controllers/AthleteController.php?action=update_application" method="post">
                        <input type="hidden" name="application_id" id="edit-application-id">
                        <input type="hidden" name="csrf_token" value="<?= athleteEscape($_SESSION['athlete_csrf']) ?>">
                        <div class="form-group">
                            <label for="edit-application-sport">Sport</label>
                            <select name="sport_id" id="edit-application-sport" required>
                                <?php foreach ($availableSports as $sport): ?>
                                    <option value="<?= (int)$sport['sport_id'] ?>"><?= athleteEscape($sport['sport_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit-application-season">Season</label>
                            <select name="season_id" id="edit-application-season" required>
                                <?php foreach ($availableSeasons as $season): ?>
                                    <option value="<?= (int)$season['season_id'] ?>">Season <?= (int)$season['season_id'] ?> (<?= athleteEscape($season['season_year']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit-application-statement">Statement of Interest</label>
                            <textarea name="statement_of_interest" id="edit-application-statement" rows="5" maxlength="16000"></textarea>
                        </div>
                        <p id="edit-application-error" class="text-red" role="alert" hidden></p>
                        <div class="form-actions right">
                            <button type="button" class="btn btn-secondary" id="cancel-application-edit">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="save-application-edit">Save Changes</button>
                        </div>
                    </form>
                </section>
                <?php require __DIR__ . '/partials/athlete_profile.php'; ?>
                <?php require __DIR__ . '/partials/athlete_calendar.php'; ?>

            </div>
        </main>
    </div>
    <script src="../assets/js/athletic_regestration.js?v=<?= time() ?>"></script>
</body>
</html>
