<?php
require_once '../config/auth_guard.php';
requireRole('TIC');

require_once '../models/Equipment.php';
$equipModel = new Equipment();
$inventory  = $equipModel->getInventory();
$stats      = $equipModel->getStats();

$totalItems = $stats['total_items'];
$totalOut   = $stats['total_out'];
$lowStock   = $stats['low_stock'];

$pdo = Database::connect();
$allSports = $pdo->query("SELECT sport_id, sport_name FROM sports ORDER BY sport_name ASC")->fetchAll();

$pageTitle = 'Stock Inventory';
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
    <style>
        .filter-pills {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }
        .filter-pill {
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid var(--border);
            background: var(--card);
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text-2);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .filter-pill:hover {
            border-color: var(--accent);
            color: var(--text);
        }
        .filter-pill.active {
            background: var(--side-bg);
            color: #fff;
            border-color: var(--side-bg);
        }
    </style>
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

        <!-- Sub-Nav -->
        <div class="sub-nav">
            <a href="tic_equipment_requests.php">Pending Requests</a>
            <a href="tic_checkout_log.php">Check-out Log</a>
            <a href="tic_stock_inventory.php" class="active">Stock Inventory</a>
        </div>

        <!-- Top 3 Stats Cards (Accurate & Synced) -->
        <div class="grid-3">
            <div class="stat-card">
                <div>
                    <div class="stat-label">Total Units in Stock</div>
                    <div class="stat-value"><?= number_format($totalItems) ?></div>
                </div>
                <div class="stat-icon">
                    <span class="material-symbols-outlined">inventory_2</span>
                </div>
            </div>
            <div class="stat-card">
                <div>
                    <div class="stat-label">Currently Checked Out</div>
                    <div class="stat-value"><?= number_format($totalOut) ?> <span style="font-size:13px; font-weight:500; color:var(--text-3);">units</span></div>
                </div>
                <div class="stat-icon" style="color:#3B82F6">
                    <span class="material-symbols-outlined">shopping_cart_checkout</span>
                </div>
            </div>
            <div class="stat-card">
                <div>
                    <div class="stat-label">Low Stock Alerts</div>
                    <div class="stat-value" style="color:var(--danger);"><?= $lowStock ?></div>
                </div>
                <div class="stat-icon" style="color:var(--danger)">
                    <span class="material-symbols-outlined">warning</span>
                </div>
            </div>
        </div>

        <!-- Action Cards -->
        <div class="grid-2 mb-14">
            <button class="action-card" onclick="openModal('logItemModal')">
                <div class="action-card-icon">
                    <span class="material-symbols-outlined">add</span>
                </div>
                <div>
                    <div class="action-card-title">Log or Restock Item</div>
                    <div class="action-card-desc">Auto-detects existing gear & restocks units</div>
                </div>
            </button>
            <button class="action-card" onclick="alert('Exporting inventory ledger to CSV / PDF...');">
                <div class="action-card-icon">
                    <span class="material-symbols-outlined">download</span>
                </div>
                <div>
                    <div class="action-card-title">Export Inventory Data</div>
                    <div class="action-card-desc">Download complete equipment ledger</div>
                </div>
            </button>
        </div>

        <!-- Sport Filtering Buttons / Pills -->
        <div class="filter-pills">
            <button type="button" class="filter-pill active" onclick="filterBySport('all', this)">All Sports</button>
            <?php foreach ($allSports as $sp): ?>
                <button type="button" class="filter-pill" onclick="filterBySport('<?= htmlspecialchars(strtolower($sp['sport_name'])) ?>', this)">
                    <?= htmlspecialchars($sp['sport_name']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Table -->
        <div class="table-card">
            <div class="table-toolbar">
                <span class="table-title">Inventory List</span>
                <div class="table-filters">
                    <div class="search-box">
                        <span class="material-symbols-outlined">search</span>
                        <input type="text" class="search-input" id="searchInv" placeholder="Search inventory…">
                    </div>
                </div>
            </div>

            <div class="table-scroll">
                <table class="data-table" id="invTable">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Sport / Category</th>
                            <th>Units in Stock</th>
                            <th>Condition</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventory as $item): 
                            $isLow = $item['quantity_in_stock'] <= 5;
                        ?>
                        <tr data-sport="<?= htmlspecialchars(strtolower($item['sport_name'])) ?>">
                            <td><span class="cell-main"><?= htmlspecialchars($item['item_name']) ?></span></td>
                            <td><span class="badge badge-info"><?= htmlspecialchars($item['sport_name']) ?></span></td>
                            <td><span class="cell-main font-mono"><?= $item['quantity_in_stock'] ?></span></td>
                            <td>
                                <?php if ($item['condition_status'] === 'New'): ?>
                                    <span class="badge badge-ok">New</span>
                                <?php elseif ($item['condition_status'] === 'Good'): ?>
                                    <span class="badge" style="background:#F1F5F9; color:#475569;">Good</span>
                                <?php else: ?>
                                    <span class="badge badge-warn">Needs Repair</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item['quantity_in_stock'] == 0): ?>
                                    <span class="badge badge-danger">Out of Stock</span>
                                <?php elseif ($isLow): ?>
                                    <span class="badge badge-warn">Low Stock</span>
                                <?php else: ?>
                                    <span class="badge badge-ok">In Stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="cell-actions">
                                    <button type="button" class="btn btn-outline btn-sm" onclick="openEditModal(<?= $item['equipment_id'] ?>, '<?= htmlspecialchars(addslashes($item['item_name'])) ?>', <?= $item['quantity_in_stock'] ?>, '<?= htmlspecialchars($item['condition_status']) ?>')">
                                        <span class="material-symbols-outlined" style="font-size:15px;">edit</span> Edit
                                    </button>
                                    <form action="../controllers/TicController.php?action=delete_equipment" method="POST" style="display:inline;">
                                        <input type="hidden" name="equipment_id" value="<?= $item['equipment_id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return USMS.confirmForm(event, this, 'Are you sure you want to remove <?= htmlspecialchars(addslashes($item['item_name'])) ?> from inventory?')">
                                            <span class="material-symbols-outlined" style="font-size:15px;">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <span id="invCountText">Showing <?= count($inventory) ?> equipment entries</span>
                <div class="page-btns">
                    <button class="page-btn" disabled><span class="material-symbols-outlined">chevron_left</span></button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn"><span class="material-symbols-outlined">chevron_right</span></button>
                </div>
            </div>
        </div>

    </div><!-- .page-content -->
</div><!-- .main-wrapper -->

<!-- ===== LOG / RESTOCK MODAL (WITH AUTO-DETECTION) ===== -->
<div class="modal-overlay" id="logItemModal">
    <div class="modal" style="max-width: 520px;">
        <div class="modal-header">
            <span class="modal-title">Log or Restock Equipment</span>
            <button class="modal-close" onclick="closeModal('logItemModal')">&times;</button>
        </div>
        <form action="../controllers/TicController.php?action=add_equipment" method="POST">
            <div class="modal-body">
                <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--r); padding: 10px 12px; margin-bottom: 14px; font-size: 12.5px; color: var(--text-2); display: flex; gap: 8px; align-items: flex-start;">
                    <span class="material-symbols-outlined" style="font-size: 18px; color: var(--side-bg);">info</span>
                    <div><strong>Smart Restock:</strong> If the item name & sport match an existing item in stock, the quantity will be added automatically without creating duplicate rows.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Item Name</label>
                    <input type="text" class="form-input" name="item_name" placeholder="e.g. Wilson Pro Staff Tennis Rackets" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Sport Category</label>
                        <select class="form-select searchable" name="sport_id" data-placeholder="Select sport…" required>
                            <option value="" disabled selected>Select sport…</option>
                            <?php foreach ($allSports as $sp): ?>
                                <option value="<?= $sp['sport_id'] ?>"><?= htmlspecialchars($sp['sport_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Quantity to Add</label>
                        <input type="number" class="form-input" name="quantity" value="10" min="1" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Condition Status</label>
                    <select class="form-select" name="condition">
                        <option value="New">New</option>
                        <option value="Good" selected>Good</option>
                        <option value="Needs Repair">Needs Repair</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('logItemModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save / Restock Item</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== EDIT / UPDATE MODAL ===== -->
<div class="modal-overlay" id="editItemModal">
    <div class="modal" style="max-width: 500px;">
        <div class="modal-header">
            <span class="modal-title">Edit Equipment Details</span>
            <button class="modal-close" onclick="closeModal('editItemModal')">&times;</button>
        </div>
        <form action="../controllers/TicController.php?action=edit_equipment" method="POST">
            <input type="hidden" name="equipment_id" id="edit_equipment_id">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Item Name</label>
                    <input type="text" class="form-input" name="item_name" id="edit_item_name" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Current Stock Quantity</label>
                        <input type="number" class="form-input" name="quantity" id="edit_quantity" min="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Condition Status</label>
                        <select class="form-select" name="condition" id="edit_condition">
                            <option value="New">New</option>
                            <option value="Good">Good</option>
                            <option value="Needs Repair">Needs Repair</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editItemModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Item</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/dashboard.js"></script>
<script>
setupTablePagination('invTable', 8, 'searchInv');

function openEditModal(id, name, qty, condition) {
    document.getElementById('edit_equipment_id').value = id;
    document.getElementById('edit_item_name').value = name;
    document.getElementById('edit_quantity').value = qty;
    document.getElementById('edit_condition').value = condition;
    openModal('editItemModal');
}

var currentSportFilter = 'all';

function filterBySport(sport, btn) {
    currentSportFilter = sport;
    var pills = document.querySelectorAll('.filter-pill');
    pills.forEach(function(p) { p.classList.remove('active'); });
    if (btn) btn.classList.add('active');

    var rows = document.querySelectorAll('#invTable tbody tr');
    var visible = 0;
    rows.forEach(function(row) {
        var rowSport = row.getAttribute('data-sport') || '';
        if (sport === 'all' || rowSport.indexOf(sport) > -1) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });

    var countText = document.getElementById('invCountText');
    if (countText) {
        countText.textContent = 'Showing ' + visible + ' items';
    }
}
</script>
</body>
</html>
