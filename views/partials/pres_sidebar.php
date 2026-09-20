<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <span class="material-symbols-outlined" style="color:var(--accent); font-size:24px;">campaign</span>
        <span>USMS President</span>
    </div>
    <nav class="sidebar-nav">
        <div class="sidebar-section-label">President Portal</div>

        <!-- 1. Announcement Command Center (Default landing) -->
        <a href="pres_announcements.php"
           class="sidebar-link <?= ($currentPage === 'pres_announcements.php') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">campaign</span>
            Announcement Center
        </a>
    </nav>
</aside>
