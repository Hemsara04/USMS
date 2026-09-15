<?php
/**
 * Facility Model
 * Handles facility reservations, bookings, and locations for TIC
 */

require_once __DIR__ . '/../config/db.php';

class Facility {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Get pending facility booking requests
     */
    public function getPendingBookings() {
        $sql = "
            SELECT 
                fb.booking_id,
                fb.facility_id,
                fb.sport_id,
                fb.booking_date,
                fb.start_time,
                fb.duration_minutes,
                fb.approval_status,
                f.facility_name,
                f.type AS facility_type,
                f.capacity,
                s.sport_name
            FROM facility_bookings fb
            JOIN facilities f ON fb.facility_id = f.facility_id
            JOIN sports s ON fb.sport_id = s.sport_id
            WHERE fb.approval_status = 'Pending'
            ORDER BY fb.booking_date ASC, fb.start_time ASC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get reserved / approved locations
     */
    public function getReservedLocations() {
        $sql = "
            SELECT 
                fb.booking_id,
                fb.facility_id,
                fb.sport_id,
                fb.booking_date,
                fb.start_time,
                fb.duration_minutes,
                fb.approval_status,
                f.facility_name,
                f.type AS facility_type,
                f.capacity,
                s.sport_name
            FROM facility_bookings fb
            JOIN facilities f ON fb.facility_id = f.facility_id
            JOIN sports s ON fb.sport_id = s.sport_id
            WHERE fb.approval_status = 'Approved'
            ORDER BY fb.booking_date DESC, fb.start_time ASC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get all facilities
     */
    public function getAllFacilities() {
        $stmt = $this->db->query("SELECT * FROM facilities ORDER BY facility_name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Get single booking by ID
     */
    public function getBookingById($booking_id) {
        $stmt = $this->db->prepare("SELECT * FROM facility_bookings WHERE booking_id = :id");
        $stmt->execute([':id' => $booking_id]);
        return $stmt->fetch();
    }

    /**
     * Approve booking
     */
    public function approveBooking($booking_id) {
        $stmt = $this->db->prepare("UPDATE facility_bookings SET approval_status = 'Approved' WHERE booking_id = :id");
        return $stmt->execute([':id' => $booking_id]);
    }

    /**
     * Reject booking
     */
    public function rejectBooking($booking_id) {
        $stmt = $this->db->prepare("UPDATE facility_bookings SET approval_status = 'Rejected' WHERE booking_id = :id");
        return $stmt->execute([':id' => $booking_id]);
    }

    /**
     * Release / Complete facility booking
     */
    public function releaseBooking($booking_id) {
        $stmt = $this->db->prepare("DELETE FROM facility_bookings WHERE booking_id = :id");
        return $stmt->execute([':id' => $booking_id]);
    }

    /**
     * Check for scheduling conflicts (same location, same date, overlapping time slot)
     */
    public function checkConflict($facility_id, $booking_date, $start_time, $duration_minutes, $exclude_booking_id = null) {
        $sql = "
            SELECT 
                fb.booking_id,
                fb.facility_id,
                fb.booking_date,
                fb.start_time,
                fb.duration_minutes,
                f.facility_name,
                s.sport_name
            FROM facility_bookings fb
            JOIN facilities f ON fb.facility_id = f.facility_id
            JOIN sports s ON fb.sport_id = s.sport_id
            WHERE fb.facility_id = :fac_id
              AND fb.booking_date = :bdate
              AND fb.approval_status = 'Approved'
              AND (
                  :stime1 < ADDTIME(fb.start_time, SEC_TO_TIME(fb.duration_minutes * 60))
                  AND ADDTIME(:stime2, SEC_TO_TIME(:dur * 60)) > fb.start_time
              )
        ";
        if ($exclude_booking_id) {
            $sql .= " AND fb.booking_id != :exclude_id";
        }
        $stmt = $this->db->prepare($sql);
        $params = [
            ':fac_id' => $facility_id,
            ':bdate'  => $booking_date,
            ':stime1' => $start_time,
            ':stime2' => $start_time,
            ':dur'    => $duration_minutes
        ];
        if ($exclude_booking_id) {
            $params[':exclude_id'] = $exclude_booking_id;
        }
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Create a new booking
     */
    public function createBooking($facility_id, $sport_id, $booking_date, $start_time, $duration_minutes = 90, $status = 'Approved') {
        $stmt = $this->db->prepare("
            INSERT INTO facility_bookings (facility_id, sport_id, booking_date, start_time, duration_minutes, approval_status)
            VALUES (:fac_id, :sport_id, :bdate, :stime, :dur, :status)
        ");
        return $stmt->execute([
            ':fac_id'   => $facility_id,
            ':sport_id' => $sport_id,
            ':bdate'    => $booking_date,
            ':stime'    => $start_time,
            ':dur'      => $duration_minutes,
            ':status'   => $status
        ]);
    }

    /**
     * Get facility stats
     */
    public function getStats() {
        $totalPending = (int) $this->db->query("SELECT COUNT(*) FROM facility_bookings WHERE approval_status = 'Pending'")->fetchColumn();
        $totalBookings = (int) $this->db->query("SELECT COUNT(*) FROM facility_bookings WHERE approval_status = 'Approved'")->fetchColumn();

        return [
            'total_pending'  => $totalPending,
            'total_bookings' => $totalBookings
        ];
    }
}
?>
