<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <span class="material-symbols-outlined logo-icon">campaign</span>
        <div class="logo-text">
            <h1>USMS</h1>
            <p>PRESIDENT PORTAL</p>
        </div>
    </div>
    <nav class="sidebar-nav">
        <!-- 1. Announcement Command Center (Default landing) -->
        <a href="pres_announcements.php"
           class="sidebar-link <?= ($currentPage === 'pres_announcements.php') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">campaign</span>
            <span>Announcement Center</span>
        </a>

        <!-- 2. Profile & Settings -->
        <a href="profile.php"
           class="sidebar-link <?= ($currentPage === 'profile.php') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">manage_accounts</span>
            <span>Profile &amp; Settings</span>
        </a>
    </nav>
    <div class="sidebar-footer">
        <a href="../controllers/AuthController.php?action=logout" class="sidebar-link">
            <span class="material-symbols-outlined">logout</span>
            <span>Sign Out</span>
        </a>
    </div>
</aside>
