<?php
// Run with XAMPP PHP. All test records are rolled back.
require_once __DIR__ . '/../models/SportApplication.php';
$db = Database::connect();
$model = new SportApplication($db);
function applicationCheck($ok, $message) {
    if (!$ok) throw new RuntimeException($message);
}
$db->beginTransaction();
try {
    $athletes = $db->query('SELECT athlete_id FROM Athletic ORDER BY athlete_id LIMIT 2')->fetchAll(PDO::FETCH_COLUMN);
    applicationCheck(count($athletes) === 2, 'Two athletes required for ownership checks.');
    $sport = $db->query('SELECT sport_id FROM sports LIMIT 1')->fetchColumn();
    $season = $db->query('SELECT season_id FROM seasons LIMIT 1')->fetchColumn();
    $insert = $db->prepare('INSERT INTO sport_application (athletic_id, sport_id, season_id, status, rejection_reason) VALUES (?, ?, ?, ?, ?)');
    $before = $model->getApplicationStats($athletes[0]);
    foreach (['Pending', 'Shortlisted', 'Approved', 'Rejected'] as $status) {
        $insert->execute([$athletes[0], $sport, $season, $status, 'Saved reason']);
        $id = (int)$db->lastInsertId();
        applicationCheck(!$model->deleteAthleteApplication($id, $athletes[1]), 'Another athlete must not delete the application.');
        $apps = array_column($model->getAthleteApplications($athletes[0]), null, 'application_id');
        applicationCheck(isset($apps[$id]) && $apps[$id]['rejection_reason'] === 'Saved reason', 'Saved details survive unauthorized deletion.');
        applicationCheck($model->deleteAthleteApplication($id, $athletes[0]), 'Owner can delete a ' . $status . ' application.');
        applicationCheck(!$model->deleteAthleteApplication($id, $athletes[0]), 'Repeated deletion reports missing application.');
        applicationCheck($model->getApplicationStats($athletes[0]) === $before, 'Counts return to their original values.');
    }
    $db->exec("INSERT INTO seasons (season_year, academic_year, status) VALUES (2091, 2091, 'Active')");
    $editSeason = $db->lastInsertId();
    $insert->execute([$athletes[0], $sport, $editSeason, 'Rejected', 'Old reason']);
    $editId = (int)$db->lastInsertId();
    applicationCheck(!$model->updateAthleteApplication($editId, $athletes[1], $sport, $editSeason, 'Unauthorized'), 'Other athletes cannot edit.');
    applicationCheck($model->updateAthleteApplication($editId, $athletes[0], $sport, $editSeason, 'Updated statement'), 'Owner can edit.');
    $apps = array_column($model->getAthleteApplications($athletes[0]), null, 'application_id');
    applicationCheck($apps[$editId]['statement_of_interest'] === 'Updated statement' && $apps[$editId]['status'] === 'Pending' && $apps[$editId]['rejection_reason'] === null, 'Changes persist and return to review.');
    applicationCheck($model->updateAthleteApplication($editId, $athletes[0], $sport, $editSeason, 'Updated statement'), 'Unchanged save succeeds.');
    try {
        $model->updateAthleteApplication($editId, $athletes[0], 2147483647, $editSeason, 'Invalid');
        throw new RuntimeException('Missing sport accepted.');
    } catch (InvalidArgumentException $expected) {}
    $insert->execute([$athletes[0], $sport, $editSeason, 'Approved', null]);
    try {
        $model->updateAthleteApplication($editId, $athletes[0], $sport, $editSeason, 'Duplicate');
        throw new RuntimeException('Duplicate accepted.');
    } catch (InvalidArgumentException $expected) {}
    echo "PASS: editing ownership, persistence, review reset, unchanged save, sport validation and duplicate prevention.\n";
    echo "PASS: deletion ownership, all statuses, saved rejection reason, missing applications and statistics.\n";
} finally {
    $db->rollBack();
}
