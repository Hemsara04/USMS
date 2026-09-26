<section id="calendar-view" class="view-section <?= $currentView === 'calendar-view' ? 'active' : '' ?>">
    <div class="calendar-header-card">
        <div class="cal-title"><h2><i class="ph ph-calendar-blank"></i> Training & Matches</h2><p><?= $weekStart->format('M j, Y') ?> — <?= $weekEnd->modify('-1 day')->format('M j, Y') ?></p></div>
        <div class="cal-controls">
            <div class="legend"><span class="legend-item training"><span class="dot"></span> TRAINING</span><span class="legend-item match"><span class="dot"></span> MATCH</span></div>
            <div class="cal-nav">
                <a class="btn-icon" aria-label="Previous week" href="?view=calendar-view&amp;week=<?= $weekStart->modify('-7 days')->format('Y-m-d') ?>"><i class="ph ph-caret-left"></i></a>
                <a class="btn btn-secondary btn-sm" href="?view=calendar-view">TODAY</a>
                <a class="btn-icon" aria-label="Next week" href="?view=calendar-view&amp;week=<?= $weekEnd->format('Y-m-d') ?>"><i class="ph ph-caret-right"></i></a>
            </div>
        </div>
    </div>
    <p class="schedule-note text-muted">Training for your approved sport registrations and assigned sessions; matches where your squad selection is approved. Times are in Sri Lanka time.</p>
    <?php if (!$calendarDays): ?><p class="profile-notice">No training or matches scheduled for you this week.</p><?php endif; ?>
    <div class="calendar-grid">
        <?php for ($i = 0; $i < 7; $i++): $day = $weekStart->modify("+$i days"); $events = $calendarDays[$day->format('Y-m-d')] ?? []; ?>
            <div class="cal-day <?= $i >= 5 ? 'weekend' : '' ?> <?= $day->format('Y-m-d') === $now->format('Y-m-d') ? 'active-day' : '' ?>">
                <div class="day-header"><?= strtoupper($day->format('D')) ?> <strong><?= $day->format('j') ?></strong></div>
                <div class="events-container <?= !$events ? 'empty' : '' ?>">
                    <?php if (!$events): ?><div class="empty-text">No events</div><?php endif; ?>
                    <?php foreach ($events as $event): ?>
                        <div class="event-card <?= $event['kind'] ?>">
                            <?php if ($event['kind'] === 'match'): ?><span class="match-badge">MATCH</span><?php endif; ?>
                            <p class="time"><?= athleteEscape(substr($event['event_time'], 0, 5)) ?><?= $event['kind'] === 'training' ? ' – ' . athleteEscape(substr($event['end_time'], 0, 5)) : '' ?></p>
                            <h4><?= athleteEscape($event['kind'] === 'training' ? ($event['focus_area'] ?: $event['session_type']) : $event['home_team'] . ' vs ' . $event['opponent']) ?></h4>
                            <p><?= athleteEscape($event['sport_name']) ?></p>
                            <p class="location"><i class="ph ph-map-pin"></i> <?= athleteEscape($event['facility_name'] ?: 'Venue to be confirmed') ?></p>
                            <?php if ($event['kind'] === 'training' && $event['notes']): ?><p><?= athleteEscape($event['notes']) ?></p><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endfor; ?>
    </div>
</section>
