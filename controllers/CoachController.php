<?php
/**
 * Coach Controller
 * USMS - University Sports Management System
 * Processes all Coach operations: training scheduling, attendance sheets,
 * squad formulation submissions, match results, and equipment requisitions.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/auth_guard.php';
requireRole('Coach');

require_once __DIR__ . '/../models/Coach.php';

class CoachController {
    private $coachModel;

    public function __construct() {
        $this->coachModel = new Coach();
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? $_POST['action'] ?? '';

        switch ($action) {
            case 'schedule_session':
                $this->scheduleSession();
                break;

            case 'update_session':
                $this->updateSession();
                break;

            case 'delete_session':
                $this->deleteSession();
                break;

            case 'save_attendance':
                $this->saveAttendance();
                break;

            case 'submit_squad':
                $this->submitSquad();
                break;

            case 'log_match_result':
                $this->logMatchResult();
                break;

            case 'request_equipment':
                $this->requestEquipment();
                break;

            default:
                header('Location: ../views/coach_training_sessions.php');
                exit;
        }
    }

    /**
     * Schedule a new team practice / conditioning drill
     */
    private function scheduleSession() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/coach_training_sessions.php');
            exit;
        }

        $sportId       = (int)($_POST['sport_id'] ?? 1);
        $facilityId    = (int)($_POST['facility_id'] ?? 2);
        $sessionDate   = trim($_POST['session_date'] ?? date('Y-m-d'));
        $startTime     = trim($_POST['start_time'] ?? '16:00:00');
        $durationTime  = trim($_POST['duration_minutes'] ?? '01:30:00');
        $sessionType   = trim($_POST['session_type'] ?? 'Practice');
        $focusArea     = trim($_POST['focus_area'] ?? '');
        $notes         = trim($_POST['notes'] ?? '');

        if (empty($sessionDate) || empty($startTime)) {
            $_SESSION['flash_error'] = 'Session date and start time are required.';
            header('Location: ../views/coach_training_sessions.php');
            exit;
        }

        try {
            $sessionId = $this->coachModel->createTrainingSession(
                $sportId,
                $facilityId,
                $sessionDate,
                $startTime,
                $durationTime,
                $sessionType,
                $focusArea,
                $notes
            );

            $_SESSION['flash_success'] = 'Training session scheduled successfully for ' . htmlspecialchars($sessionDate) . '!';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Failed to schedule session: ' . $e->getMessage();
        }

        header('Location: ../views/coach_training_sessions.php');
        exit;
    }

    /**
     * Update an existing training session
     */
    private function updateSession() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/coach_training_sessions.php');
            exit;
        }

        $sessionId     = (int)($_POST['session_id'] ?? 0);
        $sportId       = (int)($_POST['sport_id'] ?? 1);
        $facilityId    = (int)($_POST['facility_id'] ?? 1);
        $sessionDate   = trim($_POST['session_date'] ?? '');
        $startTime     = trim($_POST['start_time'] ?? '');
        $durationTime  = trim($_POST['duration_minutes'] ?? '01:30:00');
        $sessionType   = trim($_POST['session_type'] ?? 'Practice');
        $focusArea     = trim($_POST['focus_area'] ?? '');
        $notes         = trim($_POST['notes'] ?? '');

        if ($sessionId <= 0 || empty($sessionDate) || empty($startTime)) {
            $_SESSION['flash_error'] = 'Invalid session details or required fields missing.';
            header('Location: ../views/coach_training_sessions.php');
            exit;
        }

        try {
            $this->coachModel->updateTrainingSession(
                $sessionId,
                $sportId,
                $facilityId,
                $sessionDate,
                $startTime,
                $durationTime,
                $sessionType,
                $focusArea,
                $notes
            );
            $_SESSION['flash_success'] = 'Training session TRN-' . str_pad($sessionId, 3, '0', STR_PAD_LEFT) . ' updated successfully!';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Failed to update session: ' . $e->getMessage();
        }

        header('Location: ../views/coach_training_sessions.php');
        exit;
    }

    /**
     * Delete / Cancel a scheduled training session
     */
    private function deleteSession() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/coach_training_sessions.php');
            exit;
        }

        $sessionId = (int)($_POST['session_id'] ?? 0);
        if ($sessionId <= 0) {
            $_SESSION['flash_error'] = 'Invalid session ID for cancellation.';
            header('Location: ../views/coach_training_sessions.php');
            exit;
        }

        try {
            $this->coachModel->deleteTrainingSession($sessionId);
            $_SESSION['flash_success'] = 'Training session TRN-' . str_pad($sessionId, 3, '0', STR_PAD_LEFT) . ' has been removed.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Failed to delete session: ' . $e->getMessage();
        }

        header('Location: ../views/coach_training_sessions.php');
        exit;
    }

    /**
     * Record/update attendance statuses for players
     */
    private function saveAttendance() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/coach_training_sessions.php');
            exit;
        }

        $sessionId     = (int)($_POST['session_id'] ?? 0);
        $attendanceMap = $_POST['attendance'] ?? [];

        if ($sessionId <= 0 || empty($attendanceMap)) {
            $_SESSION['flash_error'] = 'Invalid session attendance submission.';
            header('Location: ../views/coach_training_sessions.php');
            exit;
        }

        try {
            $this->coachModel->saveAttendance($sessionId, $attendanceMap);
            $_SESSION['flash_success'] = 'Attendance sheet saved and synchronized with sports registry!';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Failed to save attendance: ' . $e->getMessage();
        }

        header('Location: ../views/coach_training_sessions.php');
        exit;
    }

    /**
     * Submit squad selection to TIC for approval
     */
    private function submitSquad() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/coach_squad_selection.php');
            exit;
        }

        $fixtureId  = (int)($_POST['fixture_id'] ?? 1);
        $athleteIds = $_POST['selected_athletes'] ?? [];

        if ($fixtureId <= 0) {
            $_SESSION['flash_error'] = 'Please select a valid tournament match fixture.';
            header('Location: ../views/coach_squad_selection.php');
            exit;
        }

        // If no athletes passed via checkboxes, populate default pool
        if (empty($athleteIds)) {
            $pool = $this->coachModel->getAvailableAthletesForSport(1);
            $athleteIds = array_slice(array_column($pool, 'athlete_id'), 0, 15);
        }

        try {
            $this->coachModel->saveSquadDraft($fixtureId, $athleteIds);
            $_SESSION['flash_success'] = 'Official match squad submitted to TIC Officer for review and accreditation!';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Failed to submit squad: ' . $e->getMessage();
        }

        header('Location: ../views/coach_squad_selection.php');
        exit;
    }

    /**
     * Log match result score and scorers
     */
    private function logMatchResult() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/coach_fixtures_results.php');
            exit;
        }

        $fixtureId = (int)($_POST['fixture_id'] ?? 0);
        $scoreUSMS = trim($_POST['score_usms'] ?? '0');
        $scoreOpp  = trim($_POST['score_opponent'] ?? '0');
        $scores    = "$scoreUSMS - $scoreOpp";
        $scorers   = trim($_POST['scorers'] ?? '');
        $report    = trim($_POST['match_report'] ?? '');

        if ($fixtureId <= 0) {
            $_SESSION['flash_error'] = 'Please select an official match fixture.';
            header('Location: ../views/coach_fixtures_results.php');
            exit;
        }

        try {
            $this->coachModel->logMatchResult($fixtureId, $scores, $scorers, $report);
            $_SESSION['flash_success'] = 'Match result and official scoreline recorded in USMS registry!';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Failed to record match result: ' . $e->getMessage();
        }

        header('Location: ../views/coach_fixtures_results.php');
        exit;
    }

    /**
     * Request sports equipment from TIC warehouse
     */
    private function requestEquipment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/coach_equipment_requests.php');
            exit;
        }

        $equipmentId = (int)($_POST['equipment_id'] ?? 0);
        $quantity    = max(1, (int)($_POST['quantity'] ?? 1));
        $returnDate  = trim($_POST['return_date'] ?? date('Y-m-d', strtotime('+7 days')));
        $purpose     = trim($_POST['purpose'] ?? 'Training practice and match preparation');
        $coachName   = $_SESSION['username'] ?? 'Coach Silva';

        if ($equipmentId <= 0) {
            $_SESSION['flash_error'] = 'Please select an equipment item from the catalogue.';
            header('Location: ../views/coach_equipment_requests.php');
            exit;
        }

        try {
            $this->coachModel->submitEquipmentRequisition($equipmentId, $coachName, $quantity, $returnDate, $purpose);
            $_SESSION['flash_success'] = 'Requisition request submitted to TIC warehouse successfully!';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Failed to submit requisition: ' . $e->getMessage();
        }

        header('Location: ../views/coach_equipment_requests.php');
        exit;
    }
}

$controller = new CoachController();
$controller->handleRequest();
