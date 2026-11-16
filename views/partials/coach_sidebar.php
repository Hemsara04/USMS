<?php
/**
 * USMS - Coach Portal Sidebar Navigation
 * Adheres to 3-Tier MVC Design System & Academic Presentation Feedback
 */
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <span class="material-symbols-outlined logo-icon" style="color: var(--teal, #008080);">sports</span>
        <div class="logo-text">
            <h1 style="font-size: 1.1rem; font-weight: 800; letter-spacing: 0.05em; margin: 0;">USMS</h1>
            <p style="font-size: 0.72rem; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.08em; margin: 0;">COACH PORTAL</p>
        </div>
    </div>

    <!-- Role Context Banner (Interim Evaluation Feedback: User role clarity) -->
    <div style="padding: 10px 14px; margin: 0 12px 14px 12px; background: rgba(0, 128, 128, 0.12); border: 1px solid rgba(0, 128, 128, 0.25); border-radius: 8px;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 7px;">
                <span class="material-symbols-outlined" style="font-size: 16px; color: #008080;">sports_martial_arts</span>
                <span style="font-size: 11.5px; font-weight: 700; color: #E2E8F0;">Coach Sunil</span>
            </div>
            <span style="font-size: 10px; font-weight: 700; background: #008080; color: #FFF; padding: 1px 6px; border-radius: 4px; text-transform: uppercase;">Coach</span>
        </div>
        <div style="font-size: 10.5px; color: #94A3B8; margin-top: 3px;">Varsity Football &amp; Track</div>
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

    <div class="sidebar-footer">
        <a href="../controllers/AuthController.php?action=logout" class="sidebar-link" style="color: #EF4444;">
            <span class="material-symbols-outlined">logout</span>
            <span>Sign Out</span>
        </a>
    </div>
</aside>
