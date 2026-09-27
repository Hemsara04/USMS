<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errorMsg   = $_SESSION['error'] ?? '';
$successMsg = $_SESSION['success'] ?? '';
$oldEmail   = $_SESSION['old_fp_email'] ?? '';
unset($_SESSION['error'], $_SESSION['success'], $_SESSION['old_fp_email']);

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
    <title>Forgot Password - UOC Sports Management</title>
    <!-- Global & Auth styles -->
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
        .alert-success {
            background-color: #F0FDF4;
            color: #16A34A;
            border: 1px solid #BBF7D0;
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
                <h1 class="auth-sidebar-title">RECOVER<br>ACCOUNT</h1>
                <p class="auth-sidebar-subtitle">University of Colombo Sports Portal</p>
            </div>
        </div>

        <!-- Right Side (Forgot Password Form) -->
        <div class="auth-main">
            <div class="auth-card" style="max-width: 500px;">
                
                <div class="auth-header" style="margin-bottom: 1.25rem;">
                    <h2 class="auth-title">Reset Password</h2>
                    <p class="auth-subtitle">Verify your identity to set a new password</p>
                </div>

                <!-- Flash Message Alerts -->
                <?php if (!empty($errorMsg)): ?>
                    <div class="alert-box alert-error">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <span><?= htmlspecialchars($errorMsg) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($successMsg)): ?>
                    <div class="alert-box alert-success">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <span><?= htmlspecialchars($successMsg) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Reset Form -->
                <form action="../controllers/AuthController.php?action=forgot_password" method="POST">
                    
                    <!-- University Email -->
                    <div class="auth-form-group" style="margin-bottom: 0.9rem;">
                        <label class="auth-form-label" for="email">University Email (@uoc.ac.lk)</label>
                        <div class="auth-input-wrapper">
                            <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            <input type="email" id="email" name="email" class="auth-input" placeholder="student@uoc.ac.lk" pattern=".+@uoc\.ac\.lk" title="Must be a valid @uoc.ac.lk email" value="<?= htmlspecialchars($oldEmail) ?>" required>
                        </div>
                    </div>

                    <!-- Security Verification Section -->
                    <div style="background: #F8FAFC; border: 1px solid var(--border-color); border-radius: var(--border-radius-lg); padding: 0.85rem; margin-bottom: 0.9rem;">
                        <div class="auth-form-group" style="margin-bottom: 0.6rem;">
                            <label class="auth-form-label" for="security_question">Security Question</label>
                            <div class="auth-input-wrapper">
                                <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                <select id="security_question" name="security_question" class="auth-input" style="background: white;" required>
                                    <?php foreach ($securityQuestions as $q): ?>
                                        <option value="<?= htmlspecialchars($q) ?>"><?= htmlspecialchars($q) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="auth-form-group" style="margin-bottom: 0;">
                            <label class="auth-form-label" for="security_answer">Your Secret Answer</label>
                            <div class="auth-input-wrapper">
                                <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-6.5 6.5l-3 3-1.5-1.5-3 3 1.5 1.5-3 3 1.5 1.5 4.5-4.5m10-10l-2 2"></path><circle cx="16.5" cy="7.5" r="3.5"></circle></svg>
                                <input type="text" id="security_answer" name="security_answer" class="auth-input" style="background: white;" placeholder="Enter your secret answer" required>
                            </div>
                            <div class="auth-hint">Answer the security question you selected during registration.</div>
                        </div>
                    </div>

                    <!-- Passwords Grid (8+ Characters & Show/Hide Toggles) -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; margin-bottom: 0.9rem;">
                        <!-- New Password Field -->
                        <div class="auth-form-group" style="margin-bottom: 0;">
                            <label class="auth-form-label" for="new_password">New Password</label>
                            <div class="auth-input-wrapper">
                                <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                <input type="password" id="new_password" name="new_password" class="auth-input has-toggle" placeholder="••••••••" minlength="8" required>
                                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('new_password', this)" aria-label="Show password">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                            </div>
                            <div class="auth-hint">Min 8 characters</div>
                        </div>

                        <!-- Confirm New Password Field -->
                        <div class="auth-form-group" style="margin-bottom: 0;">
                            <label class="auth-form-label" for="confirm_password">Confirm Password</label>
                            <div class="auth-input-wrapper">
                                <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                <input type="password" id="confirm_password" name="confirm_password" class="auth-input has-toggle" placeholder="••••••••" minlength="8" required>
                                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('confirm_password', this)" aria-label="Show password">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                            </div>
                            <div class="auth-hint">Re-enter new password</div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary auth-btn">Reset Password</button>
                    
                </form>

                <!-- Footer Link to Login -->
                <div class="auth-footer">
                    Remember your password? <a href="login.php" class="auth-link">Back to Log In</a>
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
