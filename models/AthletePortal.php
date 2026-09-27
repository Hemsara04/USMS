<?php
/** Shared athlete-scoped schedule and profile persistence. */
class AthletePortal {
    private $db;
    public function __construct(PDO $db) { $this->db = $db; }

    /** Monday-inclusive, next-Monday-exclusive range in Sri Lanka time. */
    public static function calendarWeek($requestedWeek = '', ?DateTimeImmutable $now = null) {
        $timezone = new DateTimeZone('Asia/Colombo');
        $today = ($now ?? new DateTimeImmutable('now', $timezone))->setTimezone($timezone)->setTime(0, 0);
        $date = is_string($requestedWeek) && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $requestedWeek)
            ? DateTimeImmutable::createFromFormat('!Y-m-d', $requestedWeek, $timezone) : false;
        if (!$date || $date->format('Y-m-d') !== $requestedWeek || (int)$date->format('Y') < 1900 || (int)$date->format('Y') > 2100) {
            $date = $today;
        }
        $start = $date->modify('-' . ((int)$date->format('N') - 1) . ' days');
        return [$start, $start->modify('+7 days')];
    }

    public function schedule($athleteId, $from, $to = null) {
        $training = "SELECT ts.*, s.sport_name, fac.facility_name,
            ts.session_date AS event_date, ts.start_time AS event_time,
            ADDTIME(ts.start_time, ts.duration_minutes) AS end_time
            FROM training_sessions ts JOIN sports s ON s.sport_id = ts.sport_id
            LEFT JOIN facilities fac ON fac.facility_id = ts.facility_id
            WHERE (EXISTS (SELECT 1 FROM sport_application sa JOIN seasons se ON se.season_id = sa.season_id
                WHERE sa.athletic_id = ? AND sa.sport_id = ts.sport_id AND sa.status = 'Approved'
                AND se.season_year = YEAR(ts.session_date))
                OR EXISTS (SELECT 1 FROM training_attendance ta WHERE ta.session_id = ts.session_id AND ta.athlete_id = ?))
            AND TIMESTAMP(ts.session_date, ts.start_time) >= ?";
        $matches = "SELECT f.*, s.sport_name, fac.facility_name,
            f.fixture_date AS event_date, f.fixture_time AS event_time
            FROM fixtures f JOIN sports s ON s.sport_id = f.sport_id
            LEFT JOIN facilities fac ON fac.facility_id = f.facility_id
            WHERE EXISTS (SELECT 1 FROM match_squads ms WHERE ms.fixture_id = f.fixture_id
                AND ms.athlete_id = ? AND ms.tic_approval_status = 'Approved')
            AND TIMESTAMP(f.fixture_date, f.fixture_time) >= ?";
        $tp = [$athleteId, $athleteId, $from];
        $mp = [$athleteId, $from];
        if ($to !== null) {
            $training .= ' AND ts.session_date < ?'; $tp[] = $to;
            $matches .= ' AND f.fixture_date < ?'; $mp[] = $to;
        }
        $stmt = $this->db->prepare($training . ' ORDER BY event_date, event_time, ts.session_id');
        $stmt->execute($tp);
        $sessions = $stmt->fetchAll();
        $stmt = $this->db->prepare($matches . ' ORDER BY event_date, event_time, f.fixture_id');
        $stmt->execute($mp);
        return ['training' => $sessions, 'matches' => $stmt->fetchAll()];
    }

    public function updateProfile($userId, array $input) {
        $values = [];
        foreach (['full_name', 'faculty', 'academic_year', 'height', 'weight', 'emergency_Contact'] as $key) {
            if (isset($input[$key]) && !is_scalar($input[$key])) {
                throw new InvalidArgumentException('Invalid profile data.');
            }
            $values[$key] = trim((string)($input[$key] ?? ''));
        }
        foreach (['full_name' => 'Full name', 'faculty' => 'Faculty'] as $key => $label) {
            if ($values[$key] === '' || strlen($values[$key]) > 255) {
                throw new InvalidArgumentException($label . ' is required and must be at most 255 characters.');
            }
        }
        if (!ctype_digit($values['academic_year']) || (int)$values['academic_year'] < 1 || (int)$values['academic_year'] > 10) {
            throw new InvalidArgumentException('Academic year must be between 1 and 10.');
        }
        foreach (['height' => 300, 'weight' => 500] as $key => $max) {
            if ($values[$key] === '') { $values[$key] = null; continue; }
            if (!is_numeric($values[$key]) || !is_finite((float)$values[$key]) || (float)$values[$key] <= 0 || (float)$values[$key] > $max) {
                throw new InvalidArgumentException(ucfirst($key) . ' must be greater than zero and no more than ' . $max . '.');
            }
        }
        $phone = $values['emergency_Contact'];
        if ($phone !== '' && (strlen($phone) > 20 || !preg_match('/^\+?[0-9 ()-]+$/', $phone)
            || strlen(preg_replace('/\D/', '', $phone)) < 7 || strlen(preg_replace('/\D/', '', $phone)) > 15)) {
            throw new InvalidArgumentException('Enter a valid emergency phone number (7–15 digits).');
        }
        $stmt = $this->db->prepare('UPDATE Athletic SET full_name = ?, faculty = ?, academic_year = ?, height = ?, weight = ?, emergency_Contact = ? WHERE user_id = ?');
        $stmt->execute(array_merge(array_values($values), [$userId]));
    }
}
