<?php
/**
 * Medical Model
 * Handles database operations for Medical Officer portal:
 * - Health registry & clinical records
 * - Injury tracking
 * - Medical clearance issuance & history
 * - Clinical metrics & dashboard synchronization
 */

require_once __DIR__ . '/../config/db.php';

class Medical {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Get statistics for Athlete Health Registry
     */
    public function getRegistryStats() {
        $sql = "
            SELECT 
                COUNT(*) AS total,
                COALESCE(SUM(CASE WHEN medical_status = 'Fit to Compete' THEN 1 ELSE 0 END), 0) AS fit,
                COALESCE(SUM(CASE WHEN medical_status IN ('Under Review', 'Temporarily Unfit') THEN 1 ELSE 0 END), 0) AS review
            FROM Athletic
        ";
        $stmt = $this->db->query($sql);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'total'  => (int) ($res['total'] ?? 0),
            'fit'    => (int) ($res['fit'] ?? 0),
            'review' => (int) ($res['review'] ?? 0)
        ];
    }

    /**
     * Get statistics for Clinical Dashboard
     */
    public function getDashboardStats() {
        $stats = [
            'total_monitored'    => 0,
            'active_injuries'    => 0,
            'pending_clearances' => 0
        ];

        // Total athletes
        $stmt = $this->db->query("SELECT COUNT(*) FROM Athletic");
        $stats['total_monitored'] = (int) $stmt->fetchColumn();

        // Active injuries
        $stmt = $this->db->query("SELECT COUNT(*) FROM injuries WHERE active_flag = 1");
        $stats['active_injuries'] = (int) $stmt->fetchColumn();

        // Pending clearances (athletes with active injury or under review)
        $stmt = $this->db->query("
            SELECT COUNT(DISTINCT a.athlete_id) 
            FROM Athletic a
            LEFT JOIN injuries i ON a.athlete_id = i.athletic_id AND i.active_flag = 1
            WHERE i.injury_id IS NOT NULL OR a.medical_status != 'Fit to Compete'
        ");
        $stats['pending_clearances'] = (int) $stmt->fetchColumn();

        return $stats;
    }

    /**
     * Get list of registered athletes with health metrics
     */
    public function getAthleteRegistry($search = '', $sportFilter = '') {
        $sql = "
            SELECT 
                a.athlete_id,
                a.user_id,
                a.index_number,
                a.full_name,
                a.academic_year,
                a.faculty,
                a.height,
                a.weight,
                a.emergency_Contact,
                COALESCE(a.blood_group, 'O+') AS blood_group,
                COALESCE(a.medical_status, 'Fit to Compete') AS medical_status,
                COALESCE(a.allergies, 'None') AS allergies,
                a.medical_notes,
                a.last_examined_date,
                COALESCE(
                    (SELECT GROUP_CONCAT(DISTINCT s.sport_name ORDER BY s.sport_name SEPARATOR ', ')
                     FROM match_squads ms
                     JOIN fixtures f ON ms.fixture_id = f.fixture_id
                     JOIN sports s ON f.sport_id = s.sport_id
                     WHERE ms.athlete_id = a.athlete_id),
                    'University Sports'
                ) AS sports_list
            FROM Athletic a
            WHERE 1=1
        ";

        $params = [];
        if (!empty($search)) {
            $sql .= " AND (a.full_name LIKE :s1 OR a.index_number LIKE :s2 OR a.faculty LIKE :s3)";
            $params[':s1'] = "%{$search}%";
            $params[':s2'] = "%{$search}%";
            $params[':s3'] = "%{$search}%";
        }

        $sql .= " ORDER BY a.full_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get complete medical profile for a single athlete
     */
    public function getAthleteMedicalProfile($athleteId) {
        $stmt = $this->db->prepare("
            SELECT 
                a.*,
                u.email,
                u.phone_number,
                COALESCE(
                    (SELECT GROUP_CONCAT(DISTINCT s.sport_name ORDER BY s.sport_name SEPARATOR ', ')
                     FROM match_squads ms
                     JOIN fixtures f ON ms.fixture_id = f.fixture_id
                     JOIN sports s ON f.sport_id = s.sport_id
                     WHERE ms.athlete_id = a.athlete_id),
                    'University Sports'
                ) AS sports_list
            FROM Athletic a
            LEFT JOIN user u ON a.user_id = u.user_id
            WHERE a.athlete_id = :aid
            LIMIT 1
        ");
        $stmt->execute([':aid' => $athleteId]);
        $athlete = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$athlete) {
            return null;
        }

        // Fetch injury history
        $injStmt = $this->db->prepare("
            SELECT i.*, s.sport_name 
            FROM injuries i 
            LEFT JOIN sports s ON i.sport_id = s.sport_id
            WHERE i.athletic_id = :aid 
            ORDER BY i.date_of_injury DESC
        ");
        $injStmt->execute([':aid' => $athleteId]);
        $athlete['injuries'] = $injStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch clearance history
        $clrStmt = $this->db->prepare("
            SELECT mc.*, s.sport_name 
            FROM medical_clearances mc 
            LEFT JOIN sports s ON mc.sport_id = s.sport_id
            WHERE mc.athletic_id = :aid 
            ORDER BY mc.cleared_date DESC
        ");
        $clrStmt->execute([':aid' => $athleteId]);
        $athlete['clearances'] = $clrStmt->fetchAll(PDO::FETCH_ASSOC);

        return $athlete;
    }

    /**
     * Save or update athlete health metrics
     */
    public function saveHealthRecord($data) {
        $athleteId     = (int) ($data['athlete_id'] ?? 0);
        $bloodGroup    = trim($data['blood_group'] ?? 'O+');
        $height        = (float) ($data['height'] ?? 0.0);
        $weight        = (float) ($data['weight'] ?? 0.0);
        $medicalStatus = trim($data['medical_status'] ?? 'Fit to Compete');
        $examDate      = !empty($data['last_examined_date']) ? $data['last_examined_date'] : date('Y-m-d');
        $allergies     = trim($data['allergies'] ?? 'None');
        $medicalNotes  = trim($data['medical_notes'] ?? '');
        $expiryDate    = !empty($data['clearance_expiry_date']) ? $data['clearance_expiry_date'] : date('Y-m-d', strtotime('+1 year'));

        if ($athleteId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE Athletic 
            SET blood_group        = :bg,
                height             = :height,
                weight             = :weight,
                medical_status     = :status,
                allergies          = :allergies,
                medical_notes      = :notes,
                last_examined_date = :examDate
            WHERE athlete_id = :aid
        ");
        $res = $stmt->execute([
            ':bg'        => $bloodGroup,
            ':height'    => $height,
            ':weight'    => $weight,
            ':status'    => $medicalStatus,
            ':allergies' => $allergies,
            ':notes'     => $medicalNotes,
            ':examDate'  => $examDate,
            ':aid'       => $athleteId
        ]);

        if (!$res) {
            return false;
        }

        // If marked Fit to Compete, resolve active injuries and issue active clearance record
        if ($medicalStatus === 'Fit to Compete') {
            // Resolve active injuries
            $closeInj = $this->db->prepare("UPDATE injuries SET active_flag = 0 WHERE athletic_id = :aid AND active_flag = 1");
            $closeInj->execute([':aid' => $athleteId]);

            // Add clearance entry
            $clrStmt = $this->db->prepare("
                INSERT INTO medical_clearances (athletic_id, sport_id, season_id, expiry_date, status, cleared_date, notes, certifying_doctor)
                VALUES (:aid, 1, 1, :expiry, 'Cleared', :cdate, :notes, 'Dr. A. Fernando')
            ");
            $clrStmt->execute([
                ':aid'    => $athleteId,
                ':expiry' => $expiryDate,
                ':cdate'  => $examDate,
                ':notes'  => !empty($medicalNotes) ? $medicalNotes : 'Full health assessment completed. Fit to compete.'
            ]);
        }

        return true;
    }

    /**
     * Get active injuries for the Clinical Dashboard (Pending Clearances tab)
     */
    public function getActiveInjuries($search = '') {
        $sql = "
            SELECT 
                i.injury_id,
                i.athletic_id,
                i.sport_id,
                i.injury_type,
                i.severity,
                i.date_of_injury,
                i.estimated_recovery_date,
                i.active_flag,
                i.notes,
                i.reported_by,
                a.full_name,
                a.index_number,
                a.faculty,
                COALESCE(s.sport_name, 'General Athletics') AS sport_name
            FROM injuries i
            JOIN Athletic a ON i.athletic_id = a.athlete_id
            LEFT JOIN sports s ON i.sport_id = s.sport_id
            WHERE i.active_flag = 1
        ";

        $params = [];
        if (!empty($search)) {
            $sql .= " AND (a.full_name LIKE :s1 OR a.index_number LIKE :s2 OR i.injury_type LIKE :s3 OR s.sport_name LIKE :s4)";
            $params[':s1'] = "%{$search}%";
            $params[':s2'] = "%{$search}%";
            $params[':s3'] = "%{$search}%";
            $params[':s4'] = "%{$search}%";
        }

        $sql .= " ORDER BY i.date_of_injury DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get clearance history for Clinical Dashboard (Clearance History tab)
     */
    public function getClearanceHistory($search = '', $limit = 50) {
        $sql = "
            SELECT 
                mc.clearance_id,
                mc.athletic_id,
                mc.sport_id,
                mc.season_id,
                mc.expiry_date,
                mc.status,
                mc.cleared_date,
                mc.notes,
                mc.certifying_doctor,
                a.full_name,
                a.index_number,
                COALESCE(s.sport_name, 'All Sports') AS sport_name
            FROM medical_clearances mc
            JOIN Athletic a ON mc.athletic_id = a.athlete_id
            LEFT JOIN sports s ON mc.sport_id = s.sport_id
            WHERE 1=1
        ";

        $params = [];
        if (!empty($search)) {
            $sql .= " AND (a.full_name LIKE :s1 OR a.index_number LIKE :s2 OR mc.notes LIKE :s3 OR s.sport_name LIKE :s4)";
            $params[':s1'] = "%{$search}%";
            $params[':s2'] = "%{$search}%";
            $params[':s3'] = "%{$search}%";
            $params[':s4'] = "%{$search}%";
        }

        $sql .= " ORDER BY COALESCE(mc.cleared_date, mc.expiry_date) DESC LIMIT " . (int)$limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Log a new injury
     */
    public function logInjury($data) {
        $athleteId       = (int) ($data['athlete_id'] ?? 0);
        $sportId         = (int) ($data['sport_id'] ?? 1);
        $injuryType      = trim($data['injury_type'] ?? '');
        $severity        = trim($data['severity'] ?? 'Moderate');
        $dateOfInjury    = !empty($data['date_of_injury']) ? $data['date_of_injury'] : date('Y-m-d');
        $recoveryDays    = max(1, (int) ($data['recovery_days'] ?? 14));
        $notes           = trim($data['notes'] ?? '');
        $reportedBy      = trim($data['reported_by'] ?? 'Dr. A. Fernando');
        
        $estRecoveryDate = date('Y-m-d', strtotime($dateOfInjury . " + {$recoveryDays} days"));

        if ($athleteId <= 0 || empty($injuryType)) {
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO injuries (
                athletic_id, 
                sport_id, 
                injury_type, 
                severity, 
                date_of_injury, 
                estimated_recovery_date, 
                active_flag, 
                notes, 
                reported_by
            ) VALUES (
                :aid, 
                :sid, 
                :type, 
                :severity, 
                :doi, 
                :est_rec, 
                1, 
                :notes, 
                :rep_by
            )
        ");
        $res = $stmt->execute([
            ':aid'     => $athleteId,
            ':sid'     => $sportId,
            ':type'    => $injuryType,
            ':severity'=> $severity,
            ':doi'     => $dateOfInjury,
            ':est_rec' => $estRecoveryDate,
            ':notes'   => $notes,
            ':rep_by'  => $reportedBy
        ]);

        if (!$res) {
            return false;
        }

        // Update athlete medical status to Under Review
        $upd = $this->db->prepare("
            UPDATE Athletic 
            SET medical_status = 'Under Review',
                last_examined_date = :examDate
            WHERE athlete_id = :aid
        ");
        $upd->execute([
            ':examDate' => $dateOfInjury,
            ':aid'      => $athleteId
        ]);

        return true;
    }

    /**
     * Issue medical clearance for an athlete
     */
    public function issueClearance($data) {
        $injuryId         = (int) ($data['injury_id'] ?? 0);
        $athleteId        = (int) ($data['athlete_id'] ?? 0);
        $sportId          = (int) ($data['sport_id'] ?? 1);
        $decision         = trim($data['decision'] ?? 'Full Clearance');
        $clearanceDate    = !empty($data['clearance_date']) ? $data['clearance_date'] : date('Y-m-d');
        $notes            = trim($data['notes'] ?? '');
        $certifyingDoctor = trim($data['certifying_doctor'] ?? 'Dr. A. Fernando');
        $expiryDate       = date('Y-m-d', strtotime($clearanceDate . ' + 1 year'));

        if ($athleteId <= 0) {
            return false;
        }

        $status = 'Cleared';
        $athleteMedicalStatus = 'Fit to Compete';

        if (stripos($decision, 'Not Cleared') !== false || stripos($decision, 'Extend') !== false) {
            $status = 'Blocked';
            $athleteMedicalStatus = 'Temporarily Unfit';
        } elseif (stripos($decision, 'Conditional') !== false) {
            $status = 'Cleared';
            $athleteMedicalStatus = 'Fit to Compete';
        }

        // Insert clearance log
        $stmt = $this->db->prepare("
            INSERT INTO medical_clearances (
                athletic_id, 
                sport_id, 
                season_id, 
                expiry_date, 
                status, 
                cleared_date, 
                notes, 
                certifying_doctor
            ) VALUES (
                :aid, 
                :sid, 
                1, 
                :expiry, 
                :status, 
                :cdate, 
                :notes, 
                :doc
            )
        ");
        $res = $stmt->execute([
            ':aid'    => $athleteId,
            ':sid'    => $sportId,
            ':expiry' => $expiryDate,
            ':status' => $status,
            ':cdate'  => $clearanceDate,
            ':notes'  => "Decision: {$decision}. " . $notes,
            ':doc'    => $certifyingDoctor
        ]);

        if (!$res) {
            return false;
        }

        // If clearance is granted (Full or Conditional), deactivate related injury
        if ($status === 'Cleared') {
            if ($injuryId > 0) {
                $closeStmt = $this->db->prepare("UPDATE injuries SET active_flag = 0 WHERE injury_id = :iid");
                $closeStmt->execute([':iid' => $injuryId]);
            } else {
                $closeStmt = $this->db->prepare("UPDATE injuries SET active_flag = 0 WHERE athletic_id = :aid AND active_flag = 1");
                $closeStmt->execute([':aid' => $athleteId]);
            }
        }

        // Update athlete's overall medical status
        $upd = $this->db->prepare("
            UPDATE Athletic 
            SET medical_status = :status,
                last_examined_date = :cdate
            WHERE athlete_id = :aid
        ");
        $upd->execute([
            ':status' => $athleteMedicalStatus,
            ':cdate'  => $clearanceDate,
            ':aid'    => $athleteId
        ]);

        return true;
    }

    /**
     * Get all active sports for dropdowns
     */
    public function getAllSports() {
        $stmt = $this->db->query("SELECT sport_id, sport_name FROM sports ORDER BY sport_name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search registered athletes (for searchable selects & autocomplete)
     */
    public function searchAthletes($query = '', $limit = 25) {
        $sql = "
            SELECT 
                a.athlete_id,
                a.full_name,
                a.index_number,
                a.faculty,
                a.emergency_Contact,
                COALESCE(a.blood_group, 'O+') AS blood_group,
                a.height,
                a.weight,
                COALESCE(a.medical_status, 'Fit to Compete') AS medical_status,
                COALESCE(a.allergies, 'None') AS allergies,
                a.medical_notes,
                COALESCE(
                    (SELECT GROUP_CONCAT(DISTINCT s.sport_name ORDER BY s.sport_name SEPARATOR ', ')
                     FROM match_squads ms
                     JOIN fixtures f ON ms.fixture_id = f.fixture_id
                     JOIN sports s ON f.sport_id = s.sport_id
                     WHERE ms.athlete_id = a.athlete_id),
                    'University Sports'
                ) AS sports_list
            FROM Athletic a
            WHERE 1=1
        ";
        $params = [];
        if (!empty($query)) {
            $sql .= " AND (a.full_name LIKE :q1 OR a.index_number LIKE :q2 OR a.faculty LIKE :q3)";
            $params[':q1'] = "%{$query}%";
            $params[':q2'] = "%{$query}%";
            $params[':q3'] = "%{$query}%";
        }
        $sql .= " ORDER BY a.full_name ASC LIMIT " . (int)$limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete a single clearance record by ID
     */
    public function deleteClearance($clearanceId) {
        $stmt = $this->db->prepare("DELETE FROM medical_clearances WHERE clearance_id = :id");
        return $stmt->execute([':id' => (int)$clearanceId]);
    }

    /**
     * Clear all clearance history records
     */
    public function clearClearanceHistory() {
        $stmt = $this->db->prepare("DELETE FROM medical_clearances");
        return $stmt->execute();
    }
}
?>
