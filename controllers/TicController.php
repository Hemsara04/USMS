<?php
/**
 * TIC Controller
 * Handles all backend actions for Teacher In Charge (Equipment, Squads, Facilities)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/auth_guard.php';
requireRole('TIC');

require_once __DIR__ . '/../models/Equipment.php';
require_once __DIR__ . '/../models/Squad.php';
require_once __DIR__ . '/../models/Facility.php';

class TicController {
    private $equipmentModel;
    private $squadModel;
    private $facilityModel;

    public function __construct() {
        $this->equipmentModel = new Equipment();
        $this->squadModel     = new Squad();
        $this->facilityModel  = new Facility();
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? $_POST['action'] ?? '';

        switch ($action) {
            // Equipment Requests
            case 'approve_request':
                $this->approveRequest();
                break;
            case 'reject_request':
                $this->rejectRequest();
                break;
            case 'add_manual_request':
                $this->addManualRequest();
                break;

            // Stock Inventory
            case 'add_equipment':
                $this->addEquipment();
                break;
            case 'edit_equipment':
                $this->editEquipment();
                break;
            case 'delete_equipment':
                $this->deleteEquipment();
                break;

            // Checkouts
            case 'mark_returned':
                $this->markReturned();
                break;
            case 'new_checkout':
                $this->newCheckout();
                break;

            // Squad Approvals
            case 'approve_squad':
                $this->approveSquad();
                break;
            case 'reject_squad':
                $this->rejectSquad();
                break;
            case 'approve_fixture_squad':
                $this->approveFixtureSquad();
                break;
            case 'reject_fixture_squad':
                $this->rejectFixtureSquad();
                break;
            case 'nominate_squad':
                $this->nominateSquad();
                break;
            case 'add_fixture':
                $this->addFixture();
                break;
            case 'delete_fixture':
                $this->deleteFixture();
                break;

            // Facility Bookings
            case 'approve_booking':
                $this->approveBooking();
                break;
            case 'reject_booking':
                $this->rejectBooking();
                break;
            case 'release_booking':
                $this->releaseBooking();
                break;
            case 'add_booking':
                $this->addBooking();
                break;

            // AJAX APIs
            case 'ajax_search_recipients':
                $this->ajaxSearchRecipients();
                break;
            case 'ajax_search_equipment':
                $this->ajaxSearchEquipment();
                break;

            default:
                header('Location: ../views/tic_equipment_requests.php');
                exit;
        }
    }

    // --- Equipment Handlers ---
    private function approveRequest() {
        $issueId = (int) ($_POST['issue_id'] ?? $_GET['id'] ?? 0);
        if ($issueId > 0 && $this->equipmentModel->approveRequest($issueId)) {
            $_SESSION['flash_success'] = 'Equipment request approved and issued successfully!';
        } else {
            $_SESSION['flash_error'] = 'Failed to approve request. Insufficient stock available.';
        }
        header('Location: ../views/tic_equipment_requests.php');
        exit;
    }

    private function rejectRequest() {
        $issueId = (int) ($_POST['issue_id'] ?? $_GET['id'] ?? 0);
        if ($issueId > 0 && $this->equipmentModel->rejectRequest($issueId)) {
            $_SESSION['flash_success'] = 'Equipment request rejected.';
        } else {
            $_SESSION['flash_error'] = 'Failed to reject request.';
        }
        header('Location: ../views/tic_equipment_requests.php');
        exit;
    }

    private function addManualRequest() {
        $equipmentId   = (int) ($_POST['equipment_id'] ?? 0);
        $quantity      = max(1, (int) ($_POST['quantity'] ?? 1));
        $recipientType = $_POST['recipient_type'] ?? 'Athlete';
        $athleteId     = ($recipientType === 'Athlete') ? (int) ($_POST['athlete_id'] ?? 0) : null;
        $recipientName = trim($_POST['recipient_name'] ?? '');
        $expReturn     = $_POST['expected_return_date'] ?? date('Y-m-d', strtotime('+7 days'));

        if ($equipmentId > 0 && ($athleteId > 0 || !empty($recipientName))) {
            $this->equipmentModel->createRequest($equipmentId, $quantity, $recipientType, $athleteId, $recipientName, date('Y-m-d'), $expReturn, 'Pending');
            $_SESSION['flash_success'] = 'New manual equipment request created!';
        } else {
            $_SESSION['flash_error'] = 'Please specify valid equipment and recipient details.';
        }
        header('Location: ../views/tic_equipment_requests.php');
        exit;
    }

    private function addEquipment() {
        $sportId   = (int) ($_POST['sport_id'] ?? 0);
        $itemName  = trim($_POST['item_name'] ?? '');
        $quantity  = max(1, (int) ($_POST['quantity'] ?? 1));
        $condition = $_POST['condition'] ?? 'Good';

        if ($sportId > 0 && !empty($itemName)) {
            $res = $this->equipmentModel->upsertEquipment($sportId, $itemName, $quantity, $condition);
            if ($res['status'] === 'restocked') {
                $_SESSION['flash_success'] = "Restocked existing item '{$itemName}' with +{$quantity} units (Total: {$res['new_total']})!";
            } else {
                $_SESSION['flash_success'] = "Added new equipment '{$itemName}' ({$quantity} units) to inventory!";
            }
        } else {
            $_SESSION['flash_error'] = 'Invalid equipment details provided.';
        }
        header('Location: ../views/tic_stock_inventory.php');
        exit;
    }

    private function editEquipment() {
        $equipmentId = (int) ($_POST['equipment_id'] ?? 0);
        $itemName    = trim($_POST['item_name'] ?? '');
        $quantity    = max(0, (int) ($_POST['quantity'] ?? 0));
        $condition   = $_POST['condition'] ?? 'Good';

        if ($equipmentId > 0 && !empty($itemName)) {
            $this->equipmentModel->updateEquipment($equipmentId, $itemName, $quantity, $condition);
            $_SESSION['flash_success'] = "Updated equipment '{$itemName}' successfully!";
        } else {
            $_SESSION['flash_error'] = 'Invalid equipment update parameters.';
        }
        header('Location: ../views/tic_stock_inventory.php');
        exit;
    }

    private function deleteEquipment() {
        $equipmentId = (int) ($_POST['equipment_id'] ?? $_GET['id'] ?? 0);
        if ($equipmentId > 0) {
            $this->equipmentModel->deleteEquipment($equipmentId);
            $_SESSION['flash_success'] = 'Equipment item removed from inventory.';
        }
        header('Location: ../views/tic_stock_inventory.php');
        exit;
    }

    private function markReturned() {
        $issueId = (int) ($_POST['issue_id'] ?? $_GET['id'] ?? 0);
        if ($issueId > 0 && $this->equipmentModel->markReturned($issueId)) {
            $_SESSION['flash_success'] = 'Item successfully marked as returned and restocked!';
        } else {
            $_SESSION['flash_error'] = 'Failed to mark item as returned.';
        }
        header('Location: ../views/tic_checkout_log.php');
        exit;
    }

    private function newCheckout() {
        $equipmentId   = (int) ($_POST['equipment_id'] ?? 0);
        $quantity      = max(1, (int) ($_POST['quantity'] ?? 1));
        $recipientType = $_POST['recipient_type'] ?? 'Athlete';
        $athleteId     = ($recipientType === 'Athlete') ? (int) ($_POST['athlete_id'] ?? 0) : null;
        $recipientName = trim($_POST['recipient_name'] ?? '');
        $expReturn     = $_POST['expected_return_date'] ?? date('Y-m-d', strtotime('+7 days'));
        $redirectView  = $_POST['redirect_to'] ?? 'tic_checkout_log.php';

        // Validate recipient
        if ($recipientType === 'Athlete' && $athleteId <= 0) {
            $_SESSION['flash_error'] = 'Please select a valid athlete for this check-out.';
            header("Location: ../views/{$redirectView}");
            exit;
        }

        if ($recipientType === 'Coach' && empty($recipientName)) {
            $_SESSION['flash_error'] = 'Please select or specify the Coach recipient.';
            header("Location: ../views/{$redirectView}");
            exit;
        }

        if ($equipmentId > 0) {
            $ok = $this->equipmentModel->directCheckout($equipmentId, $quantity, $recipientType, $athleteId, $recipientName, $expReturn);
            if ($ok) {
                $_SESSION['flash_success'] = "Direct check-out completed ({$quantity} units issued)!";
            } else {
                $_SESSION['flash_error'] = "Check-out failed. Not enough units in stock for this request.";
            }
        } else {
            $_SESSION['flash_error'] = 'Please select a valid equipment item.';
        }

        header("Location: ../views/{$redirectView}");
        exit;
    }

    // --- Squad Handlers ---
    private function approveSquad() {
        $squadId = (int) ($_POST['squad_id'] ?? $_GET['id'] ?? 0);
        if ($squadId > 0 && $this->squadModel->approveSquad($squadId)) {
            $_SESSION['flash_success'] = 'Athlete squad participation approved!';
        }
        header('Location: ../views/tic_squad_approval.php');
        exit;
    }

    private function rejectSquad() {
        $squadId = (int) ($_POST['squad_id'] ?? $_GET['id'] ?? 0);
        if ($squadId > 0 && $this->squadModel->rejectSquad($squadId)) {
            $_SESSION['flash_success'] = 'Athlete squad participation rejected.';
        }
        header('Location: ../views/tic_squad_approval.php');
        exit;
    }

    private function approveFixtureSquad() {
        $fixtureId = (int) ($_POST['fixture_id'] ?? $_GET['fixture_id'] ?? 0);
        if ($fixtureId > 0 && $this->squadModel->approveFixtureSquad($fixtureId)) {
            $_SESSION['flash_success'] = 'All pending athletes for this team squad approved!';
        } else {
            $_SESSION['flash_error'] = 'Failed to approve squad.';
        }
        header('Location: ../views/tic_squad_approval.php');
        exit;
    }

    private function rejectFixtureSquad() {
        $fixtureId = (int) ($_POST['fixture_id'] ?? $_GET['fixture_id'] ?? 0);
        if ($fixtureId > 0 && $this->squadModel->rejectFixtureSquad($fixtureId)) {
            $_SESSION['flash_success'] = 'Pending squad roster rejected for this match fixture.';
        } else {
            $_SESSION['flash_error'] = 'Failed to reject squad.';
        }
        header('Location: ../views/tic_squad_approval.php');
        exit;
    }

    private function nominateSquad() {
        $fixtureId  = (int) ($_POST['fixture_id'] ?? 0);
        $athleteIds = $_POST['athlete_ids'] ?? [];
        if ($fixtureId > 0 && !empty($athleteIds)) {
            $this->squadModel->nominateSquad($fixtureId, (array) $athleteIds);
            $_SESSION['flash_success'] = 'Athletes successfully nominated to the fixture squad!';
        } else {
            $_SESSION['flash_error'] = 'Please select a fixture and at least one athlete.';
        }
        header('Location: ../views/tic_squad_approval.php');
        exit;
    }

    private function addFixture() {
        $sportId     = (int) ($_POST['sport_id'] ?? 0);
        $facilityId  = (int) ($_POST['facility_id'] ?? 0);
        $opponent    = trim($_POST['opponent'] ?? '');
        $fixtureType = $_POST['fixture_type'] ?? 'Inter-University';
        $fixtureDate = $_POST['fixture_date'] ?? date('Y-m-d');
        $fixtureTime = $_POST['fixture_time'] ?? '15:00:00';

        if ($sportId > 0 && $facilityId > 0 && !empty($opponent)) {
            $this->squadModel->createFixture($sportId, $facilityId, $opponent, $fixtureType, $fixtureDate, $fixtureTime);
            $_SESSION['flash_success'] = "Match fixture vs {$opponent} added successfully!";
        } else {
            $_SESSION['flash_error'] = 'Please fill all required fixture details.';
        }
        header('Location: ../views/tic_squad_approval.php');
        exit;
    }

    private function deleteFixture() {
        $fixtureId = (int) ($_POST['fixture_id'] ?? $_GET['id'] ?? 0);
        if ($fixtureId > 0 && $this->squadModel->deleteFixture($fixtureId)) {
            $_SESSION['flash_success'] = 'Match fixture and assigned squad removed successfully!';
        } else {
            $_SESSION['flash_error'] = 'Failed to delete fixture.';
        }
        header('Location: ../views/tic_squad_approval.php');
        exit;
    }

    // --- Facility Handlers ---
    private function approveBooking() {
        $bookingId = (int) ($_POST['booking_id'] ?? $_GET['id'] ?? 0);
        if ($bookingId > 0) {
            $booking = $this->facilityModel->getBookingById($bookingId);
            if ($booking) {
                $conflict = $this->facilityModel->checkConflict($booking['facility_id'], $booking['booking_date'], $booking['start_time'], $booking['duration_minutes'], $bookingId);
                if ($conflict) {
                    $_SESSION['flash_error'] = "Approval Denied: Scheduling conflict with {$conflict['sport_name']} reservation at {$conflict['facility_name']} (" . substr($conflict['start_time'], 0, 5) . ", {$conflict['duration_minutes']} min).";
                    header('Location: ../views/tic_facility_booking.php');
                    exit;
                }
            }

            if ($this->facilityModel->approveBooking($bookingId)) {
                $_SESSION['flash_success'] = 'Facility reservation approved!';
            } else {
                $_SESSION['flash_error'] = 'Failed to approve booking.';
            }
        }
        header('Location: ../views/tic_facility_booking.php');
        exit;
    }

    private function rejectBooking() {
        $bookingId = (int) ($_POST['booking_id'] ?? $_GET['id'] ?? 0);
        if ($bookingId > 0 && $this->facilityModel->rejectBooking($bookingId)) {
            $_SESSION['flash_success'] = 'Facility booking rejected.';
        }
        header('Location: ../views/tic_facility_booking.php');
        exit;
    }

    private function releaseBooking() {
        $bookingId = (int) ($_POST['booking_id'] ?? $_GET['id'] ?? 0);
        if ($bookingId > 0 && $this->facilityModel->releaseBooking($bookingId)) {
            $_SESSION['flash_success'] = 'Facility reservation completed and venue released!';
        } else {
            $_SESSION['flash_error'] = 'Failed to release reservation.';
        }
        header('Location: ../views/tic_facility_booking.php');
        exit;
    }

    private function addBooking() {
        $facilityId = (int) ($_POST['facility_id'] ?? 0);
        $sportId    = (int) ($_POST['sport_id'] ?? 0);
        $bDate      = $_POST['booking_date'] ?? date('Y-m-d');
        $sTime      = $_POST['start_time'] ?? '16:00:00';
        $duration   = (int) ($_POST['duration_minutes'] ?? 90);

        if ($facilityId > 0 && $sportId > 0) {
            $conflict = $this->facilityModel->checkConflict($facilityId, $bDate, $sTime, $duration);
            if ($conflict) {
                $_SESSION['flash_error'] = "Schedule Conflict: {$conflict['facility_name']} is already booked by {$conflict['sport_name']} from " . substr($conflict['start_time'], 0, 5) . " ({$conflict['duration_minutes']} mins) on {$bDate}.";
            } else {
                $this->facilityModel->createBooking($facilityId, $sportId, $bDate, $sTime, $duration, 'Approved');
                $_SESSION['flash_success'] = 'Facility reserved successfully!';
            }
        } else {
            $_SESSION['flash_error'] = 'Invalid reservation details.';
        }
        header('Location: ../views/tic_facility_booking.php');
        exit;
    }

    // --- AJAX Search Endpoints ---
    private function ajaxSearchRecipients() {
        header('Content-Type: application/json');
        $type = $_GET['type'] ?? 'Athlete';
        $q    = trim($_GET['q'] ?? '');

        $db = Database::connect();
        if ($type === 'Coach') {
            $sql = "SELECT user_id AS id, username AS name, email, 'Coach' AS subtitle FROM user WHERE role = 'Coach'";
            $params = [];
            if (!empty($q)) {
                $sql .= " AND (username LIKE :q1 OR email LIKE :q2)";
                $params[':q1'] = "%{$q}%";
                $params[':q2'] = "%{$q}%";
            }
            $sql .= " ORDER BY username ASC LIMIT 20";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $sql = "SELECT athlete_id AS id, full_name AS name, index_number AS subtitle, faculty FROM Athletic";
            $params = [];
            if (!empty($q)) {
                $sql .= " WHERE full_name LIKE :q1 OR index_number LIKE :q2";
                $params[':q1'] = "%{$q}%";
                $params[':q2'] = "%{$q}%";
            }
            $sql .= " ORDER BY full_name ASC LIMIT 20";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    private function ajaxSearchEquipment() {
        header('Content-Type: application/json');
        $q       = trim($_GET['q'] ?? '');
        $sportId = (int) ($_GET['sport_id'] ?? 0);

        $db = Database::connect();
        $sql = "
            SELECT e.equipment_id AS id, e.item_name AS name, e.quantity_in_stock AS stock, e.condition_status, s.sport_name
            FROM equipment e
            JOIN sports s ON e.sport_id = s.sport_id
            WHERE 1=1
        ";
        $params = [];
        if (!empty($q)) {
            $sql .= " AND (e.item_name LIKE :q1 OR s.sport_name LIKE :q2)";
            $params[':q1'] = "%{$q}%";
            $params[':q2'] = "%{$q}%";
        }
        if ($sportId > 0) {
            $sql .= " AND e.sport_id = :sid";
            $params[':sid'] = $sportId;
        }
        $sql .= " ORDER BY e.item_name ASC LIMIT 25";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }
}

$controller = new TicController();
$controller->handleRequest();
?>
