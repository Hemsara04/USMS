<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errorMsg = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - UOC Sports Management</title>
    <!-- Link to global styles and auth-specific styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <style>
        .alert-box {
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .alert-error {
            background-color: #FEE2E2;
            color: #DC2626;
            border: 1px solid #FECACA;
        }
    </style>
</head>
<body>

    <!-- Main Wrapper for Split Screen Design -->
    <div class="auth-wrapper">
        
        <!-- Left Sidebar (Branding & Image) -->
        <div class="auth-sidebar">
            <div class="auth-sidebar-bg"></div>
            <div class="auth-sidebar-content">
                <h1 class="auth-sidebar-title">JOIN THE<br>TEAM</h1>
                <p class="auth-sidebar-subtitle">University of Colombo Sports Portal</p>
            </div>
        </div>

        <!-- Right Side (Registration Form) -->
        <div class="auth-main">
            <div class="auth-card" style="max-width: 500px;">
                
                <div class="auth-header" style="margin-bottom: 1.5rem;">
                    <h2 class="auth-title">Create Account</h2>
                    <p class="auth-subtitle">Register for the sports portal</p>
                </div>

                <!-- Flash Message Alerts -->
                <?php if (!empty($errorMsg)): ?>
                    <div class="alert-box alert-error">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <span><?= htmlspecialchars($errorMsg) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Registration Form -->
                <form action="../controllers/AuthController.php?action=register" method="POST">
                    
                    <!-- Full Name -->
                    <div class="auth-form-group" style="margin-bottom: 1rem;">
                        <label class="auth-form-label" for="fullname">Full Name</label>
                        <div class="auth-input-wrapper">
                            <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <input type="text" id="fullname" name="fullname" class="auth-input" placeholder="e.g. Kasun Perera" required>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="auth-form-group" style="margin-bottom: 1rem;">
                        <label class="auth-form-label" for="email">University Email</label>
                        <div class="auth-input-wrapper">
                            <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            <input type="email" id="email" name="email" class="auth-input" placeholder="student@uoc.ac.lk" required>
                        </div>
                    </div>

                    <!-- Role Dropdown -->
                    <div class="auth-form-group" style="margin-bottom: 1rem;">
                        <label class="auth-form-label" for="role">Registering As</label>
                        <div class="auth-input-wrapper">
                            <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                            <select id="role" name="role" class="auth-input" required>
                                <option value="" disabled selected>Select Role</option>
                                <option value="athlete">Athlete</option>
                                <option value="coach">Coach</option>
                                <option value="tic">TIC (Teacher in Charge)</option>
                                <option value="medical_officer">Medical Officer</option>
                                <option value="president">President (Sports Council)</option>
                            </select>
                        </div>
                    </div>

                    <!-- 2-Column Grid for Passwords -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <!-- Password Field -->
                        <div class="auth-form-group" style="margin-bottom: 0;">
                            <label class="auth-form-label" for="password">Password</label>
                            <div class="auth-input-wrapper">
                                <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                <input type="password" id="password" name="password" class="auth-input" placeholder="••••••••" required>
                            </div>
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="auth-form-group" style="margin-bottom: 0;">
                            <label class="auth-form-label" for="confirm_password">Confirm Password</label>
                            <div class="auth-input-wrapper">
                                <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                <input type="password" id="confirm_password" name="confirm_password" class="auth-input" placeholder="••••••••" required>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary auth-btn">Create Account</button>
                    
                </form>

                <!-- Footer Link to Login -->
                <div class="auth-footer">
                    Already have an account? <a href="login.php" class="auth-link">Log In here</a>
                </div>

            </div>
        </div>

    </div>

</body>
</html>
