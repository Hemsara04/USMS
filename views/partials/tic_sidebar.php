<?php
// Detect current page for active state
$currentPage = basename($_SERVER['PHP_SELF']);

// Equipment Management pages group
$equipPages = ['tic_equipment_requests.php', 'tic_stock_inventory.php', 'tic_checkout_log.php'];
$isEquipActive = in_array($currentPage, $equipPages);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <span class="material-symbols-outlined logo-icon">sports_score</span>
        <div class="logo-text">
            <h1>USMS</h1>
            <p>TIC PORTAL</p>
        </div>
    </div>
    <nav class="sidebar-nav">
        <!-- 1. Equipment Management (Default TIC landing) -->
        <a href="tic_equipment_requests.php"
           class="sidebar-link <?= $isEquipActive ? 'active' : '' ?>">
            <span class="material-symbols-outlined">inventory_2</span>
            <span>Equipment Management</span>
        </a>

        <!-- 2. Squad Approvals -->
        <a href="tic_squad_approval.php"
           class="sidebar-link <?= ($currentPage === 'tic_squad_approval.php') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">groups</span>
            <span>Squad Approvals</span>
        </a>

        <!-- 3. Facility Bookings -->
        <a href="tic_facility_booking.php"
           class="sidebar-link <?= ($currentPage === 'tic_facility_booking.php') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">event_available</span>
            <span>Facility Bookings</span>
        </a>

        <!-- 4. Profile & Settings -->
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
