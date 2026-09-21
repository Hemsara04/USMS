<?php
require_once __DIR__ . '/../config/auth_guard.php';
requireRole('President');

require_once __DIR__ . '/../models/Announcement.php';

$announcementModel = new Announcement();
$stats = $announcementModel->getStats();
$announcements = $announcementModel->getAllAnnouncements();

$pageTitle = 'Announcement Command Center';
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

    <!-- SIDEBAR -->
    <?php include __DIR__ . '/partials/pres_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <div class="main-wrapper">
        <?php include __DIR__ . '/partials/tic_header.php'; ?>

        <div class="page-content">

            <!-- Flash Messages -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success" style="margin-bottom:18px; display:flex; align-items:center; gap:8px;">
                    <span class="material-symbols-outlined" style="font-size:20px;">check_circle</span>
                    <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger" style="margin-bottom:18px; display:flex; align-items:center; gap:8px;">
                    <span class="material-symbols-outlined" style="font-size:20px;">error</span>
                    <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <!-- Page Title Row -->
            <div class="page-title-row">
                <div>
                    <h2 class="page-title">Campus Sports Announcement Center</h2>
                    <p style="font-size:13px; color:var(--text-3); margin-top:2px;">Broadcast official notices, urgent sports bulletins, and updates to athletes, coaches, and university staff</p>
                </div>
                <div style="margin-left: auto; flex-shrink: 0;">
                    <button class="btn btn-teal" onclick="openModal('composeModal')" style="display:flex; align-items:center; gap:6px; padding:9px 18px; font-size:13.5px; font-weight:600;">
                        <span class="material-symbols-outlined" style="font-size:18px;">add_circle</span>
                        Broadcast Announcement
                    </button>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid-4" style="margin-bottom:20px;">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Active Broadcasts</div>
                        <div class="stat-value" style="color:var(--teal);"><?= $stats['active'] ?></div>
                    </div>
                    <div class="stat-icon" style="color:var(--teal);">
                        <span class="material-symbols-outlined">campaign</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-label">Urgent Bulletins</div>
                        <div class="stat-value" style="color:var(--danger);"><?= $stats['urgent'] ?></div>
                    </div>
                    <div class="stat-icon" style="color:var(--danger);">
                        <span class="material-symbols-outlined">emergency</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-label">Archived Notices</div>
                        <div class="stat-value" style="color:var(--text-3);"><?= $stats['archived'] ?></div>
                    </div>
                    <div class="stat-icon" style="color:var(--text-3);">
                        <span class="material-symbols-outlined">inventory_2</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-label">Total Published</div>
                        <div class="stat-value" style="color:var(--text-1);"><?= $stats['total'] ?></div>
                    </div>
                    <div class="stat-icon" style="color:var(--text-2);">
                        <span class="material-symbols-outlined">history_edu</span>
                    </div>
                </div>
            </div>

            <!-- Filter & Search Toolbar -->
            <div class="toolbar" style="margin-bottom:16px;">
                <div class="tab-group" id="filterTabs">
                    <button class="tab-btn active" onclick="setFeedFilter('all', this)">
                        All Notices <span class="tab-counter" id="cntAll"><?= count($announcements) ?></span>
                    </button>
                    <button class="tab-btn" onclick="setFeedFilter('Active', this)">
                        Active <span class="tab-counter"><?= $stats['active'] ?></span>
                    </button>
                    <button class="tab-btn" onclick="setFeedFilter('Urgent', this)">
                        Urgent <span class="tab-counter critical"><?= $stats['urgent'] ?></span>
                    </button>
                    <button class="tab-btn" onclick="setFeedFilter('Archived', this)">
                        Archived <span class="tab-counter"><?= $stats['archived'] ?></span>
                    </button>
                </div>

                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                    <div style="min-width:180px;">
                        <select class="form-select" id="audienceFilter" onchange="applyFilters()" style="padding:7px 10px; font-size:13px; height:38px; border:1.5px solid #CBD5E1;">
                            <option value="">All Target Audiences</option>
                            <option value="All Users">All Users</option>
                            <option value="Student Athletes">Student Athletes</option>
                            <option value="Coaches & Staff">Coaches & Staff</option>
                            <option value="Medical Staff">Medical Staff</option>
                            <option value="TIC">TIC</option>
                        </select>
                    </div>
                    <div class="search-box" style="width:260px;">
                        <span class="material-symbols-outlined">search</span>
                        <input type="text" class="search-input" id="searchFeed" placeholder="Search notices, title, content…" oninput="applyFilters()">
                    </div>
                </div>
            </div>

            <!-- Feed Status / Counter -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <div style="font-size:13px; color:var(--text-3); font-weight:600;" id="feedCountText">
                    Showing <?= count($announcements) ?> announcements
                </div>
            </div>

            <!-- Announcement Card Grid -->
            <div class="announcement-grid" id="announcementGrid">
                <?php if (empty($announcements)): ?>
                    <div style="grid-column: 1 / -1; background:#FFFFFF; border:1px solid var(--border); border-radius:10px; padding:48px 24px; text-align:center;">
                        <span class="material-symbols-outlined" style="font-size:48px; color:var(--text-3); margin-bottom:8px;">campaign</span>
                        <h3 style="font-size:16px; color:var(--text-1); margin-bottom:4px;">No Announcements Found</h3>
                        <p style="font-size:13px; color:var(--text-3); max-width:400px; margin:0 auto 16px auto;">There are currently no official announcements published. Click below to compose your first notice.</p>
                        <button class="btn btn-teal btn-sm" onclick="openModal('composeModal')">Broadcast Announcement</button>
                    </div>
                <?php else: ?>
                    <?php foreach ($announcements as $ann): 
                        $priorityClass = 'priority-' . strtolower($ann['priority']);
                        $statusClass = 'status-' . strtolower($ann['status']);
                        
                        $badgeClass = 'badge-normal';
                        $priorityIcon = 'campaign';
                        if ($ann['priority'] === 'Urgent') {
                            $badgeClass = 'badge-urgent';
                            $priorityIcon = 'emergency';
                        } elseif ($ann['priority'] === 'Important') {
                            $badgeClass = 'badge-important';
                            $priorityIcon = 'announcement';
                        }

                        $dateDisplay = date('M d, Y · h:i A', strtotime($ann['created_at']));
                    ?>
                        <div class="announcement-card <?= $priorityClass ?> <?= $statusClass ?>"
                             data-status="<?= htmlspecialchars($ann['status']) ?>"
                             data-priority="<?= htmlspecialchars($ann['priority']) ?>"
                             data-audience="<?= htmlspecialchars($ann['target_audience']) ?>"
                             data-title="<?= htmlspecialchars(strtolower($ann['title'])) ?>"
                             data-content="<?= htmlspecialchars(strtolower($ann['content'])) ?>">
                            
                            <!-- Card Header -->
                            <div class="announcement-card-header">
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <span class="badge-audience">
                                        <span class="material-symbols-outlined" style="font-size:13px;">group</span>
                                        <?= htmlspecialchars($ann['target_audience']) ?>
                                    </span>
                                    <?php if ($ann['status'] === 'Active'): ?>
                                        <span class="badge badge-ok" style="font-size:10.5px; padding:2px 7px;">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-gray" style="font-size:10.5px; padding:2px 7px;">Archived</span>
                                    <?php endif; ?>
                                </div>
                                <span class="<?= $badgeClass ?>">
                                    <span class="material-symbols-outlined" style="font-size:13px;"><?= $priorityIcon ?></span>
                                    <?= htmlspecialchars($ann['priority']) ?>
                                </span>
                            </div>

                            <!-- Card Body -->
                            <div class="announcement-card-body">
                                <div class="announcement-title">
                                    <?= htmlspecialchars($ann['title']) ?>
                                </div>
                                <div class="announcement-content-text"><?= nl2br(htmlspecialchars(trim($ann['content']))) ?></div>

                                <div class="announcement-meta">
                                    <div style="display:flex; align-items:center; gap:4px;">
                                        <span class="material-symbols-outlined" style="font-size:14px; color:var(--text-3);">schedule</span>
                                        <span><?= $dateDisplay ?></span>
                                    </div>
                                    <div style="font-weight:600; color:var(--text-2);">
                                        By <?= htmlspecialchars($ann['publisher_name']) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Footer / Actions -->
                            <div class="announcement-card-footer">
                                <!-- Status Toggle Form -->
                                <form action="../controllers/PresidentController.php?action=toggle_status" method="POST" style="display:inline;"
                                      onsubmit="return USMS.confirmForm(this, {
                                          title: '<?= ($ann['status'] === 'Active') ? 'Archive Notice' : 'Reactivate Notice' ?>',
                                          message: 'Are you sure you want to mark this notice as <?= ($ann['status'] === 'Active') ? 'Archived' : 'Active' ?>?',
                                          type: 'teal',
                                          confirmText: 'Confirm'
                                      });">
                                    <input type="hidden" name="announcement_id" value="<?= (int)$ann['announcement_id'] ?>">
                                    <button type="submit" class="btn btn-outline btn-sm" style="font-size:11.5px; padding:3px 8px;" title="Toggle active status">
                                        <span class="material-symbols-outlined" style="font-size:14px;">
                                            <?= ($ann['status'] === 'Active') ? 'archive' : 'unarchive' ?>
                                        </span>
                                        <?= ($ann['status'] === 'Active') ? 'Archive' : 'Activate' ?>
                                    </button>
                                </form>

                                <!-- Action Buttons -->
                                <div class="announcement-actions">
                                    <button type="button" class="btn btn-outline btn-sm" style="padding:4px 8px;" onclick="openViewModal(<?= htmlspecialchars(json_encode($ann)) ?>)" title="View Announcement">
                                        <span class="material-symbols-outlined" style="font-size:15px;">visibility</span>
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm" style="padding:4px 8px;" onclick="openEditModal(<?= htmlspecialchars(json_encode($ann)) ?>)" title="Edit Announcement">
                                        <span class="material-symbols-outlined" style="font-size:15px; color:var(--teal);">edit</span>
                                    </button>
                                    <form action="../controllers/PresidentController.php?action=delete_announcement" method="POST" style="display:inline;"
                                          onsubmit="return USMS.confirmForm(this, {
                                              title: 'Delete Announcement',
                                              message: 'Are you sure you want to permanently delete this announcement? It will be removed immediately from all user notifications.',
                                              type: 'danger',
                                              confirmText: 'Yes, Delete'
                                          });">
                                        <input type="hidden" name="announcement_id" value="<?= (int)$ann['announcement_id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" style="padding:4px 8px;" title="Delete Announcement">
                                            <span class="material-symbols-outlined" style="font-size:15px;">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Empty Search Results Placeholder (hidden by default) -->
            <div id="noMatchState" style="display:none; background:#FFFFFF; border:1px solid var(--border); border-radius:10px; padding:40px 20px; text-align:center; margin-top:20px;">
                <span class="material-symbols-outlined" style="font-size:42px; color:var(--text-3); margin-bottom:8px;">search_off</span>
                <h3 style="font-size:15px; color:var(--text-1); margin-bottom:4px;">No matching announcements found</h3>
                <p style="font-size:13px; color:var(--text-3);">Try adjusting your search query or audience filter.</p>
            </div>

        </div><!-- .page-content -->
    </div><!-- .main-wrapper -->

    <!-- ===== MODAL 1: COMPOSE ANNOUNCEMENT ===== -->
    <div class="modal-overlay" id="composeModal">
        <div class="modal" style="max-width:620px;">
            <div class="modal-header">
                <div>
                    <span class="modal-title">Broadcast Official Announcement</span>
                    <p style="font-size:12px; color:var(--text-3); margin-top:2px;">Publish notice to athlete portals, coaching staff, and university notifications</p>
                </div>
                <button class="modal-close" onclick="closeModal('composeModal')">&times;</button>
            </div>
            <form action="../controllers/PresidentController.php?action=create_announcement" method="POST"
                  onsubmit="return USMS.confirmForm(this, {
                      title: 'Publish Announcement',
                      message: 'Are you sure you want to broadcast this announcement across the university sports network?',
                      type: 'teal',
                      confirmText: 'Broadcast Notice'
                  });">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Announcement Title <span style="color:var(--danger)">*</span></label>
                        <input type="text" name="title" class="form-input" placeholder="e.g., Inter-Faculty Track & Field Trials 2026" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Target Audience <span style="color:var(--danger)">*</span></label>
                            <select name="target_audience" class="form-select" required>
                                <option value="All Users" selected>All Users (Campus-Wide)</option>
                                <option value="Student Athletes">Student Athletes</option>
                                <option value="Coaches & Staff">Coaches & Staff</option>
                                <option value="Medical Staff">Medical Staff</option>
                                <option value="TIC">TIC (Teachers-in-Charge)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Priority Level <span style="color:var(--danger)">*</span></label>
                            <select name="priority" class="form-select" required>
                                <option value="Normal" selected>Normal</option>
                                <option value="Important">Important</option>
                                <option value="Urgent">Urgent (Emergency Alert)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Initial Status</label>
                        <select name="status" class="form-select">
                            <option value="Active" selected>Active (Broadcast Immediately)</option>
                            <option value="Archived">Archived (Draft / Save for later)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Announcement Content / Message Body <span style="color:var(--danger)">*</span></label>
                        <textarea name="content" class="form-textarea" rows="5" placeholder="Write full details, scheduling information, venue, instructions, or deadlines..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('composeModal')">Cancel</button>
                    <button type="submit" class="btn btn-teal">
                        <span class="material-symbols-outlined" style="font-size:16px;">send</span>
                        Publish Broadcast
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL 2: EDIT ANNOUNCEMENT ===== -->
    <div class="modal-overlay" id="editModal">
        <div class="modal" style="max-width:620px;">
            <div class="modal-header">
                <div>
                    <span class="modal-title">Edit Announcement</span>
                    <p style="font-size:12px; color:var(--text-3); margin-top:2px;">Modify announcement content, audience, or priority level</p>
                </div>
                <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
            </div>
            <form action="../controllers/PresidentController.php?action=update_announcement" method="POST"
                  onsubmit="return USMS.confirmForm(this, {
                      title: 'Update Announcement',
                      message: 'Save changes and synchronize updates to the notification broadcast feed?',
                      type: 'teal',
                      confirmText: 'Save Changes'
                  });">
                <input type="hidden" name="announcement_id" id="editId">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Announcement Title <span style="color:var(--danger)">*</span></label>
                        <input type="text" name="title" id="editTitle" class="form-input" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Target Audience <span style="color:var(--danger)">*</span></label>
                            <select name="target_audience" id="editAudience" class="form-select" required>
                                <option value="All Users">All Users (Campus-Wide)</option>
                                <option value="Student Athletes">Student Athletes</option>
                                <option value="Coaches & Staff">Coaches & Staff</option>
                                <option value="Medical Staff">Medical Staff</option>
                                <option value="TIC">TIC (Teachers-in-Charge)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Priority Level <span style="color:var(--danger)">*</span></label>
                            <select name="priority" id="editPriority" class="form-select" required>
                                <option value="Normal">Normal</option>
                                <option value="Important">Important</option>
                                <option value="Urgent">Urgent (Emergency Alert)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Publication Status</label>
                        <select name="status" id="editStatus" class="form-select">
                            <option value="Active">Active</option>
                            <option value="Archived">Archived</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Message Content <span style="color:var(--danger)">*</span></label>
                        <textarea name="content" id="editContent" class="form-textarea" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" class="btn btn-teal">
                        <span class="material-symbols-outlined" style="font-size:16px;">save</span>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL 3: VIEW ANNOUNCEMENT FULL READER ===== -->
    <div class="modal-overlay" id="viewModal">
        <div class="modal" style="max-width:620px;">
            <div class="modal-header">
                <div>
                    <span class="modal-title" id="viewModalTitle">Announcement Details</span>
                    <p style="font-size:12px; color:var(--text-3); margin-top:2px;" id="viewModalDate">Published Date</p>
                </div>
                <button class="modal-close" onclick="closeModal('viewModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div style="display:flex; gap:8px; align-items:center; margin-bottom:14px;">
                    <span id="viewPriorityBadge" class="badge-normal">Normal</span>
                    <span id="viewAudienceBadge" class="badge-audience">All Users</span>
                    <span id="viewStatusBadge" class="badge badge-ok">Active</span>
                </div>

                <div style="background:#F8FAFC; border:1px solid var(--border); border-radius:8px; padding:14px 16px; margin-bottom:14px;">
                    <h4 style="font-size:15px; color:var(--text-1); margin-bottom:8px; line-height:1.4;" id="viewTitle">Notice Title</h4>
                    <div style="font-size:13.5px; color:var(--text-2); line-height:1.6; white-space:pre-wrap;" id="viewContent">Content body...</div>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; font-size:12px; color:var(--text-3); padding:0 4px;">
                    <span id="viewAuthor">Published by: Student President</span>
                    <span>University Sports Management System</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('viewModal')">Close</button>
            </div>
        </div>
    </div>

    <script src="../assets/js/dashboard.js"></script>
    <script>
        var currentStatusFilter = 'all';

        function setFeedFilter(status, btn) {
            currentStatusFilter = status;
            var buttons = document.querySelectorAll('#filterTabs .tab-btn');
            buttons.forEach(function(b) { b.classList.remove('active'); });
            if (btn) btn.classList.add('active');
            applyFilters();
        }

        function applyFilters() {
            var searchVal = (document.getElementById('searchFeed').value || '').trim().toLowerCase();
            var audienceVal = document.getElementById('audienceFilter').value;
            var cards = document.querySelectorAll('.announcement-card');
            var matchCount = 0;

            cards.forEach(function(card) {
                var cardStatus = card.getAttribute('data-status');
                var cardPriority = card.getAttribute('data-priority');
                var cardAudience = card.getAttribute('data-audience');
                var cardTitle = card.getAttribute('data-title') || '';
                var cardContent = card.getAttribute('data-content') || '';

                // Tab Status Filter
                var matchesTab = false;
                if (currentStatusFilter === 'all') {
                    matchesTab = true;
                } else if (currentStatusFilter === 'Active' && cardStatus === 'Active') {
                    matchesTab = true;
                } else if (currentStatusFilter === 'Urgent' && cardPriority === 'Urgent') {
                    matchesTab = true;
                } else if (currentStatusFilter === 'Archived' && cardStatus === 'Archived') {
                    matchesTab = true;
                }

                // Audience Filter
                var matchesAudience = (!audienceVal || cardAudience === audienceVal);

                // Search Filter
                var matchesSearch = true;
                if (searchVal) {
                    matchesSearch = (cardTitle.indexOf(searchVal) !== -1 || cardContent.indexOf(searchVal) !== -1 || cardAudience.toLowerCase().indexOf(searchVal) !== -1);
                }

                if (matchesTab && matchesAudience && matchesSearch) {
                    card.style.display = 'flex';
                    matchCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Update Feed Count
            var countEl = document.getElementById('feedCountText');
            if (countEl) {
                countEl.textContent = 'Showing ' + matchCount + ' of ' + cards.length + ' announcements';
            }

            // No match placeholder
            var noMatchEl = document.getElementById('noMatchState');
            if (noMatchEl) {
                noMatchEl.style.display = (matchCount === 0 && cards.length > 0) ? 'block' : 'none';
            }
        }

        function openEditModal(ann) {
            document.getElementById('editId').value = ann.announcement_id;
            document.getElementById('editTitle').value = ann.title;
            document.getElementById('editAudience').value = ann.target_audience;
            document.getElementById('editPriority').value = ann.priority;
            document.getElementById('editStatus').value = ann.status;
            document.getElementById('editContent').value = ann.content;
            openModal('editModal');
        }

        function openViewModal(ann) {
            document.getElementById('viewModalTitle').textContent = ann.title;
            document.getElementById('viewModalDate').textContent = 'Broadcasted on ' + ann.created_at;
            document.getElementById('viewTitle').textContent = ann.title;
            document.getElementById('viewContent').textContent = ann.content;
            document.getElementById('viewAuthor').textContent = 'Published by: ' + (ann.publisher_name || 'Student President');
            
            var pBadge = document.getElementById('viewPriorityBadge');
            pBadge.textContent = ann.priority;
            pBadge.className = (ann.priority === 'Urgent' ? 'badge-urgent' : (ann.priority === 'Important' ? 'badge-important' : 'badge-normal'));

            document.getElementById('viewAudienceBadge').textContent = ann.target_audience;
            
            var sBadge = document.getElementById('viewStatusBadge');
            sBadge.textContent = ann.status;
            sBadge.className = 'badge ' + (ann.status === 'Active' ? 'badge-ok' : 'badge-gray');

            openModal('viewModal');
        }
    </script>
</body>
</html>
