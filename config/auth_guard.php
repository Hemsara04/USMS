<?php
/**
 * Authentication Guard Middleware
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Ensures the user is logged in
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../views/login.php");
        exit;
    }
}

/**
 * Ensures the user has a specific role, otherwise redirects
 * @param array|string $allowedRoles
 */
function requireRole($allowedRoles) {
    requireLogin();
    
    $roles = (array) $allowedRoles;
    
    if (!in_array($_SESSION['role'], $roles)) {
        // User is logged in but doesn't have permission for this page
        // Redirect them to their appropriate dashboard based on their role
        switch ($_SESSION['role']) {
            case 'TIC':
                header("Location: ../views/tic_equipment_requests.php");
                break;
            case 'Medical_Officer':
                header("Location: ../views/med_athlete_roster.php");
                break;
            case 'President':
                header("Location: ../views/pres_announcements.php");
                break;
            case 'Coach':
                header("Location: ../views/coach_training_sessions.php");
                break;
            case 'System_Admin':
            case 'Admin':
                header("Location: ../views/admin_user_management.php");
                break;
            case 'Athlete':
                header("Location: ../views/athletic_regestration.php");
                break;
            default:
                header("Location: ../views/login.php");
                break;
        }
        exit;
    }
}
?>
