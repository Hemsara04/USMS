<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <span class="material-symbols-outlined" style="color:var(--accent); font-size:24px;">sports</span>
        <span>USMS Coach Portal</span>
    </div>
    <nav class="sidebar-nav">
        <div class="sidebar-section-label">Coach Operations</div>

        <!-- 1. Training & Attendance -->
        <a href="coach_training_sessions.php"
           class="sidebar-link <?= ($currentPage === 'coach_training_sessions.php') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">fitness_center</span>
            Training & Attendance
        </a>

        <!-- 2. Squad Formulation -->
        <a href="coach_squad_selection.php"
           class="sidebar-link <?= ($currentPage === 'coach_squad_selection.php') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">groups</span>
            Squad Formulation
        </a>

        <!-- 3. Equipment Requisitions -->
        <a href="coach_equipment_requests.php"
           class="sidebar-link <?= ($currentPage === 'coach_equipment_requests.php') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">sports_soccer</span>
            Equipment Requisitions
        </a>

        <!-- 4. Fixtures & Results -->
        <a href="coach_fixtures_results.php"
           class="sidebar-link <?= ($currentPage === 'coach_fixtures_results.php') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">scoreboard</span>
            Fixtures & Results
        </a>

        <div class="sidebar-section-label" style="margin-top: 24px;">My Account</div>

        <!-- 5. Profile & Settings -->
        <a href="profile.php"
           class="sidebar-link <?= ($currentPage === 'profile.php') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">manage_accounts</span>
            Profile &amp; Settings
        </a>
    </nav>
</aside>
