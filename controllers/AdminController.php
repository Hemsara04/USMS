<?php
/**
 * Admin Controller
 * Handles administrative actions and user management CRUD requests
 */

require_once __DIR__ . '/../config/auth_guard.php';
requireRole(['System_Admin', 'Admin']);

require_once __DIR__ . '/../models/Admin.php';

class AdminController {
    private $adminModel;

    public function __construct() {
        $this->adminModel = new Admin();
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'create_user':
                $this->handleCreateUser();
                break;

            case 'update_user':
                $this->handleUpdateUser();
                break;

            case 'delete_user':
                $this->handleDeleteUser();
                break;

            case 'verify_app':
                $this->handleVerifyApplication();
                break;

            case 'create_sport':
                $this->handleCreateSport();
                break;

            case 'update_sport':
                $this->handleUpdateSport();
                break;

            case 'create_season':
                $this->handleCreateSeason();
                break;

            case 'create_color':
                $this->handleCreateColor();
                break;

            case 'update_color_status':
                $this->handleUpdateColorStatus();
                break;

            case 'delete_color':
                $this->handleDeleteColor();
                break;

            default:
                header('Location: ../views/admin_user_management.php');
                exit;
        }
    }

    private function handleCreateUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/admin_user_management.php');
            exit;
        }

        try {
            $this->adminModel->createUser($_POST);
            $_SESSION['flash_success'] = "New user account '{$_POST['username']}' registered successfully.";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        header('Location: ../views/admin_user_management.php');
        exit;
    }

    private function handleUpdateUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/admin_user_management.php');
            exit;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        if (!$userId) {
            $_SESSION['flash_error'] = "Invalid user specified for update.";
            header('Location: ../views/admin_user_management.php');
            exit;
        }

        try {
            $this->adminModel->updateUser($userId, $_POST);
            $_SESSION['flash_success'] = "User account '{$_POST['username']}' updated successfully.";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        header('Location: ../views/admin_user_management.php');
        exit;
    }

    private function handleDeleteUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/admin_user_management.php');
            exit;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $currentAdminId = (int)($_SESSION['user_id'] ?? 0);

        if (!$userId) {
            $_SESSION['flash_error'] = "Invalid user specified for deletion.";
            header('Location: ../views/admin_user_management.php');
            exit;
        }

        try {
            $this->adminModel->deleteUser($userId, $currentAdminId);
            $_SESSION['flash_success'] = "User account (ID #{$userId}) has been deleted successfully.";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        header('Location: ../views/admin_user_management.php');
        exit;
    }

    private function handleVerifyApplication() {
        $appId = (int)($_GET['id'] ?? 0);
        $decision = $_GET['decision'] ?? '';

        if (!$appId || !in_array($decision, ['Approved', 'Rejected'])) {
            $_SESSION['flash_error'] = "Invalid verification request parameters.";
            header('Location: ../views/admin_user_management.php');
            exit;
        }

        try {
            $this->adminModel->updateApplicationStatus($appId, $decision);
            $_SESSION['flash_success'] = "Application #{$appId} has been marked as {$decision}.";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        header('Location: ../views/admin_user_management.php');
        exit;
    }

    private function handleCreateSport() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/admin_sports_catalog.php');
            exit;
        }

        $sportName = trim($_POST['sport_name'] ?? '');

        // In-memory logical guard
        if (empty($sportName)) {
            $_SESSION['flash_error'] = "Sport name cannot be empty.";
            header('Location: ../views/admin_sports_catalog.php');
            exit;
        }
        if (strlen($sportName) < 2) {
            $_SESSION['flash_error'] = "Sport name must be at least 2 characters long.";
            header('Location: ../views/admin_sports_catalog.php');
            exit;
        }

        try {
            $pdo = Database::connect();
            $chk = $pdo->prepare("SELECT COUNT(*) FROM `sports` WHERE LOWER(sport_name) = LOWER(:name)");
            $chk->execute([':name' => $sportName]);
            if ($chk->fetchColumn() > 0) {
                throw new Exception("A sport named '{$sportName}' already exists.");
            }

            $stmt = $pdo->prepare("INSERT INTO `sports` (`sport_name`) VALUES (:name)");
            $stmt->execute([':name' => $sportName]);
            $_SESSION['flash_success'] = "Sport '{$sportName}' added to the institution catalog successfully.";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        header('Location: ../views/admin_sports_catalog.php');
        exit;
    }

    private function handleUpdateSport() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/admin_sports_catalog.php');
            exit;
        }

        $sportId   = (int)($_POST['sport_id'] ?? 0);
        $sportName = trim($_POST['sport_name'] ?? '');

        if (!$sportId || empty($sportName)) {
            $_SESSION['flash_error'] = "Invalid sport data provided for update.";
            header('Location: ../views/admin_sports_catalog.php');
            exit;
        }

        try {
            $pdo = Database::connect();
            $stmt = $pdo->prepare("UPDATE `sports` SET `sport_name` = :name WHERE `sport_id` = :id");
            $stmt->execute([':name' => $sportName, ':id' => $sportId]);
            $_SESSION['flash_success'] = "Sport catalog entry updated successfully.";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        header('Location: ../views/admin_sports_catalog.php');
        exit;
    }

    private function handleCreateSeason() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/admin_seasons_setup.php');
            exit;
        }

        $seasonYear   = (int)($_POST['season_year'] ?? 0);
        $academicYear = (int)($_POST['academic_year'] ?? 1);
        $status       = trim($_POST['status'] ?? 'Active');

        // Logical in-memory pre-validation guard
        if ($seasonYear < 2020 || $seasonYear > 2040) {
            $_SESSION['flash_error'] = "Season year must be a realistic positive year (between 2020 and 2040).";
            header('Location: ../views/admin_seasons_setup.php');
            exit;
        }

        if ($academicYear < 1 || $academicYear > 6) {
            $_SESSION['flash_error'] = "Academic year must be between 1 and 6.";
            header('Location: ../views/admin_seasons_setup.php');
            exit;
        }

        if (!in_array($status, ['Active', 'Archived'], true)) {
            $_SESSION['flash_error'] = "Invalid status specified for season.";
            header('Location: ../views/admin_seasons_setup.php');
            exit;
        }

        try {
            $pdo = Database::connect();
            $stmt = $pdo->prepare("INSERT INTO `seasons` (`season_year`, `academic_year`, `status`) VALUES (:syr, :ayr, :st)");
            $stmt->execute([
                ':syr' => $seasonYear,
                ':ayr' => $academicYear,
                ':st'  => $status
            ]);
            $_SESSION['flash_success'] = "New athletic season (Year {$seasonYear}, Term {$academicYear}) established.";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        header('Location: ../views/admin_seasons_setup.php');
        exit;
    }

    private function handleCreateColor() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/admin_university_colors.php');
            exit;
        }

        try {
            $this->adminModel->createColorAward($_POST);
            $_SESSION['flash_success'] = "University Colours award recorded successfully.";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        header('Location: ../views/admin_university_colors.php');
        exit;
    }

    private function handleUpdateColorStatus() {
        $awardId = (int)($_POST['award_id'] ?? $_GET['id'] ?? 0);
        $status  = trim($_POST['status'] ?? $_GET['status'] ?? '');

        if (!$awardId || empty($status)) {
            $_SESSION['flash_error'] = "Invalid parameters for award status update.";
            header('Location: ../views/admin_university_colors.php');
            exit;
        }

        try {
            $this->adminModel->updateColorStatus($awardId, $status);
            $_SESSION['flash_success'] = "Colours award status updated to '{$status}'.";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        header('Location: ../views/admin_university_colors.php');
        exit;
    }

    private function handleDeleteColor() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/admin_university_colors.php');
            exit;
        }

        $awardId = (int)($_POST['award_id'] ?? 0);
        if (!$awardId) {
            $_SESSION['flash_error'] = "Invalid colours award record specified.";
            header('Location: ../views/admin_university_colors.php');
            exit;
        }

        try {
            $this->adminModel->deleteColorAward($awardId);
            $_SESSION['flash_success'] = "Colours award record deleted successfully.";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        header('Location: ../views/admin_university_colors.php');
        exit;
    }
}

// Bootstrap
$controller = new AdminController();
$controller->handleRequest();
