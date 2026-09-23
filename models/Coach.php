<?php
/**
 * Coach Model
 * USMS - University Sports Management System
 * Handles training sessions, attendance sheets, squad nominations,
 * fixtures, match results, and team equipment requisitions.
 */

require_once __DIR__ . '/../config/db.php';

class Coach {
    private $db;

    public function __construct($db = null) {
        $this->db = $db ?: Database::connect();
    }

    /**
     * Get high-level statistics for Coach Dashboard
     */
    public function getStats($coachId = null) {
        // Total & upcoming training sessions
        $stmtTotal = $this->db->query("SELECT COUNT(*) FROM training_sessions");
        $totalSessions = (int)$stmtTotal->fetchColumn();

        $stmtUpcoming = $this->db->query("SELECT COUNT(*) FROM training_sessions WHERE session_date >= CURDATE()");
        $upcomingSessions = (int)$stmtUpcoming->fetchColumn();

        // Average attendance percentage from real attendance records
        $stmtAtt = $this->db->query("
            SELECT 
                COUNT(*) as total_logs,
                SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_logs
            FROM training_attendance
        ");
        $attData = $stmtAtt->fetch();
        $avgAttendance = 'N/A';
        if ($attData && $attData['total_logs'] > 0) {
            $avgAttendance = round(($attData['present_logs'] / $attData['total_logs']) * 100) . '%';
        }

        // Medically inactive / restricted athletes
        $stmtMed = $this->db->query("SELECT COUNT(*) FROM Athletic WHERE medical_status IS NOT NULL AND medical_status != 'Fit to Compete'");
        $medInactive = (int)$stmtMed->fetchColumn();

        // Match stats from actual fixtures and results
        $stmtWon = $this->db->query("
            SELECT COUNT(*) FROM match_results mr 
            WHERE CAST(SUBSTRING_INDEX(mr.scores, '-', 1) AS UNSIGNED) > CAST(SUBSTRING_INDEX(mr.scores, '-', -1) AS UNSIGNED)
        ");
        $won = (int)$stmtWon->fetchColumn();

        $stmtDrawn = $this->db->query("
            SELECT COUNT(*) FROM match_results mr 
            WHERE CAST(SUBSTRING_INDEX(mr.scores, '-', 1) AS UNSIGNED) = CAST(SUBSTRING_INDEX(mr.scores, '-', -1) AS UNSIGNED)
              AND mr.scores IS NOT NULL AND mr.scores != ''
        ");
        $drawn = (int)$stmtDrawn->fetchColumn();

        $stmtLost = $this->db->query("
            SELECT COUNT(*) FROM match_results mr 
            WHERE CAST(SUBSTRING_INDEX(mr.scores, '-', 1) AS UNSIGNED) < CAST(SUBSTRING_INDEX(mr.scores, '-', -1) AS UNSIGNED)
        ");
        $lost = (int)$stmtLost->fetchColumn();

        $stmtMatches = $this->db->query("SELECT COUNT(*) FROM fixtures");
        $totalMatches = (int)$stmtMatches->fetchColumn();

        return [
            'total_sessions'     => $totalSessions,
            'upcoming_sessions'  => $upcomingSessions,
            'avg_attendance'     => $avgAttendance,
            'medically_inactive' => $medInactive,
            'total_matches'      => $totalMatches,
            'won'                => $won,
            'drawn'              => $drawn,
            'lost'               => $lost
        ];
    }

    /**
     * Get single training session by ID
     */
    public function getTrainingSessionById($sessionId) {
        $stmt = $this->db->prepare("
            SELECT 
                ts.session_id,
                ts.sport_id,
                ts.facility_id,
                ts.session_date,
                ts.start_time,
                ts.duration_minutes,
                ts.session_type,
                ts.focus_area,
                ts.notes,
                s.sport_name,
                COALESCE(fac.facility_name, 'Main University Ground') as facility_name
            FROM training_sessions ts
            JOIN sports s ON ts.sport_id = s.sport_id
            LEFT JOIN facilities fac ON ts.facility_id = fac.facility_id
            WHERE ts.session_id = ?
        ");
        $stmt->execute([(int)$sessionId]);
        return $stmt->fetch();
    }

    /**
     * Get training sessions with attendance stats
     */
    public function getTrainingSessions($sportId = null) {
        $sql = "
            SELECT 
                ts.session_id,
                ts.sport_id,
                ts.facility_id,
                ts.session_date,
                ts.start_time,
                ts.duration_minutes,
                ts.session_type,
                ts.focus_area,
                ts.notes,
                s.sport_name,
                COALESCE(fac.facility_name, 'Main University Ground') as facility_name
            FROM training_sessions ts
            JOIN sports s ON ts.sport_id = s.sport_id
            LEFT JOIN facilities fac ON ts.facility_id = fac.facility_id
        ";
        $params = [];
        if ($sportId) {
            $sql .= " WHERE ts.sport_id = :sid";
            $params[':sid'] = $sportId;
        }
        $sql .= " ORDER BY ts.session_date DESC, ts.start_time DESC LIMIT 30";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $sessions = $stmt->fetchAll();

        $result = [];
        $stmtAttCount = $this->db->prepare("
            SELECT 
                COUNT(*) as total_athletes,
                SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_count
            FROM training_attendance 
            WHERE session_id = ?
        ");

        foreach ($sessions as $s) {
            $stmtAttCount->execute([$s['session_id']]);
            $att = $stmtAttCount->fetch();
            $totalAtt = (int)($att['total_athletes'] ?? 0);
            $presAtt = (int)($att['present_count'] ?? 0);

            $pct = $totalAtt > 0 ? round(($presAtt / $totalAtt) * 100) : 0;
            $ratioStr = $totalAtt > 0 ? "$presAtt / $totalAtt" : "Pending Roster";

            $isPast = (strtotime($s['session_date'] . ' ' . $s['start_time']) < time());
            $status = $isPast ? 'Completed' : 'Scheduled';

            $dateFormatted = date('M d, Y • h:i A', strtotime($s['session_date'] . ' ' . $s['start_time']));
            if ($s['session_date'] === date('Y-m-d')) {
                $dateFormatted = 'Today • ' . date('h:i A', strtotime($s['start_time']));
            } elseif ($s['session_date'] === date('Y-m-d', strtotime('-1 day'))) {
                $dateFormatted = 'Yesterday • ' . date('h:i A', strtotime($s['start_time']));
            }

            $result[] = [
                'id'                 => 'TRN-' . str_pad($s['session_id'], 3, '0', STR_PAD_LEFT),
                'session_id'         => $s['session_id'],
                'sport'              => $s['sport_name'],
                'sport_id'           => $s['sport_id'],
                'facility_id'        => $s['facility_id'],
                'venue'              => $s['facility_name'],
                'session_date'       => $s['session_date'],
                'start_time'         => substr($s['start_time'], 0, 5),
                'duration_minutes'   => $s['duration_minutes'],
                'datetime'           => $s['session_date'] . ' ' . substr($s['start_time'], 0, 5),
                'display_date'       => $dateFormatted,
                'session_type'       => $s['session_type'],
                'focus_area'         => $s['focus_area'] ?: 'General Drills & Conditioning',
                'notes'              => $s['notes'] ?: '',
                'attendance_present' => $presAtt,
                'attendance_total'   => $totalAtt,
                'attendance_ratio'   => $ratioStr,
                'attendance_pct'     => $pct,
                'status'             => $status
            ];
        }

        return $result;
    }

    /**
     * Update an existing training session
     */
    public function updateTrainingSession($sessionId, $sportId, $facilityId, $sessionDate, $startTime, $durationTime, $sessionType, $focusArea, $notes) {
        $stmt = $this->db->prepare("
            UPDATE training_sessions 
            SET sport_id = ?, 
                facility_id = ?, 
                session_date = ?, 
                start_time = ?, 
                duration_minutes = ?, 
                session_type = ?, 
                focus_area = ?, 
                notes = ?
            WHERE session_id = ?
        ");
        return $stmt->execute([
            $sportId,
            $facilityId ?: null,
            $sessionDate,
            $startTime,
            $durationTime ?: '01:30:00',
            $sessionType ?: 'Practice',
            $focusArea ?: 'Tactical Drills',
            $notes ?: '',
            (int)$sessionId
        ]);
    }

    /**
     * Delete / Cancel a training session and clean up attendance cascade
     */
    public function deleteTrainingSession($sessionId) {
        $this->db->beginTransaction();
        try {
            $stmtAtt = $this->db->prepare("DELETE FROM training_attendance WHERE session_id = ?");
            $stmtAtt->execute([(int)$sessionId]);

            $stmt = $this->db->prepare("DELETE FROM training_sessions WHERE session_id = ?");
            $stmt->execute([(int)$sessionId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Create a new training session and auto-populate squad athletes for attendance
     */
    public function createTrainingSession($sportId, $facilityId, $sessionDate, $startTime, $durationTime, $sessionType, $focusArea, $notes) {
        $stmt = $this->db->prepare("
            INSERT INTO training_sessions 
            (sport_id, facility_id, session_date, start_time, duration_minutes, session_type, focus_area, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $sportId,
            $facilityId ?: null,
            $sessionDate,
            $startTime,
            $durationTime ?: '01:30:00',
            $sessionType ?: 'Practice',
            $focusArea ?: 'Tactical Drills',
            $notes ?: ''
        ]);
        $sessionId = (int)$this->db->lastInsertId();

        // Auto-seed initial attendance sheet with athletes registered for this sport
        $stmtAthletes = $this->db->prepare("
            SELECT DISTINCT a.athlete_id 
            FROM Athletic a 
            LEFT JOIN sport_application sa ON a.athlete_id = sa.athletic_id 
            WHERE sa.sport_id = ? OR a.athlete_id IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10)
            LIMIT 24
        ");
        $stmtAthletes->execute([$sportId]);
        $athletes = $stmtAthletes->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($athletes)) {
            $stmtAtt = $this->db->prepare("
                INSERT INTO training_attendance (session_id, athlete_id, status)
                VALUES (?, ?, 'Present')
            ");
            foreach ($athletes as $athId) {
                $stmtAtt->execute([$sessionId, $athId]);
            }
        }

        return $sessionId;
    }

    /**
     * Get attendance roster for a specific session
     */
    public function getSessionAttendance($sessionId) {
        $stmt = $this->db->prepare("
            SELECT 
                ta.attendance_id,
                ta.session_id,
                ta.status,
                a.athlete_id,
                a.full_name,
                a.index_number,
                a.faculty,
                a.medical_status
            FROM training_attendance ta
            JOIN Athletic a ON ta.athlete_id = a.athlete_id
            WHERE ta.session_id = ?
            ORDER BY a.full_name ASC
        ");
        $stmt->execute([$sessionId]);
        $records = $stmt->fetchAll();

        // If no records yet, populate with first 15 athletes
        if (empty($records)) {
            $athletes = $this->db->query("SELECT athlete_id, full_name, index_number, faculty, medical_status FROM Athletic LIMIT 15")->fetchAll();
            $stmtInsert = $this->db->prepare("INSERT INTO training_attendance (session_id, athlete_id, status) VALUES (?, ?, 'Present')");
            foreach ($athletes as $a) {
                $stmtInsert->execute([$sessionId, $a['athlete_id']]);
            }
            return $this->getSessionAttendance($sessionId);
        }

        return $records;
    }

    /**
     * Save/update bulk attendance sheet
     */
    public function saveAttendance($sessionId, array $attendanceMap) {
        $stmtCheck = $this->db->prepare("SELECT attendance_id FROM training_attendance WHERE session_id = ? AND athlete_id = ?");
        $stmtUpdate = $this->db->prepare("UPDATE training_attendance SET status = ? WHERE attendance_id = ?");
        $stmtInsert = $this->db->prepare("INSERT INTO training_attendance (session_id, athlete_id, status) VALUES (?, ?, ?)");

        foreach ($attendanceMap as $athId => $status) {
            $status = in_array($status, ['Present', 'Absent', 'Excused']) ? $status : 'Present';
            $stmtCheck->execute([$sessionId, $athId]);
            $existingId = $stmtCheck->fetchColumn();

            if ($existingId) {
                $stmtUpdate->execute([$status, $existingId]);
            } else {
                $stmtInsert->execute([$sessionId, $athId, $status]);
            }
        }

        return true;
    }

    /**
     * Get fixtures for Coach with squad & result status
     */
    public function getFixturesWithSquadStatus($sportId = null) {
        $sql = "
            SELECT 
                f.fixture_id,
                f.sport_id,
                f.opponent,
                f.home_team,
                f.fixture_type,
                f.fixture_date,
                f.fixture_time,
                s.sport_name,
                COALESCE(fac.facility_name, 'Main University Ground') as venue,
                mr.scores,
                mr.scorers,
                mr.match_report
            FROM fixtures f
            JOIN sports s ON f.sport_id = s.sport_id
            LEFT JOIN facilities fac ON f.facility_id = fac.facility_id
            LEFT JOIN match_results mr ON f.fixture_id = mr.fixture_id
        ";
        $params = [];
        if ($sportId) {
            $sql .= " WHERE f.sport_id = :sid";
            $params[':sid'] = $sportId;
        }
        $sql .= " ORDER BY f.fixture_date DESC, f.fixture_time DESC LIMIT 20";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $fixtures = $stmt->fetchAll();

        $stmtSquadStatus = $this->db->prepare("
            SELECT 
                COUNT(*) as squad_count,
                tic_approval_status
            FROM match_squads 
            WHERE fixture_id = ? 
            GROUP BY tic_approval_status
            LIMIT 1
        ");

        $result = [];
        foreach ($fixtures as $f) {
            $stmtSquadStatus->execute([$f['fixture_id']]);
            $sq = $stmtSquadStatus->fetch();

            $squadStatus = 'Draft in Progress';
            $squadCount = (int)($sq['squad_count'] ?? 0);
            if ($squadCount > 0) {
                $squadStatus = ($sq['tic_approval_status'] === 'Approved') ? 'Approved by TIC' : 'Pending TIC Review';
            }

            $isPast = (strtotime($f['fixture_date'] . ' ' . $f['fixture_time']) < time());
            $displayDate = date('M d, Y • h:i A', strtotime($f['fixture_date'] . ' ' . $f['fixture_time']));

            $result[] = [
                'fixture_id'   => $f['fixture_id'],
                'id'           => 'FIX-' . str_pad($f['fixture_id'], 3, '0', STR_PAD_LEFT),
                'title'        => $f['home_team'] . ' vs ' . $f['opponent'],
                'opponent'     => $f['opponent'],
                'sport'        => $f['sport_name'],
                'venue'        => $f['venue'],
                'date'         => $f['fixture_date'] . ' ' . substr($f['fixture_time'], 0, 5),
                'display_date' => $displayDate,
                'status'       => $isPast ? ($f['scores'] ? 'Completed' : 'Concluded') : 'Upcoming',
                'score'        => $f['scores'] ?: '- vs -',
                'scorers'      => $f['scorers'] ?: '',
                'remarks'      => $f['match_report'] ?: 'Official inter-university fixture.',
                'squad_status' => $squadStatus,
                'squad_count'  => $squadCount
            ];
        }

        return $result;
    }

    /**
     * Get available athletes for squad formulation with live medical fitness
     */
    public function getAvailableAthletesForSport($sportId = 1) {
        $stmt = $this->db->prepare("
            SELECT 
                a.athlete_id,
                a.full_name,
                a.index_number,
                a.faculty,
                COALESCE(a.medical_status, 'Fit to Compete') as med_status,
                COALESCE(a.medical_notes, '') as med_note,
                COALESCE(a.blood_group, 'O+') as blood_group
            FROM Athletic a
            LEFT JOIN sport_application sa ON a.athlete_id = sa.athletic_id AND sa.sport_id = ?
            ORDER BY a.full_name ASC
            LIMIT 40
        ");
        $stmt->execute([$sportId]);
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $r) {
            $isFit = ($r['med_status'] === 'Fit to Compete');
            $result[] = [
                'id'         => 'ATH-' . str_pad($r['athlete_id'], 3, '0', STR_PAD_LEFT),
                'athlete_id' => $r['athlete_id'],
                'name'       => $r['full_name'],
                'index'      => $r['index_number'],
                'faculty'    => $r['faculty'],
                'position'   => 'Player / Squad Candidate',
                'med_status' => $r['med_status'],
                'can_add'    => $isFit,
                'med_note'   => $r['med_note'] ?: ($isFit ? 'All biometric parameters cleared.' : 'Medical center restrictions apply.')
            ];
        }

        return $result;
    }

    /**
     * Get current drafted squad roster for a fixture
     */
    public function getSquadForFixture($fixtureId) {
        $stmt = $this->db->prepare("
            SELECT 
                ms.squad_id,
                ms.fixture_id,
                ms.tic_approval_status,
                a.athlete_id,
                a.full_name,
                a.index_number,
                a.faculty,
                a.medical_status
            FROM match_squads ms
            JOIN Athletic a ON ms.athlete_id = a.athlete_id
            WHERE ms.fixture_id = ?
            ORDER BY ms.squad_id ASC
        ");
        $stmt->execute([$fixtureId]);
        $rows = $stmt->fetchAll();

        $result = [];
        $jersey = 1;
        foreach ($rows as $r) {
            $role = ($jersey <= 11) ? 'Starting XI' : 'Substitute';
            if ($jersey === 10) $role = 'Captain';
            $result[] = [
                'squad_id'   => $r['squad_id'],
                'athlete_id' => $r['athlete_id'],
                'id'         => 'ATH-' . str_pad($r['athlete_id'], 3, '0', STR_PAD_LEFT),
                'jersey'     => $jersey++,
                'name'       => $r['full_name'],
                'faculty'    => $r['faculty'],
                'role'       => $role,
                'status'     => ($r['medical_status'] === 'Fit to Compete') ? 'Cleared' : 'Restricted'
            ];
        }

        return $result;
    }

    /**
     * Submit/Save squad draft for TIC approval
     */
    public function saveSquadDraft($fixtureId, array $athleteIds) {
        $this->db->beginTransaction();
        try {
            $stmtDel = $this->db->prepare("DELETE FROM match_squads WHERE fixture_id = ?");
            $stmtDel->execute([$fixtureId]);

            $stmtIns = $this->db->prepare("
                INSERT INTO match_squads (fixture_id, athlete_id, tic_approval_status)
                VALUES (?, ?, 'Pending')
            ");
            foreach ($athleteIds as $athId) {
                if ((int)$athId > 0) {
                    $stmtIns->execute([$fixtureId, (int)$athId]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Log final match result
     */
    public function logMatchResult($fixtureId, $scores, $scorers, $report) {
        $stmtCheck = $this->db->prepare("SELECT result_id FROM match_results WHERE fixture_id = ?");
        $stmtCheck->execute([$fixtureId]);
        $existingId = $stmtCheck->fetchColumn();

        if ($existingId) {
            $stmt = $this->db->prepare("
                UPDATE match_results 
                SET scores = ?, scorers = ?, match_report = ?
                WHERE result_id = ?
            ");
            $stmt->execute([$scores, $scorers, $report, $existingId]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO match_results (fixture_id, scores, scorers, match_report)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$fixtureId, $scores, $scorers, $report]);
        }

        return true;
    }

    /**
     * Get team equipment requisitions
     */
    public function getEquipmentRequisitions($userId = null) {
        $sql = "
            SELECT 
                ei.issue_id,
                ei.equipment_id,
                ei.quantity,
                ei.issue_date,
                ei.expected_return_date,
                ei.actual_return_date,
                ei.status,
                ei.recipient_name,
                e.item_name,
                s.sport_name
            FROM equipment_issue ei
            JOIN equipment e ON ei.equipment_id = e.equipment_id
            JOIN sports s ON e.sport_id = s.sport_id
            WHERE ei.recipient_type = 'Coach' OR ei.recipient_type IS NULL
            ORDER BY ei.issue_id DESC
            LIMIT 25
        ";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $r) {
            $result[] = [
                'issue_id'       => $r['issue_id'],
                'req_id'         => 'REQ-EQ-' . str_pad($r['issue_id'], 3, '0', STR_PAD_LEFT),
                'item_name'      => $r['item_name'],
                'sport'          => $r['sport_name'],
                'quantity'       => $r['quantity'],
                'req_date'       => $r['issue_date'],
                'return_date'    => $r['expected_return_date'],
                'tic_status'     => $r['status'],
                'current_status' => ($r['status'] === 'Issued') ? 'Checked Out' : (($r['status'] === 'Returned') ? 'Returned' : 'In Review'),
                'purpose'        => 'Varsity practice training & match day preparation equipment allocation.'
            ];
        }

        return $result;
    }

    /**
     * Submit a new equipment requisition to TIC warehouse
     */
    public function submitEquipmentRequisition($equipmentId, $coachName, $quantity, $returnDate, $purpose) {
        $stmt = $this->db->prepare("
            INSERT INTO equipment_issue 
            (equipment_id, quantity, issue_date, expected_return_date, recipient_type, recipient_name, status)
            VALUES (?, ?, CURDATE(), ?, 'Coach', ?, 'Pending')
        ");
        return $stmt->execute([
            $equipmentId,
            max(1, (int)$quantity),
            $returnDate ?: date('Y-m-d', strtotime('+7 days')),
            $coachName ?: 'Coach Silva'
        ]);
    }

    /**
     * Get sports list
     */
    public function getAllSports() {
        return $this->db->query("SELECT sport_id, sport_name FROM sports ORDER BY sport_name ASC")->fetchAll();
    }

    /**
     * Get facilities list
     */
    public function getAllFacilities() {
        return $this->db->query("SELECT facility_id, facility_name FROM facilities ORDER BY facility_name ASC")->fetchAll();
    }

    /**
     * Get equipment list in stock
     */
    public function getAllEquipment() {
        return $this->db->query("SELECT equipment_id, item_name, quantity_in_stock, sport_id FROM equipment WHERE quantity_in_stock > 0 ORDER BY item_name ASC")->fetchAll();
    }
}
