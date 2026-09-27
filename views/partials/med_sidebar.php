<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <span class="material-symbols-outlined logo-icon">health_and_safety</span>
        <div class="logo-text">
            <h1>USMS</h1>
            <p>MEDICAL PORTAL</p>
        </div>
    </div>
    <nav class="sidebar-nav">
        <!-- 1. Clinical Dashboard (Default / Operations) -->
        <a href="med_athlete_roster.php"
           class="sidebar-link <?= ($currentPage === 'med_athlete_roster.php') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">vital_signs</span>
            <span>Clinical Dashboard</span>
        </a>

        <!-- 2. Athlete Health Registry & Profiles -->
        <a href="med_athlete_registry.php"
           class="sidebar-link <?= ($currentPage === 'med_athlete_registry.php') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">badge</span>
            <span>Athlete Health Registry</span>
        </a>

        <!-- 3. Profile & Settings -->
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
