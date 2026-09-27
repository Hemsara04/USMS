<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$headerUserId   = $_SESSION['user_id'] ?? null;
$headerUsername = $_SESSION['username'] ?? 'User';
$headerEmail    = $_SESSION['email'] ?? 'user@uoc.ac.lk';
$headerRole     = $_SESSION['role'] ?? 'TIC';
$headerPhone    = '+94 11 258 1835';
$headerFullName = ucwords(str_replace('_', ' ', $headerUsername));

if ($headerUserId) {
    try {
        require_once __DIR__ . '/../../config/db.php';
        if (!isset($headerPdo)) {
            $headerPdo = Database::connect();
        }
        $userStmt = $headerPdo->prepare("SELECT phone_number FROM user WHERE user_id = :uid LIMIT 1");
        $userStmt->execute([':uid' => $headerUserId]);
        $row = $userStmt->fetch();
        if ($row && !empty($row['phone_number'])) {
            $headerPhone = $row['phone_number'];
        }
    } catch (Exception $e) {
        // Fallback default
    }
}

// Universal Notification System: Fetch active announcements filtered by current user role
$headerNotifs = [];
try {
    require_once __DIR__ . '/../../models/Announcement.php';
    $headerAnnModel = new Announcement();
    $headerNotifs = $headerAnnModel->getActiveForNotifications(15, $headerRole);
} catch (Exception $e) {
    $headerNotifs = [];
}
$notifCount = count($headerNotifs);

$portalMap = [
    'System_Admin'    => 'Admin Console',
    'Admin'           => 'Admin Console',
    'TIC'             => 'TIC Operations',
    'Coach'           => 'Coach Portal',
    'Athlete'         => 'Athlete Portal',
    'Medical_Officer' => 'Medical Center',
    'Physiotherapist' => 'Medical Center',
    'President'       => 'President Dashboard'
];
$portalLabel = $portalMap[$headerRole] ?? 'Portal';
?>
<header class="header">
    <div class="header-left">
        <div class="header-breadcrumb" style="display: flex; align-items: center; gap: 8px; font-size: 13.5px;">
            <span class="header-badge" style="font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; padding: 3px 8px; border-radius: 6px; background: rgba(0, 153, 171, 0.1); color: var(--teal);"><?= htmlspecialchars($portalLabel) ?></span>
            <span style="color: var(--text-3); opacity: 0.4;">/</span>
            <span style="font-weight: 600; color: var(--text-2); font-size: 13.5px;"><?= htmlspecialchars($pageTitle ?? 'Overview') ?></span>
        </div>
    </div>
    <div class="header-actions">
        <!-- Notification Dropdown -->
        <div class="header-dropdown-wrap">
            <button type="button" class="icon-btn" id="notifDropdownBtn" title="Notifications" onclick="toggleHeaderDropdown('notifDropdown')">
                <span class="material-symbols-outlined">notifications</span>
                <?php if ($notifCount > 0): ?>
                    <span class="notif-dot"></span>
                <?php endif; ?>
            </button>
            <div class="header-dropdown-menu" id="notifDropdown" style="width: 380px;">
                <div class="header-dropdown-header">
                    <span class="header-dropdown-title">Official Announcements</span>
                    <span style="font-size:11px; color:var(--teal); font-weight:700;"><?= $notifCount ?> ACTIVE</span>
                </div>
                <div class="header-dropdown-body" style="max-height: 290px; overflow-y: auto; overflow-x: hidden; padding: 0;">
                    <?php if ($notifCount === 0): ?>
                        <div class="notif-empty-state">
                            <span class="material-symbols-outlined notif-empty-icon">notifications_paused</span>
                            <div class="notif-empty-title">No Active Announcements</div>
                            <div class="notif-empty-desc">You are completely caught up! Official updates published by the Student President will appear here in real-time.</div>
                        </div>
                    <?php else: ?>
                        <div class="notif-list">
                            <?php foreach ($headerNotifs as $n): 
                                $pColor = '#0D9488';
                                $pIcon = 'campaign';
                                $pBg = '#F0FDFA';
                                if ($n['priority'] === 'Urgent') {
                                    $pColor = '#D97706'; /* Amber, non-red */
                                    $pIcon = 'campaign';
                                    $pBg = '#FFFBEB';
                                } elseif ($n['priority'] === 'Important') {
                                    $pColor = '#D97706';
                                    $pIcon = 'announcement';
                                    $pBg = '#FFFBEB';
                                }
                                $timeStr = date('M d, h:i A', strtotime($n['created_at']));
                            ?>
                            <div class="notif-item" onclick="toggleNotifAccordion(this)" style="padding: 10px 14px; border-bottom: 1px solid var(--border); cursor: pointer; transition: background 0.15s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='#FFFFFF'">
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <div style="width: 28px; height: 28px; border-radius: 6px; background: <?= $pBg ?>; color: <?= $pColor ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <span class="material-symbols-outlined" style="font-size: 16px;"><?= $pIcon ?></span>
                                    </div>
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px; margin-bottom: 2px;">
                                            <span style="font-size: 10px; font-weight: 700; color: <?= $pColor ?>; text-transform: uppercase;"><?= htmlspecialchars($n['priority']) ?></span>
                                            <span style="font-size: 10px; color: var(--text-3);"><?= $timeStr ?></span>
                                        </div>
                                        <div style="font-size: 12.5px; font-weight: 600; color: var(--text-1); line-height: 1.35; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($n['title']) ?>">
                                            <?= htmlspecialchars($n['title']) ?>
                                        </div>
                                    </div>
                                    <span class="material-symbols-outlined notif-toggle-arrow" style="font-size: 18px; color: var(--text-3); transition: transform 0.2s ease; flex-shrink: 0;">expand_more</span>
                                </div>

                                <!-- Collapsed content, expands smoothly on click -->
                                <div class="notif-expandable-content" style="display: none; margin-top: 8px; padding-top: 8px; border-top: 1px dashed var(--border); font-size: 12px; color: var(--text-2); line-height: 1.5; word-break: break-word;">
                                    <div style="margin-bottom: 6px;"><?= nl2br(htmlspecialchars(trim($n['content']))) ?></div>
                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                        <span class="badge badge-teal" style="font-size: 9.5px; padding: 1px 6px;"><?= htmlspecialchars($n['target_audience']) ?></span>
                                        <span style="font-size: 10px; color: var(--text-3);">By Student President</span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="profile-popover-footer" style="padding: 8px 14px; background: #F8FAFC; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 11px; color: var(--text-3);">Broadcast Network</span>
                    <?php if ($headerRole === 'President'): ?>
                        <a href="pres_announcements.php" style="font-size: 11px; color: var(--teal); font-weight: 600; text-decoration: none;">Manage &rarr;</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- User Profile Popover (Athlete-Style Pill) -->
        <div class="header-dropdown-wrap">
            <div class="user-profile" id="profileDropdownBtn" onclick="toggleHeaderDropdown('profileDropdown')" role="button" tabindex="0" title="Account Profile (<?= htmlspecialchars($headerFullName) ?>)">
                <div class="user-info">
                    <span class="user-name"><?= htmlspecialchars($headerFullName) ?></span>
                    <span class="user-role"><?= htmlspecialchars($headerRole) ?></span>
                </div>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($headerFullName) ?>&background=0099ab&color=fff&size=40" alt="Avatar" class="avatar">
            </div>
            <div class="header-dropdown-menu" id="profileDropdown">
                <div class="header-dropdown-header">
                    <span class="header-dropdown-title">Account Profile</span>
                    <span class="profile-popover-role"><?= htmlspecialchars($headerRole) ?></span>
                </div>
                <div class="header-dropdown-body">
                    <div class="profile-popover-card">
                        <div class="profile-popover-avatar-row">
                            <div class="profile-popover-avatar">
                                <?= strtoupper(substr($headerUsername, 0, 1)) ?>
                            </div>
                            <div class="profile-popover-meta">
                                <h4><?= htmlspecialchars($headerFullName) ?></h4>
                                <span style="font-size:12px; color:var(--text-3);">@<?= htmlspecialchars($headerUsername) ?></span>
                            </div>
                        </div>
                        <div class="profile-popover-details">
                            <div class="profile-detail-row">
                                <span>Official Email:</span>
                                <strong><?= htmlspecialchars($headerEmail) ?></strong>
                            </div>
                            <div class="profile-detail-row">
                                <span>Phone Number:</span>
                                <strong><?= htmlspecialchars($headerPhone) ?></strong>
                            </div>
                            <div class="profile-detail-row">
                                <span>System Role:</span>
                                <strong><?= htmlspecialchars($headerRole) ?></strong>
                            </div>
                            <div class="profile-detail-row">
                                <span>Department:</span>
                                <strong>Sports Advisory Board</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="profile-popover-footer" style="padding: 10px 14px; background: #F8FAFC; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size:11.5px; color:var(--text-3);">University of Colombo</span>
                    <a href="profile.php" class="btn btn-teal btn-sm" style="text-decoration:none; display: inline-flex; align-items: center; gap: 4px; padding: 5px 11px; font-size: 12px; font-weight: 600;">
                        <span class="material-symbols-outlined" style="font-size:15px;">manage_accounts</span> View &amp; Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
/* Universal Header Dropdown Handler (Notifications & Profile) */
if (typeof toggleHeaderDropdown !== 'function') {
    function toggleHeaderDropdown(menuId) {
        var menu = document.getElementById(menuId);
        if (!menu) return;
        var isOpen = menu.classList.contains('active');

        // Close all header dropdowns first
        document.querySelectorAll('.header-dropdown-menu').forEach(function(m) {
            m.classList.remove('active');
        });

        if (!isOpen) {
            menu.classList.add('active');
        }
    }
}

/* Accordion toggle for Announcement / Notification items */
if (typeof toggleNotifAccordion !== 'function') {
    function toggleNotifAccordion(item) {
        var details = item.querySelector('.notif-expandable-content');
        var arrow = item.querySelector('.notif-toggle-arrow');
        if (!details) return;
        var isOpen = details.style.display === 'block';
        details.style.display = isOpen ? 'none' : 'block';
        if (arrow) {
            arrow.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    }
}

// Close header dropdowns on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('.header-dropdown-wrap')) {
        document.querySelectorAll('.header-dropdown-menu.active').forEach(function(m) {
            m.classList.remove('active');
        });
    }
});
</script>
