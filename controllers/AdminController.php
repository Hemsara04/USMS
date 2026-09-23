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
}

// Bootstrap
$controller = new AdminController();
$controller->handleRequest();
