<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Athlete') {
    header('Location: login.php');
    exit;
}

$db = Database::connect();
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
$height = htmlspecialchars($athlete['height']);
$weight = htmlspecialchars($athlete['weight']);
$academicYear = htmlspecialchars($athlete['academic_year']);
$emergencyContact = htmlspecialchars($athlete['emergency_Contact']);
$email = htmlspecialchars($athlete['email'] ?? '');

$athleteId = $athlete['athlete_id'];

// 1. Training logged
$stmtHours = $db->prepare("
    SELECT SUM(TIME_TO_SEC(TIMEDIFF(ts.duration_minutes, ts.start_time))) / 3600 AS total_hours 
    FROM training_attendance ta 
    JOIN training_sessions ts ON ta.session_id = ts.session_id 
    WHERE ta.athlete_id = ? AND ta.status = 'Present'
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

// 3. Upcoming events
// Count fixtures happening in the next 7 days
$stmtEvents = $db->prepare("
    SELECT COUNT(*) FROM fixtures 
    WHERE fixture_date >= CURDATE() 
    AND fixture_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
");
$stmtEvents->execute();
$upcomingEvents = $stmtEvents->fetchColumn();

// 4. Next Fixture
$stmtNextFixture = $db->prepare("
    SELECT f.fixture_date, f.fixture_time, f.opponent, f.home_team, s.sport_name, fac.facility_name
    FROM fixtures f
    JOIN sport_application sa ON f.sport_id = sa.sport_id
    JOIN sports s ON f.sport_id = s.sport_id
    LEFT JOIN facilities fac ON f.facility_id = fac.facility_id
    WHERE sa.athletic_id = ? 
      AND sa.status = 'Approved' 
      AND (f.fixture_date > CURDATE() OR (f.fixture_date = CURDATE() AND f.fixture_time >= CURTIME()))
    ORDER BY f.fixture_date ASC, f.fixture_time ASC
    LIMIT 1
");
$stmtNextFixture->execute([$athleteId]);
$nextFixture = $stmtNextFixture->fetch();

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

// 6. Upcoming Training
$stmtUpcomingTraining = $db->prepare("
    SELECT ts.*, s.sport_name, fac.facility_name
    FROM training_sessions ts
    JOIN sports s ON ts.sport_id = s.sport_id
    LEFT JOIN facilities fac ON ts.facility_id = fac.facility_id
    JOIN sport_application sa ON ts.sport_id = sa.sport_id
    WHERE sa.athletic_id = ? AND sa.status = 'Approved'
      AND (ts.session_date > CURDATE() OR (ts.session_date = CURDATE() AND ts.start_time >= CURTIME()))
    ORDER BY ts.session_date ASC, ts.start_time ASC
    LIMIT 5
");
$stmtUpcomingTraining->execute([$athleteId]);
$upcomingTrainings = $stmtUpcomingTraining->fetchAll();

if (empty($upcomingTrainings)) {
    $stmtGeneralTraining = $db->prepare("
        SELECT ts.*, s.sport_name, fac.facility_name
        FROM training_sessions ts
        JOIN sports s ON ts.sport_id = s.sport_id
        LEFT JOIN facilities fac ON ts.facility_id = fac.facility_id
        WHERE (ts.session_date > CURDATE() OR (ts.session_date = CURDATE() AND ts.start_time >= CURTIME()))
        ORDER BY ts.session_date ASC, ts.start_time ASC
        LIMIT 5
    ");
    $stmtGeneralTraining->execute();
    $upcomingTrainings = $stmtGeneralTraining->fetchAll();
}

// 7. Sport Application data
require_once __DIR__ . '/../models/SportApplication.php';
$sportAppModel = new SportApplication($db);
$availableSports = $sportAppModel->getAllSports();
$availableSeasons = $sportAppModel->getAllSeasons();
$myApplications = $sportAppModel->getAthleteApplications($athleteId);
$appStats = $sportAppModel->getApplicationStats($athleteId);

$currentView = $_GET['view'] ?? 'dashboard-view';
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
    <link rel="stylesheet" href="../assets/css/athletic_regestration.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
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
                <a href="#" class="nav-item <?= $currentView === 'calendar-view' ? 'active' : '' ?>" data-target="calendar-view" id="nav-calendar"><i class="ph ph-calendar-blank"></i> Training Calendar</a>
                <a href="#" class="nav-item <?= in_array($currentView, ['profile-view', 'edit-profile-view']) ? 'active' : '' ?>" data-target="profile-view" id="nav-profile"><i class="ph ph-user"></i> Profile</a>
            </nav>
            <div class="sidebar-footer">
                <a href="#" class="nav-item"><i class="ph ph-sign-out"></i> Sign Out</a>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <main class="main-content">
            <!-- Header -->
            <header class="top-header">
                <div class="header-title">University Sports Management</div>
                <div class="header-actions">
                    <button class="notification-btn"><i class="ph ph-bell"></i><span class="notification-dot"></span></button>
                    <div class="user-profile">
                        <div class="user-info">
                            <span class="user-name"><?= $fullName ?></span>
                            <span class="user-role">Varsity Track</span>
                        </div>
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($fullName) ?>&background=0D8ABC&color=fff&size=40" alt="User Avatar" class="avatar">
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
                                <p>Upcoming events this week</p>
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
                                    <p><i class="ph ph-map-pin"></i> <?= htmlspecialchars($nextFixture['facility_name']) ?> (<?= htmlspecialchars($nextFixture['sport_name']) ?>)</p>
                                    <button class="btn btn-primary btn-sm">View Details</button>
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
                                                $location = htmlspecialchars($tSession['facility_name'] ?? 'Main Field');
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
                                <button class="btn-text full-width" style="margin-top: 16px; justify-content: center;">View Full Schedule</button>
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
                <section id="applications-view" class="view-section <?= $currentView === 'applications-view' ? 'active' : '' ?>">
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
                                            <td><button class="btn-text view-details-btn" data-appid="<?= $app['application_id'] ?>" data-sport="<?= htmlspecialchars($app['sport_name']) ?>" data-season="Season ID: <?= htmlspecialchars($app['season_id']) ?> (Year <?= htmlspecialchars($app['season_year']) ?>)" data-status="<?= htmlspecialchars($app['status']) ?>" data-date="<?= date('Y-m-d', strtotime($app['applied_date'])) ?>" data-statement="<?= htmlspecialchars($app['statement_of_interest'] ?? '') ?>">View Details</button></td>
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
                            <span class="status-badge red-filled"><i class="ph-fill ph-x-circle"></i> Rejected</span>
                        </div>
                        <p class="text-muted">Soccer • Fall 2024</p>
                    </div>
                    
                    <div class="details-grid">
                        <div class="details-main">
                            <div class="alert-box error">
                                <div class="alert-icon"><i class="ph-fill ph-warning-circle"></i></div>
                                <div class="alert-content">
                                    <h4>Reason for Rejection</h4>
                                    <p>Medical clearance document expired. Please update your clearance and re-apply for the next available season.</p>
                                    <div class="alert-actions">
                                        <button class="btn btn-primary btn-sm">Re-apply for Sport</button>
                                        <button class="btn btn-secondary btn-sm"><i class="ph ph-envelope"></i> Contact Coordinator</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card documents-card">
                                <h3><i class="ph ph-file"></i> Application Documents</h3>
                                <div class="doc-list">
                                    <div class="doc-item">
                                        <div class="doc-icon"><i class="ph-fill ph-file-pdf text-red"></i></div>
                                        <div class="doc-info">
                                            <strong>Academic_Transcript_2023.pdf</strong>
                                            <p class="text-muted">Uploaded on Aug 12, 2024</p>
                                        </div>
                                        <div class="doc-actions">
                                            <span class="status-badge green-light">Verified</span>
                                            <button class="btn-icon"><i class="ph ph-download-simple"></i></button>
                                        </div>
                                    </div>
                                    <div class="doc-item error-state">
                                        <div class="doc-icon"><i class="ph-fill ph-file-pdf text-red"></i></div>
                                        <div class="doc-info">
                                            <strong>Medical_Clearance_Expired.pdf</strong>
                                            <p class="text-red">Requires attention</p>
                                        </div>
                                        <div class="doc-actions">
                                            <span class="status-badge red-light">Expired</span>
                                            <button class="btn-icon"><i class="ph ph-upload-simple"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                                    <p class="label">INDEX NUMBER</p>
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
                
                <!-- Profile View -->
                <section id="profile-view" class="view-section">
                    <div class="page-header-card profile-mode">
                        <div class="profile-summary">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($fullName) ?>&background=0D8ABC&color=fff&size=80" alt="Avatar" class="large-avatar">
                            <div class="profile-info">
                                <span class="badge">VARSITY ATHLETE</span>
                                <h2><?= $fullName ?></h2>
                                <span class="id-tag"><i class="ph ph-identification-card"></i> <?= $indexNumber ?></span>
                            </div>
                        </div>
                        <div class="header-action-buttons">
                            <button class="btn btn-secondary"><i class="ph ph-download-simple"></i> Export Record</button>
                            <button class="btn btn-primary" id="edit-profile-btn"><i class="ph ph-pencil-simple"></i> Edit Profile</button>
                        </div>
                    </div>
                    <div class="profile-grid">
                        <div class="grid-col-left">
                            <div class="card personal-details-card">
                                <h3><i class="ph ph-user"></i> Personal Details</h3>
                                <div class="detail-group">
                                    <p class="detail-label">ACADEMIC INFO</p>
                                    <div class="detail-row"><span>Year</span><strong>Year <?= $academicYear ?></strong></div>
                                    <div class="detail-row"><span>Faculty</span><strong><?= $faculty ?></strong></div>
                                </div>
                                <div class="divider"></div>
                                <div class="detail-group">
                                    <p class="detail-label">PHYSICAL PROFILE</p>
                                    <div class="physical-stats">
                                        <div class="stat"><i class="ph ph-ruler"></i><div class="stat-val"><?= $height ?><small>cm</small></div></div>
                                        <div class="stat"><i class="ph ph-scales"></i><div class="stat-val"><?= $weight ?><small>kg</small></div></div>
                                    </div>
                                </div>
                                <div class="divider"></div>
                                <div class="detail-group emergency">
                                    <p class="detail-label"><i class="ph ph-warning-circle"></i> EMERGENCY CONTACT</p>
                                    <p class="contact-name">Primary Contact</p>
                                    <p class="contact-phone"><i class="ph ph-phone"></i> <?= $emergencyContact ?></p>
                                </div>
                            </div>
                            <div class="card colours-card">
                                <h3>University Colours <i class="ph-fill ph-medal text-teal" style="float: right;"></i></h3>
                                <div class="badges">
                                    <div class="colour-badge"><div class="icon-wrap bg-blue"><i class="ph-fill ph-medal"></i></div><span>Excellence in Athletics</span></div>
                                    <div class="colour-badge"><div class="icon-wrap bg-dark"><i class="ph-fill ph-star"></i></div><span>Top Scholarship</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="grid-col-right">
                            <div class="card sport-history-card">
                                <div class="card-header">
                                    <h3><i class="ph ph-clock-counter-clockwise"></i> Sport History</h3>
                                    <button class="btn-icon"><i class="ph ph-funnel"></i></button>
                                </div>
                                <table class="history-table">
                                    <thead>
                                        <tr>
                                            <th>SEASON</th>
                                            <th>SPORT</th>
                                            <th>ROLE / RESULT</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Fall 2023</td>
                                            <td><div class="sport-name"><div class="icon-circle"><i class="ph-fill ph-basketball"></i></div> Basketball</div></td>
                                            <td><span class="status-badge varsity">Varsity</span></td>
                                        </tr>
                                        <tr>
                                            <td>Spring 2023</td>
                                            <td><div class="sport-name"><div class="icon-circle"><i class="ph-fill ph-swimming-pool"></i></div> Swimming</div></td>
                                            <td><span class="status-badge club">Club</span></td>
                                        </tr>
                                        <tr>
                                            <td>Fall 2022</td>
                                            <td><div class="sport-name"><div class="icon-circle"><i class="ph-fill ph-person-simple-run"></i></div> Track & Field</div></td>
                                            <td><span class="status-badge varsity">Varsity</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="card-footer">
                                    <button class="btn-text">View Full History <i class="ph ph-caret-down"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Edit Profile View -->
                <section id="edit-profile-view" class="view-section">
                    <div class="page-header-card edit-mode hero-gradient">
                        <div class="profile-summary">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($fullName) ?>&background=0D8ABC&color=fff&size=80" alt="Avatar" class="large-avatar">
                            <div class="profile-info">
                                <h2><?= $fullName ?></h2>
                                <div class="tags">
                                    <span class="tag"><i class="ph-fill ph-person-simple-run"></i> Varsity Track</span>
                                    <span class="tag"><i class="ph-fill ph-student"></i> Senior</span>
                                    <span class="tag active"><i class="ph-fill ph-check-circle"></i> Active Roster</span>
                                </div>
                            </div>
                        </div>
                        <div class="header-action-buttons">
                            <button class="btn btn-secondary" id="cancel-edit-btn">Cancel</button>
                            <button class="btn btn-primary" id="save-profile-btn">Save Changes</button>
                        </div>
                    </div>
                    <div class="edit-grid">
                        <div class="edit-col-left">
                            <div class="card form-card">
                                <h3><i class="ph ph-user"></i> Personal Details</h3>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>First Name</label>
                                        <input type="text" value="<?= $firstName ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Last Name</label>
                                        <input type="text" value="<?= $lastName ?>">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Student ID</label>
                                        <input type="text" value="<?= $indexNumber ?>" disabled>
                                        <small class="form-hint">Contact administration to change ID</small>
                                    </div>
                                    <div class="form-group">
                                        <label>Date of Birth</label>
                                        <input type="date" value="2001-09-15">
                                    </div>
                                </div>
                                <div class="form-group full-width">
                                    <label>University Email</label>
                                    <input type="email" value="<?= $email ?>" disabled>
                                </div>
                            </div>
                            <div class="card form-card physical-profile">
                                <h3><i class="ph ph-activity"></i> Physical Profile</h3>
                                <div class="form-row">
                                    <div class="form-group error">
                                        <label>Height (cm) <span class="required">*</span></label>
                                        <div class="input-with-icon">
                                            <input type="number" value="<?= $height ?>">
                                            <i class="ph-fill ph-warning-circle"></i>
                                        </div>
                                        <small class="error-msg">Please enter a valid height in cm</small>
                                    </div>
                                    <div class="form-group">
                                        <label>Weight (kg)</label>
                                        <input type="number" value="<?= $weight ?>">
                                    </div>
                                </div>
                                <div class="performance-index">
                                    <div class="circular-progress">
                                        <svg viewBox="0 0 36 36" class="circular-chart">
                                            <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                            <path class="circle" stroke-dasharray="90, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                        </svg>
                                        <span>90%</span>
                                    </div>
                                    <p><strong>Px Performance Index</strong><br><span class="text-muted">Based on recent physical assessments. Update metrics to recalculate.</span></p>
                                </div>
                            </div>
                        </div>
                        <div class="edit-col-right">
                            <div class="card form-card emergency-contact">
                                <h3><i class="ph ph-warning-circle"></i> Emergency Contact</h3>
                                <div class="form-group full-width">
                                    <label>Primary Contact Name</label>
                                    <input type="text" value="Primary Contact">
                                </div>
                                <div class="form-group full-width">
                                    <label>Relationship</label>
                                    <input type="text" value="Parent">
                                </div>
                                <div class="form-group full-width error">
                                    <label>Phone Number</label>
                                    <div class="input-with-icon">
                                        <input type="text" value="<?= $emergencyContact ?>">
                                        <i class="ph-fill ph-warning-circle"></i>
                                    </div>
                                    <small class="error-msg">Invalid phone format</small>
                                </div>
                            </div>
                            <div class="card form-card local-address">
                                <h3><i class="ph ph-map-pin"></i> Local Address</h3>
                                <div class="form-group full-width">
                                    <label>Street Address</label>
                                    <input type="text" value="Campus West, Dormitory B, 41">
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>City</label>
                                        <input type="text" value="University City">
                                    </div>
                                    <div class="form-group">
                                        <label>ZIP Code</label>
                                        <input type="text" value="10027">
                                    </div>
                                </div>
                                <div class="map-placeholder">
                                    <!-- map mockup -->
                                    <div class="map-bg"></div>
                                    <div class="map-pin"><i class="ph-fill ph-map-pin"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Calendar View -->
                <section id="calendar-view" class="view-section">
                    <div class="calendar-header-card">
                        <div class="cal-title">
                            <h2><i class="ph ph-calendar-blank"></i> Week View</h2>
                            <p>Oct 21 — Oct 27</p>
                        </div>
                        <div class="cal-controls">
                            <div class="legend">
                                <span class="legend-item training"><span class="dot"></span> TRAINING</span>
                                <span class="legend-item match"><span class="dot"></span> MATCH</span>
                            </div>
                            <div class="cal-nav">
                                <button class="btn-icon"><i class="ph ph-caret-left"></i></button>
                                <button class="btn btn-secondary btn-sm">TODAY</button>
                                <button class="btn-icon"><i class="ph ph-caret-right"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="calendar-grid">
                        <div class="cal-day">
                            <div class="day-header">MON <strong>21</strong></div>
                            <div class="events-container">
                                <div class="event-card training">
                                    <p class="time">06:00 - 07:30</p>
                                    <h4>Strength & Conditioning</h4>
                                    <p class="location"><i class="ph ph-map-pin"></i> Weight Room A</p>
                                </div>
                            </div>
                        </div>
                        <div class="cal-day">
                            <div class="day-header">TUE <strong>22</strong></div>
                            <div class="events-container">
                                <div class="event-card training">
                                    <p class="time">15:00 - 16:30</p>
                                    <h4>Tactical Field Session</h4>
                                    <p class="location"><i class="ph ph-map-pin"></i> Practice Pitch 2</p>
                                </div>
                            </div>
                        </div>
                        <div class="cal-day">
                            <div class="day-header">WED <strong>23</strong></div>
                            <div class="events-container">
                                <div class="event-card training">
                                    <p class="time">07:00 - 08:00</p>
                                    <h4>Recovery Swim</h4>
                                    <p class="location"><i class="ph ph-map-pin"></i> Aquatic Center</p>
                                </div>
                            </div>
                        </div>
                        <div class="cal-day active-day">
                            <div class="day-header">THU <strong>24</strong></div>
                            <div class="events-container">
                                <div class="event-card match">
                                    <span class="match-badge">MATCH</span>
                                    <p class="time">18:00 - 20:30</p>
                                    <h4>Away Match vs State Lions</h4>
                                    <p class="location"><i class="ph-fill ph-bus"></i> AWAY KIT REQUIRED</p>
                                </div>
                            </div>
                        </div>
                        <div class="cal-day">
                            <div class="day-header">FRI <strong>25</strong></div>
                            <div class="events-container">
                                <div class="event-card training">
                                    <p class="time">10:00 - 11:00</p>
                                    <h4>Team Meeting</h4>
                                    <p class="location"><i class="ph ph-video-camera"></i> Room 302</p>
                                </div>
                            </div>
                        </div>
                        <div class="cal-day weekend">
                            <div class="day-header">SAT <strong>26</strong></div>
                            <div class="events-container empty"><div class="empty-text">REST DAY</div></div>
                        </div>
                        <div class="cal-day weekend">
                            <div class="day-header">SUN <strong>27</strong></div>
                            <div class="events-container empty"><div class="empty-text">REST DAY</div></div>
                        </div>
                    </div>
                </section>

            </div>
        </main>
    </div>
    <script src="../assets/js/athletic_regestration.js?v=<?= time() ?>"></script>
</body>
</html>
