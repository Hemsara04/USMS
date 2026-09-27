<?php
require_once __DIR__ . '/../models/AthletePortal.php';
function calendarCheck($requested, $now, $start, $end) {
    [$actualStart, $actualEnd] = AthletePortal::calendarWeek($requested, new DateTimeImmutable($now, new DateTimeZone('Asia/Colombo')));
    if ($actualStart->format('Y-m-d H:i:s') !== $start . ' 00:00:00'
        || $actualEnd->format('Y-m-d H:i:s') !== $end . ' 00:00:00') {
        throw new RuntimeException('Unexpected week for ' . $now);
    }
}
calendarCheck('', '2026-09-27 23:59:59', '2026-09-21', '2026-09-28');
calendarCheck('', '2026-09-28 00:00:00', '2026-09-28', '2026-10-05');
calendarCheck('', '2026-09-29 12:00:00', '2026-09-28', '2026-10-05');
calendarCheck('2026-10-12', '2026-09-27', '2026-10-12', '2026-10-19');
calendarCheck('2027-01-01', '2026-09-27', '2026-12-28', '2027-01-04');
calendarCheck('2028-02-29', '2026-09-27', '2028-02-28', '2028-03-06');
calendarCheck('', '2026-09-27 18:30:00 UTC', '2026-09-28', '2026-10-05');
foreach (['2026-02-30', 'invalid', ['2026-10-12'], "2026-10-12\0", '2101-01-01'] as $invalid) {
    calendarCheck($invalid, '2026-09-27', '2026-09-21', '2026-09-28');
}
// Render the actual partial and verify native navigation and future event output.
[$weekStart, $weekEnd] = AthletePortal::calendarWeek('2026-10-12');
$now = new DateTimeImmutable('2026-09-27');
$currentView = 'calendar-view';
$calendarDays = ['2026-10-12' => [[
    'kind' => 'training', 'event_time' => '09:00:00', 'end_time' => '10:00:00',
    'focus_area' => 'Future training', 'session_type' => 'Practice', 'sport_name' => 'Football',
    'facility_name' => 'Ground', 'notes' => '',
]]];
function athleteEscape($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
ob_start();
require __DIR__ . '/../views/partials/athlete_calendar.php';
$html = ob_get_clean();
foreach (['week=2026-10-05', 'week=2026-10-19', 'Future training', 'type="date"', 'method="get"'] as $expected) {
    if (strpos($html, $expected) === false) throw new RuntimeException('Missing ' . $expected);
}
if (substr_count($html, 'class="day-header"') !== 7) throw new RuntimeException('Expected seven days.');
echo "PASS: Sunday/Monday rollover, future weeks, year/leap-day boundaries, timezone, invalid dates and HTML navigation/event rendering.\n";
