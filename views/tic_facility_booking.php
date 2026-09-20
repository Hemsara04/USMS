<?php
require_once '../config/auth_guard.php';
requireRole('TIC');

require_once '../models/Facility.php';
$facilityModel      = new Facility();
$bookingRequests    = $facilityModel->getPendingBookings();
$reservedLocations  = $facilityModel->getReservedLocations();
$allFacilities      = $facilityModel->getAllFacilities();
$stats              = $facilityModel->getStats();

$totalPending  = $stats['total_pending'];
$totalBookings = $stats['total_bookings'];

$pdo = Database::connect();
$allSports = $pdo->query("SELECT sport_id, sport_name FROM sports ORDER BY sport_name ASC")->fetchAll();

$pageTitle = 'Facility Bookings';
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
            <h2 class="page-title">Facility Bookings & Locations</h2>
        </div>

        <!-- Stats -->
        <div class="grid-2">
            <div class="stat-card">
                <div>
                    <div class="stat-label">Pending Booking Requests</div>
                    <div class="stat-value"><?= $totalPending ?></div>
                </div>
                <div class="stat-icon">
                    <span class="material-symbols-outlined">pending_actions</span>
                </div>
            </div>
            <div class="stat-card">
                <div>
                    <div class="stat-label">Confirmed Reservations</div>
                    <div class="stat-value"><?= $totalBookings ?></div>
                </div>
                <div class="stat-icon" style="color:#22C55E;">
                    <span class="material-symbols-outlined">event_available</span>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
            <div class="tab-group">
                <button class="tab-btn active" onclick="switchTab(this,'facilityTabs','tab-booking-req')">Booking Requests</button>
                <button class="tab-btn" onclick="switchTab(this,'facilityTabs','tab-reserved')">Reserved Locations</button>
            </div>
            <button class="btn btn-primary" onclick="openModal('reserveModal')">
                <span class="material-symbols-outlined" style="font-size:16px">add_circle</span> Reserve Manually
            </button>
        </div>

        <!-- TAB 1: Booking Requests -->
        <div class="tab-content active" id="tab-booking-req" data-tab-group="facilityTabs">
            <div class="table-card">
                <div class="table-toolbar">
                    <span class="table-title">Pending Facility Booking Queue</span>
                    <div class="table-filters">
                        <div class="search-box">
                            <span class="material-symbols-outlined">search</span>
                            <input type="text" class="search-input" id="searchBooking" placeholder="Search bookings…">
                        </div>
                    </div>
                </div>
                <div class="table-scroll">
                    <table class="data-table" id="bookingTable">
                        <thead>
                            <tr>
                                <th>Facility / Venue</th>
                                <th>Sport</th>
                                <th>Booking Date</th>
                                <th>Time & Duration</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bookingRequests)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--text-3); padding: 30px;">
                                        <span class="material-symbols-outlined" style="font-size: 32px; display: block; margin-bottom: 6px;">check_circle</span>
                                        No pending facility requests at this time.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bookingRequests as $b): ?>
                                <tr>
                                    <td>
                                        <span class="cell-main"><?= htmlspecialchars($b['facility_name']) ?></span>
                                        <span class="cell-sub"><?= htmlspecialchars($b['facility_type']) ?> (Cap: <?= $b['capacity'] ?>)</span>
                                    </td>
                                    <td><span class="badge badge-info"><?= htmlspecialchars($b['sport_name']) ?></span></td>
                                    <td><span class="cell-main"><?= date('M d, Y', strtotime($b['booking_date'])) ?></span></td>
                                    <td>
                                        <span class="cell-main"><?= date('h:i A', strtotime($b['start_time'])) ?></span>
                                        <span class="cell-sub"><?= $b['duration_minutes'] ?> mins</span>
                                    </td>
                                    <td><span class="badge badge-warn">Pending Approval</span></td>
                                    <td>
                                        <div class="cell-actions">
                                            <form action="../controllers/TicController.php?action=approve_booking" method="POST" style="display:inline;">
                                                <input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>">
                                                <button type="submit" class="btn btn-success btn-sm" onclick="return USMS.confirmForm(event, this, 'Approve this facility booking?')">
                                                    <span class="material-symbols-outlined" style="font-size:15px;">check</span> Approve
                                                </button>
                                            </form>
                                            <form action="../controllers/TicController.php?action=reject_booking" method="POST" style="display:inline;">
                                                <input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return USMS.confirmForm(event, this, 'Reject this facility booking request?')">
                                                    <span class="material-symbols-outlined" style="font-size:15px;">close</span> Reject
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
                    <span>Showing <?= count($bookingRequests) ?> of <?= $totalPending ?> requests</span>
                    <div class="page-btns">
                        <button class="page-btn" disabled><span class="material-symbols-outlined">chevron_left</span></button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn"><span class="material-symbols-outlined">chevron_right</span></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: Reserved Locations -->
        <div class="tab-content" id="tab-reserved" data-tab-group="facilityTabs">
            <div class="table-card">
                <div class="table-toolbar">
                    <span class="table-title">Confirmed Facility Reservations</span>
                    <div class="table-filters">
                        <div class="search-box">
                            <span class="material-symbols-outlined">search</span>
                            <input type="text" class="search-input" id="searchReserved" placeholder="Search reservations…">
                        </div>
                    </div>
                </div>
                <div class="table-scroll">
                    <table class="data-table" id="reservedTable">
                        <thead>
                            <tr>
                                <th>Facility</th>
                                <th>Sport</th>
                                <th>Date Reserved</th>
                                <th>Time Slot</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reservedLocations)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--text-3); padding: 30px;">
                                        No confirmed reservations recorded yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reservedLocations as $res): ?>
                                <tr>
                                    <td>
                                        <span class="cell-main"><?= htmlspecialchars($res['facility_name']) ?></span>
                                        <span class="cell-sub"><?= htmlspecialchars($res['facility_type']) ?></span>
                                    </td>
                                    <td><span class="badge badge-info"><?= htmlspecialchars($res['sport_name']) ?></span></td>
                                    <td><span class="cell-main"><?= date('M d, Y', strtotime($res['booking_date'])) ?></span></td>
                                    <td>
                                        <span class="cell-main"><?= date('h:i A', strtotime($res['start_time'])) ?></span>
                                        <span class="cell-sub"><?= $res['duration_minutes'] ?> minutes</span>
                                    </td>
                                    <td><span class="badge badge-ok">Confirmed</span></td>
                                    <td class="text-right">
                                        <form action="../controllers/TicController.php?action=release_booking" method="POST" style="display:inline;">
                                            <input type="hidden" name="booking_id" value="<?= $res['booking_id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return USMS.confirmForm(event, this, 'Complete and release reservation for <?= htmlspecialchars(addslashes($res['facility_name'])) ?>? This will free up the venue.')" title="Complete & Release Location">
                                                <span class="material-symbols-outlined" style="font-size:15px;">delete</span> Release / Done
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination">
                    <span>Showing <?= count($reservedLocations) ?> reservations</span>
                    <div class="page-btns"></div>
                </div>
            </div>
        </div>

    </div><!-- .page-content -->
</div><!-- .main-wrapper -->

<!-- ===== RESERVE MODAL ===== -->
<div class="modal-overlay" id="reserveModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Manual Facility Reservation</span>
            <button class="modal-close" onclick="closeModal('reserveModal')">&times;</button>
        </div>
        <form action="../controllers/TicController.php?action=add_booking" method="POST">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Facility / Location</label>
                    <select class="form-select searchable" name="facility_id" data-placeholder="Select location…" required>
                        <option value="" disabled selected>Select location…</option>
                        <?php foreach ($allFacilities as $fac): ?>
                            <option value="<?= $fac['facility_id'] ?>">
                                <?= htmlspecialchars($fac['facility_name']) ?> (<?= htmlspecialchars($fac['type']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Sport / Activity</label>
                        <select class="form-select searchable" name="sport_id" data-placeholder="Select sport…" required>
                            <option value="" disabled selected>Select sport…</option>
                            <?php foreach ($allSports as $sp): ?>
                                <option value="<?= $sp['sport_id'] ?>"><?= htmlspecialchars($sp['sport_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Reservation Date</label>
                        <input type="date" class="form-input" name="booking_date" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Time</label>
                        <input type="time" class="form-input" name="start_time" value="16:00" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duration (Minutes)</label>
                        <input type="number" class="form-input" name="duration_minutes" value="90" step="15" min="15">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('reserveModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm Reservation</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/dashboard.js"></script>
<script>
setupTablePagination('bookingTable', 8, 'searchBooking');
setupTablePagination('reservedTable', 8, 'searchReserved');
</script>
</body>
</html>
