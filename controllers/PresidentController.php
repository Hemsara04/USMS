<?php
/**
 * President Controller
 * Handles backend actions for the Student President Portal:
 * - Broadcasting announcements across the university
 * - Full CRUD operations (Create, Edit, Update, Delete, Toggle Status)
 * - AJAX retrieval for dynamic view/edit modals
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/auth_guard.php';
requireRole('President');

require_once __DIR__ . '/../models/Announcement.php';

class PresidentController {
    private $announcementModel;
    private $userId;

    public function __construct() {
        $this->announcementModel = new Announcement();
        $this->userId = $_SESSION['user_id'] ?? 1;
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? $_POST['action'] ?? '';

        switch ($action) {
            case 'create_announcement':
                $this->createAnnouncement();
                break;

            case 'update_announcement':
                $this->updateAnnouncement();
                break;

            case 'delete_announcement':
                $this->deleteAnnouncement();
                break;

            case 'toggle_status':
                $this->toggleStatus();
                break;

            case 'ajax_get_announcement':
                $this->ajaxGetAnnouncement();
                break;

            default:
                header('Location: ../views/pres_announcements.php');
                exit;
        }
    }

    /**
     * Create a new announcement broadcast
     */
    private function createAnnouncement() {
        $title   = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if (empty($title) || empty($content)) {
            $_SESSION['flash_error'] = 'Announcement title and content cannot be empty.';
            header('Location: ../views/pres_announcements.php');
            exit;
        }

        $success = $this->announcementModel->createAnnouncement($_POST, $this->userId);
        if ($success) {
            $_SESSION['flash_success'] = 'Announcement broadcasted successfully to all designated users!';
        } else {
            $_SESSION['flash_error'] = 'Failed to publish announcement. Please check your input and try again.';
        }

        header('Location: ../views/pres_announcements.php');
        exit;
    }

    /**
     * Update an existing announcement
     */
    private function updateAnnouncement() {
        $id      = (int)($_POST['announcement_id'] ?? 0);
        $title   = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if ($id <= 0 || empty($title) || empty($content)) {
            $_SESSION['flash_error'] = 'Invalid announcement data. Title and message body are required.';
            header('Location: ../views/pres_announcements.php');
            exit;
        }

        $success = $this->announcementModel->updateAnnouncement($id, $_POST);
        if ($success) {
            $_SESSION['flash_success'] = 'Announcement updated successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to update announcement.';
        }

        header('Location: ../views/pres_announcements.php');
        exit;
    }

    /**
     * Delete an announcement
     */
    private function deleteAnnouncement() {
        $id = (int)($_POST['announcement_id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Invalid announcement ID.';
            header('Location: ../views/pres_announcements.php');
            exit;
        }

        $success = $this->announcementModel->deleteAnnouncement($id);
        if ($success) {
            $_SESSION['flash_success'] = 'Announcement permanently deleted and removed from all notification feeds.';
        } else {
            $_SESSION['flash_error'] = 'Failed to delete announcement.';
        }

        header('Location: ../views/pres_announcements.php');
        exit;
    }

    /**
     * Toggle status between Active and Archived
     */
    private function toggleStatus() {
        $id = (int)($_POST['announcement_id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Invalid announcement ID.';
            header('Location: ../views/pres_announcements.php');
            exit;
        }

        $success = $this->announcementModel->toggleStatus($id);
        if ($success) {
            $_SESSION['flash_success'] = 'Announcement status toggled successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to toggle announcement status.';
        }

        header('Location: ../views/pres_announcements.php');
        exit;
    }

    /**
     * AJAX endpoint: Get announcement details for modals
     */
    private function ajaxGetAnnouncement() {
        header('Content-Type: application/json');
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
            exit;
        }

        $data = $this->announcementModel->getAnnouncementById($id);
        if (!$data) {
            echo json_encode(['status' => 'error', 'message' => 'Announcement not found']);
            exit;
        }

        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }
}

$controller = new PresidentController();
$controller->handleRequest();
?>
