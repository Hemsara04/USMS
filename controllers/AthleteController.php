<?php
/**
 * Athlete Controller
 * Handles application submission and athlete portal actions.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/SportApplication.php';

class AthleteController {
    private $db;
    private $sportAppModel;
    private $userId;

    public function __construct() {
        $this->db = Database::connect();
        $this->sportAppModel = new SportApplication($this->db);
        $this->userId = $_SESSION['user_id'] ?? null;
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? $_POST['action'] ?? '';

        switch ($action) {
            case 'update_profile':
                $this->updateProfile();
                break;

            case 'submit_application':
                $this->submitApplication();
                break;

            case 'update_application':
                $this->updateApplication();
                break;

            case 'delete_application':
                $this->deleteApplication();
                break;

            case 'get_my_applications':
                $this->getMyApplications();
                break;

            default:
                $this->sendResponse('error', 'Invalid action');
                break;
        }
    }

    private function updateProfile() {
        if (!$this->userId || ($_SESSION['role'] ?? '') !== 'Athlete') {
            http_response_code(403);
            $this->sendResponse('error', 'Unauthorized');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            $this->sendResponse('error', 'Use POST to save a profile.');
        }
        if (!is_string($_POST['csrf_token'] ?? null) || empty($_SESSION['athlete_csrf'])
            || !hash_equals($_SESSION['athlete_csrf'], $_POST['csrf_token'])) {
            http_response_code(403);
            $this->sendResponse('error', 'Your session expired. Reload the page and try again.');
        }
        $stmt = $this->db->prepare('SELECT athlete_id FROM Athletic WHERE user_id = ?');
        $stmt->execute([$this->userId]);
        if (!$stmt->fetchColumn()) {
            http_response_code(404);
            $this->sendResponse('error', 'Athlete profile not found.');
        }
        require_once __DIR__ . '/../models/AthletePortal.php';
        try {
            (new AthletePortal($this->db))->updateProfile($this->userId, $_POST);
            $_SESSION['athlete_profile_success'] = 'Profile updated successfully.';
            $this->sendResponse('success', 'Profile updated successfully.');
        } catch (InvalidArgumentException $e) {
            http_response_code(422);
            $this->sendResponse('error', $e->getMessage());
        } catch (PDOException $e) {
            error_log('Athlete profile save: ' . $e->getMessage());
            http_response_code(500);
            $this->sendResponse('error', 'Unable to save your profile. Please try again.');
        }
    }

    private function updateApplication() {
        if (!$this->userId || ($_SESSION['role'] ?? '') !== 'Athlete') {
            http_response_code(403);
            $this->sendResponse('error', 'Unauthorized');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            $this->sendResponse('error', 'Use POST to update an application.');
        }
        if (!is_string($_POST['csrf_token'] ?? null) || empty($_SESSION['athlete_csrf'])
            || !hash_equals($_SESSION['athlete_csrf'], $_POST['csrf_token'])) {
            http_response_code(403);
            $this->sendResponse('error', 'Your session expired. Reload the page and try again.');
        }
        $applicationId = filter_var($_POST['application_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$applicationId) {
            http_response_code(422);
            $this->sendResponse('error', 'Invalid application.');
        }
        try {
            $stmt = $this->db->prepare('SELECT athlete_id FROM Athletic WHERE user_id = ?');
            $stmt->execute([$this->userId]);
            $athleteId = $stmt->fetchColumn();
            $sportId = filter_var($_POST['sport_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $seasonId = filter_var($_POST['season_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $statement = $_POST['statement_of_interest'] ?? '';
            if (!$sportId || !$seasonId || !is_string($statement) || strlen($statement) > 64000) {
                throw new InvalidArgumentException('Please select a valid sport and season, and keep your statement under 16,000 characters.');
            }
            if (!$athleteId || !$this->sportAppModel->updateAthleteApplication($applicationId, $athleteId, $sportId, $seasonId, trim($statement))) {
                http_response_code(404);
                $this->sendResponse('error', 'Application not found.');
            }
            if (($_SESSION['last_application']['application_id'] ?? null) == $applicationId) {
                unset($_SESSION['last_application']);
            }
            $this->sendResponse('success', 'Application updated successfully.');
        } catch (InvalidArgumentException $e) {
            http_response_code(422);
            $this->sendResponse('error', $e->getMessage());
        } catch (PDOException $e) {
            error_log('Athlete application update: ' . $e->getMessage());
            http_response_code(500);
            $this->sendResponse('error', 'Unable to update your application. Please try again.');
        }
    }

    private function deleteApplication() {
        if (!$this->userId || ($_SESSION['role'] ?? '') !== 'Athlete') {
            http_response_code(403);
            $this->sendResponse('error', 'Unauthorized');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            $this->sendResponse('error', 'Use POST to delete an application.');
        }
        if (!is_string($_POST['csrf_token'] ?? null) || empty($_SESSION['athlete_csrf'])
            || !hash_equals($_SESSION['athlete_csrf'], $_POST['csrf_token'])) {
            http_response_code(403);
            $this->sendResponse('error', 'Your session expired. Reload the page and try again.');
        }
        $applicationId = filter_var($_POST['application_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$applicationId) {
            http_response_code(422);
            $this->sendResponse('error', 'Invalid application.');
        }
        try {
            $stmt = $this->db->prepare('SELECT athlete_id FROM Athletic WHERE user_id = ?');
            $stmt->execute([$this->userId]);
            $athleteId = $stmt->fetchColumn();
            if (!$athleteId || !$this->sportAppModel->deleteAthleteApplication($applicationId, $athleteId)) {
                http_response_code(404);
                $this->sendResponse('error', 'Application not found.');
            }
            if (($_SESSION['last_application']['application_id'] ?? null) == $applicationId) {
                unset($_SESSION['last_application']);
            }
            $this->sendResponse('success', 'Application deleted successfully.');
        } catch (PDOException $e) {
            error_log('Athlete application delete: ' . $e->getMessage());
            http_response_code(500);
            $this->sendResponse('error', 'Unable to delete your application. Please try again.');
        }
    }

    /**
     * Submit an application for a sport
     */
    private function submitApplication() {
        $role = strtolower($_SESSION['role'] ?? '');
        if (!$this->userId || $role !== 'athlete') {
            $this->sendResponse('error', 'Unauthorized. Please login as an athlete.');
            return;
        }

        // Fetch athlete profile for logged-in user
        $stmt = $this->db->prepare("SELECT athlete_id, full_name, index_number FROM Athletic WHERE user_id = ?");
        $stmt->execute([$this->userId]);
        $athlete = $stmt->fetch();

        // Fallback: If user_id has discrepancy, check athlete_id from POST
        if (!$athlete && !empty($_POST['athlete_id'])) {
            $stmt = $this->db->prepare("SELECT athlete_id, full_name, index_number FROM Athletic WHERE athlete_id = ?");
            $stmt->execute([(int)$_POST['athlete_id']]);
            $athlete = $stmt->fetch();
        }

        if (!$athlete) {
            $this->sendResponse('error', 'Athlete profile not found.');
            return;
        }

        $sportId = (int)($_POST['sport_id'] ?? 0);
        $seasonId = (int)($_POST['season_id'] ?? 0);
        $statement = trim($_POST['statement_of_interest'] ?? '');
        $termsAgree = isset($_POST['terms_agree']) && in_array(strtolower((string)$_POST['terms_agree']), ['1', 'true', 'on', 'yes']);

        // Check if AJAX request
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
                  || isset($_POST['ajax']) 
                  || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
                  || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($sportId <= 0) {
            $this->handleError('Please select a valid sport from the list.', $isAjax);
            return;
        }

        if ($seasonId <= 0) {
            $this->handleError('Please select a target season from the list.', $isAjax);
            return;
        }

        if (!$termsAgree) {
            $this->handleError('You must acknowledge and agree to the Athlete Code of Conduct and Compliance Guidelines.', $isAjax);
            return;
        }

        try {
            $result = $this->sportAppModel->applyForSport(
                $athlete['athlete_id'],
                $sportId,
                $seasonId,
                $statement
            );

            if ($isAjax) {
                $this->sendResponse('success', 'Your application has been submitted successfully.', $result);
            } else {
                $_SESSION['last_application'] = $result;
                $_SESSION['flash_success'] = 'Application submitted successfully for ' . $result['sport_name'] . '!';
                header('Location: ../views/athletic_regestration.php?view=submission-view');
                exit;
            }
        } catch (Exception $e) {
            $this->handleError($e->getMessage(), $isAjax);
        }
    }

    /**
     * Handle error for either AJAX or standard redirect
     */
    private function handleError($msg, $isAjax) {
        if ($isAjax) {
            $this->sendResponse('error', $msg);
        } else {
            $_SESSION['flash_error'] = $msg;
            header('Location: ../views/athletic_regestration.php?view=apply-view');
            exit;
        }
    }

    /**
     * Get athlete's applications via AJAX
     */
    private function getMyApplications() {
        if (!$this->userId || ($_SESSION['role'] ?? '') !== 'Athlete') {
            $this->sendResponse('error', 'Unauthorized');
            return;
        }

        $stmt = $this->db->prepare("SELECT athlete_id FROM Athletic WHERE user_id = ?");
        $stmt->execute([$this->userId]);
        $athlete = $stmt->fetch();

        if (!$athlete) {
            $this->sendResponse('error', 'Athlete not found');
            return;
        }

        $apps = $this->sportAppModel->getAthleteApplications($athlete['athlete_id']);
        $stats = $this->sportAppModel->getApplicationStats($athlete['athlete_id']);

        $this->sendResponse('success', 'Applications retrieved', [
            'applications' => $apps,
            'stats' => $stats
        ]);
    }

    /**
     * Send structured JSON response
     */
    private function sendResponse($status, $message, $data = null) {
        header('Content-Type: application/json');
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'data'    => $data
        ]);
        exit;
    }
}

$controller = new AthleteController();
$controller->handleRequest();
