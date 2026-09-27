<?php
/**
 * Sport Application Model
 * Handles sports applications for athletes, sports listing, and season retrieval.
 */

require_once __DIR__ . '/../config/db.php';

class SportApplication {
    private $db;

    public function __construct($db = null) {
        $this->db = $db ?: Database::connect();
    }

    /**
     * Retrieve all sports from sports table
     * @return array
     */
    public function getAllSports() {
        $stmt = $this->db->query("SELECT sport_id, sport_name FROM sports ORDER BY sport_name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Retrieve all seasons from seasons table
     * @return array
     */
    public function getAllSeasons() {
        $stmt = $this->db->query("SELECT season_id, season_year, academic_year, status FROM seasons ORDER BY status ASC, season_year DESC, season_id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Get single sport by ID
     * @param int $sportId
     * @return array|false
     */
    public function getSportById($sportId) {
        $stmt = $this->db->prepare("SELECT sport_id, sport_name FROM sports WHERE sport_id = ?");
        $stmt->execute([(int)$sportId]);
        return $stmt->fetch();
    }

    /**
     * Get single season by ID
     * @param int $seasonId
     * @return array|false
     */
    public function getSeasonById($seasonId) {
        $stmt = $this->db->prepare("SELECT season_id, season_year, academic_year, status FROM seasons WHERE season_id = ?");
        $stmt->execute([(int)$seasonId]);
        return $stmt->fetch();
    }

    /**
     * Submit application for an athlete
     * 
     * @param int $athleticId
     * @param int $sportId
     * @param int $seasonId
     * @param string|null $statementOfInterest
     * @return array
     * @throws Exception
     */
    public function applyForSport($athleticId, $sportId, $seasonId, $statementOfInterest = null) {
        $athleticId = (int)$athleticId;
        $sportId = (int)$sportId;
        $seasonId = (int)$seasonId;

        if ($athleticId <= 0) {
            throw new Exception("Invalid athlete ID.");
        }
        if ($sportId <= 0) {
            throw new Exception("Please select a valid sport.");
        }
        if ($seasonId <= 0) {
            throw new Exception("Please select a valid season.");
        }

        // Verify athlete exists
        $stmtAthlete = $this->db->prepare("SELECT athlete_id, full_name FROM Athletic WHERE athlete_id = ?");
        $stmtAthlete->execute([$athleticId]);
        $athlete = $stmtAthlete->fetch();
        if (!$athlete) {
            throw new Exception("Athlete profile not found.");
        }

        // Verify sport exists
        $sport = $this->getSportById($sportId);
        if (!$sport) {
            throw new Exception("Selected sport does not exist.");
        }

        // Verify season exists
        $season = $this->getSeasonById($seasonId);
        if (!$season) {
            throw new Exception("Selected season does not exist.");
        }

        // Check if an active/pending application already exists for this sport & season
        $stmtCheck = $this->db->prepare("
            SELECT application_id, status 
            FROM sport_application 
            WHERE athletic_id = ? AND sport_id = ? AND season_id = ? AND status IN ('Pending', 'Shortlisted', 'Approved')
        ");
        $stmtCheck->execute([$athleticId, $sportId, $seasonId]);
        $existing = $stmtCheck->fetch();

        if ($existing) {
            throw new Exception("You already have an active application (" . htmlspecialchars($existing['status']) . ") for " . htmlspecialchars($sport['sport_name']) . " in this season.");
        }

        // Insert new application
        $sql = "INSERT INTO sport_application (athletic_id, sport_id, season_id, status, rejection_reason, statement_of_interest, applied_date)
                VALUES (?, ?, ?, 'Pending', NULL, ?, NOW())";
        $stmtInsert = $this->db->prepare($sql);
        $stmtInsert->execute([$athleticId, $sportId, $seasonId, $statementOfInterest]);

        $appId = (int)$this->db->lastInsertId();
        $refNumber = 'APP-' . date('Y') . '-' . str_pad($appId, 4, '0', STR_PAD_LEFT);

        return [
            'application_id' => $appId,
            'reference_number' => $refNumber,
            'sport_id' => $sportId,
            'sport_name' => $sport['sport_name'],
            'season_id' => $seasonId,
            'season_year' => $season['season_year'],
            'status' => 'Pending',
            'applied_date' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Retrieve all applications submitted by a specific athlete
     * 
     * @param int $athleticId
     * @return array
     */
    public function getAthleteApplications($athleticId) {
        $sql = "
            SELECT 
                sa.application_id,
                sa.athletic_id,
                sa.sport_id,
                sa.season_id,
                sa.status,
                sa.rejection_reason,
                sa.statement_of_interest,
                sa.applied_date,
                s.sport_name,
                se.season_year,
                se.academic_year
            FROM sport_application sa
            JOIN sports s ON sa.sport_id = s.sport_id
            JOIN seasons se ON sa.season_id = se.season_id
            WHERE sa.athletic_id = ?
            ORDER BY sa.applied_date DESC, sa.application_id DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$athleticId]);
        return $stmt->fetchAll();
    }

    /**
     * Delete only an application owned by the authenticated athlete.
     */
    public function deleteAthleteApplication($applicationId, $athleticId) {
        $stmt = $this->db->prepare(
            "DELETE FROM sport_application WHERE application_id = ? AND athletic_id = ?"
        );
        $stmt->execute([(int)$applicationId, (int)$athleticId]);
        return $stmt->rowCount() === 1;
    }

    /**
     * Get application status counts for an athlete
     * 
     * @param int $athleticId
     * @return array
     */
    public function getApplicationStats($athleticId) {
        $sql = "
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN status = 'Shortlisted' THEN 1 ELSE 0 END) AS shortlisted_count,
                SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) AS approved_count,
                SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS rejected_count
            FROM sport_application
            WHERE athletic_id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$athleticId]);
        $row = $stmt->fetch();

        return [
            'total' => (int)($row['total'] ?? 0),
            'under_review' => (int)($row['pending_count'] ?? 0) + (int)($row['shortlisted_count'] ?? 0),
            'action_required' => (int)($row['rejected_count'] ?? 0),
            'accepted' => (int)($row['approved_count'] ?? 0),
        ];
    }
}
