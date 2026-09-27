<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errorMsg   = $_SESSION['error'] ?? '';
$oldInput   = $_SESSION['old_input'] ?? [];
unset($_SESSION['error'], $_SESSION['old_input']);

$selectedRole = $oldInput['role'] ?? '';
$selectedSecQ = $oldInput['security_question'] ?? 'What is your favorite sport?';

$securityQuestions = [
    'What is your favorite sport?',
    'What was the name of your first school?',
    'What is your birthplace / hometown?',
    'What is your mother\'s maiden name?',
    'What was your childhood nickname?'
];
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
            background-color: #FFFBEB;
            color: #B45309;
            border: 1px solid #FDE68A;
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
        <div class="auth-main" style="align-items: flex-start; padding: 2rem 1.5rem;">
            <div class="auth-card" style="max-width: 530px; margin: 0.5rem auto 2.5rem auto;">
                
                <div class="auth-header" style="margin-bottom: 1.15rem;">
                    <h2 class="auth-title" style="font-size: 1.65rem;">Create Account</h2>
                    <p class="auth-subtitle">Join the University of Colombo Sports Community</p>
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
                    <input type="hidden" name="role" value="athlete">
                    
                    <!-- Full Name -->
                    <div class="auth-form-group" style="margin-bottom: 0.9rem;">
                        <label class="auth-form-label" for="fullname">Full Name</label>
                        <div class="auth-input-wrapper">
                            <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <input type="text" id="fullname" name="fullname" class="auth-input" placeholder="e.g. Kasun Perera" value="<?= htmlspecialchars($oldInput['fullname'] ?? '') ?>" required>
                        </div>
                    </div>

                    <!-- Email Address (Accepts university or personal email) -->
                    <div class="auth-form-group" style="margin-bottom: 0.9rem;">
                        <label class="auth-form-label" for="email">Email Address</label>
                        <div class="auth-input-wrapper">
                            <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            <input type="email" id="email" name="email" class="auth-input" placeholder="e.g. student@uoc.ac.lk or name@example.com" value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>" required>
                        </div>
                        <div class="auth-hint">Enter your official or personal email address</div>
                    </div>

                    <!-- Student Details: Index Number & Faculty -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; margin-bottom: 0.9rem;">
                        <!-- Index Number -->
                        <div class="auth-form-group" style="margin-bottom: 0;">
                            <label class="auth-form-label" for="index_number">Student Index Number</label>
                            <div class="auth-input-wrapper">
                                <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                                <input type="text" id="index_number" name="index_number" class="auth-input" placeholder="e.g. SC/2023/1024" value="<?= htmlspecialchars($oldInput['index_number'] ?? '') ?>" required>
                            </div>
                            <div class="auth-hint">e.g. SC/2023/1024</div>
                        </div>

                        <!-- Faculty -->
                        <div class="auth-form-group" style="margin-bottom: 0;">
                            <label class="auth-form-label" for="faculty">Faculty</label>
                            <div class="auth-input-wrapper">
                                <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
                                <select id="faculty" name="faculty" class="auth-input" required>
                                    <option value="" disabled <?= empty($oldInput['faculty']) ? 'selected' : '' ?>>Select Faculty</option>
                                    <option value="Faculty of Science" <?= (($oldInput['faculty'] ?? '') === 'Faculty of Science') ? 'selected' : '' ?>>Faculty of Science</option>
                                    <option value="Faculty of Arts" <?= (($oldInput['faculty'] ?? '') === 'Faculty of Arts') ? 'selected' : '' ?>>Faculty of Arts</option>
                                    <option value="Faculty of Medicine" <?= (($oldInput['faculty'] ?? '') === 'Faculty of Medicine') ? 'selected' : '' ?>>Faculty of Medicine</option>
                                    <option value="Faculty of Management" <?= (($oldInput['faculty'] ?? '') === 'Faculty of Management') ? 'selected' : '' ?>>Faculty of Management</option>
                                    <option value="Faculty of Technology" <?= (($oldInput['faculty'] ?? '') === 'Faculty of Technology') ? 'selected' : '' ?>>Faculty of Technology</option>
                                    <option value="Faculty of Law" <?= (($oldInput['faculty'] ?? '') === 'Faculty of Law') ? 'selected' : '' ?>>Faculty of Law</option>
                                    <option value="UCSC" <?= (($oldInput['faculty'] ?? '') === 'UCSC') ? 'selected' : '' ?>>UCSC (School of Computing)</option>
                                    <option value="Faculty of Education" <?= (($oldInput['faculty'] ?? '') === 'Faculty of Education') ? 'selected' : '' ?>>Faculty of Education</option>
                                </select>
                            </div>
                            <div class="auth-hint">UOC Faculty</div>
                        </div>
                    </div>

                    <!-- Academic Year & Contact Phone -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; margin-bottom: 0.9rem;">
                        <!-- Academic Year -->
                        <div class="auth-form-group" style="margin-bottom: 0;">
                            <label class="auth-form-label" for="academic_year">Academic Year</label>
                            <div class="auth-input-wrapper">
                                <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                <select id="academic_year" name="academic_year" class="auth-input" required>
                                    <option value="1" <?= (($oldInput['academic_year'] ?? 1) == 1) ? 'selected' : '' ?>>1st Year</option>
                                    <option value="2" <?= (($oldInput['academic_year'] ?? 1) == 2) ? 'selected' : '' ?>>2nd Year</option>
                                    <option value="3" <?= (($oldInput['academic_year'] ?? 1) == 3) ? 'selected' : '' ?>>3rd Year</option>
                                    <option value="4" <?= (($oldInput['academic_year'] ?? 1) == 4) ? 'selected' : '' ?>>4th Year</option>
                                </select>
                            </div>
                            <div class="auth-hint">Year of Study</div>
                        </div>

                        <!-- Phone Number -->
                        <div class="auth-form-group" style="margin-bottom: 0;">
                            <label class="auth-form-label" for="phone">Contact Number</label>
                            <div class="auth-input-wrapper">
                                <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                <input type="tel" id="phone" name="phone" class="auth-input" placeholder="077 123 4567" value="<?= htmlspecialchars($oldInput['phone'] ?? '') ?>" required>
                            </div>
                            <div class="auth-hint">Mobile / Emergency Contact</div>
                        </div>
                    </div>

                    <!-- Security Question & Answer (For Password Recovery) -->
                    <div style="background: #F8FAFC; border: 1px solid var(--border-color); border-radius: var(--border-radius-lg); padding: 0.85rem; margin-bottom: 0.9rem;">
                        <div class="auth-form-group" style="margin-bottom: 0.6rem;">
                            <label class="auth-form-label" for="security_question">Security Question (Password Recovery)</label>
                            <div class="auth-input-wrapper">
                                <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                <select id="security_question" name="security_question" class="auth-input" style="background: white;" required>
                                    <?php foreach ($securityQuestions as $q): ?>
                                        <option value="<?= htmlspecialchars($q) ?>" <?= ($selectedSecQ === $q) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($q) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="auth-form-group" style="margin-bottom: 0;">
                            <label class="auth-form-label" for="security_answer">Your Secret Answer</label>
                            <div class="auth-input-wrapper">
                                <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-6.5 6.5l-3 3-1.5-1.5-3 3 1.5 1.5-3 3 1.5 1.5 4.5-4.5m10-10l-2 2"></path><circle cx="16.5" cy="7.5" r="3.5"></circle></svg>
                                <input type="text" id="security_answer" name="security_answer" class="auth-input" style="background: white;" placeholder="e.g. Football" value="<?= htmlspecialchars($oldInput['security_answer'] ?? '') ?>" required>
                            </div>
                            <div class="auth-hint">Used to verify your identity if you ever forget your password.</div>
                        </div>
                    </div>

                    <!-- 2-Column Grid for Passwords with Min 8 Chars & Eye Toggles -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; margin-bottom: 0.9rem;">
                        <!-- Password Field -->
                        <div class="auth-form-group" style="margin-bottom: 0;">
                            <label class="auth-form-label" for="password">Password (8+ chars)</label>
                            <div class="auth-input-wrapper">
                                <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                <input type="password" id="password" name="password" class="auth-input has-toggle" placeholder="••••••••" minlength="8" required>
                                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password', this)" aria-label="Show password">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                            </div>
                            <div class="auth-hint">Min 8 characters</div>
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="auth-form-group" style="margin-bottom: 0;">
                            <label class="auth-form-label" for="confirm_password">Confirm Password</label>
                            <div class="auth-input-wrapper">
                                <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                <input type="password" id="confirm_password" name="confirm_password" class="auth-input has-toggle" placeholder="••••••••" minlength="8" required>
                                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('confirm_password', this)" aria-label="Show password">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                            </div>
                            <div class="auth-hint">Re-enter same password</div>
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

    <!-- Password visibility toggle script -->
    <script>
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        btn.innerHTML = isPassword
            ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>'
            : '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
        btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
    }
    </script>
</body>
</html>
