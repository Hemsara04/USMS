<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <span>UOC Sport</span>
    </div>
    <nav class="sidebar-nav">
        <div class="sidebar-section-label">Medical Portal</div>

        <!-- 1. Clinical Dashboard (Default / Operations) -->
        <a href="med_athlete_roster.php"
           class="sidebar-link <?= ($currentPage === 'med_athlete_roster.php') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">health_and_safety</span>
            Clinical Dashboard
        </a>

        <!-- 2. Athlete Health Registry & Profiles -->
        <a href="med_athlete_registry.php"
           class="sidebar-link <?= ($currentPage === 'med_athlete_registry.php') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">badge</span>
            Athlete Health Registry
        </a>
    </nav>
</aside>
