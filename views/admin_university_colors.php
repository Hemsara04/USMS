<?php
/**
 * University Colours & Awards Management Portal
 * USMS - University Sports Management System
 * System Admin module for nominating, reviewing, and conferring university sports colours
 */

require_once __DIR__ . '/../config/auth_guard.php';
requireRole(['System_Admin', 'Admin']);
require_once __DIR__ . '/../models/Admin.php';

$activeAdminPage = 'colors';
$adminModel = new Admin();

// Filters
$filterStatus = trim($_GET['status'] ?? '');
$filterSport  = trim($_GET['sport_id'] ?? '');
$filterSeason = trim($_GET['season_id'] ?? '');
$search       = trim($_GET['q'] ?? '');

// Data retrieval
$colorsStats   = $adminModel->getColorsStats();
$colorsList    = $adminModel->getAllColors($filterStatus, $filterSport, $filterSeason, $search);
$athletesList  = $adminModel->getEligibleAthletes();
$sportsList    = $adminModel->getAllSportsList();
$seasonsList   = $adminModel->getAllSeasonsList();

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$pageTitle = 'University Colours';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>University Colours &amp; Awards | USMS Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Material+Symbols+Outlined">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .colors-hero {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }
        .colors-hero h1 {
            margin: 0 0 4px;
            font-size: 20px;
            font-weight: 700;
            color: var(--admin-navy);
        }
        .colors-hero p {
            margin: 0;
            color: var(--admin-muted);
            font-size: 13px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 20px;
        }
        @media (max-width: 900px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        .metric {
            min-height: 90px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 16px 18px;
            background: #fff;
            border: 1px solid var(--admin-border);
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }
        .metric > span {
            color: #475467;
            font-size: 11.5px;
            letter-spacing: .05em;
            text-transform: uppercase;
            font-weight: 600;
        }
        .metric strong {
            font-size: 24px;
            font-weight: 700;
            color: #101828;
            line-height: 1.2;
        }
        .metric small {
            color: var(--admin-teal);
            font-size: 11.5px;
            font-weight: 500;
        }
        .metric.dark {
            color: #fff;
            background: var(--admin-navy);
        }
        .metric.dark > span, .metric.dark small {
            color: #cbd5e1;
        }
        .metric.dark strong {
            color: #fff;
        }

        .filter-card {
            background: #fff;
            border: 1px solid var(--admin-border);
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
        }
        .filter-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .filter-search-box {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 0 10px;
            border-radius: 6px;
            background: #f8fafc;
            border: 1px solid var(--admin-border);
        }
        .filter-search-box input {
            height: 36px;
            border: 0;
            outline: 0;
            background: transparent;
            font-size: 13px;
            width: 220px;
        }
        .filter-select {
            height: 36px;
            padding: 0 10px;
            border: 1px solid var(--admin-border);
            border-radius: 6px;
            background: #fff;
            font-size: 13px;
            outline: none;
        }

        .athlete-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .athlete-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(0, 153, 171, 0.12);
            color: var(--admin-teal);
            font-weight: 700;
            font-size: 13px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }
        .athlete-info strong {
            display: block;
            font-size: 13px;
            color: #101828;
        }
        .athlete-info span {
            display: block;
            font-size: 11px;
            color: #64748b;
        }

        /* Status Chips */
        .color-status-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .color-status-chip.nominated {
            background: #fffbeb;
            color: #b54708;
            border: 1px solid #fef08a;
        }
        .color-status-chip.approved {
            background: #eff8ff;
            color: #175cd3;
            border: 1px solid #bfdbfe;
        }
        .color-status-chip.awarded {
            background: #ecfdf3;
            color: #067647;
            border: 1px solid #abefc6;
        }

        .btn-action-sm {
            padding: 4px 9px;
            border-radius: 5px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.15s;
        }
        .btn-action-approve {
            background: #eff8ff;
            color: #175cd3;
            border-color: #bfdbfe;
        }
        .btn-action-approve:hover {
            background: #dbeafe;
        }
        .btn-action-award {
            background: #ecfdf3;
            color: #067647;
            border-color: #abefc6;
        }
        .btn-action-award:hover {
            background: #d1fadf;
        }
        .btn-action-revert {
            background: #fffbeb;
            color: #b54708;
            border-color: #fde68a;
        }
        .btn-action-revert:hover {
            background: #fef3c7;
        }
        .btn-action-delete {
            background: #f8fafc;
            color: #64748b;
            border-color: #e2e8f0;
        }
        .btn-action-delete:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        /* Modal Backdrop & Box */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(4, 20, 39, 0.65);
            backdrop-filter: blur(3px);
            z-index: 50;
            display: grid;
            place-items: center;
            padding: 20px;
        }
        .modal-box {
            width: min(520px, 95vw);
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 30px rgba(0,0,0,0.2);
            animation: modalFadeIn 0.2s ease-out;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .modal-header {
            padding: 16px 20px;
            background: #f8fafc;
            border-bottom: 1px solid var(--admin-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .modal-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: var(--admin-navy);
        }
        .modal-header p {
            margin: 3px 0 0;
            font-size: 12px;
            color: var(--admin-muted);
        }
        .modal-close-btn {
            border: 0;
            background: transparent;
            font-size: 18px;
            cursor: pointer;
            color: var(--admin-muted);
        }
        .modal-form {
            padding: 20px;
            display: grid;
            gap: 14px;
        }
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 10px;
            padding-top: 14px;
            border-top: 1px solid var(--admin-border);
        }
    </style>
</head>
<body class="dashboard">
    <?php require __DIR__ . '/partials/admin_sidebar.php'; ?>
    <div class="main-wrapper">
        <?php require __DIR__ . '/partials/tic_header.php'; ?>
        <div class="page-content">

            <?php if ($flashSuccess): ?>
                <div class="admin-alert success-alert" role="alert" style="display:flex; align-items:center; gap:8px; padding:12px 16px; border-radius:6px; margin-bottom:16px; background:#ecfdf3; color:#067647; border:1px solid #abefc6;">
                    <span class="material-symbols-outlined" style="font-size:18px;">check_circle</span>
                    <div><?= htmlspecialchars($flashSuccess) ?></div>
                    <button type="button" onclick="this.parentElement.remove()" style="margin-left:auto; border:0; background:transparent; cursor:pointer; color:inherit;">✕</button>
                </div>
            <?php endif; ?>

            <?php if ($flashError): ?>
                <div class="admin-alert error-alert" role="alert" style="display:flex; align-items:center; gap:8px; padding:12px 16px; border-radius:6px; margin-bottom:16px; background:#fffbeb; color:#b54708; border:1px solid #fedf89;">
                    <span class="material-symbols-outlined" style="font-size:18px;">warning</span>
                    <div><?= htmlspecialchars($flashError) ?></div>
                    <button type="button" onclick="this.parentElement.remove()" style="margin-left:auto; border:0; background:transparent; cursor:pointer; color:inherit;">✕</button>
                </div>
            <?php endif; ?>

            <div class="colors-hero">
                <div>
                    <h1>University Colours &amp; Awards</h1>
                    <p>Review athlete achievements, manage colors night nominations, and officially confer university sports honours.</p>
                </div>
                <button type="button" class="admin-primary-button" onclick="openNominateModal()">
                    <span class="material-symbols-outlined">emoji_events</span> Nominate / Record Colours
                </button>
            </div>

            <!-- Proportional 4-Stat Metric Grid -->
            <div class="stats-grid">
                <article class="metric">
                    <span>Total Honours</span>
                    <strong><?= number_format($colorsStats['total']) ?></strong>
                    <small>All registered honours</small>
                </article>
                <article class="metric dark">
                    <span>Awarded Colours</span>
                    <strong><?= number_format($colorsStats['awarded']) ?></strong>
                    <small>Conferred Varsity Colours</small>
                </article>
                <article class="metric">
                    <span>Approved Nominations</span>
                    <strong style="color: #175cd3;"><?= number_format($colorsStats['approved']) ?></strong>
                    <small>Ready for Colours Night</small>
                </article>
                <article class="metric">
                    <span>Pending Nominations</span>
                    <strong style="<?= $colorsStats['nominated'] > 0 ? 'color:var(--admin-warning);' : '' ?>"><?= number_format($colorsStats['nominated']) ?></strong>
                    <small><?= $colorsStats['nominated'] > 0 ? 'Review awaiting decision' : 'No pending queue' ?></small>
                </article>
            </div>

            <!-- Live Filter & Search Toolbar -->
            <div class="filter-card">
                <div class="filter-controls">
                    <div class="filter-search-box">
                        <span class="material-symbols-outlined" style="color:var(--admin-muted); font-size:18px;">search</span>
                        <input type="search" id="colorsSearchInput" placeholder="Search athlete, index, sport, award..." oninput="filterColorsTable(this.value)">
                    </div>

                    <select id="statusFilterSelect" class="filter-select" onchange="filterColorsStatus(this.value)">
                        <option value="all">All Award Statuses</option>
                        <option value="Nominated" <?= $filterStatus === 'Nominated' ? 'selected' : '' ?>>Nominated</option>
                        <option value="Approved" <?= $filterStatus === 'Approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="Awarded" <?= $filterStatus === 'Awarded' ? 'selected' : '' ?>>Awarded</option>
                    </select>

                    <select id="sportFilterSelect" class="filter-select" onchange="filterColorsSport(this.value)">
                        <option value="all">All Sports</option>
                        <?php foreach ($sportsList as $sp): ?>
                            <option value="<?= htmlspecialchars($sp['sport_name']) ?>">
                                <?= htmlspecialchars($sp['sport_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="button" class="admin-secondary-button" onclick="resetFilters()">
                        <span class="material-symbols-outlined" style="font-size:16px;">refresh</span> Reset
                    </button>
                </div>
            </div>

            <!-- Honours Records Table -->
            <section class="admin-card">
                <div class="admin-card-header">
                    <div>
                        <h2>Colours &amp; Awards Registry</h2>
                        <p>Institutional record of athlete honorary titles and distinctions</p>
                    </div>
                    <span class="admin-chip neutral" id="colorsCountBadge">
                        <?= count($colorsList) ?> records
                    </span>
                </div>

                <div class="admin-table-wrap">
                    <table class="admin-table" id="colorsTable">
                        <thead>
                            <tr>
                                <th>Honoured Athlete</th>
                                <th>Discipline</th>
                                <th>Season / Term</th>
                                <th>Award Title</th>
                                <th>Conferral Status</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($colorsList)): ?>
                                <tr id="emptyRow">
                                    <td colspan="6" style="text-align:center; padding:32px 14px; color:var(--admin-muted);">
                                        <span class="material-symbols-outlined" style="font-size:32px; display:block; margin-bottom:6px; color:var(--admin-teal);">emoji_events</span>
                                        No university colours records found. Click "Nominate / Record Colours" to add nominations.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($colorsList as $c): 
                                    $st = strtolower($c['status']);
                                    $initials = strtoupper(substr($c['athlete_name'], 0, 1) . substr(strstr($c['athlete_name'], ' ') ?: ' ', 1, 1));
                                ?>
                                    <tr data-status="<?= htmlspecialchars($c['status']) ?>" data-sport="<?= htmlspecialchars($c['sport_name']) ?>">
                                        <td>
                                            <div class="athlete-cell">
                                                <div class="athlete-avatar">
                                                    <?= htmlspecialchars(trim($initials) ?: 'U') ?>
                                                </div>
                                                <div class="athlete-info">
                                                    <strong><?= htmlspecialchars($c['athlete_name']) ?></strong>
                                                    <span><?= htmlspecialchars($c['index_number']) ?> &bull; <?= htmlspecialchars($c['faculty']) ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong style="color:#1e293b;"><?= htmlspecialchars($c['sport_name']) ?></strong>
                                        </td>
                                        <td>
                                            <span>Season <?= htmlspecialchars($c['season_year']) ?></span>
                                            <small style="display:block; color:#64748b; font-size:11px;">Term <?= htmlspecialchars($c['academic_year']) ?></small>
                                        </td>
                                        <td>
                                            <strong style="color:var(--admin-teal);"><?= htmlspecialchars($c['award_name']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="color-status-chip <?= $st ?>">
                                                <?= htmlspecialchars($c['status']) ?>
                                            </span>
                                        </td>
                                        <td style="text-align:right;">
                                            <div style="display:inline-flex; align-items:center; gap:6px;">
                                                <?php if ($c['status'] === 'Nominated'): ?>
                                                    <form method="POST" action="../controllers/AdminController.php?action=update_color_status" style="display:inline;">
                                                        <input type="hidden" name="award_id" value="<?= $c['award_id'] ?>">
                                                        <input type="hidden" name="status" value="Approved">
                                                        <button type="submit" class="btn-action-sm btn-action-approve" title="Approve nomination for Colours Night">
                                                            <span class="material-symbols-outlined" style="font-size:14px;">check</span> Approve
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="../controllers/AdminController.php?action=update_color_status" style="display:inline;">
                                                        <input type="hidden" name="award_id" value="<?= $c['award_id'] ?>">
                                                        <input type="hidden" name="status" value="Awarded">
                                                        <button type="submit" class="btn-action-sm btn-action-award" title="Directly confer colours award">
                                                            <span class="material-symbols-outlined" style="font-size:14px;">verified</span> Award
                                                        </button>
                                                    </form>
                                                <?php elseif ($c['status'] === 'Approved'): ?>
                                                    <form method="POST" action="../controllers/AdminController.php?action=update_color_status" style="display:inline;">
                                                        <input type="hidden" name="award_id" value="<?= $c['award_id'] ?>">
                                                        <input type="hidden" name="status" value="Awarded">
                                                        <button type="submit" class="btn-action-sm btn-action-award" title="Officially confer colours">
                                                            <span class="material-symbols-outlined" style="font-size:14px;">verified</span> Award
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="../controllers/AdminController.php?action=update_color_status" style="display:inline;">
                                                        <input type="hidden" name="award_id" value="<?= $c['award_id'] ?>">
                                                        <input type="hidden" name="status" value="Nominated">
                                                        <button type="submit" class="btn-action-sm btn-action-revert" title="Revert to nominated">
                                                            <span class="material-symbols-outlined" style="font-size:14px;">undo</span> Revert
                                                        </button>
                                                    </form>
                                                <?php elseif ($c['status'] === 'Awarded'): ?>
                                                    <form method="POST" action="../controllers/AdminController.php?action=update_color_status" style="display:inline;">
                                                        <input type="hidden" name="award_id" value="<?= $c['award_id'] ?>">
                                                        <input type="hidden" name="status" value="Approved">
                                                        <button type="submit" class="btn-action-sm btn-action-revert" title="Revert to approved nomination">
                                                            <span class="material-symbols-outlined" style="font-size:14px;">undo</span> Revert
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <form method="POST" action="../controllers/AdminController.php?action=delete_color" style="display:inline;" onsubmit="return confirm('Revoke and delete this Colours award record?');">
                                                    <input type="hidden" name="award_id" value="<?= $c['award_id'] ?>">
                                                    <button type="submit" class="btn-action-sm btn-action-delete" title="Delete record">
                                                        <span class="material-symbols-outlined" style="font-size:14px;">delete</span>
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
            </section>
        </div>
    </div>

    <!-- Nominate / Record Colours Modal -->
    <div id="nominateColorModal" class="modal-backdrop" style="display:none;">
        <div class="modal-box">
            <div class="modal-header">
                <div>
                    <h3>Nominate / Record Colours Award</h3>
                    <p>Register an official varsity colours nomination or conferral</p>
                </div>
                <button type="button" class="modal-close-btn" onclick="closeModal('nominateColorModal')">✕</button>
            </div>
            <form method="POST" action="../controllers/AdminController.php?action=create_color" class="modal-form" onsubmit="return validateColorForm(event)">
                <div class="admin-field">
                    <label for="colorAthleteId">Student Athlete *</label>
                    <select id="colorAthleteId" name="athlete_id" required>
                        <option value="" disabled selected>Select Varsity Athlete…</option>
                        <?php foreach ($athletesList as $ath): ?>
                            <option value="<?= $ath['athlete_id'] ?>">
                                <?= htmlspecialchars($ath['full_name']) ?> (<?= htmlspecialchars($ath['index_number']) ?> - <?= htmlspecialchars($ath['faculty']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-field">
                    <label for="colorSportId">Sport Discipline *</label>
                    <select id="colorSportId" name="sport_id" required>
                        <option value="" disabled selected>Select Sport…</option>
                        <?php foreach ($sportsList as $sp): ?>
                            <option value="<?= $sp['sport_id'] ?>">
                                <?= htmlspecialchars($sp['sport_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-field">
                    <label for="colorSeasonId">Athletic Season *</label>
                    <select id="colorSeasonId" name="season_id" required>
                        <option value="" disabled selected>Select Season…</option>
                        <?php foreach ($seasonsList as $sea): ?>
                            <option value="<?= $sea['season_id'] ?>">
                                Season <?= htmlspecialchars($sea['season_year']) ?> (Term <?= htmlspecialchars($sea['academic_year']) ?> - <?= htmlspecialchars($sea['status']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-field">
                    <label for="colorAwardName">Award Designation Title *</label>
                    <input type="text" id="colorAwardName" name="award_name" required placeholder="e.g. University Full Colours, Half Colours, Sports Merit Award" value="University Full Colours">
                </div>

                <div class="admin-field">
                    <label for="colorStatus">Initial Status *</label>
                    <select id="colorStatus" name="status" required>
                        <option value="Nominated" selected>Nominated (Under review for Colours Night)</option>
                        <option value="Approved">Approved (Vetted by Sports Council)</option>
                        <option value="Awarded">Awarded (Officially Conferred)</option>
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="button" class="admin-secondary-button" onclick="closeModal('nominateColorModal')">Cancel</button>
                    <button type="submit" class="admin-primary-button">Record Colours</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openNominateModal() {
            document.getElementById('nominateColorModal').style.display = 'grid';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-backdrop')) {
                e.target.style.display = 'none';
            }
        });

        function filterColorsTable(q) {
            q = (q || '').toLowerCase();
            var rows = document.querySelectorAll('#colorsTable tbody tr:not(#emptyRow)');
            var count = 0;
            rows.forEach(function(row) {
                var text = row.textContent.toLowerCase();
                var matches = text.indexOf(q) > -1;
                row.style.display = matches ? '' : 'none';
                if (matches) count++;
            });
            updateCountBadge(count);
        }

        function filterColorsStatus(status) {
            var rows = document.querySelectorAll('#colorsTable tbody tr:not(#emptyRow)');
            var count = 0;
            rows.forEach(function(row) {
                if (status === 'all' || row.dataset.status === status) {
                    row.style.display = '';
                    count++;
                } else {
                    row.style.display = 'none';
                }
            });
            updateCountBadge(count);
        }

        function filterColorsSport(sport) {
            var rows = document.querySelectorAll('#colorsTable tbody tr:not(#emptyRow)');
            var count = 0;
            rows.forEach(function(row) {
                if (sport === 'all' || row.dataset.sport === sport) {
                    row.style.display = '';
                    count++;
                } else {
                    row.style.display = 'none';
                }
            });
            updateCountBadge(count);
        }

        function resetFilters() {
            document.getElementById('colorsSearchInput').value = '';
            document.getElementById('statusFilterSelect').value = 'all';
            document.getElementById('sportFilterSelect').value = 'all';
            var rows = document.querySelectorAll('#colorsTable tbody tr:not(#emptyRow)');
            rows.forEach(function(row) { row.style.display = ''; });
            updateCountBadge(rows.length);
        }

        function updateCountBadge(cnt) {
            var badge = document.getElementById('colorsCountBadge');
            if (badge) badge.textContent = cnt + ' records';
        }

        function validateColorForm(e) {
            var ath = document.getElementById('colorAthleteId').value;
            var sp  = document.getElementById('colorSportId').value;
            var sea = document.getElementById('colorSeasonId').value;
            var name = document.getElementById('colorAwardName').value.trim();

            if (!ath || !sp || !sea || !name) {
                alert('Please fill in all required fields to record University Colours.');
                e.preventDefault();
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
