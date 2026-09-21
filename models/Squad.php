<?php
/**
 * Squad Model
 * Handles squad approvals, match fixtures, and team rosters for TIC
 */

require_once __DIR__ . '/../config/db.php';

class Squad {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Get pending squad approval requests
     */
    public function getPendingApprovals() {
        $sql = "
            SELECT 
                ms.squad_id,
                ms.fixture_id,
                ms.tic_approval_status,
                f.opponent,
                f.fixture_type,
                f.home_team,
                f.fixture_date,
                f.fixture_time,
                s.sport_name,
                fac.facility_name,
                a.full_name AS athlete_name,
                a.index_number,
                a.faculty,
                COUNT(ms.athlete_id) OVER(PARTITION BY ms.fixture_id) AS squad_size
            FROM match_squads ms
            JOIN fixtures f ON ms.fixture_id = f.fixture_id
            JOIN sports s ON f.sport_id = s.sport_id
            JOIN facilities fac ON f.facility_id = fac.facility_id
            JOIN Athletic a ON ms.athlete_id = a.athlete_id
            WHERE ms.tic_approval_status = 'Pending'
            ORDER BY f.fixture_date ASC, ms.squad_id ASC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get upcoming match fixtures with squad summary
     */
    public function getUpcomingMatches() {
        $sql = "
            SELECT 
                f.fixture_id,
                f.opponent,
                f.fixture_type,
                f.home_team,
                f.fixture_date,
                f.fixture_time,
                s.sport_name,
                fac.facility_name,
                (SELECT COUNT(*) FROM match_squads ms WHERE ms.fixture_id = f.fixture_id) AS total_players,
                (SELECT COUNT(*) FROM match_squads ms WHERE ms.fixture_id = f.fixture_id AND ms.tic_approval_status = 'Approved') AS approved_players,
                (SELECT COUNT(*) FROM match_squads ms WHERE ms.fixture_id = f.fixture_id AND ms.tic_approval_status = 'Pending') AS pending_players
            FROM fixtures f
            JOIN sports s ON f.sport_id = s.sport_id
            JOIN facilities fac ON f.facility_id = fac.facility_id
            WHERE f.fixture_date >= CURDATE()
            ORDER BY f.fixture_date ASC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get pending squad groups by match fixture
     */
    public function getPendingSquadGroups() {
        $sql = "
            SELECT 
                f.fixture_id,
                f.opponent,
                f.fixture_type,
                f.home_team,
                f.fixture_date,
                f.fixture_time,
                s.sport_name,
                fac.facility_name,
                COUNT(ms.athlete_id) AS total_players,
                SUM(CASE WHEN ms.tic_approval_status = 'Pending' THEN 1 ELSE 0 END) AS pending_players,
                SUM(CASE WHEN ms.tic_approval_status = 'Approved' THEN 1 ELSE 0 END) AS approved_players
            FROM fixtures f
            JOIN sports s ON f.sport_id = s.sport_id
            JOIN facilities fac ON f.facility_id = fac.facility_id
            JOIN match_squads ms ON f.fixture_id = ms.fixture_id
            GROUP BY f.fixture_id, f.opponent, f.fixture_type, f.home_team, f.fixture_date, f.fixture_time, s.sport_name, fac.facility_name
            HAVING pending_players > 0
            ORDER BY f.fixture_date ASC, f.fixture_time ASC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get roster of athletes nominated for a specific fixture
     */
    public function getFixtureRoster($fixture_id) {
        $sql = "
            SELECT 
                ms.squad_id,
                ms.athlete_id,
                ms.tic_approval_status,
                a.full_name AS athlete_name,
                a.index_number,
                a.faculty
            FROM match_squads ms
            JOIN Athletic a ON ms.athlete_id = a.athlete_id
            WHERE ms.fixture_id = :fid
            ORDER BY a.full_name ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':fid' => $fixture_id]);
        return $stmt->fetchAll();
    }

    /**
     * Approve squad entry
     */
    public function approveSquad($squad_id) {
        $stmt = $this->db->prepare("UPDATE match_squads SET tic_approval_status = 'Approved' WHERE squad_id = :id");
        return $stmt->execute([':id' => $squad_id]);
    }

    /**
     * Approve all pending players for a fixture (Team Squad Approval)
     */
    public function approveFixtureSquad($fixture_id) {
        $stmt = $this->db->prepare("UPDATE match_squads SET tic_approval_status = 'Approved' WHERE fixture_id = :fid AND tic_approval_status = 'Pending'");
        return $stmt->execute([':fid' => $fixture_id]);
    }

    /**
     * Reject all pending players for a fixture (Team Squad Rejection)
     */
    public function rejectFixtureSquad($fixture_id) {
        $stmt = $this->db->prepare("UPDATE match_squads SET tic_approval_status = 'Rejected' WHERE fixture_id = :fid AND tic_approval_status = 'Pending'");
        return $stmt->execute([':fid' => $fixture_id]);
    }

    /**
     * Nominate multiple athletes to a fixture squad
     */
    public function nominateSquad($fixture_id, array $athlete_ids) {
        $stmt = $this->db->prepare("
            INSERT INTO match_squads (fixture_id, athlete_id, tic_approval_status)
            VALUES (:fixture_id, :athlete_id, 'Pending')
            ON DUPLICATE KEY UPDATE tic_approval_status = 'Pending'
        ");
        foreach ($athlete_ids as $aid) {
            $aid = (int) $aid;
            if ($aid > 0) {
                $stmt->execute([':fixture_id' => $fixture_id, ':athlete_id' => $aid]);
            }
        }
        return true;
    }

    /**
     * Reject single squad entry
     */
    public function rejectSquad($squad_id) {
        $stmt = $this->db->prepare("UPDATE match_squads SET tic_approval_status = 'Rejected' WHERE squad_id = :id");
        return $stmt->execute([':id' => $squad_id]);
    }

    /**
     * Add new fixture
     */
    public function createFixture($sport_id, $facility_id, $opponent, $fixture_type, $fixture_date, $fixture_time, $home_team = 'University of Colombo') {
        $stmt = $this->db->prepare("
            INSERT INTO fixtures (sport_id, facility_id, opponent, fixture_type, home_team, fixture_date, fixture_time)
            VALUES (:sport_id, :facility_id, :opponent, :fixture_type, :home_team, :fixture_date, :fixture_time)
        ");
        return $stmt->execute([
            ':sport_id'     => $sport_id,
            ':facility_id'  => $facility_id,
            ':opponent'     => $opponent,
            ':fixture_type' => $fixture_type,
            ':home_team'    => $home_team,
            ':fixture_date' => $fixture_date,
            ':fixture_time' => $fixture_time
        ]);
    }

    /**
     * Add athlete to a match squad
     */
    public function addAthleteToSquad($fixture_id, $athlete_id, $status = 'Pending') {
        $stmt = $this->db->prepare("
            INSERT INTO match_squads (fixture_id, athlete_id, tic_approval_status)
            VALUES (:fixture_id, :athlete_id, :status)
        ");
        return $stmt->execute([
            ':fixture_id' => $fixture_id,
            ':athlete_id' => $athlete_id,
            ':status'     => $status
        ]);
    }

    /**
     * Delete / Cancel a match fixture and its squad
     */
    public function deleteFixture($fixture_id) {
        $this->db->beginTransaction();
        try {
            $stmt1 = $this->db->prepare("DELETE FROM match_squads WHERE fixture_id = :id");
            $stmt1->execute([':id' => $fixture_id]);

            $stmt2 = $this->db->prepare("DELETE FROM fixtures WHERE fixture_id = :id");
            $stmt2->execute([':id' => $fixture_id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Get athletes with optional sport prioritization
     */
    public function getAthletesForSport($sport_id = null) {
        if (!empty($sport_id)) {
            $sql = "
                SELECT DISTINCT a.athlete_id, a.full_name, a.index_number, a.faculty,
                       (CASE WHEN sa.sport_id IS NOT NULL THEN 1 ELSE 0 END) AS is_registered_for_sport
                FROM Athletic a
                LEFT JOIN sport_application sa ON a.athlete_id = sa.athlete_id AND sa.sport_id = :sid
                ORDER BY is_registered_for_sport DESC, a.full_name ASC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':sid' => $sport_id]);
            return $stmt->fetchAll();
        }

        $stmt = $this->db->query("SELECT athlete_id, full_name, index_number, faculty, 1 AS is_registered_for_sport FROM Athletic ORDER BY full_name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Get squad stats
     */
    public function getStats() {
        $totalPending = (int) $this->db->query("SELECT COUNT(*) FROM match_squads WHERE tic_approval_status = 'Pending'")->fetchColumn();
        $totalMatches = (int) $this->db->query("SELECT COUNT(*) FROM fixtures WHERE fixture_date >= CURDATE()")->fetchColumn();

        return [
            'total_pending' => $totalPending,
            'total_matches' => $totalMatches
        ];
    }
}
?>
