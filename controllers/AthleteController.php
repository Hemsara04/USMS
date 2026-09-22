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
            case 'submit_application':
                $this->submitApplication();
                break;

            case 'get_my_applications':
                $this->getMyApplications();
                break;

            default:
                $this->sendResponse('error', 'Invalid action');
                break;
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
