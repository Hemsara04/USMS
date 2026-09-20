<?php
/**
 * Medical Controller
 * Handles backend actions for Medical Officer portal:
 * - Health registry record updates
 * - Injury logging
 * - Medical clearance authorization
 * - AJAX athlete medical searches & profiles
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/auth_guard.php';
requireRole('Medical_Officer');

require_once __DIR__ . '/../models/Medical.php';

class MedicalController {
    private $medModel;

    public function __construct() {
        $this->medModel = new Medical();
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? $_POST['action'] ?? '';

        switch ($action) {
            case 'save_health_record':
                $this->saveHealthRecord();
                break;

            case 'log_injury':
                $this->logInjury();
                break;

            case 'issue_clearance':
                $this->issueClearance();
                break;

            case 'delete_clearance':
                $this->deleteClearance();
                break;

            case 'clear_clearance_history':
                $this->clearClearanceHistory();
                break;

            case 'ajax_search_athletes':
                $this->ajaxSearchAthletes();
                break;

            case 'ajax_get_athlete_medical':
                $this->ajaxGetAthleteMedical();
                break;

            default:
                header('Location: ../views/med_athlete_roster.php');
                exit;
        }
    }

    /**
     * Save student medical record / physical metrics
     */
    private function saveHealthRecord() {
        $athleteId = (int) ($_POST['athlete_id'] ?? 0);
        if ($athleteId <= 0) {
            $_SESSION['flash_error'] = 'Invalid athlete selected. Please select a registered student athlete.';
            header('Location: ../views/med_athlete_registry.php');
            exit;
        }

        $success = $this->medModel->saveHealthRecord($_POST);
        if ($success) {
            $_SESSION['flash_success'] = 'Student medical record updated and clearance status synchronized successfully!';
        } else {
            $_SESSION['flash_error'] = 'Failed to update student medical record. Please try again.';
        }

        header('Location: ../views/med_athlete_registry.php');
        exit;
    }

    /**
     * Log a new injury
     */
    private function logInjury() {
        $athleteId  = (int) ($_POST['athlete_id'] ?? 0);
        $injuryType = trim($_POST['injury_type'] ?? '');

        if ($athleteId <= 0 || empty($injuryType)) {
            $_SESSION['flash_error'] = 'Please select an athlete and enter the injury/condition description.';
            header('Location: ../views/med_athlete_roster.php');
            exit;
        }

        $success = $this->medModel->logInjury($_POST);
        if ($success) {
            $_SESSION['flash_success'] = 'New injury logged successfully. Athlete status set to Under Review.';
        } else {
            $_SESSION['flash_error'] = 'Failed to log injury record.';
        }

        header('Location: ../views/med_athlete_roster.php');
        exit;
    }

    /**
     * Issue medical clearance
     */
    private function issueClearance() {
        $athleteId = (int) ($_POST['athlete_id'] ?? 0);

        if ($athleteId <= 0) {
            $_SESSION['flash_error'] = 'Invalid athlete clearance request.';
            header('Location: ../views/med_athlete_roster.php');
            exit;
        }

        $success = $this->medModel->issueClearance($_POST);
        if ($success) {
            $_SESSION['flash_success'] = 'Medical clearance authorized and recorded successfully!';
        } else {
            $_SESSION['flash_error'] = 'Failed to record medical clearance.';
        }

        header('Location: ../views/med_athlete_roster.php');
        exit;
    }

    /**
     * Delete a single clearance record
     */
    private function deleteClearance() {
        $id = (int) ($_POST['clearance_id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Invalid clearance record selected.';
            header('Location: ../views/med_athlete_roster.php?tab=history');
            exit;
        }

        $success = $this->medModel->deleteClearance($id);
        if ($success) {
            $_SESSION['flash_success'] = 'Clearance record deleted successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to delete clearance record.';
        }

        header('Location: ../views/med_athlete_roster.php?tab=history');
        exit;
    }

    /**
     * Clear all clearance history records
     */
    private function clearClearanceHistory() {
        $success = $this->medModel->clearClearanceHistory();
        if ($success) {
            $_SESSION['flash_success'] = 'All medical clearance history records cleared successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to clear clearance history.';
        }

        header('Location: ../views/med_athlete_roster.php?tab=history');
        exit;
    }

    /**
     * AJAX endpoint: Search registered athletes
     */
    private function ajaxSearchAthletes() {
        header('Content-Type: application/json');
        $q = trim($_GET['q'] ?? '');
        $athletes = $this->medModel->searchAthletes($q, 30);

        $data = array_map(function($a) {
            return [
                'id'                => $a['athlete_id'],
                'name'              => $a['full_name'],
                'subtitle'          => $a['index_number'],
                'faculty'           => $a['faculty'],
                'emergency_contact' => $a['emergency_Contact'],
                'blood_group'       => $a['blood_group'],
                'height'            => $a['height'],
                'weight'            => $a['weight'],
                'medical_status'    => $a['medical_status'],
                'allergies'         => $a['allergies'],
                'medical_notes'     => $a['medical_notes'],
                'sports_list'       => $a['sports_list']
            ];
        }, $athletes);

        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    /**
     * AJAX endpoint: Get full profile of a single athlete
     */
    private function ajaxGetAthleteMedical() {
        header('Content-Type: application/json');
        $aid = (int) ($_GET['athlete_id'] ?? 0);
        $profile = $this->medModel->getAthleteMedicalProfile($aid);

        if (!$profile) {
            echo json_encode(['status' => 'error', 'message' => 'Athlete not found']);
            exit;
        }

        echo json_encode(['status' => 'success', 'data' => $profile]);
        exit;
    }
}

$controller = new MedicalController();
$controller->handleRequest();
?>
