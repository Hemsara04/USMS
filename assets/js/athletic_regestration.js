document.addEventListener('DOMContentLoaded', () => {
    // View Management
    const navItems = document.querySelectorAll('.sidebar-nav .nav-item[data-target]');
    const views = document.querySelectorAll('.view-section');
    
    // Buttons for quick actions
    const editProfileBtn = document.getElementById('edit-profile-btn');
    const cancelEditBtn = document.getElementById('cancel-edit-btn');
    const saveProfileBtn = document.getElementById('save-profile-btn');
    
    // Application Flow Elements
    const submitAppBtn = document.getElementById('submit-application-btn');
    const cancelAppBtn = document.getElementById('cancel-apply-btn');
    const viewMyAppsBtn = document.getElementById('view-my-apps-btn');
    const backDashBtn = document.getElementById('back-dash-btn');
    const backToAppsBtn = document.getElementById('back-to-apps-btn');
    const newAppBtn = document.getElementById('new-app-btn');
    const applyErrorMsg = document.getElementById('apply-error-msg');
    
    // Form Inputs
    const sportSelect = document.getElementById('apply-sport-id');
    const seasonSelect = document.getElementById('apply-season-id');
    const athleteIdInput = document.getElementById('apply-athlete-id');
    const statementInput = document.getElementById('apply-statement');
    const termsCheckbox = document.getElementById('terms-agree');
    
    // Confirmation Elements
    const submissionConfirmText = document.getElementById('submission-confirm-text');
    const submissionProgramTitle = document.getElementById('submission-program-title');
    const submissionRefCode = document.getElementById('submission-ref-code');

    // Table & Stats
    const myAppsTable = document.getElementById('my-apps-table');
    const appSearchInput = document.getElementById('app-search-input');
    const statTotalApps = document.getElementById('stat-total-apps');
    const statUnderReview = document.getElementById('stat-under-review');
    const statActionReq = document.getElementById('stat-action-req');
    const statAccepted = document.getElementById('stat-accepted');
    const tableFooterText = document.getElementById('table-footer-text');

    // Function to switch active view
    function switchView(targetId) {
        // Update Views
        views.forEach(view => {
            if (view.id === targetId) {
                view.classList.add('active');
            } else {
                view.classList.remove('active');
            }
        });

        // Update Nav Active State
        navItems.forEach(nav => {
            let expectedTarget = targetId;
            
            // Map sub-views to their parent nav items
            if (targetId === 'edit-profile-view') expectedTarget = 'profile-view';
            if (targetId === 'submission-view') expectedTarget = 'apply-view';
            if (targetId === 'application-details-view') expectedTarget = 'applications-view';

            if (nav.getAttribute('data-target') === expectedTarget) {
                nav.classList.add('active');
            } else {
                nav.classList.remove('active');
            }
        });
        
        // Scroll to top when switching views
        window.scrollTo(0, 0);
    }

    // Sidebar Navigation Click Handlers
    navItems.forEach(nav => {
        nav.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = nav.getAttribute('data-target');
            if (targetId) {
                switchView(targetId);
            }
        });
    });

    // Profile Flow
    if (editProfileBtn) editProfileBtn.addEventListener('click', () => switchView('edit-profile-view'));
    const profileForm = document.getElementById('athlete-profile-form');
    const profileError = document.getElementById('profile-error');
    if (cancelEditBtn) cancelEditBtn.addEventListener('click', () => {
        profileForm.reset();
        profileError.hidden = true;
        switchView('profile-view');
    });
    document.getElementById('export-profile-btn')?.addEventListener('click', () => window.print());
    if (profileForm) profileForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!profileForm.reportValidity() || saveProfileBtn.disabled) return;
        const data = new FormData(profileForm);
        saveProfileBtn.disabled = true;
        cancelEditBtn.disabled = true;
        saveProfileBtn.textContent = 'Saving…';
        profileError.hidden = true;
        try {
            const response = await fetch(profileForm.action, {
                method: 'POST', body: data, headers: { 'Accept': 'application/json' }
            });
            const result = await response.json();
            if (!response.ok || result.status !== 'success') throw new Error(result.message || 'Unable to save profile.');
            window.location.assign('?view=profile-view');
        } catch (error) {
            profileError.textContent = error.message || 'Unable to save profile. Please try again.';
            profileError.hidden = false;
            profileError.scrollIntoView({ block: 'center' });
            saveProfileBtn.disabled = false;
            cancelEditBtn.disabled = false;
            saveProfileBtn.textContent = 'Save Changes';
        }
    });

    // New Application Button
    if (newAppBtn) {
        newAppBtn.addEventListener('click', () => {
            if (applyErrorMsg) {
                applyErrorMsg.style.display = 'none';
                applyErrorMsg.textContent = '';
            }
            switchView('apply-view');
        });
    }

    // Cancel Application Button
    if (cancelAppBtn) {
        cancelAppBtn.addEventListener('click', () => {
            if (applyErrorMsg) {
                applyErrorMsg.style.display = 'none';
                applyErrorMsg.textContent = '';
            }
            switchView('dashboard-view');
        });
    }

    // Navigation buttons
    if (viewMyAppsBtn) viewMyAppsBtn.addEventListener('click', () => switchView('applications-view'));
    if (backDashBtn) backDashBtn.addEventListener('click', () => switchView('dashboard-view'));
    if (backToAppsBtn) backToAppsBtn.addEventListener('click', () => switchView('applications-view'));

    // Submit Application Flow
    const sportAppForm = document.getElementById('sport-application-form');

    async function handleApplicationSubmission(e) {
        if (e) e.preventDefault();

        // Clear previous errors
        if (applyErrorMsg) {
            applyErrorMsg.style.display = 'none';
            applyErrorMsg.textContent = '';
        }

        const sportId = sportSelect ? sportSelect.value : '';
        const seasonId = seasonSelect ? seasonSelect.value : '';
        const athleteId = athleteIdInput ? athleteIdInput.value : '';
        const statement = statementInput ? statementInput.value.trim() : '';
        const isAgreed = termsCheckbox ? termsCheckbox.checked : false;

        // Client-side validations
        if (!sportId) {
            showApplyError('Please select a sport program from the list.');
            if (sportSelect) sportSelect.focus();
            return;
        }

        if (!seasonId) {
            showApplyError('Please select a target season from the list.');
            if (seasonSelect) seasonSelect.focus();
            return;
        }

        if (!isAgreed) {
            showApplyError('You must acknowledge and agree to the Athlete Code of Conduct and Compliance Guidelines.');
            if (termsCheckbox) termsCheckbox.focus();
            return;
        }

        const originalBtnHtml = submitAppBtn ? submitAppBtn.innerHTML : 'Submit Application';
        if (submitAppBtn) {
            submitAppBtn.disabled = true;
            submitAppBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Submitting...';
        }

        const formData = new FormData();
        formData.append('sport_id', sportId);
        formData.append('season_id', seasonId);
        formData.append('athlete_id', athleteId);
        formData.append('statement_of_interest', statement);
        formData.append('terms_agree', '1');
        formData.append('ajax', '1');

        try {
            const response = await fetch('../controllers/AthleteController.php?action=submit_application', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const result = await response.json();

            if (result.status === 'success') {
                const data = result.data;

                // Update confirmation view
                if (submissionProgramTitle) {
                    submissionProgramTitle.textContent = `${data.sport_name} - Season ID: ${data.season_id} (${data.season_year})`;
                }
                if (submissionRefCode) {
                    submissionRefCode.textContent = data.reference_number;
                }

                // Reset form
                if (sportSelect) sportSelect.value = '';
                if (seasonSelect) seasonSelect.value = '';
                if (statementInput) statementInput.value = '';
                if (termsCheckbox) termsCheckbox.checked = false;

                // Reload applications table dynamically
                await refreshApplicationsList();

                // Switch to confirmation view
                switchView('submission-view');
            } else {
                showApplyError(result.message || 'An error occurred while submitting your application.');
            }
        } catch (err) {
            console.error('Submission error:', err);
            // If AJAX fetch failed unexpectedly, fall back to native form submission
            if (sportAppForm) {
                sportAppForm.submit();
                return;
            }
            showApplyError('Network error or server unavailable. Please try again.');
        } finally {
            if (submitAppBtn) {
                submitAppBtn.disabled = false;
                submitAppBtn.innerHTML = originalBtnHtml;
            }
        }
    }

    if (sportAppForm) {
        sportAppForm.addEventListener('submit', handleApplicationSubmission);
    } else if (submitAppBtn) {
        submitAppBtn.addEventListener('click', handleApplicationSubmission);
    }

    function showApplyError(message) {
        if (applyErrorMsg) {
            applyErrorMsg.textContent = message;
            applyErrorMsg.style.display = 'block';
            applyErrorMsg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            alert(message);
        }
    }

    // Refresh Applications List via AJAX
    async function refreshApplicationsList() {
        if (!myAppsTable) return;

        try {
            const response = await fetch('../controllers/AthleteController.php?action=get_my_applications', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const result = await response.json();

            if (result.status === 'success' && result.data) {
                const apps = result.data.applications || [];
                const stats = result.data.stats || {};

                // Update Stats
                if (statTotalApps) statTotalApps.textContent = stats.total ?? apps.length;
                if (statUnderReview) statUnderReview.textContent = stats.under_review ?? 0;
                if (statActionReq) statActionReq.textContent = stats.action_required ?? 0;
                if (statAccepted) statAccepted.textContent = stats.accepted ?? 0;

                // Update Table Rows
                const tbody = myAppsTable.querySelector('tbody');
                if (tbody) {
                    if (apps.length === 0) {
                        tbody.innerHTML = `
                            <tr id="empty-apps-row">
                                <td colspan="5" style="text-align: center; padding: 2.5rem; color: var(--text-muted, #64748b);">
                                    <i class="ph ph-clipboard-text" style="font-size: 2rem; display: block; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                                    No sport applications submitted yet. Click <strong>"New Application"</strong> to apply for a sport.
                                </td>
                            </tr>
                        `;
                    } else {
                        tbody.innerHTML = apps.map(app => {
                            let badgeClass = 'dark';
                            const statusUpper = (app.status || 'Pending').toUpperCase();
                            if (app.status === 'Approved') badgeClass = 'purple';
                            else if (app.status === 'Shortlisted') badgeClass = 'teal';
                            else if (app.status === 'Rejected') badgeClass = 'red';

                            let iconClass = 'ph-trophy';
                            const sName = (app.sport_name || '').toLowerCase();
                            if (sName.includes('basket')) iconClass = 'ph-basketball';
                            else if (sName.includes('swim')) iconClass = 'ph-swimming-pool';
                            else if (sName.includes('track')) iconClass = 'ph-person-simple-run';
                            else if (sName.includes('foot') || sName.includes('soccer')) iconClass = 'ph-soccer-ball';
                            else if (sName.includes('volley')) iconClass = 'ph-volleyball';
                            else if (sName.includes('tennis')) iconClass = 'ph-tennis-ball';

                            const appNumber = String(app.application_id).padStart(4, '0');
                            const dateApplied = (app.applied_date || '').split(' ')[0] || '';

                            return `
                                <tr>
                                    <td>
                                        <div class="program-cell">
                                            <div class="icon-circle"><i class="ph-fill ${iconClass}"></i></div>
                                            <div>
                                                <strong>${escapeHtml(app.sport_name)}</strong>
                                                <p class="text-muted">App #${appNumber}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Season ID: ${escapeHtml(String(app.season_id))} (Year ${escapeHtml(String(app.season_year))})</td>
                                    <td>${escapeHtml(dateApplied)}</td>
                                    <td><span class="status-badge ${badgeClass}">${statusUpper}</span></td>
                                    <td>
                                        <button class="btn-text view-details-btn" 
                                            data-appid="${app.application_id}" 
                                            data-sport="${escapeHtml(app.sport_name)}" 
                                            data-season="Season ID: ${app.season_id} (Year ${app.season_year})" 
                                            data-status="${escapeHtml(app.status)}" 
                                            data-date="${escapeHtml(dateApplied)}" 
                                            data-rejection-reason="${escapeHtml(app.rejection_reason || '')}"
                                            data-statement="${escapeHtml(app.statement_of_interest || '')}">
                                            View Details
                                        </button>
                                        <button type="button" class="btn-text text-red delete-application-btn" data-appid="${app.application_id}">Delete</button>
                                    </td>
                                </tr>
                            `;
                        }).join('');
                    }
                }

                if (tableFooterText) {
                    tableFooterText.textContent = `Showing ${apps.length} application(s)`;
                }

                // Rebind View Details buttons
                bindViewDetailsButtons();
            }
        } catch (err) {
            console.error('Error refreshing applications list:', err);
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    // View Details Buttons Handler
    function bindViewDetailsButtons() {
        const btns = document.querySelectorAll('.view-details-btn');
        btns.forEach(btn => {
            btn.onclick = () => {
                const sport = btn.getAttribute('data-sport');
                const season = btn.getAttribute('data-season');
                const status = btn.getAttribute('data-status');
                const appId = btn.getAttribute('data-appid');
                const date = btn.getAttribute('data-date');
                const statement = btn.getAttribute('data-statement');

                // Update application-details-view if available
                const detailsView = document.getElementById('application-details-view');
                if (detailsView) {
                    const rejection = document.getElementById('application-rejection');
                    rejection.style.display = status === 'Rejected' ? '' : 'none';
                    document.getElementById('application-rejection-reason').textContent =
                        status === 'Rejected' ? (btn.dataset.rejectionReason.trim() || 'No rejection reason was provided.') : '';
                    document.getElementById('application-statement').textContent = statement || 'No statement of interest provided.';
                    document.getElementById('delete-detail-application').dataset.appid = appId;
                    const subtitle = detailsView.querySelector('.details-header p.text-muted');
                    if (subtitle && sport && season) subtitle.textContent = `${sport} • ${season}`;

                    const statusBadge = detailsView.querySelector('.details-header .status-badge');
                    if (statusBadge && status) {
                        statusBadge.className = `status-badge ${status === 'Approved' ? 'purple' : (status === 'Shortlisted' ? 'teal' : (status === 'Rejected' ? 'red-filled' : 'dark'))}`;
                        statusBadge.textContent = status;
                    }

                    const summaryRows = detailsView.querySelectorAll('.summary-card .summary-row .value');
                    if (summaryRows.length >= 4) {
                        if (sport) summaryRows[0].textContent = sport;
                        if (season) summaryRows[1].textContent = season;
                        if (date) summaryRows[2].textContent = date;
                        if (appId) summaryRows[3].textContent = `APP-${(date || '').slice(0, 4)}-${String(appId).padStart(4, '0')}`;
                    }
                }

                switchView('application-details-view');
            };
        });
    }

    bindViewDetailsButtons();

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('.delete-application-btn');
        if (!button || button.disabled || !button.dataset.appid) return;
        if (!window.confirm('Delete this application permanently? This cannot be undone.')) return;
        button.disabled = true;
        const data = new FormData();
        data.set('application_id', button.dataset.appid);
        data.set('csrf_token', document.getElementById('applications-view').dataset.csrf);
        try {
            const response = await fetch('../controllers/AthleteController.php?action=delete_application', {
                method: 'POST', body: data, headers: { 'Accept': 'application/json' }
            });
            const result = await response.json();
            if (!response.ok || result.status !== 'success') {
                throw new Error(result.message || 'Unable to delete the application.');
            }
            window.location.href = 'athletic_regestration.php?view=applications-view';
        } catch (error) {
            window.alert(error.message || 'Unable to delete the application. Please try again.');
            button.disabled = false;
        }
    });

    // Real-time Search Filter for Applications Table
    if (appSearchInput && myAppsTable) {
        appSearchInput.addEventListener('input', () => {
            const query = appSearchInput.value.toLowerCase().trim();
            const rows = myAppsTable.querySelectorAll('tbody tr:not(#empty-apps-row)');
            let visibleCount = 0;

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (tableFooterText) {
                tableFooterText.textContent = query ? `Found ${visibleCount} matching application(s)` : `Showing ${rows.length} application(s)`;
            }
        });
    }
});
