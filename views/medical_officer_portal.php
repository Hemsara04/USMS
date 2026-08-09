<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Officer Portal - USMS</title>
    <!-- Link to the custom CSS file we generated -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Google Fonts for Modern Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <!-- 
      Navbar Section 
      Contains the branding/logo on the left.
    -->
    <nav class="navbar">
        <div class="navbar-brand">
            <!-- Replace with actual logo SVG/IMG if available -->
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3"/><path d="M8 15v6"/><path d="M5 21h6"/></svg>
            MEDICAL OFFICER PORTAL
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container">
        
        <!-- 
            We use a relative container to hold the main content.
            The drawer (Log Athlete Injury) is positioned absolute/fixed on the right.
        -->
        <div class="side-drawer-container">
            
            <!-- Left Side: Main Content Area -->
            <div class="main-content">
                <div class="card">
                    <!-- Search Header inside the card -->
                    <div class="card-header">
                        <div class="search-bar">
                            <!-- Search Icon (SVG) -->
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            <input type="text" class="form-control" placeholder="Search students...">
                        </div>
                    </div>

                    <!-- Students Table -->
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Student ID</th>
                                    <th>Assigned Sport</th>
                                    <th>Last Assessment Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Example Row 1 -->
                                <!-- In PHP, you will wrap this in a foreach loop: foreach($students as $student): -->
                                <tr>
                                    <td>
                                        <div class="user-cell">
                                            <!-- Fallback profile image -->
                                            <img src="https://ui-avatars.com/api/?name=Faston+Name&background=random" alt="Faston">
                                            Faston Name
                                        </div>
                                    </td>
                                    <td>10200731</td>
                                    <td>Padel</td>
                                    <td>Jan 01, 2023</td>
                                    <td>
                                        <!-- Primary action button for each row -->
                                        <button class="btn btn-primary btn-sm">Issue Clearance</button>
                                    </td>
                                </tr>

                                <!-- Example Row 2 -->
                                <tr>
                                    <td>
                                        <div class="user-cell">
                                            <img src="https://ui-avatars.com/api/?name=Darily+Hayon&background=random" alt="Darily">
                                            Darily Hayon
                                        </div>
                                    </td>
                                    <td>10200256</td>
                                    <td>Tennis</td>
                                    <td>Jul 23, 2023</td>
                                    <td>
                                        <button class="btn btn-primary btn-sm">Issue Clearance</button>
                                    </td>
                                </tr>

                                <!-- Example Row 3 -->
                                <tr>
                                    <td>
                                        <div class="user-cell">
                                            <img src="https://ui-avatars.com/api/?name=Claxie+Smith&background=random" alt="Claxie">
                                            Claxie Smith
                                        </div>
                                    </td>
                                    <td>10200137</td>
                                    <td>Squash</td>
                                    <td>Jul 23, 2022</td>
                                    <td>
                                        <button class="btn btn-primary btn-sm">Issue Clearance</button>
                                    </td>
                                </tr>
                                <!-- End PHP loop here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 
                Right Side: Log Athlete Injury Drawer / Modal
                This can be toggled via JavaScript. For now, it's visible to match the UI image.
            -->
            <div class="side-drawer" id="injuryDrawer">
                <div class="drawer-header">
                    <h2 class="drawer-title">Log Athlete Injury</h2>
                    <button class="close-btn">&times;</button>
                </div>
                
                <!-- Form body for logging an injury -->
                <div class="drawer-body">
                    <!-- Form should point to a controller action, e.g., action="/medical/log-injury" -->
                    <form id="injuryForm" action="#" method="POST">
                        
                        <!-- Injury Type Select -->
                        <div class="form-group">
                            <label class="form-label">Injury Type</label>
                            <select class="form-control" name="injury_type">
                                <option>Select Injury Type</option>
                                <option value="sprain">Sprain</option>
                                <option value="fracture">Fracture</option>
                                <option value="concussion">Concussion</option>
                                <option value="muscle_strain">Muscle Strain</option>
                            </select>
                        </div>

                        <!-- Severity Toggle Buttons -->
                        <!-- A hidden input can capture the value of these toggle buttons via JS, 
                             or you can use custom styled radio buttons. 
                             Here we use UI toggles. -->
                        <div class="form-group">
                            <label class="form-label">Severity</label>
                            <div class="toggle-group">
                                <button type="button" class="toggle-btn active">Negative</button>
                                <button type="button" class="toggle-btn">Medium</button>
                                <button type="button" class="toggle-btn">Priority</button>
                            </div>
                            <!-- Hidden field to pass to PHP backend -->
                            <input type="hidden" name="severity" value="negative" id="severityInput">
                        </div>

                        <!-- Estimated Recovery Time -->
                        <div class="form-group">
                            <label class="form-label">Estimated Recovery Time</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="recovery_time" placeholder="e.g., 2">
                                <span class="input-group-text">Weeks</span>
                            </div>
                        </div>

                        <!-- Automatic Removal Checkbox -->
                        <div class="form-group" style="margin-top: 1.5rem;">
                            <label class="checkbox-label">
                                <input type="checkbox" name="remove_from_squads" value="1">
                                <span>Automatically remove from current squads</span>
                            </label>
                        </div>

                    </form>
                </div>
                
                <!-- Drawer Footer (Buttons) -->
                <div class="drawer-footer">
                    <button type="button" class="btn btn-outline">Cancel</button>
                    <!-- Triggers form submission -->
                    <button type="submit" form="injuryForm" class="btn btn-success">Log Injury</button>
                </div>
            </div>

        </div> <!-- End side-drawer-container -->
    </div> <!-- End container -->

    <footer class="app-footer">
        Minimal footer &copy; <?= date('Y') ?> USMS
    </footer>

</body>
</html>
