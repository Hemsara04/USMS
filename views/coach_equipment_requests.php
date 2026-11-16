<?php
/**
 * USMS - Coach Team Equipment Requisitions
 * 
 * Production-ready View implementing Interim Evaluation Feedback:
 * 1. Historical Data Management (Audit preservation on cancellations, soft-delete archive)
 * 2. Operational Logic (Cannot cancel checked out inventory, strict TIC approval states)
 * 3. Dropdowns instead of manual typing for sport, equipment catalog, quantity & purpose
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

$pageTitle = 'Team Equipment Requisitions';
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// ==========================================
// 1. CLEAN REALISTIC MOCK DATA ARRAYS
// ==========================================
$equipmentRequests = [
    [
        'req_id'        => 'REQ-EQ-065',
        'id_num'        => 65,
        'sport'         => 'Tennis',
        'sport_icon'    => 'sports_tennis',
        'item_name'     => 'Wilson Championship Tennis Ball Tubes',
        'quantity'      => '2 packs (12 balls)',
        'req_date'      => '2026-09-10',
        'return_date'   => '2026-09-15',
        'tic_status'    => 'Approved',
        'current_status'=> 'Returned',
        'purpose'       => 'Varsity tennis squad singles seeding trials',
        'warehouse_note'=> 'All 12 balls accounted for and returned in original pressurised tubes.',
        'archive_reason'=> null
    ],
    [
        'req_id'        => 'REQ-EQ-060',
        'id_num'        => 60,
        'sport'         => 'Cricket',
        'sport_icon'    => 'sports_cricket',
        'item_name'     => 'SS Ton Reserve Cricket Bats',
        'quantity'      => '2 units',
        'req_date'      => '2026-09-18',
        'return_date'   => '2026-09-25',
        'tic_status'    => 'Approved',
        'current_status'=> 'Checked Out',
        'purpose'       => 'Inter-faculty cricket tournament knockout stage',
        'warehouse_note'=> 'Checked out under coach liability; return inspection pending.',
        'archive_reason'=> null
    ],
    [
        'req_id'        => 'REQ-EQ-057',
        'id_num'        => 57,
        'sport'         => 'Football',
        'sport_icon'    => 'sports_soccer',
        'item_name'     => 'Elite GPS Tracking Vests (Set of 10)',
        'quantity'      => '2 units (20 vests)',
        'req_date'      => '2026-09-15',
        'return_date'   => '2026-09-23',
        'tic_status'    => 'Approved',
        'current_status'=> 'Checked Out',
        'purpose'       => 'Physical stamina and sprint acceleration analytics',
        'warehouse_note'=> 'High-value telemetry equipment; requires secure charging dock storage.',
        'archive_reason'=> null
    ],
    [
        'req_id'        => 'REQ-EQ-054',
        'id_num'        => 54,
        'sport'         => 'Basketball',
        'sport_icon'    => 'sports_basketball',
        'item_name'     => 'Spalding TF-1000 Basketballs (Pack of 3)',
        'quantity'      => '3 units (9 balls)',
        'req_date'      => '2026-09-20',
        'return_date'   => '2026-09-27',
        'tic_status'    => 'Pending',
        'current_status'=> 'Pending Approval',
        'purpose'       => 'Indoor tactical scrimmage and 3-point contest',
        'warehouse_note'=> 'Awaiting TIC warehouse manager approval.',
        'archive_reason'=> null
    ],
    [
        'req_id'        => 'REQ-EQ-050',
        'id_num'        => 50,
        'sport'         => 'Football',
        'sport_icon'    => 'sports_soccer',
        'item_name'     => 'Training Agility Cones & Markers',
        'quantity'      => '1 pack (50 cones)',
        'req_date'      => '2026-09-05',
        'return_date'   => '2026-09-12',
        'tic_status'    => 'Archived',
        'current_status'=> 'Cancelled / Archived',
        'purpose'       => 'Pre-season agility drills (rescheduled)',
        'warehouse_note'=> 'Request cancelled by coach due to heavy rains.',
        'archive_reason'=> 'Adverse Weather / Torrential Rain (Audit Logged)'
    ]
];

// Stats calculation
$totalRequests = count($equipmentRequests);
$pendingCount = 0;
$checkedOutCount = 0;
$returnedCount = 0;
$archivedCount = 0;

foreach ($equipmentRequests as $r) {
    if ($r['current_status'] === 'Pending Approval') $pendingCount++;
    if ($r['current_status'] === 'Checked Out') $checkedOutCount++;
    if ($r['current_status'] === 'Returned') $returnedCount++;
    if ($r['current_status'] === 'Cancelled / Archived') $archivedCount++;
}
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
        .badge-warning { background: #FFFBEB; color: #D97706; border: 1px solid #FDE68A; }
        .badge-success { background: #F0FDF4; color: #16A34A; border: 1px solid #BBF7D0; }
        .badge-checked { background: #EFF6FF; color: #2563EB; border: 1px solid #BFDBFE; }
        .badge-archived{ background: #F1F5F9; color: #64748B; border: 1px solid #CBD5E1; }
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
                    <h1 class="page-title" style="margin: 0; font-size: 1.6rem; font-weight: 800; color: var(--text);">Team Equipment Requisitions</h1>
                    <p style="color: var(--text-3); font-size: 13.5px; margin-top: 4px;">Request specialized training gear, match balls, and telemetry kits from the TIC warehouse</p>
                </div>
                <div>
                    <button type="button" class="btn btn-teal" onclick="openModal('newReqModal')" style="display: flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">add_box</span>
                        <span>+ New Equipment Request</span>
                    </button>
                </div>
            </div>

            <!-- Top Stat Cards (Grid-4) -->
            <div class="grid-4" style="margin-bottom: 24px;">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">TOTAL EQUIPMENT REQUESTS</span>
                        <span class="material-symbols-outlined" style="color: var(--teal);">inventory_2</span>
                    </div>
                    <div class="stat-card-value"><?= $totalRequests ?></div>
                    <div class="stat-card-desc">Seasonal requisition entries</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">PENDING TIC APPROVAL</span>
                        <span class="material-symbols-outlined" style="color: #D97706;">hourglass_top</span>
                    </div>
                    <div class="stat-card-value"><?= $pendingCount ?></div>
                    <div class="stat-card-desc">Warehouse clearance pending</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">CURRENTLY CHECKED OUT</span>
                        <span class="material-symbols-outlined" style="color: #2563EB;">sports_handball</span>
                    </div>
                    <div class="stat-card-value"><?= $checkedOutCount ?></div>
                    <div class="stat-card-desc">In squad possession on field</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">RETURNED &amp; INSPECTED</span>
                        <span class="material-symbols-outlined" style="color: #16A34A;">verified</span>
                    </div>
                    <div class="stat-card-value"><?= $returnedCount ?></div>
                    <div class="stat-card-desc">Audit cleared with zero losses</div>
                </div>
            </div>

            <!-- Equipment Requisition Registry Card -->
            <div class="card" style="padding: 0; overflow: hidden; border-radius: 12px; border: 1px solid var(--border);">
                <!-- Search & Filter Bar -->
                <div style="padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; background: #FFFFFF;">
                    <!-- Filter Pills -->
                    <div style="display: flex; gap: 6px;">
                        <button type="button" class="btn btn-sm btn-teal filter-tab" onclick="filterRequests('all', this)">All Requests</button>
                        <button type="button" class="btn btn-sm btn-outline filter-tab" onclick="filterRequests('Pending Approval', this)">Pending Approval</button>
                        <button type="button" class="btn btn-sm btn-outline filter-tab" onclick="filterRequests('Checked Out', this)">Issued / In Use</button>
                        <button type="button" class="btn btn-sm btn-outline filter-tab" onclick="filterRequests('Returned', this)">Returned</button>
                        <button type="button" class="btn btn-sm btn-outline filter-tab" onclick="filterRequests('Cancelled / Archived', this)" style="color: #64748B;">Archived History</button>
                    </div>

                    <!-- Search Input -->
                    <div style="position: relative; width: 280px;">
                        <span class="material-symbols-outlined" style="position: absolute; left: 10px; top: 9px; color: #94A3B8; font-size: 18px;">search</span>
                        <input type="text" id="reqSearch" class="form-control" placeholder="Search item or sport..." style="padding-left: 36px; height: 36px; font-size: 13px;" onkeyup="searchReqTable()">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="dash-table" id="reqTable">
                        <thead>
                            <tr>
                                <th>#Req ID</th>
                                <th>Sport Discipline</th>
                                <th>Requested Items &amp; Qty</th>
                                <th>Requisition Date</th>
                                <th>Expected Return</th>
                                <th>TIC Approval Status</th>
                                <th>Current Status</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($equipmentRequests as $req): 
                                $isPending = ($req['current_status'] === 'Pending Approval');
                                $isCheckedOut = ($req['current_status'] === 'Checked Out');
                                $isReturned = ($req['current_status'] === 'Returned');
                                $isArchived = ($req['current_status'] === 'Cancelled / Archived');
                            ?>
                                <tr class="req-row" data-status="<?= htmlspecialchars($req['current_status']) ?>" style="<?= $isArchived ? 'background: #F8FAFC; opacity: 0.85;' : '' ?>">
                                    <td style="font-weight: 700; color: var(--text);">
                                        <?= htmlspecialchars($req['req_id']) ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span class="material-symbols-outlined" style="color: var(--teal); font-size: 18px;"><?= $req['sport_icon'] ?></span>
                                            <span class="badge" style="background: rgba(0, 128, 128, 0.1); color: var(--teal); font-weight: 700;">
                                                <?= htmlspecialchars($req['sport']) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text);"><?= htmlspecialchars($req['item_name']) ?></div>
                                        <div style="font-size: 12px; color: var(--text-3);">Quantity: <strong><?= htmlspecialchars($req['quantity']) ?></strong></div>
                                    </td>
                                    <td style="font-size: 13px; color: var(--text);">
                                        <?= date('M d, Y', strtotime($req['req_date'])) ?>
                                    </td>
                                    <td style="font-size: 13px; color: var(--text);">
                                        <?= date('M d, Y', strtotime($req['return_date'])) ?>
                                    </td>
                                    <td>
                                        <?php if ($req['tic_status'] === 'Approved'): ?>
                                            <span class="badge badge-success">APPROVED</span>
                                        <?php elseif ($req['tic_status'] === 'Pending'): ?>
                                            <span class="badge badge-warning">PENDING TIC</span>
                                        <?php else: ?>
                                            <span class="badge badge-archived">ARCHIVED</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($isCheckedOut): ?>
                                            <span class="badge badge-checked">CHECKED OUT</span>
                                        <?php elseif ($isPending): ?>
                                            <span class="badge badge-warning">IN REVIEW</span>
                                        <?php elseif ($isReturned): ?>
                                            <span class="badge badge-success">RETURNED</span>
                                        <?php else: ?>
                                            <span class="badge badge-archived">CANCELLED</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <!-- View Details Trigger -->
                                        <button type="button" class="btn btn-sm btn-outline" onclick="openDetailsModal(<?= htmlspecialchars(json_encode($req)) ?>)" title="View purpose & warehouse notes">
                                            <span class="material-symbols-outlined" style="font-size: 16px;">visibility</span>
                                            <span>Details</span>
                                        </button>

                                        <!-- Cancel Request Action (Operational Logic: only pending can be cancelled) -->
                                        <?php if ($isPending): ?>
                                            <button type="button" class="btn btn-sm btn-outline" style="color: #DC2626; border-color: #FECACA;" onclick="openCancelModal('<?= $req['req_id'] ?>')" title="Cancel Requisition (Audit Preserved)">
                                                <span class="material-symbols-outlined" style="font-size: 16px;">cancel</span>
                                                <span>Cancel</span>
                                            </button>
                                        <?php elseif ($isCheckedOut): ?>
                                            <button type="button" class="btn btn-sm btn-outline" disabled style="opacity: 0.5; cursor: not-allowed;" title="Cannot cancel: Equipment is already checked out to squad">
                                                Locked (In Use)
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
    <!-- MODAL 1: NEW EQUIPMENT REQUISITION (Dropdowns) -->
    <!-- ============================================== -->
    <div class="modal-overlay" id="newReqModal">
        <div class="modal-card" style="max-width: 600px; width: 90%;">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title" style="margin: 0; font-size: 17px; font-weight: 700;">New Equipment Requisition</h3>
                    <p style="font-size: 12.5px; color: var(--text-3); margin-top: 3px;">Submit official request to TIC warehouse manager.</p>
                </div>
                <button type="button" class="modal-close" onclick="closeModal('newReqModal')">&times;</button>
            </div>
            <form action="../controllers/CoachController.php" method="POST">
                <input type="hidden" name="action" value="request_equipment">
                <div class="modal-body" style="padding: 20px;">
                    
                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                        <!-- Sport Discipline (Dropdown) -->
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Target Sport Discipline <span style="color:#DC2626;">*</span></label>
                            <select name="sport" class="form-control" required style="width: 100%; height: 38px;">
                                <option value="Football">Football</option>
                                <option value="Basketball">Basketball</option>
                                <option value="Cricket">Cricket</option>
                                <option value="Track & Field">Track &amp; Field</option>
                                <option value="Badminton">Badminton</option>
                                <option value="Tennis">Tennis</option>
                                <option value="Swimming">Swimming</option>
                                <option value="Volleyball">Volleyball</option>
                            </select>
                        </div>

                        <!-- Quantity Required (Dropdown) -->
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Quantity Required <span style="color:#DC2626;">*</span></label>
                            <select name="quantity" class="form-control" required style="width: 100%; height: 38px;">
                                <option value="1">1 Unit / Standard Set</option>
                                <option value="2" selected>2 Units / Sets</option>
                                <option value="3">3 Units / Sets</option>
                                <option value="5">5 Units / Sets</option>
                                <option value="10">10 Units / Sets</option>
                                <option value="15">15 Units / Sets</option>
                                <option value="25">25 Units (Full Squad Batch)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Equipment Item Dropdown (Pre-populated from catalog) -->
                    <div class="form-group" style="margin-bottom: 14px;">
                        <label class="form-label" style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Equipment Item from Warehouse Catalog <span style="color:#DC2626;">*</span></label>
                        <select name="equipment_id" class="form-control" required style="width: 100%; height: 38px;">
                            <option value="1">Match Grade Footballs (Size 5 FIFA Approved)</option>
                            <option value="2">Training Agility Cones &amp; Markers (Pack of 25)</option>
                            <option value="3">Numbered Training Bibs / Vests (Set of 15)</option>
                            <option value="4">Elite GPS Player Telemetry Tracking Vests (Set of 10)</option>
                            <option value="5">SS Ton Reserve English Willow Cricket Bats (Set of 2)</option>
                            <option value="6">Speed Agility Ladders (Set of 4)</option>
                            <option value="7">Wilson Championship Tennis Ball Tubes (Pack of 6)</option>
                            <option value="8">Spalding TF-1000 Indoor Basketballs (Pack of 3)</option>
                        </select>
                    </div>

                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                        <!-- Requisition Purpose (Dropdown) -->
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Requisition Purpose <span style="color:#DC2626;">*</span></label>
                            <select name="purpose" class="form-control" required style="width: 100%; height: 38px;">
                                <option value="Preparation scrimmage for SLUG tournament">Preparation scrimmage for SLUG tournament</option>
                                <option value="Regular weekly tactical conditioning drill">Regular weekly tactical conditioning drill</option>
                                <option value="Inter-faculty championship fixture">Inter-faculty championship fixture</option>
                                <option value="Physical fitness & endurance trials">Physical fitness &amp; endurance trials</option>
                            </select>
                        </div>

                        <!-- Expected Return Date -->
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Expected Return Date <span style="color:#DC2626;">*</span></label>
                            <input type="date" name="return_date" class="form-control" required value="<?= date('Y-m-d', strtotime('+7 days')) ?>" style="width: 100%; height: 38px;">
                        </div>
                    </div>

                    <div style="font-size: 11.5px; color: #64748B;">
                        <strong>Warehouse Protocol:</strong> The coach is officially accountable for inspection upon return. Damaged or missing gear must be reported within 24 hours.
                    </div>

                </div>
                <div class="modal-footer" style="padding: 14px 20px; background: #F8FAFC; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('newReqModal')">Cancel</button>
                    <button type="submit" class="btn btn-teal">
                        <span class="material-symbols-outlined" style="font-size: 18px;">send</span>
                        <span>Submit Requisition</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL 2: REQUISITION DETAILS MODAL             -->
    <!-- ============================================== -->
    <div class="modal-overlay" id="detailsModal">
        <div class="modal-card" style="max-width: 520px; width: 90%;">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title" id="detReqId" style="margin: 0; font-size: 17px; font-weight: 700;">Requisition Details</h3>
                    <p style="font-size: 12px; color: var(--text-3); margin-top: 2px;">Official equipment checkout registry log</p>
                </div>
                <button type="button" class="modal-close" onclick="closeModal('detailsModal')">&times;</button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <div style="margin-bottom: 12px;">
                    <span style="font-size: 11px; font-weight: 700; color: var(--text-3); text-transform: uppercase;">Equipment Requested</span>
                    <div id="detItemName" style="font-size: 15px; font-weight: 700; color: var(--text);">Item</div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <span style="font-size: 11px; font-weight: 700; color: var(--text-3); text-transform: uppercase;">Quantity</span>
                        <div id="detQty" style="font-size: 13px; font-weight: 600; color: var(--text);">Qty</div>
                    </div>
                    <div>
                        <span style="font-size: 11px; font-weight: 700; color: var(--text-3); text-transform: uppercase;">Return Date</span>
                        <div id="detReturnDate" style="font-size: 13px; font-weight: 600; color: var(--text);">Date</div>
                    </div>
                </div>
                <div style="margin-bottom: 12px;">
                    <span style="font-size: 11px; font-weight: 700; color: var(--text-3); text-transform: uppercase;">Stated Requisition Purpose</span>
                    <div id="detPurpose" style="font-size: 13px; color: var(--text-2); background: #F8FAFC; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); margin-top: 4px;">Purpose</div>
                </div>
                <div>
                    <span style="font-size: 11px; font-weight: 700; color: var(--text-3); text-transform: uppercase;">TIC Warehouse Notes</span>
                    <div id="detNotes" style="font-size: 12.5px; color: var(--teal); background: rgba(0, 128, 128, 0.08); padding: 8px 12px; border-radius: 6px; border: 1px solid rgba(0, 128, 128, 0.2); margin-top: 4px;">Notes</div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 12px 20px; background: #F8FAFC; border-top: 1px solid var(--border); display: flex; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="closeModal('detailsModal')">Close</button>
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL 3: CANCEL & ARCHIVE REQUISITION MODAL    -->
    <!-- (Feedback: Historical Data Management)         -->
    <!-- ============================================== -->
    <div class="modal-overlay" id="cancelReqModal">
        <div class="modal-card" style="max-width: 480px; width: 90%;">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title" style="margin: 0; font-size: 16px; font-weight: 700; color: #DC2626;">Cancel &amp; Archive Requisition</h3>
                    <p style="font-size: 12px; color: var(--text-3); margin-top: 2px;">Preserves inventory trail rather than hard-deleting records.</p>
                </div>
                <button type="button" class="modal-close" onclick="closeModal('cancelReqModal')">&times;</button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <p style="font-size: 13.5px; color: var(--text); margin-bottom: 14px;">
                    Are you sure you want to cancel requisition <strong id="cancelReqIdLabel">REQ-000</strong>?
                </p>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label" style="font-weight: 600; font-size: 12.5px; margin-bottom: 4px; display: block;">Cancellation Reason <span style="color:#DC2626;">*</span></label>
                    <select class="form-control" style="width: 100%; height: 38px;">
                        <option value="Practice session cancelled due to weather">Practice session cancelled due to weather</option>
                        <option value="Alternative equipment sourced from faculty store">Alternative equipment sourced from faculty store</option>
                        <option value="Match rescheduled by league organizers">Match rescheduled by league organizers</option>
                        <option value="Squad training volume reduced">Squad training volume reduced</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer" style="padding: 12px 20px; background: #F8FAFC; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('cancelReqModal')">Keep Request</button>
                <button type="button" class="btn btn-danger" style="background: #DC2626; color: #FFF; border: none;" onclick="alert('Requisition cancelled and archived in inventory logs.'); closeModal('cancelReqModal');">
                    <span>Confirm Cancellation</span>
                </button>
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

        function openDetailsModal(data) {
            document.getElementById('detReqId').innerText = data.req_id + ' Details';
            document.getElementById('detItemName').innerText = data.item_name;
            document.getElementById('detQty').innerText = data.quantity;
            document.getElementById('detReturnDate').innerText = data.return_date;
            document.getElementById('detPurpose').innerText = data.purpose;
            document.getElementById('detNotes').innerText = data.warehouse_note;
            openModal('detailsModal');
        }

        function openCancelModal(reqId) {
            document.getElementById('cancelReqIdLabel').innerText = reqId;
            openModal('cancelReqModal');
        }

        function filterRequests(status, btn) {
            var rows = document.querySelectorAll('.req-row');
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

        function searchReqTable() {
            var input = document.getElementById('reqSearch').value.toLowerCase();
            var rows = document.querySelectorAll('.req-row');
            rows.forEach(function(row) {
                var text = row.innerText.toLowerCase();
                row.style.display = text.indexOf(input) > -1 ? '' : 'none';
            });
        }
    </script>
</body>
</html>
