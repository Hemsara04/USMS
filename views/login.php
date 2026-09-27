<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errorMsg   = $_SESSION['error'] ?? '';
$successMsg = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UOC Sports Management</title>
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
                <h1 class="auth-sidebar-title">UOC SPORTS<br>MANAGEMENT</h1>
                <p class="auth-sidebar-subtitle">University of Colombo Sports Portal</p>
            </div>
        </div>

        <!-- Right Side (Login Form) -->
        <div class="auth-main">
            <div class="auth-card">
                
                <div class="auth-header">
                    <h2 class="auth-title">Unified Login Portal</h2>
                    <p class="auth-subtitle">Access your sports account</p>
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

                <!-- Login Form -->
                <form action="../controllers/AuthController.php?action=login" method="POST">
                    
                    <!-- Username / Email Field -->
                    <div class="auth-form-group">
                        <label class="auth-form-label" for="username">Email / Username</label>
                        <div class="auth-input-wrapper">
                            <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <input type="text" id="username" name="username" class="auth-input" placeholder="Email or Username" required>
                        </div>
                    </div>

                    <!-- Password Field with Show/Hide Toggle -->
                    <div class="auth-form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                            <label class="auth-form-label" for="password" style="margin-bottom: 0;">Password</label>
                            <a href="forgot_password.php" class="auth-link" style="font-size: 0.8rem; font-weight: 500;">Forgot Password?</a>
                        </div>
                        <div class="auth-input-wrapper">
                            <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            <input type="password" id="password" name="password" class="auth-input has-toggle" placeholder="••••••••" required>
                            <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password', this)" aria-label="Show password" title="Toggle password visibility">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Role Dropdown -->
                    <div class="auth-form-group">
                        <label class="auth-form-label" for="role">Select your role</label>
                        <div class="auth-input-wrapper">
                            <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                            <select id="role" name="role" class="auth-input">
                                <option value="" selected>Auto Detect from Account</option>
                                <option value="tic">TIC (Teacher in Charge)</option>
                                <option value="medical_officer">Medical Officer</option>
                                <option value="president">President (Sports Council)</option>
                                <option value="athlete">Athlete</option>
                                <option value="coach">Coach</option>
                                <option value="admin">System Admin</option>
                            </select>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-success auth-btn">Log In</button>
                    
                </form>

                <!-- Footer Link to Registration -->
                <div class="auth-footer">
                    Don't have an account? <a href="register.php" id="register-link" class="auth-link">Register here</a>
                </div>

            </div>
        </div>

    </div>

    <!-- Password visibility toggle script and Client-side Logical Guards -->
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

    // Role-based Registration Link Update & Logical Form Validation Guard
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        const registerLink = document.getElementById('register-link');
        const loginForm = document.querySelector('form[action*="login"]');
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        
        if (roleSelect && registerLink) {
            roleSelect.addEventListener('change', function() {
                if (this.value === 'athlete') {
                    registerLink.href = 'athletic_regestration.php';
                } else {
                    registerLink.href = 'register.php';
                }
            });
            roleSelect.dispatchEvent(new Event('change'));
        }

        // Logical Client-side Guard: Block invalid submissions before reaching backend/DB
        if (loginForm && usernameInput && passwordInput) {
            loginForm.addEventListener('submit', function(e) {
                const val = usernameInput.value.trim();
                const pw = passwordInput.value;
                let clientError = '';

                if (!val) {
                    clientError = 'Please enter your username or email address.';
                } else if (val.includes('@')) {
                    // Check if actually a valid email structure
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(val)) {
                        clientError = 'Please enter a valid email address (e.g. name@domain.com).';
                    }
                } else if (val.length < 3) {
                    clientError = 'Username must be at least 3 characters long.';
                } else if (!/^[a-zA-Z0-9_.-]+$/.test(val)) {
                    clientError = 'Username contains invalid characters.';
                }

                if (!clientError && pw.length < 4) {
                    clientError = 'Password must be at least 4 characters long.';
                }

                if (clientError) {
                    e.preventDefault();
                    // Remove existing client alert if any
                    let existingAlert = document.getElementById('client-login-alert');
                    if (!existingAlert) {
                        existingAlert = document.createElement('div');
                        existingAlert.id = 'client-login-alert';
                        existingAlert.className = 'alert-box alert-error';
                        existingAlert.style.marginBottom = '1rem';
                        loginForm.parentNode.insertBefore(existingAlert, loginForm);
                    }
                    existingAlert.innerHTML = `
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <span>${clientError}</span>
                    `;
                    usernameInput.focus();
                }
            });
        }
    });
    </script>
</body>
</html>
