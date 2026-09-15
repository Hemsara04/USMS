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
            background-color: #FEE2E2;
            color: #DC2626;
            border: 1px solid #FECACA;
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

                    <!-- Password Field -->
                    <div class="auth-form-group">
                        <label class="auth-form-label" for="password">Password</label>
                        <div class="auth-input-wrapper">
                            <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            <input type="password" id="password" name="password" class="auth-input" placeholder="••••••••" required>
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
                    Don't have an account? <a href="register.php" class="auth-link">Register here</a>
                </div>

            </div>
        </div>

    </div>

</body>
</html>
