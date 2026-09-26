<?php
// Run with XAMPP PHP. All test records and updates are rolled back.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/AthletePortal.php';
$db = Database::connect();
$portal = new AthletePortal($db);
function check($ok, $message) { if (!$ok) throw new RuntimeException($message); }
$db->beginTransaction();
try {
    $athletes = $db->query('SELECT * FROM Athletic ORDER BY athlete_id LIMIT 2')->fetchAll();
    check(count($athletes) === 2, 'Two athletes needed for isolation checks');
    $a = $athletes[0]; $b = $athletes[1];
    $sport = $db->query('SELECT sport_id FROM sports LIMIT 1')->fetchColumn();
    $facility = $db->query('SELECT facility_id FROM facilities LIMIT 1')->fetchColumn();
    $db->exec("INSERT INTO seasons (season_year, academic_year, status) VALUES (2090, 2090, 'Active')");
    $season = $db->lastInsertId();
    $stmt = $db->prepare("INSERT INTO sport_application (athletic_id, sport_id, season_id, status) VALUES (?, ?, ?, 'Approved')");
    $stmt->execute([$a['athlete_id'], $sport, $season]);
    $stmt->execute([$a['athlete_id'], $sport, $season]); // Duplicate approval must not duplicate events.
    $stmt = $db->prepare("INSERT INTO training_sessions (sport_id, session_date, start_time, duration_minutes, session_type, focus_area) VALUES (?, '2090-06-05', '09:00:00', '01:30:00', 'Practice', 'Isolation check')");
    $stmt->execute([$sport]); $session = $db->lastInsertId();
    $stmt = $db->prepare("INSERT INTO fixtures (sport_id, facility_id, opponent, fixture_date, fixture_time) VALUES (?, ?, 'Test opponent', '2090-06-05', '15:00:00')");
    $stmt->execute([$sport, $facility]); $fixture = $db->lastInsertId();
    $stmt = $db->prepare("INSERT INTO match_squads (fixture_id, athlete_id, tic_approval_status) VALUES (?, ?, 'Approved'), (?, ?, 'Pending')");
    $stmt->execute([$fixture, $a['athlete_id'], $fixture, $b['athlete_id']]);
    $own = $portal->schedule($a['athlete_id'], '2090-06-05', '2090-06-06');
    $other = $portal->schedule($b['athlete_id'], '2090-06-05', '2090-06-06');
    check(count($own['training']) === 1 && count($own['matches']) === 1, 'Own events appear once');
    check($own['training'][0]['end_time'] === '10:30:00', 'Duration calculated');
    check(!$other['training'] && !$other['matches'], 'Other athlete events and pending selections excluded');
    check(!$portal->schedule($a['athlete_id'], '2090-06-06', '2090-06-13')['training'], 'Week boundaries applied');
    $stmt = $db->prepare("INSERT INTO training_attendance (session_id, athlete_id, status) VALUES (?, ?, 'Present')");
    $stmt->execute([$session, $b['athlete_id']]);
    check(count($portal->schedule($b['athlete_id'], '2090-06-05', '2090-06-06')['training']) === 1, 'Assigned training included');
    $input = ['full_name' => 'Profile test', 'faculty' => 'Test faculty', 'academic_year' => '3', 'height' => '175.5', 'weight' => '70.25', 'emergency_Contact' => '+94 77 123 4567', 'user_id' => $b['user_id']];
    $portal->updateProfile($a['user_id'], $input);
    $stmt = $db->prepare('SELECT * FROM Athletic WHERE athlete_id = ?');
    $stmt->execute([$a['athlete_id']]); $saved = $stmt->fetch();
    check($saved['full_name'] === 'Profile test' && (float)$saved['height'] === 175.5, 'Profile persisted');
    $stmt->execute([$b['athlete_id']]); check($stmt->fetch() === $b, 'Submitted user ID cannot change another profile');
    foreach (['height' => '-1', 'weight' => 'no', 'academic_year' => '0', 'emergency_Contact' => 'abc', 'full_name' => ''] as $key => $value) {
        try { $portal->updateProfile($a['user_id'], array_replace($input, [$key => $value])); throw new RuntimeException('Invalid ' . $key . ' accepted'); }
        catch (InvalidArgumentException $expected) {}
    }
    $portal->updateProfile($a['user_id'], array_replace($input, ['height' => '', 'weight' => '', 'emergency_Contact' => '']));
    echo "PASS: schedule isolation, deduplication, squad approval, week bounds, duration, assigned training, profile persistence and validation.\n";
} finally {
    $db->rollBack();
}
