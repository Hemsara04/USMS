<?php
// Detect current page for active state
$currentPage = basename($_SERVER['PHP_SELF']);

// Equipment Management pages group
$equipPages = ['tic_equipment_requests.php', 'tic_stock_inventory.php', 'tic_checkout_log.php'];
$isEquipActive = in_array($currentPage, $equipPages);
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <span>UOC Sport</span>
    </div>
    <nav class="sidebar-nav">
        <div class="sidebar-section-label">Main Menu</div>

        <!-- 1. Equipment Management (Default TIC landing) -->
        <a href="tic_equipment_requests.php"
           class="sidebar-link <?= $isEquipActive ? 'active' : '' ?>">
            <span class="material-symbols-outlined">inventory_2</span>
            Equipment Management
        </a>

        <!-- 2. Squad Approvals -->
        <a href="tic_squad_approval.php"
           class="sidebar-link <?= ($currentPage === 'tic_squad_approval.php') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">groups</span>
            Squad Approvals
        </a>

        <!-- 3. Facility Bookings -->
        <a href="tic_facility_booking.php"
           class="sidebar-link <?= ($currentPage === 'tic_facility_booking.php') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">event_available</span>
            Facility Bookings
        </a>
    </nav>
</aside>
