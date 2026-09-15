<header class="header">
    <div class="header-left">
        <h1 class="header-title"><?= $pageTitle ?? 'Dashboard' ?></h1>
    </div>
    <div class="header-actions">
        <button class="icon-btn" title="Notifications">
            <span class="material-symbols-outlined">notifications</span>
            <span class="notif-dot"></span>
        </button>
        <div class="user-avatar" title="Profile">
            <?php
            // Use the first letter of the username from session
            echo isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 1)) : 'U';
            ?>
        </div>
        <a href="../controllers/AuthController.php?action=logout" class="icon-btn" title="Logout">
            <span class="material-symbols-outlined">logout</span>
        </a>
    </div>
</header>
