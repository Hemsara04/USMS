<?php
require_once '../config/auth_guard.php';
requireRole('President');

$pageTitle = 'Announcements';
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
    <?php include 'partials/pres_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <div class="main-wrapper">
        <?php include 'partials/tic_header.php'; ?>

        <div class="page-content">

            <!-- Page Title -->
            <div class="page-title-row">
                <h2 class="page-title">Announcements</h2>
            </div>

            <!-- Compose + Action -->
            <div class="grid-2">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Total Sent</div>
                        <div class="stat-value">28</div>
                    </div>
                    <div class="stat-icon">
                        <span class="material-symbols-outlined">campaign</span>
                    </div>
                </div>
                <button class="action-card" onclick="openModal('composeModal')">
                    <div class="action-card-icon">
                        <span class="material-symbols-outlined">add</span>
                    </div>
                    <div>
                        <div class="action-card-title">New Announcement</div>
                        <div class="action-card-desc">Broadcast a new notice</div>
                    </div>
                </button>
            </div>

            <!-- Toolbar -->
            <div class="toolbar">
                <div class="tab-group">
                    <button class="tab-btn active" onclick="filterTab('all', this)">All</button>
                    <button class="tab-btn" onclick="filterTab('active', this)">Active</button>
                    <button class="tab-btn" onclick="filterTab('archived', this)">Archived</button>
                </div>
                <div class="search-box">
                    <span class="material-symbols-outlined">search</span>
                    <input type="text" class="search-input" placeholder="Search announcements..." oninput="searchTable(this.value)">
                </div>
            </div>

            <!-- Announcements Table -->
            <div class="table-card">
                <div class="table-scroll">
                    <table class="data-table" id="announcementsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Target Audience</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <tr data-status="active">
                                <td>Aug 20, 2026<br><span class="cell-sub">09:15 AM</span></td>
                                <td><span class="badge badge-info">ALL USERS</span></td>
                                <td>
                                    <span class="cell-main">Season kickoff meeting scheduled for next Friday</span>
                                    <span class="cell-sub">All varsity athletes must attend at the Main Gymnasium.</span>
                                </td>
                                <td><span class="badge badge-ok">Active</span></td>
                                <td class="cell-actions">
                                    <button class="btn btn-sm btn-outline" onclick="viewAnnouncement(0)">
                                        <span class="material-symbols-outlined" style="font-size:16px;">visibility</span> View
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteRow(this)">
                                        <span class="material-symbols-outlined" style="font-size:16px;">delete</span>
                                    </button>
                                </td>
                            </tr>

                            <tr data-status="active">
                                <td>Aug 19, 2026<br><span class="cell-sub">02:30 PM</span></td>
                                <td><span class="badge badge-purple">MEDICAL STAFF</span></td>
                                <td>
                                    <span class="cell-main">Updated protocols for court-side injury triage</span>
                                    <span class="cell-sub">Please review the updated clearance workflow before Friday.</span>
                                </td>
                                <td><span class="badge badge-ok">Active</span></td>
                                <td class="cell-actions">
                                    <button class="btn btn-sm btn-outline" onclick="viewAnnouncement(1)">
                                        <span class="material-symbols-outlined" style="font-size:16px;">visibility</span> View
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteRow(this)">
                                        <span class="material-symbols-outlined" style="font-size:16px;">delete</span>
                                    </button>
                                </td>
                            </tr>

                            <tr data-status="active">
                                <td>Aug 18, 2026<br><span class="cell-sub">06:00 PM</span></td>
                                <td><span class="badge badge-danger">URGENT</span></td>
                                <td>
                                    <span class="cell-main">Practice courts 3 and 4 are closed for maintenance</span>
                                    <span class="cell-sub">Court resurfacing underway until Tuesday morning. Use Court 1.</span>
                                </td>
                                <td><span class="badge badge-warn">Urgent</span></td>
                                <td class="cell-actions">
                                    <button class="btn btn-sm btn-outline" onclick="viewAnnouncement(2)">
                                        <span class="material-symbols-outlined" style="font-size:16px;">visibility</span> View
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteRow(this)">
                                        <span class="material-symbols-outlined" style="font-size:16px;">delete</span>
                                    </button>
                                </td>
                            </tr>

                            <tr data-status="archived">
                                <td>Aug 15, 2026<br><span class="cell-sub">11:20 AM</span></td>
                                <td><span class="badge badge-info">ALL ATHLETES</span></td>
                                <td>
                                    <span class="cell-main">Annual Colors Night nominations are now open</span>
                                    <span class="cell-sub">Submit nominations through the Sports Council portal by Aug 30.</span>
                                </td>
                                <td><span class="badge">Archived</span></td>
                                <td class="cell-actions">
                                    <button class="btn btn-sm btn-outline" onclick="viewAnnouncement(3)">
                                        <span class="material-symbols-outlined" style="font-size:16px;">visibility</span> View
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteRow(this)">
                                        <span class="material-symbols-outlined" style="font-size:16px;">delete</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    <span>Showing 1–4 of 28</span>
                    <div class="page-btns">
                        <button class="page-btn" disabled><span class="material-symbols-outlined">chevron_left</span></button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn">2</button>
                        <button class="page-btn"><span class="material-symbols-outlined">chevron_right</span></button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ========================================
         MODAL: Compose New Announcement
         ======================================== -->
    <div class="modal-overlay" id="composeModal" onclick="if(event.target===this)closeModal('composeModal')">
        <div class="modal" style="width: 520px;">
            <form onsubmit="sendAnnouncement(event)">
                <div class="modal-header">
                    <h3 class="modal-title">New Announcement</h3>
                    <button type="button" class="modal-close" onclick="closeModal('composeModal')">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Target Audience</label>
                        <select class="form-select" id="cAudience">
                            <option value="All Users">All Users</option>
                            <option value="All Athletes">All Athletes</option>
                            <option value="Coaches">Coaches & Staff</option>
                            <option value="TIC">Teachers in Charge</option>
                            <option value="Medical Staff">Medical Officers</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Message</label>
                        <textarea class="form-textarea" id="cMessage" rows="4" placeholder="Type your announcement here..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Priority</label>
                        <div style="display: flex; gap: 16px; margin-top: 4px;">
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 14px; cursor: pointer;">
                                <input type="radio" name="priority" value="Standard" checked style="accent-color: var(--accent-dark);"> Standard Notice
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 14px; color: var(--danger); cursor: pointer;">
                                <input type="radio" name="priority" value="Urgent" style="accent-color: var(--danger);"> Urgent
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('composeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="material-symbols-outlined" style="font-size: 16px;">send</span> Send Broadcast
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================
         MODAL: View Announcement Detail
         ======================================== -->
    <div class="modal-overlay" id="viewModal" onclick="if(event.target===this)closeModal('viewModal')">
        <div class="modal" style="width: 500px;">
            <div class="modal-header">
                <h3 class="modal-title">Announcement Detail</h3>
                <button class="modal-close" onclick="closeModal('viewModal')">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Target Audience</label>
                    <p id="vAudience" style="color: var(--text); font-weight: 600;">—</p>
                </div>
                <div class="form-group">
                    <label class="form-label">Message</label>
                    <p id="vMessage" style="color: var(--text); line-height: 1.6; white-space: pre-wrap;">—</p>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <p id="vStatus" style="color: var(--text); font-weight: 600;">—</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date Sent</label>
                        <p id="vDate" style="color: var(--text-2);">—</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeModal('viewModal')">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Announcement data (placeholder – will come from DB)
        const announcements = [
            { audience: 'All Users', message: 'Season kickoff meeting scheduled for next Friday at the Main Gymnasium. All varsity athletes must attend. Physical clearance certificates will be verified at the entrance.', status: 'Active', date: 'Aug 20, 2026 — 09:15 AM' },
            { audience: 'Medical Staff', message: 'Updated protocols for court-side injury triage have been uploaded. Please review the updated clearance workflow and submit clearance logs before Friday.', status: 'Active', date: 'Aug 19, 2026 — 02:30 PM' },
            { audience: 'Urgent — Specific Teams', message: 'Practice courts 3 and 4 are closed immediately due to court resurfacing until Tuesday morning. Training sessions are temporarily moved to Court 1.', status: 'Urgent', date: 'Aug 18, 2026 — 06:00 PM' },
            { audience: 'All Athletes', message: 'Nominations for University Colors and Special Athletic Achievements are now open through the Sports Council portal. Deadline is Aug 30.', status: 'Archived', date: 'Aug 15, 2026 — 11:20 AM' }
        ];

        // Modal helpers
        function openModal(id) {
            document.getElementById(id).classList.add('show');
        }
        function closeModal(id) {
            document.getElementById(id).classList.remove('show');
        }

        // View announcement detail
        function viewAnnouncement(idx) {
            const a = announcements[idx];
            document.getElementById('vAudience').textContent = a.audience;
            document.getElementById('vMessage').textContent = a.message;
            document.getElementById('vStatus').textContent = a.status;
            document.getElementById('vDate').textContent = a.date;
            openModal('viewModal');
        }

        // Send new announcement
        function sendAnnouncement(e) {
            e.preventDefault();
            const audience = document.getElementById('cAudience').value;
            const message = document.getElementById('cMessage').value.trim();
            const priority = document.querySelector('input[name="priority"]:checked').value;

            if (!message) return;

            // Add to table
            const tbody = document.getElementById('tableBody');
            const tr = document.createElement('tr');
            tr.setAttribute('data-status', 'active');

            const now = new Date();
            const dateStr = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

            const badgeClass = priority === 'Urgent' ? 'badge-danger' : 'badge-info';
            const badgeText = priority === 'Urgent' ? 'URGENT' : audience.toUpperCase();
            const statusBadge = priority === 'Urgent' ? '<span class="badge badge-warn">Urgent</span>' : '<span class="badge badge-ok">Active</span>';
            const preview = message.length > 70 ? message.substring(0, 70) + '...' : message;

            const newIdx = announcements.length;
            announcements.push({ audience: audience, message: message, status: priority === 'Urgent' ? 'Urgent' : 'Active', date: dateStr + ' — ' + timeStr });

            tr.innerHTML = `
                <td>${dateStr}<br><span class="cell-sub">${timeStr}</span></td>
                <td><span class="badge ${badgeClass}">${badgeText}</span></td>
                <td>
                    <span class="cell-main">${escapeHtml(preview)}</span>
                </td>
                <td>${statusBadge}</td>
                <td class="cell-actions">
                    <button class="btn btn-sm btn-outline" onclick="viewAnnouncement(${newIdx})">
                        <span class="material-symbols-outlined" style="font-size:16px;">visibility</span> View
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteRow(this)">
                        <span class="material-symbols-outlined" style="font-size:16px;">delete</span>
                    </button>
                </td>
            `;
            tbody.insertBefore(tr, tbody.firstChild);

            // Reset & close
            document.getElementById('cMessage').value = '';
            closeModal('composeModal');
        }

        // Delete row
        function deleteRow(btn) {
            if (confirm('Delete this announcement?')) {
                btn.closest('tr').remove();
            }
        }

        // Search table
        function searchTable(q) {
            q = q.toLowerCase();
            document.querySelectorAll('#tableBody tr').forEach(r => {
                r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        }

        // Filter tabs
        function filterTab(status, btn) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            document.querySelectorAll('#tableBody tr').forEach(r => {
                if (status === 'all') {
                    r.style.display = '';
                } else {
                    r.style.display = r.dataset.status === status ? '' : 'none';
                }
            });
        }

        function escapeHtml(s) {
            return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
    </script>
</body>
</html>
