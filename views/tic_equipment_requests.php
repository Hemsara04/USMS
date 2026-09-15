<?php
require_once '../config/auth_guard.php';
requireRole('TIC');

require_once '../models/Equipment.php';
$equipModel   = new Equipment();
$requests     = $equipModel->getPendingRequests();
$stats        = $equipModel->getStats();
$totalPending = $stats['total_pending'];

$pdo = Database::connect();
$allEquipment = $pdo->query("SELECT equipment_id, item_name, quantity_in_stock FROM equipment WHERE quantity_in_stock > 0 ORDER BY item_name ASC")->fetchAll();
$allAthletes  = $pdo->query("SELECT athlete_id, full_name, index_number, faculty FROM Athletic ORDER BY full_name ASC LIMIT 50")->fetchAll();
$allCoaches   = $equipModel->getCoaches();

$pageTitle = 'Equipment Requests';
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

        <!-- Sub-Nav: Equipment tabs -->
        <div class="sub-nav">
            <a href="tic_equipment_requests.php" class="active">Pending Requests</a>
            <a href="tic_checkout_log.php">Check-out Log</a>
            <a href="tic_stock_inventory.php">Stock Inventory</a>
        </div>

        <!-- Stats (2 Column Grid with direct checkout action) -->
        <div class="grid-2">
            <div class="stat-card">
                <div>
                    <div class="stat-label">Pending Approval Requests</div>
                    <div class="stat-value"><?= $totalPending ?></div>
                </div>
                <div class="stat-icon">
                    <span class="material-symbols-outlined">pending_actions</span>
                </div>
            </div>
            <button class="action-card" onclick="openModal('newCheckoutModal')">
                <div class="action-card-icon">
                    <span class="material-symbols-outlined">shopping_cart_checkout</span>
                </div>
                <div>
                    <div class="action-card-title">New Equipment Check-out</div>
                    <div class="action-card-desc">Directly issue equipment to athlete or coach</div>
                </div>
            </button>
        </div>

        <!-- Table -->
        <div class="table-card">
            <div class="table-toolbar">
                <span class="table-title">Pending Approval Queue</span>
                <div class="table-filters">
                    <div class="search-box">
                        <span class="material-symbols-outlined">search</span>
                        <input type="text" class="search-input" id="searchReq" placeholder="Search requests…">
                    </div>
                </div>
            </div>

            <div class="table-scroll">
                <table class="data-table" id="reqTable">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Sport / Category</th>
                            <th>Requested By</th>
                            <th>Units Requested</th>
                            <th>Date Requested</th>
                            <th>Expected Return</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($requests)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--text-3); padding: 30px;">
                                    <span class="material-symbols-outlined" style="font-size: 32px; display: block; margin-bottom: 6px;">check_circle</span>
                                    No pending equipment requests at this time.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($requests as $r): ?>
                            <tr>
                                <td><span class="cell-main"><?= htmlspecialchars($r['item_name']) ?></span></td>
                                <td><span class="badge badge-info"><?= htmlspecialchars($r['sport_name']) ?></span></td>
                                <td>
                                    <span class="cell-main"><?= htmlspecialchars($r['requested_by']) ?></span>
                                    <span class="cell-sub cell-mono"><?= htmlspecialchars($r['index_number']) ?></span>
                                </td>
                                <td><span class="badge badge-warning" style="font-weight:700;"><?= (int)($r['quantity'] ?? 1) ?> units</span></td>
                                <td><span class="cell-sub"><?= date('M d, Y', strtotime($r['issue_date'])) ?></span></td>
                                <td><span class="cell-sub"><?= date('M d, Y', strtotime($r['expected_return_date'])) ?></span></td>
                                <td>
                                    <div class="cell-actions">
                                        <form action="../controllers/TicController.php?action=approve_request" method="POST" style="display:inline;">
                                            <input type="hidden" name="issue_id" value="<?= $r['issue_id'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Approve and issue this equipment (<?= (int)($r['quantity'] ?? 1) ?> units)?')">
                                                <span class="material-symbols-outlined" style="font-size:15px;">check</span> Approve
                                            </button>
                                        </form>
                                        <form action="../controllers/TicController.php?action=reject_request" method="POST" style="display:inline;">
                                            <input type="hidden" name="issue_id" value="<?= $r['issue_id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Reject this request?')">
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
                <span>Showing <?= count($requests) ?> of <?= $totalPending ?> requests</span>
                <div class="page-btns">
                    <button class="page-btn" disabled><span class="material-symbols-outlined">chevron_left</span></button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn"><span class="material-symbols-outlined">chevron_right</span></button>
                </div>
            </div>
        </div>

    </div><!-- .page-content -->
</div><!-- .main-wrapper -->

<!-- ===== NEW CHECK-OUT MODAL (UNIFIED ON BOTH PAGES) ===== -->
<div class="modal-overlay" id="newCheckoutModal">
    <div class="modal" style="max-width: 540px;">
        <div class="modal-header">
            <span class="modal-title">New Equipment Check-out</span>
            <button class="modal-close" onclick="closeModal('newCheckoutModal')">&times;</button>
        </div>
        <form action="../controllers/TicController.php?action=new_checkout" method="POST">
            <input type="hidden" name="redirect_to" value="tic_equipment_requests.php">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Select Equipment to Issue</label>
                    <select class="form-select searchable" name="equipment_id" data-placeholder="Choose equipment item…" data-ajax-url="../controllers/TicController.php?action=ajax_search_equipment" required>
                        <option value="" disabled selected>Choose equipment item…</option>
                        <?php foreach ($allEquipment as $eq): ?>
                            <option value="<?= $eq['equipment_id'] ?>">
                                <?= htmlspecialchars($eq['item_name']) ?> (<?= $eq['quantity_in_stock'] ?> available)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Quantity to Issue</label>
                    <input type="number" class="form-input" name="quantity" value="1" min="1" max="100" required>
                </div>

                <!-- Recipient Type Switch -->
                <div class="form-group">
                    <label class="form-label">Issue Recipient Role</label>
                    <div style="display:flex; gap:10px;">
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-weight:600; font-size:13.5px;">
                            <input type="radio" name="recipient_type" value="Athlete" checked onchange="toggleRecipientType('Athlete')"> Athlete
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-weight:600; font-size:13.5px;">
                            <input type="radio" name="recipient_type" value="Coach" onchange="toggleRecipientType('Coach')"> Coach / Sports Staff
                        </label>
                    </div>
                </div>

                <!-- Athlete Select -->
                <div class="form-group" id="athleteFieldWrap">
                    <label class="form-label">Select Athlete</label>
                    <select class="form-select searchable" name="athlete_id" id="athleteSelect" data-placeholder="Search athlete by name or index…" data-ajax-url="../controllers/TicController.php?action=ajax_search_recipients&type=Athlete">
                        <option value="" disabled selected>Search athlete by name or index…</option>
                        <?php foreach ($allAthletes as $ath): ?>
                            <option value="<?= $ath['athlete_id'] ?>">
                                <?= htmlspecialchars($ath['full_name']) ?> — <?= htmlspecialchars($ath['index_number']) ?> (<?= htmlspecialchars($ath['faculty']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Coach Select / Input -->
                <div class="form-group" id="coachFieldWrap" style="display:none;">
                    <label class="form-label">Select Coach / Staff Name</label>
                    <select class="form-select" name="recipient_name" id="coachSelect">
                        <option value="" disabled selected>Choose coach…</option>
                        <?php foreach ($allCoaches as $c): ?>
                            <option value="<?= htmlspecialchars($c['username']) ?>">
                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $c['username']))) ?> (<?= htmlspecialchars($c['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                        <option value="Coach Other">Other Official / Coach</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Expected Return Date</label>
                    <input type="date" class="form-input" name="expected_return_date" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" min="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('newCheckoutModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Complete Check-out</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/dashboard.js"></script>
<script>
setupTablePagination('reqTable', 8, 'searchReq');

function toggleRecipientType(type) {
    var athWrap = document.getElementById('athleteFieldWrap');
    var coaWrap = document.getElementById('coachFieldWrap');
    var athSel  = document.getElementById('athleteSelect');
    var coaSel  = document.getElementById('coachSelect');

    if (type === 'Coach') {
        athWrap.style.display = 'none';
        coaWrap.style.display = 'block';
        athSel.removeAttribute('required');
        coaSel.setAttribute('required', 'required');
    } else {
        athWrap.style.display = 'block';
        coaWrap.style.display = 'none';
        athSel.setAttribute('required', 'required');
        coaSel.removeAttribute('required');
    }
}
</script>
</body>
</html>
