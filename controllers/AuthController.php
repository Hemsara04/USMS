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
            case 'TIC':
                header('Location: ../views/tic_equipment_requests.php');
                exit;

            case 'Medical_Officer':
                header('Location: ../views/med_athlete_roster.php');
                exit;

            case 'President':
                header('Location: ../views/pres_announcements.php');
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

        $fullname         = trim($_POST['fullname'] ?? '');
        $email            = trim($_POST['email'] ?? '');
        $role             = trim($_POST['role'] ?? '');
        $password         = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password)) {
            $_SESSION['error'] = 'All fields are required.';
            header('Location: ../views/register.php');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Please enter a valid university email address.';
            header('Location: ../views/register.php');
            exit;
        }

        if ($password !== $confirm_password) {
            $_SESSION['error'] = 'Passwords do not match.';
            header('Location: ../views/register.php');
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Password must be at least 6 characters long.';
            header('Location: ../views/register.php');
            exit;
        }

        // Check if email already registered
        if ($this->userModel->emailExists($email)) {
            $_SESSION['error'] = 'An account with this email already exists.';
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

        // Create User
        $created = $this->userModel->create($username, $email, $password, $dbRole);

        if ($created) {
            $_SESSION['success'] = 'Account registered successfully! Please log in with your credentials.';
            header('Location: ../views/login.php');
            exit;
        } else {
            $_SESSION['error'] = 'Failed to register account. Please try again.';
            header('Location: ../views/register.php');
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
}

// Route request to appropriate controller action
$authController = new AuthController();
$action = $_GET['action'] ?? '';

if ($action === 'login') {
    $authController->handleLogin();
} elseif ($action === 'register') {
    $authController->handleRegister();
} elseif ($action === 'logout') {
    $authController->handleLogout();
}
