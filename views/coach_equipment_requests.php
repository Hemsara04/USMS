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

$pageTitle = 'Team Equipment Requisitions';

// Realistic Mock Data for Equipment Requests
$stats = [
    'total_requests' => 14,
    'pending_approval' => 3,
    'checked_out' => 8,
    'returned' => 3
];

$equipmentRequests = [
    [
        'req_id' => 'REQ-EQ-104',
        'sport' => 'Football',
        'sport_icon' => 'sports_soccer',
        'item_name' => 'Football Training Cones & Space Markers',
        'quantity' => 25,
        'req_date' => '2026-09-22',
        'return_date' => '2026-09-29',
        'tic_status' => 'Pending',
        'current_status' => 'In Review',
        'purpose' => 'Preparation drills and agility space conditioning for upcoming Inter-Faculty Derby vs Faculty of Science.',
        'notes' => 'Awaiting warehouse inspection from TIC Officer Mr. Wickramasinghe.'
    ],
    [
        'req_id' => 'REQ-EQ-103',
        'sport' => 'Basketball',
        'sport_icon' => 'sports_basketball',
        'item_name' => 'Reversible Scrimmage Bibs (Set of 20 - Neon/Navy)',
        'quantity' => 20,
        'req_date' => '2026-09-21',
        'return_date' => '2026-09-28',
        'tic_status' => 'Pending',
        'current_status' => 'In Review',
        'purpose' => 'Tactical half-court offensive transition drills ahead of SLUG tournament opening.',
        'notes' => 'Submitted for TIC clearance. Stock availability confirmed in Indoor Warehouse.'
    ],
    [
        'req_id' => 'REQ-EQ-102',
        'sport' => 'Football',
        'sport_icon' => 'sports_soccer',
        'item_name' => 'Match Grade FIFA Pro Footballs (Size 5)',
        'quantity' => 6,
        'req_date' => '2026-09-18',
        'return_date' => '2026-09-25',
        'tic_status' => 'Approved',
        'current_status' => 'Issued & Active',
        'purpose' => 'High-velocity shooting drills and competitive match simulation scrimmage.',
        'notes' => 'Checked out by Coach Silva on Sep 18. Checked and pressurized by TIC staff.'
    ],
    [
        'req_id' => 'REQ-EQ-101',
        'sport' => 'Cricket',
        'sport_icon' => 'sports_cricket',
        'item_name' => 'SG Tournament Leather Cricket Balls (Box of 12)',
        'quantity' => 12,
        'req_date' => '2026-09-17',
        'return_date' => '2026-09-26',
        'tic_status' => 'Approved',
        'current_status' => 'Issued & Active',
        'purpose' => 'Turf pitch match simulation against Moratuwa and intense net bowling sessions.',
        'notes' => 'Issued from Main Store. Maintained in equipment locker under coach supervision.'
    ],
    [
        'req_id' => 'REQ-EQ-100',
        'sport' => 'Badminton',
        'sport_icon' => 'sports_tennis',
        'item_name' => 'Yonex Aerosensa 30 Feather Shuttlecock Tubes',
        'quantity' => 8,
        'req_date' => '2026-09-15',
        'return_date' => '2026-09-20',
        'tic_status' => 'Approved',
        'current_status' => 'Returned',
        'purpose' => 'National University Invitational trials and singles ranking knockout stage.',
        'notes' => 'Returned on Sep 20. 2 tubes unopened and verified by TIC inventory controller.'
    ],
    [
        'req_id' => 'REQ-EQ-099',
        'sport' => 'Track & Field',
        'sport_icon' => 'timer',
        'item_name' => 'Seiko Digital Timing Stopwatches & Split Timers',
        'quantity' => 4,
        'req_date' => '2026-09-12',
        'return_date' => '2026-09-16',
        'tic_status' => 'Rejected',
        'current_status' => 'Denied',
        'purpose' => 'Athletics sprint acceleration time trials and relay baton exchange recording.',
        'notes' => 'Denied by TIC: Equipment currently reserved for Inter-University Games Secretariat.'
    ],
    [
        'req_id' => 'REQ-EQ-098',
        'sport' => 'Football',
        'sport_icon' => 'sports_soccer',
        'item_name' => 'Quick-Foot Speed Agility Ladders (6m)',
        'quantity' => 4,
        'req_date' => '2026-09-10',
        'return_date' => '2026-09-15',
        'tic_status' => 'Approved',
        'current_status' => 'Returned',
        'purpose' => 'Pre-season athletic conditioning and footwork reactivity training.',
        'notes' => 'Returned in immaculate condition. Cleared in warehouse stock logs.'
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
        /* USMS Coach Portal - Module Specific Enhancements */
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
        .badge-issued {
            background: var(--accent-bg);
            color: var(--accent-dark);
            border: 1px solid rgba(0, 128, 128, 0.28);
        }
        .badge-returned {
            background: #F1F5F9;
            color: #475569;
            border: 1px solid #CBD5E1;
        }
        .badge-denied {
            background: var(--danger-bg);
            color: var(--danger);
            border: 1px solid var(--danger-border);
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
                <h2 class="page-title">Team Equipment Requisitions</h2>
                <p style="font-size: 13px; color: var(--text-3); margin-top: 3px;">
                    Request specialized training gear, match balls, and kits from the TIC warehouse
                </p>
            </div>
            <div>
                <button type="button" class="btn btn-teal" onclick="openModal('newEquipModal')">
                    <span class="material-symbols-outlined" style="font-size: 18px;">add_circle</span>
                    + New Equipment Request
                </button>
            </div>
        </div>

        <!-- Top Stat Cards (Grid-4) -->
        <div class="grid-4">
            <div class="stat-card">
                <div>
                    <div class="stat-label">Total Equipment Requests</div>
                    <div class="stat-value"><?= $stats['total_requests'] ?></div>
                </div>
                <div class="stat-icon">
                    <span class="material-symbols-outlined">inventory_2</span>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Pending TIC Approval</div>
                    <div class="stat-value" style="color: #D97706;"><?= $stats['pending_approval'] ?></div>
                </div>
                <div class="stat-icon" style="color: #D97706; background: #FFFBEB;">
                    <span class="material-symbols-outlined">hourglass_top</span>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Currently Checked Out</div>
                    <div class="stat-value" style="color: var(--accent);"><?= $stats['checked_out'] ?></div>
                </div>
                <div class="stat-icon" style="color: var(--accent); background: var(--accent-bg);">
                    <span class="material-symbols-outlined">sports_handball</span>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Returned & Inspected</div>
                    <div class="stat-value" style="color: #16A34A;"><?= $stats['returned'] ?></div>
                </div>
                <div class="stat-icon" style="color: #16A34A; background: #F0FDF4;">
                    <span class="material-symbols-outlined">task_alt</span>
                </div>
            </div>
        </div>

        <!-- Data Table Card -->
        <div class="table-card">
            <div class="table-toolbar">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="table-title">Equipment Requisition Registry</span>
                    <span class="tab-counter"><?= count($equipmentRequests) ?> total</span>
                </div>

                <div class="table-filters">
                    <!-- Filter Pills -->
                    <div class="tab-group" id="filterPills">
                        <button type="button" class="tab-btn active" onclick="filterByStatus('all', this)">
                            All Requests
                        </button>
                        <button type="button" class="tab-btn" onclick="filterByStatus('Pending', this)">
                            Pending Approval
                        </button>
                        <button type="button" class="tab-btn" onclick="filterByStatus('Issued', this)">
                            Issued / In Use
                        </button>
                        <button type="button" class="tab-btn" onclick="filterByStatus('Returned', this)">
                            Returned
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="search-box">
                        <input type="text" class="search-input" id="searchEquipInput" placeholder="Search item or sport…" onkeyup="filterEquipTable()">
                        <span class="material-symbols-outlined">search</span>
                    </div>
                </div>
            </div>

            <div class="table-scroll">
                <table class="dash-table data-table" id="equipTable">
                    <thead>
                        <tr>
                            <th>#Req ID</th>
                            <th>Sport Discipline</th>
                            <th>Requested Items & Qty</th>
                            <th>Requisition Date</th>
                            <th>Expected Return</th>
                            <th>TIC Approval Status</th>
                            <th>Current Status</th>
                            <th class="text-right" style="text-align: right; padding-right: 18px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($equipmentRequests as $req): 
                            // Approval Badge
                            $approvalBadge = 'badge-warning';
                            if ($req['tic_status'] === 'Approved') {
                                $approvalBadge = 'badge-success';
                            } elseif ($req['tic_status'] === 'Rejected') {
                                $approvalBadge = 'badge-danger';
                            }

                            // Current Status Badge
                            $currentBadge = 'badge-subtle';
                            if (strpos($req['current_status'], 'Issued') !== false) {
                                $currentBadge = 'badge-issued';
                            } elseif ($req['current_status'] === 'Returned') {
                                $currentBadge = 'badge-returned';
                            } elseif ($req['current_status'] === 'In Review') {
                                $currentBadge = 'badge-warning';
                            } elseif ($req['current_status'] === 'Denied') {
                                $currentBadge = 'badge-denied';
                            }
                            
                            $isIssued = (strpos($req['current_status'], 'Issued') !== false);
                            $isReturned = ($req['current_status'] === 'Returned');
                            $reqJson = htmlspecialchars(json_encode($req), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr data-status="<?= htmlspecialchars($req['tic_status']) ?>" data-current="<?= htmlspecialchars($req['current_status']) ?>">
                            <td>
                                <span class="cell-mono" style="font-weight: 700; color: var(--text);"><?= htmlspecialchars($req['req_id']) ?></span>
                            </td>
                            <td>
                                <div class="cell-flex">
                                    <span class="mini-icon">
                                        <span class="material-symbols-outlined"><?= htmlspecialchars($req['sport_icon']) ?></span>
                                    </span>
                                    <span class="badge badge-info"><?= htmlspecialchars($req['sport']) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="cell-main"><?= htmlspecialchars($req['item_name']) ?></span>
                                <span class="cell-sub" style="color: var(--text-2); font-weight: 600;">
                                    Quantity: <strong style="color: var(--accent-dark);"><?= (int)$req['quantity'] ?> units</strong>
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 13px; font-weight: 500;"><?= date('M d, Y', strtotime($req['req_date'])) ?></span>
                            </td>
                            <td>
                                <span style="font-size: 13px; font-weight: 600; color: var(--text-2);">
                                    <?= date('M d, Y', strtotime($req['return_date'])) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $approvalBadge ?>">
                                    <?= htmlspecialchars($req['tic_status']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $currentBadge ?>">
                                    <?= htmlspecialchars($req['current_status']) ?>
                                </span>
                            </td>
                            <td style="text-align: right; padding-right: 18px;">
                                <div class="cell-actions">
                                    <button type="button" class="btn btn-outline btn-sm" onclick='viewDetails(<?= $reqJson ?>)'>
                                        <span class="material-symbols-outlined" style="font-size: 15px;">visibility</span>
                                        View Details
                                    </button>
                                    <?php if ($isIssued || $isReturned): ?>
                                        <button type="button" class="btn btn-outline btn-sm" disabled style="opacity: 0.45; cursor: not-allowed;" title="Cannot cancel already issued or returned equipment">
                                            Cancel
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="alert('Requisition <?= $req['req_id'] ?> cancelled successfully.')">
                                            Cancel
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

<!-- MODAL 1: New Equipment Requisition Modal -->
<div class="modal-overlay" id="newEquipModal">
    <div class="modal" style="width: 540px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-outlined" style="color: var(--accent); font-size: 22px;">post_add</span>
                <span class="modal-title">New Equipment Requisition</span>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('newEquipModal')">&times;</button>
        </div>
        <form onsubmit="event.preventDefault(); alert('Requisition request submitted to TIC warehouse successfully!'); closeModal('newEquipModal');">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Target Sport Discipline</label>
                        <select class="form-select" id="targetSport" required>
                            <option value="">Select Sport…</option>
                            <option value="Football" selected>Football</option>
                            <option value="Basketball">Basketball</option>
                            <option value="Cricket">Cricket</option>
                            <option value="Badminton">Badminton</option>
                            <option value="Track & Field">Track & Field</option>
                            <option value="Swimming">Swimming</option>
                            <option value="Rugby">Rugby</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Equipment Item</label>
                        <select class="form-select" id="equipItem" required>
                            <option value="">Choose item…</option>
                            <option value="Training Balls">Training Balls (Set of 10)</option>
                            <option value="Bibs / Pinnies">Practice Bibs / Pinnies (Set of 20)</option>
                            <option value="Cones & Space Markers">Cones & Space Markers</option>
                            <option value="Goal Nets">Match Regulation Goal Nets</option>
                            <option value="Corner Flags">Spring-Loaded Corner Flags (Set of 4)</option>
                            <option value="Agility Ladders">6-Meter Agility Ladders</option>
                            <option value="Digital Stopwatches">Digital Memory Stopwatches</option>
                            <option value="Helmets & Protective Pads">Batting Helmets & Pads</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Quantity Required</label>
                        <input type="number" class="form-input" id="reqQty" min="1" max="100" value="10" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Warehouse Storage</label>
                        <input type="text" class="form-input" value="Main Sports Ground Depot" readonly style="background: var(--bg); color: var(--text-2);">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Required Date</label>
                        <input type="date" class="form-input" id="reqDate" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Expected Return Date</label>
                        <input type="date" class="form-input" id="returnDate" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Purpose / Session Details</label>
                    <textarea class="form-textarea" id="reqPurpose" placeholder="e.g. Preparation scrimmage for SLUG semi-final against University of Moratuwa..." required rows="3">Preparation scrimmage and tactical set-piece drills for SLUG tournament.</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('newEquipModal')">Cancel</button>
                <button type="submit" class="btn btn-teal">
                    <span class="material-symbols-outlined" style="font-size: 16px;">send</span>
                    Submit Requisition
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: View Requisition Details Modal -->
<div class="modal-overlay" id="viewEquipModal">
    <div class="modal" style="width: 520px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-outlined" style="color: var(--accent); font-size: 22px;">visibility</span>
                <span class="modal-title">Requisition Details</span>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('viewEquipModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--r); padding: 14px 16px; margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                    <div>
                        <span id="dtlReqId" style="font-family: monospace; font-size: 14px; font-weight: 700; color: var(--text);">REQ-EQ-104</span>
                        <div id="dtlItemName" style="font-size: 15px; font-weight: 700; color: var(--accent-dark); margin-top: 2px;">Item Name</div>
                    </div>
                    <span id="dtlStatusBadge" class="badge badge-warning">Pending</span>
                </div>
                <div style="font-size: 12.5px; color: var(--text-2);">
                    Sport: <strong id="dtlSport" style="color: var(--text);">Football</strong> &nbsp;|&nbsp; 
                    Quantity: <strong id="dtlQty" style="color: var(--text);">25 units</strong>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div style="padding: 10px 12px; border: 1px solid var(--border); border-radius: 6px;">
                    <span style="font-size: 11px; font-weight: 700; color: var(--text-3); text-transform: uppercase; display: block;">Requisition Date</span>
                    <span id="dtlReqDate" style="font-size: 13.5px; font-weight: 600; color: var(--text);">Sep 22, 2026</span>
                </div>
                <div style="padding: 10px 12px; border: 1px solid var(--border); border-radius: 6px;">
                    <span style="font-size: 11px; font-weight: 700; color: var(--text-3); text-transform: uppercase; display: block;">Expected Return Date</span>
                    <span id="dtlReturnDate" style="font-size: 13.5px; font-weight: 600; color: var(--text);">Sep 29, 2026</span>
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label class="form-label" style="font-size: 12px; color: var(--text-3); text-transform: uppercase;">Purpose & Session Details</label>
                <div id="dtlPurpose" style="font-size: 13.5px; color: var(--text); background: var(--bg); padding: 10px 12px; border-radius: 6px; border: 1px solid var(--border); line-height: 1.5;">
                    -
                </div>
            </div>

            <div>
                <label class="form-label" style="font-size: 12px; color: var(--text-3); text-transform: uppercase;">Warehouse Inspection & TIC Notes</label>
                <div id="dtlNotes" style="font-size: 13px; color: var(--text-2); background: var(--bg); padding: 10px 12px; border-radius: 6px; border: 1px solid var(--border); line-height: 1.5;">
                    -
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('viewEquipModal')">Close</button>
        </div>
    </div>
</div>

<!-- JavaScript Modal Helper & Live Search/Filter -->
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

// View Details Modal Populator
function viewDetails(req) {
    document.getElementById('dtlReqId').textContent = req.req_id;
    document.getElementById('dtlItemName').textContent = req.item_name;
    document.getElementById('dtlSport').textContent = req.sport;
    document.getElementById('dtlQty').textContent = req.quantity + ' units';
    document.getElementById('dtlReqDate').textContent = req.req_date;
    document.getElementById('dtlReturnDate').textContent = req.return_date;
    document.getElementById('dtlPurpose').textContent = req.purpose;
    document.getElementById('dtlNotes').textContent = req.notes;

    var badge = document.getElementById('dtlStatusBadge');
    badge.textContent = req.tic_status;
    badge.className = 'badge ' + (req.tic_status === 'Approved' ? 'badge-success' : (req.tic_status === 'Rejected' ? 'badge-danger' : 'badge-warning'));

    openModal('viewEquipModal');
}

// Live Search
function filterEquipTable() {
    var q = document.getElementById('searchEquipInput').value.toLowerCase();
    var rows = document.querySelectorAll('#equipTable tbody tr');
    rows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.indexOf(q) > -1 ? '' : 'none';
    });
}

// Filter by Status Pills
function filterByStatus(status, btn) {
    var pills = document.querySelectorAll('#filterPills .tab-btn');
    pills.forEach(function(p) { p.classList.remove('active'); });
    btn.classList.add('active');

    var rows = document.querySelectorAll('#equipTable tbody tr');
    rows.forEach(function(row) {
        if (status === 'all') {
            row.style.display = '';
        } else if (status === 'Pending') {
            row.style.display = (row.dataset.status === 'Pending') ? '' : 'none';
        } else if (status === 'Issued') {
            row.style.display = (row.dataset.current.indexOf('Issued') > -1) ? '' : 'none';
        } else if (status === 'Returned') {
            row.style.display = (row.dataset.current === 'Returned') ? '' : 'none';
        }
    });
}
</script>

</body>
</html>
