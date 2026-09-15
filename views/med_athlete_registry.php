<?php
// ===== BACKEND CONNECTION PLACEHOLDER =====
// require_once '../config/database.php';
// require_once '../models/MedicalModel.php';
// $medModel         = new MedicalModel($db);
// $totalAthletes    = $medModel->getTotalRegisteredAthletes();
// $fitAthletes      = $medModel->getFitAthletesCount();
// $reviewAthletes   = $medModel->getReviewAthletesCount();
// $athleteRegistry  = $medModel->getAthleteRegistry();

$pageTitle      = 'Athlete Health Registry';
$totalAthletes  = 248;
$fitAthletes    = 236;
$reviewAthletes = 12;
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

<?php include 'partials/med_sidebar.php'; ?>

<div class="main-wrapper">
    <?php include 'partials/tic_header.php'; ?>

    <div class="page-content">

        <!-- Page Title & Add Button -->
        <div class="page-title-row" style="display:flex; justify-content:space-between; align-items:center;">
            <h2 class="page-title">Athlete Health Registry</h2>
            <button class="btn btn-primary" onclick="openModal('addAthleteModal')">
                <span class="material-symbols-outlined" style="font-size:16px">person_add</span> Add Student Record
            </button>
        </div>

        <!-- 3 Simple Stat Cards -->
        <div class="grid-3">
            <div class="stat-card">
                <div>
                    <div class="stat-label">Total Athletes</div>
                    <div class="stat-value"><?= $totalAthletes ?></div>
                </div>
                <div class="stat-icon">
                    <span class="material-symbols-outlined">groups</span>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Fit to Compete</div>
                    <div class="stat-value" style="color:var(--accent-dark);"><?= $fitAthletes ?></div>
                </div>
                <div class="stat-icon" style="color:var(--accent-dark);">
                    <span class="material-symbols-outlined">check_circle</span>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-label">Under Medical Review</div>
                    <div class="stat-value" style="color:#D97706;"><?= $reviewAthletes ?></div>
                </div>
                <div class="stat-icon" style="color:#D97706;">
                    <span class="material-symbols-outlined">health_metrics</span>
                </div>
            </div>
        </div>

        <!-- Registry Table Card -->
        <div class="table-card">
            <div class="table-toolbar">
                <span class="table-title">Registered Student Athletes</span>
                <div class="table-filters">
                    <div class="search-box">
                        <span class="material-symbols-outlined">search</span>
                        <input type="text" class="search-input" id="searchRegistry" placeholder="Search athlete, ID, sport…">
                    </div>
                </div>
            </div>

            <div class="table-scroll">
                <table class="data-table" id="registryTable">
                    <thead>
                        <tr>
                            <th>Athlete Name</th>
                            <th>Student ID</th>
                            <th>Faculty</th>
                            <th>Sport</th>
                            <th>Blood Group</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $athletes = [
                            ['Dilshan Perera', 'DP', '10000045', 'Management', 'Rugby',         'O+', 'Under Review',   'badge-warn', 'Nihal Perera — +94 77 452 9901'],
                            ['Payton Reese',  'PR', '10000701', 'Science',    'Basketball',    'A+', 'Fit to Compete', 'badge-ok',   'Elena Reese — +94 71 882 1190'],
                            ['Darby Hayes',   'DH', '10000226', 'Arts',       'Cricket',       'B+', 'Under Review',   'badge-warn', 'Samantha Hayes — +94 76 991 4432'],
                            ['Olivia Smith',  'OS', '10000117', 'Medicine',   'Swimming',      'O+', 'Fit to Compete', 'badge-ok',   'Mark Smith — +94 77 124 8830'],
                            ['Saman Arachchi','SA', '10000089', 'Law',        'Football',      'AB+','Under Review',   'badge-warn', 'Anoma Arachchi — +94 70 334 1120'],
                            ['Maria Smith',   'MS', '10000104', 'Science',    'Track & Field', 'A+', 'Under Review',   'badge-warn', 'George Smith — +94 77 901 2284'],
                            ['Kevin Raj',     'KR', '10000832', 'Management', 'Track & Field', 'B-', 'Fit to Compete', 'badge-ok',   'P. Raj — +94 72 443 8819'],
                        ];
                        foreach ($athletes as $a): ?>
                        <tr>
                            <td>
                                <div class="cell-flex">
                                    <div class="mini-avatar"><?= $a[1] ?></div>
                                    <span class="cell-main"><?= $a[0] ?></span>
                                </div>
                            </td>
                            <td><span class="cell-mono"><?= $a[2] ?></span></td>
                            <td><span class="cell-main"><?= $a[3] ?></span></td>
                            <td><span class="cell-main"><?= $a[4] ?></span></td>
                            <td><span class="badge badge-purple"><?= $a[5] ?></span></td>
                            <td><span class="badge <?= $a[7] ?>"><?= $a[6] ?></span></td>
                            <td>
                                <div class="cell-actions">
                                    <button class="btn btn-outline btn-sm"
                                            onclick="openProfileModal('<?= addslashes($a[0]) ?>', '<?= $a[2] ?>', '<?= addslashes($a[3]) ?>', '<?= $a[4] ?>', '<?= $a[5] ?>', '<?= $a[6] ?>', '<?= addslashes($a[8]) ?>')">
                                        View
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <span>Showing 1–7 of <?= $totalAthletes ?> registered athletes</span>
                <div class="page-btns">
                    <button class="page-btn" disabled><span class="material-symbols-outlined">chevron_left</span></button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn"><span class="material-symbols-outlined">chevron_right</span></button>
                </div>
            </div>
        </div>

    </div><!-- .page-content -->
</div><!-- .main-wrapper -->

<!-- ===== MODAL 1: ADD STUDENT RECORD ===== -->
<div class="modal-overlay" id="addAthleteModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Add Student Medical Record</span>
            <button class="modal-close" onclick="closeModal('addAthleteModal')">&times;</button>
        </div>
        <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Student medical record registered!'); closeModal('addAthleteModal');">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-input" placeholder="e.g. Kasun Silva" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Student ID</label>
                        <input type="text" class="form-input" placeholder="e.g. 10001055" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Faculty</label>
                        <select class="form-select" required>
                            <option value="">Select Faculty…</option>
                            <option>Science</option>
                            <option>Medicine</option>
                            <option>Arts</option>
                            <option>Management</option>
                            <option>Law</option>
                            <option>Technology</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sport</label>
                        <select class="form-select" required>
                            <option value="">Select Sport…</option>
                            <option>Rugby</option>
                            <option>Cricket</option>
                            <option>Football</option>
                            <option>Basketball</option>
                            <option>Swimming</option>
                            <option>Track & Field</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Blood Group</label>
                        <select class="form-select" required>
                            <option value="O+">O+</option>
                            <option value="A+">A+</option>
                            <option value="B+">B+</option>
                            <option value="AB+">AB+</option>
                            <option value="O-">O-</option>
                            <option value="A-">A-</option>
                            <option value="B-">B-</option>
                            <option value="AB-">AB-</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Medical Status</label>
                        <select class="form-select">
                            <option value="Fit to Compete">Fit to Compete</option>
                            <option value="Under Review">Under Review</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Emergency Contact (Name & Phone)</label>
                    <input type="text" class="form-input" placeholder="e.g. N. Silva (Father) — 0771234567" required>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Medical / Allergy Notes</label>
                    <input type="text" class="form-input" placeholder="e.g. Asthma, Penicillin allergy, or None">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addAthleteModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Record</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL 2: VIEW STUDENT PROFILE ===== -->
<div class="modal-overlay" id="viewProfileModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Student Medical Profile</span>
            <button class="modal-close" onclick="closeModal('viewProfileModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:14px; padding-bottom:12px; border-bottom:1px solid var(--border);">
                <div class="mini-avatar" id="profAvatar" style="width:40px; height:40px; font-size:14px;">DP</div>
                <div>
                    <div style="font-weight:700; font-size:15px;" id="profName">Dilshan Perera</div>
                    <div style="font-size:12px; color:var(--text-3);" id="profMeta">ID: 10000045 · Management · Rugby</div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px 16px; font-size:13px;">
                <div>
                    <div style="color:var(--text-3); font-size:11px; text-transform:uppercase; font-weight:700;">Blood Group</div>
                    <div style="font-weight:600; color:var(--danger);" id="profBlood">O+</div>
                </div>
                <div>
                    <div style="color:var(--text-3); font-size:11px; text-transform:uppercase; font-weight:700;">Medical Status</div>
                    <span class="badge badge-ok" id="profStatus">Fit to Compete</span>
                </div>
                <div style="grid-column: span 2;">
                    <div style="color:var(--text-3); font-size:11px; text-transform:uppercase; font-weight:700;">Emergency Contact</div>
                    <div style="font-weight:600;" id="profContact">Nihal Perera — +94 77 452 9901</div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('viewProfileModal')">Close</button>
        </div>
    </div>
</div>

<script src="../assets/js/dashboard.js"></script>
<script>
    setupSearch('searchRegistry', 'registryTable');

    function openProfileModal(name, id, faculty, sport, blood, status, contact) {
        document.getElementById('profName').textContent = name;
        document.getElementById('profMeta').textContent = 'ID: ' + id + ' · ' + faculty + ' · ' + sport;
        document.getElementById('profBlood').textContent = blood;
        document.getElementById('profContact').textContent = contact;
        
        var stBadge = document.getElementById('profStatus');
        stBadge.textContent = status;
        stBadge.className = status === 'Fit to Compete' ? 'badge badge-ok' : 'badge badge-warn';

        var initials = name.split(' ').map(function(w){ return w[0]; }).join('').toUpperCase();
        document.getElementById('profAvatar').textContent = initials;
        openModal('viewProfileModal');
    }
</script>
</body>
</html>
