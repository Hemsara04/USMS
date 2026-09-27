<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <span class="material-symbols-outlined logo-icon">sports</span>
        <div class="logo-text">
            <h1>USMS</h1>
            <p>COACH PORTAL</p>
        </div>
    </div>
    <nav class="sidebar-nav">
        <!-- 1. Training & Attendance -->
        <a href="coach_training_sessions.php"
           class="sidebar-link <?= ($currentPage === 'coach_training_sessions.php') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">fitness_center</span>
            <span>Training &amp; Attendance</span>
        </a>

        <!-- 2. Squad Formulation -->
        <a href="coach_squad_selection.php"
           class="sidebar-link <?= ($currentPage === 'coach_squad_selection.php') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">groups</span>
            <span>Squad Formulation</span>
        </a>

        <!-- 3. Equipment Requisitions -->
        <a href="coach_equipment_requests.php"
           class="sidebar-link <?= ($currentPage === 'coach_equipment_requests.php') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">sports_soccer</span>
            <span>Equipment Requisitions</span>
        </a>

        <!-- 4. Fixtures & Results -->
        <a href="coach_fixtures_results.php"
           class="sidebar-link <?= ($currentPage === 'coach_fixtures_results.php') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">scoreboard</span>
            <span>Fixtures &amp; Results</span>
        </a>

        <!-- 5. Profile & Settings -->
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
