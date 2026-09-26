<section id="profile-view" class="view-section <?= $currentView === 'profile-view' ? 'active' : '' ?>">
    <?php if (!empty($_SESSION['athlete_profile_success'])): ?>
        <p class="profile-notice" role="status"><?= athleteEscape($_SESSION['athlete_profile_success']) ?></p>
        <?php unset($_SESSION['athlete_profile_success']); ?>
    <?php endif; ?>
    <div class="page-header-card profile-mode">
        <div class="profile-summary">
            <div class="profile-info"><span class="badge">STUDENT ATHLETE</span><h2><?= $fullName ?></h2><span class="id-tag"><?= $indexNumber ?></span></div>
        </div>
        <div class="header-action-buttons">
            <button type="button" class="btn btn-secondary" id="export-profile-btn"><i class="ph ph-download-simple"></i> Print / Save PDF</button>
            <button type="button" class="btn btn-primary" id="edit-profile-btn"><i class="ph ph-pencil-simple"></i> Edit Profile</button>
        </div>
    </div>
    <div class="profile-grid">
        <div class="grid-col-left">
            <div class="card personal-details-card">
                <h3>Personal Details</h3>
                <div class="detail-group">
                    <div class="detail-row"><span>Email</span><strong><?= $email ?></strong></div>
                    <div class="detail-row"><span>Academic year</span><strong><?= $academicYear ?></strong></div>
                    <div class="detail-row"><span>Faculty</span><strong><?= $faculty ?></strong></div>
                    <div class="detail-row"><span>Height</span><strong><?= $height ?><?= $athlete['height'] ? ' cm' : '' ?></strong></div>
                    <div class="detail-row"><span>Weight</span><strong><?= $weight ?><?= $athlete['weight'] ? ' kg' : '' ?></strong></div>
                </div>
                <div class="divider"></div>
                <div class="detail-group emergency"><p class="detail-label">EMERGENCY CONTACT</p><p class="contact-phone"><?= $emergencyContact ?: 'Not provided' ?></p></div>
            </div>
            <div class="card colours-card">
                <h3>University Colours</h3>
                <?php if (!$athleteAwards): ?><p class="text-muted">No university colours recorded yet.</p><?php endif; ?>
                <?php foreach ($athleteAwards as $award): ?>
                    <div class="detail-group"><strong><?= athleteEscape($award['sport_name']) ?> · <?= athleteEscape($award['season_year']) ?></strong><p><?= athleteEscape($award['status']) ?> · Award #<?= (int)$award['award_name'] ?></p></div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="grid-col-right">
            <div class="card sport-history-card">
                <div class="card-header"><h3>Sport History</h3></div>
                <table class="history-table"><thead><tr><th>SEASON</th><th>SPORT</th><th>APPLICATION STATUS</th></tr></thead><tbody>
                    <?php if (!$myApplications): ?><tr><td colspan="3">No sport applications recorded yet.</td></tr><?php endif; ?>
                    <?php foreach ($myApplications as $application): ?>
                        <tr><td><?= athleteEscape($application['season_year']) ?> (Season <?= (int)$application['season_id'] ?>)</td><td><?= athleteEscape($application['sport_name']) ?></td><td><?= athleteEscape($application['status']) ?></td></tr>
                    <?php endforeach; ?>
                </tbody></table>
            </div>
        </div>
    </div>
</section>
<section id="edit-profile-view" class="view-section <?= $currentView === 'edit-profile-view' ? 'active' : '' ?>">
    <form id="athlete-profile-form" action="../controllers/AthleteController.php?action=update_profile" method="post">
        <input type="hidden" name="csrf_token" value="<?= athleteEscape($_SESSION['athlete_csrf']) ?>">
        <div class="page-header-card edit-mode hero-gradient">
            <div class="profile-info"><h2>Edit Profile</h2><p><?= $fullName ?> · <?= $indexNumber ?></p></div>
            <div class="header-action-buttons"><button type="button" class="btn btn-secondary" id="cancel-edit-btn">Cancel</button><button type="submit" class="btn btn-primary" id="save-profile-btn">Save Changes</button></div>
        </div>
        <p id="profile-error" class="profile-error" role="alert" hidden></p>
        <div class="edit-grid">
            <div class="edit-col-left">
                <div class="card form-card"><h3>Personal Details</h3>
                    <div class="form-group"><label for="profile-name">Full name</label><input id="profile-name" name="full_name" maxlength="255" required value="<?= $fullName ?>" autocomplete="name"></div>
                    <div class="form-row">
                        <div class="form-group"><label for="profile-index">Student ID</label><input id="profile-index" value="<?= $indexNumber ?>" disabled></div>
                        <div class="form-group"><label for="profile-email">University email</label><input id="profile-email" value="<?= $email ?>" disabled></div>
                    </div>
                    <p class="form-hint">Contact administration to change your student ID or university email.</p>
                    <div class="form-row">
                        <div class="form-group"><label for="profile-faculty">Faculty</label><input id="profile-faculty" name="faculty" maxlength="255" required value="<?= $faculty ?>"></div>
                        <div class="form-group"><label for="profile-year">Academic year</label><input id="profile-year" name="academic_year" type="number" min="1" max="10" required value="<?= $academicYear ?>"></div>
                    </div>
                </div>
            </div>
            <div class="edit-col-right">
                <div class="card form-card"><h3>Physical Profile</h3>
                    <div class="form-row">
                        <div class="form-group"><label for="profile-height">Height (cm)</label><input id="profile-height" name="height" type="number" min="0.01" max="300" step="0.01" value="<?= athleteEscape($athlete['height'] ?: '') ?>"></div>
                        <div class="form-group"><label for="profile-weight">Weight (kg)</label><input id="profile-weight" name="weight" type="number" min="0.01" max="500" step="0.01" value="<?= athleteEscape($athlete['weight'] ?: '') ?>"></div>
                    </div>
                    <p class="form-hint">Leave measurements blank if they are not available.</p>
                </div>
                <div class="card form-card"><h3>Emergency Contact</h3><div class="form-group"><label for="profile-phone">Phone number</label><input id="profile-phone" name="emergency_Contact" type="tel" maxlength="20" value="<?= $emergencyContact ?>"><small class="form-hint">Include the country code where applicable.</small></div></div>
            </div>
        </div>
    </form>
</section>
