<?php
/**
 * Auth Controller
 * Handles Login, Registration, and Logout flows
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Handle Login POST request
     */
    public function handleLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/login.php');
            exit;
        }

        $identifier = trim($_POST['username'] ?? '');
        $password   = $_POST['password'] ?? '';
        $selectedRole = trim($_POST['role'] ?? '');

        // Validation
        if (empty($identifier) || empty($password)) {
            $_SESSION['error'] = 'Please enter both username/email and password.';
            header('Location: ../views/login.php');
            exit;
        }

        $user = $this->userModel->findByEmailOrUsername($identifier);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION['error'] = 'Invalid username/email or password.';
            header('Location: ../views/login.php');
            exit;
        }

        // Optional check if selected role matches DB role
        if (!empty($selectedRole) && strtolower($selectedRole) !== strtolower($user['role'])) {
            // If they picked TIC but account is Medical_Officer or vice-versa
            $_SESSION['error'] = 'Role mismatch: Your account role is ' . htmlspecialchars($user['role']) . '.';
            header('Location: ../views/login.php');
            exit;
        }

        // Establish Session
        $_SESSION['user_id']  = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email']    = $user['email'];
        $_SESSION['role']     = $user['role'];

        // Role-based Redirection
        switch ($user['role']) {
            case 'Athlete':
                header('Location: ../views/athletic_regestration.php');
                exit;

            case 'TIC':
                header('Location: ../views/tic_equipment_requests.php');
                exit;

            case 'Medical_Officer':
                header('Location: ../views/med_athlete_roster.php');
                exit;

            case 'President':
                header('Location: ../views/pres_announcements.php');
                exit;

            case 'Coach':
                header('Location: ../views/coach_training_sessions.php');
                exit;

            default:
                // For other roles or admin
                header('Location: ../views/login.php?info=role_landing');
                exit;
        }
    }

    /**
     * Handle Registration POST request
     */
    public function handleRegister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/register.php');
            exit;
        }

        $fullname          = trim($_POST['fullname'] ?? '');
        $email             = trim($_POST['email'] ?? '');
        $index_number      = strtoupper(trim($_POST['index_number'] ?? ''));
        $faculty           = trim($_POST['faculty'] ?? '');
        $academic_year     = max(1, min(4, (int)($_POST['academic_year'] ?? 1)));
        $phone             = trim($_POST['phone'] ?? '+94 11 258 1835');
        $role              = trim($_POST['role'] ?? 'athlete');
        $security_question = trim($_POST['security_question'] ?? 'What is your favorite sport?');
        $security_answer   = trim($_POST['security_answer'] ?? '');
        $password          = $_POST['password'] ?? '';
        $confirm_password  = $_POST['confirm_password'] ?? '';

        // Helper to preserve input on validation error
        $preserveInput = function() use ($fullname, $email, $index_number, $faculty, $academic_year, $phone, $role, $security_question, $security_answer) {
            $_SESSION['old_input'] = [
                'fullname'          => $fullname,
                'email'             => $email,
                'index_number'      => $index_number,
                'faculty'           => $faculty,
                'academic_year'     => $academic_year,
                'phone'             => $phone,
                'role'              => $role,
                'security_question' => $security_question,
                'security_answer'   => $security_answer
            ];
        };

        // Validation: Required fields
        if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password) || empty($security_answer)) {
            $_SESSION['error'] = 'All required fields, including the security question, must be filled.';
            $preserveInput();
            header('Location: ../views/register.php');
            exit;
        }

        // Student validation: index number and faculty are required for athlete registration
        if (strtolower($role) === 'athlete' || empty($role)) {
            if (empty($index_number) || empty($faculty)) {
                $_SESSION['error'] = 'Student Index Number and Faculty are required for student registration.';
                $preserveInput();
                header('Location: ../views/register.php');
                exit;
            }
        }

        // Validation: Must be a valid email and end with @uoc.ac.lk
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with(strtolower($email), '@uoc.ac.lk')) {
            $_SESSION['error'] = 'Invalid email domain. Only University of Colombo emails (@uoc.ac.lk) are allowed.';
            $preserveInput();
            header('Location: ../views/register.php');
            exit;
        }

        // Validation: Password length minimum 8 characters
        if (strlen($password) < 8) {
            $_SESSION['error'] = 'Password must be at least 8 characters long.';
            $preserveInput();
            header('Location: ../views/register.php');
            exit;
        }

        // Validation: Passwords match
        if ($password !== $confirm_password) {
            $_SESSION['error'] = 'Passwords do not match.';
            $preserveInput();
            header('Location: ../views/register.php');
            exit;
        }

        // Check if email already registered
        if ($this->userModel->emailExists($email)) {
            $_SESSION['error'] = 'An account with this email address is already registered.';
            $preserveInput();
            header('Location: ../views/register.php');
            exit;
        }

        // Check if student index number already registered
        if (!empty($index_number) && $this->userModel->indexExists($index_number)) {
            $_SESSION['error'] = "A student profile with Index Number '{$index_number}' is already registered.";
            $preserveInput();
            header('Location: ../views/register.php');
            exit;
        }

        // Generate username from full name or email prefix
        $username = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $fullname)));
        if (empty($username)) {
            $username = explode('@', $email)[0];
        }

        // Ensure unique username
        $baseUsername = $username;
        $counter = 1;
        while ($this->userModel->usernameExists($username)) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        // Map role to exact DB Enum
        $validRoles = [
            'athlete'         => 'Athlete',
            'coach'           => 'Coach',
            'tic'             => 'TIC',
            'medical'         => 'Medical_Officer',
            'medical_officer' => 'Medical_Officer',
            'president'       => 'President',
            'admin'           => 'System_Admin'
        ];
        $dbRole = $validRoles[strtolower($role)] ?? 'Athlete';

        // Create User & Athletic record atomically if Athlete
        if ($dbRole === 'Athlete') {
            $created = $this->userModel->createAthlete($username, $email, $phone, $password, $fullname, $index_number, $faculty, $academic_year, $security_question, $security_answer);
        } else {
            $created = $this->userModel->create($username, $email, $password, $dbRole, $security_question, $security_answer, $phone);
        }

        if ($created) {
            unset($_SESSION['old_input']);
            $_SESSION['success'] = 'Student profile registered successfully! You can now log in.';
            header('Location: ../views/login.php');
            exit;
        } else {
            $_SESSION['error'] = 'Failed to register account. Please try again.';
            $preserveInput();
            header('Location: ../views/register.php');
            exit;
        }
    }

    /**
     * Handle Forgot / Reset Password POST request
     */
    public function handleForgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/forgot_password.php');
            exit;
        }

        $email            = trim($_POST['email'] ?? '');
        $security_answer  = trim($_POST['security_answer'] ?? '');
        $new_password     = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Helper to preserve email on error
        $preserveEmail = function() use ($email) {
            $_SESSION['old_fp_email'] = $email;
        };

        // Validation: Required fields
        if (empty($email) || empty($security_answer) || empty($new_password) || empty($confirm_password)) {
            $_SESSION['error'] = 'Please fill in all required fields.';
            $preserveEmail();
            header('Location: ../views/forgot_password.php');
            exit;
        }

        // Validate @uoc.ac.lk domain
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with(strtolower($email), '@uoc.ac.lk')) {
            $_SESSION['error'] = 'Please enter a valid University of Colombo email (@uoc.ac.lk).';
            $preserveEmail();
            header('Location: ../views/forgot_password.php');
            exit;
        }

        // Validate password match
        if ($new_password !== $confirm_password) {
            $_SESSION['error'] = 'New passwords do not match.';
            $preserveEmail();
            header('Location: ../views/forgot_password.php');
            exit;
        }

        // Validate password length 8+
        if (strlen($new_password) < 8) {
            $_SESSION['error'] = 'New password must be at least 8 characters long.';
            $preserveEmail();
            header('Location: ../views/forgot_password.php');
            exit;
        }

        // Find user by email
        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            $_SESSION['error'] = 'No account found with this university email address.';
            $preserveEmail();
            header('Location: ../views/forgot_password.php');
            exit;
        }

        // Verify security answer using single standard bcrypt method
        if (!$this->userModel->verifySecurityAnswer($user, $security_answer)) {
            $_SESSION['error'] = 'Incorrect security answer. Please try again.';
            $preserveEmail();
            header('Location: ../views/forgot_password.php');
            exit;
        }

        // Update password with bcrypt hash
        $updated = $this->userModel->updatePassword($user['user_id'], $new_password);

        if ($updated) {
            unset($_SESSION['old_fp_email']);
            $_SESSION['success'] = 'Password reset successfully! You can now log in with your new password.';
            header('Location: ../views/login.php');
            exit;
        } else {
            $_SESSION['error'] = 'Failed to update password. Please try again.';
            $preserveEmail();
            header('Location: ../views/forgot_password.php');
            exit;
        }
    }

    /**
     * Handle Logout
     */
    public function handleLogout() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        header('Location: ../views/login.php?logged_out=1');
        exit;
    }

    /**
     * Handle Profile Information Update (Email & Phone)
     */
    public function handleUpdateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/profile.php');
            exit;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: ../views/login.php');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone_number'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Please provide a valid official email address.';
            header('Location: ../views/profile.php');
            exit;
        }

        if (empty($phone)) {
            $_SESSION['flash_error'] = 'Contact phone number cannot be blank.';
            header('Location: ../views/profile.php');
            exit;
        }

        try {
            $this->userModel->updateProfile($userId, $email, $phone);
            $_SESSION['email'] = $email;
            $_SESSION['flash_success'] = 'Your profile details have been successfully updated!';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Failed to update profile: ' . $e->getMessage();
        }

        header('Location: ../views/profile.php');
        exit;
    }

    /**
     * Handle Password Change from User Profile
     */
    public function handleChangePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/profile.php');
            exit;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: ../views/login.php');
            exit;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['flash_error'] = 'All password fields are required.';
            header('Location: ../views/profile.php#security');
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['flash_error'] = 'New password and confirmation do not match.';
            header('Location: ../views/profile.php#security');
            exit;
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['flash_error'] = 'New password must be at least 6 characters long.';
            header('Location: ../views/profile.php#security');
            exit;
        }

        // Verify current password
        if (!$this->userModel->verifyPassword($userId, $currentPassword)) {
            $_SESSION['flash_error'] = 'Your current password was entered incorrectly.';
            header('Location: ../views/profile.php#security');
            exit;
        }

        try {
            $this->userModel->updatePassword($userId, $newPassword);
            $_SESSION['flash_success'] = 'Password changed successfully! Keep your credentials secure.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Failed to update password: ' . $e->getMessage();
        }

        header('Location: ../views/profile.php#security');
        exit;
    }
}

// Route request to appropriate controller action
$authController = new AuthController();
$action = $_GET['action'] ?? '';

if ($action === 'login') {
    $authController->handleLogin();
} elseif ($action === 'register') {
    $authController->handleRegister();
} elseif ($action === 'forgot_password') {
    $authController->handleForgotPassword();
} elseif ($action === 'logout') {
    $authController->handleLogout();
} elseif ($action === 'update_profile') {
    $authController->handleUpdateProfile();
} elseif ($action === 'change_password') {
    $authController->handleChangePassword();
}

