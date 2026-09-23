<?php
/**
 * Admin Model
 * Handles administrative queries and Full User Management CRUD
 */

require_once __DIR__ . '/../config/db.php';

class Admin {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Get summary metrics for admin dashboard cards
     */
    public function getStats() {
        $stats = [
            'total_users'       => 0,
            'active_athletes'   => 0,
            'pending_approvals' => 0,
            'staff_accounts'    => 0,
        ];

        try {
            $stmt = $this->db->query("
                SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN role = 'Athlete' THEN 1 ELSE 0 END) AS athletes,
                    SUM(CASE WHEN role IN ('Coach', 'TIC', 'Medical_Officer', 'President', 'System_Admin', 'Physiotherapist', 'Facility_Manager') THEN 1 ELSE 0 END) AS staff
                FROM `user`
            ");
            $row = $stmt->fetch();
            if ($row) {
                $stats['total_users']     = (int)$row['total'];
                $stats['active_athletes'] = (int)$row['athletes'];
                $stats['staff_accounts']  = (int)$row['staff'];
            }

            // Check pending applications from sport_application table if available
            $appStmt = $this->db->query("SELECT COUNT(*) FROM `sport_application` WHERE `status` = 'Pending'");
            $stats['pending_approvals'] = (int)$appStmt->fetchColumn();
        } catch (Exception $e) {
            // Keep default values
        }

        return $stats;
    }

    /**
     * Retrieve pending applicant registrations for the verification queue
     */
    public function getPendingVerifications($limit = 6) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    sa.application_id,
                    sa.athletic_id,
                    sa.sport_id,
                    sa.status,
                    sa.applied_date,
                    a.full_name,
                    a.index_number,
                    a.faculty,
                    s.sport_name
                FROM `sport_application` sa
                JOIN `Athletic` a ON sa.athletic_id = a.athlete_id
                JOIN `sports` s ON sa.sport_id = s.sport_id
                WHERE sa.status = 'Pending'
                ORDER BY sa.application_id DESC
                LIMIT :lim
            ");
            $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Retrieve all users with optional role filtering and keyword search
     */
    public function getAllUsers($filterRole = '', $search = '') {
        $sql = "
            SELECT 
                u.user_id,
                u.username,
                u.email,
                u.phone_number,
                u.role,
                a.full_name AS athlete_name,
                a.index_number,
                a.faculty
            FROM `user` u
            LEFT JOIN `Athletic` a ON u.user_id = a.user_id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filterRole)) {
            $sql .= " AND u.role = :role";
            $params[':role'] = $filterRole;
        }

        if (!empty($search)) {
            $sql .= " AND (u.username LIKE :q OR u.email LIKE :q OR u.phone_number LIKE :q OR a.full_name LIKE :q OR a.index_number LIKE :q)";
            $params[':q'] = '%' . trim($search) . '%';
        }

        $sql .= " ORDER BY u.user_id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find single user with details by ID
     */
    public function getUserById($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                u.user_id,
                u.username,
                u.email,
                u.phone_number,
                u.role,
                a.athlete_id,
                a.full_name AS athlete_name,
                a.index_number,
                a.faculty,
                a.academic_year
            FROM `user` u
            LEFT JOIN `Athletic` a ON u.user_id = a.user_id
            WHERE u.user_id = :uid
            LIMIT 1
        ");
        $stmt->execute([':uid' => (int)$userId]);
        return $stmt->fetch();
    }

    /**
     * Check if username exists (excluding specific user ID if editing)
     */
    public function usernameExists($username, $excludeUserId = null) {
        $sql = "SELECT COUNT(*) FROM `user` WHERE `username` = :username";
        $params = [':username' => trim($username)];
        if ($excludeUserId) {
            $sql .= " AND `user_id` != :uid";
            $params[':uid'] = (int)$excludeUserId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Check if email exists (excluding specific user ID if editing)
     */
    public function emailExists($email, $excludeUserId = null) {
        $sql = "SELECT COUNT(*) FROM `user` WHERE `email` = :email";
        $params = [':email' => trim($email)];
        if ($excludeUserId) {
            $sql .= " AND `user_id` != :uid";
            $params[':uid'] = (int)$excludeUserId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Create a new user (CRUD: CREATE)
     */
    public function createUser($data) {
        $username = trim($data['username'] ?? '');
        $email    = trim($data['email'] ?? '');
        $role     = trim($data['role'] ?? 'Coach');
        $phone    = trim($data['phone_number'] ?? '+94 11 258 1835');
        $password = $data['password'] ?? 'password123';

        if (empty($username) || empty($email) || empty($password)) {
            throw new Exception("Username, email, and password are required.");
        }

        if ($this->usernameExists($username)) {
            throw new Exception("The username '{$username}' is already taken.");
        }

        if ($this->emailExists($email)) {
            throw new Exception("The email '{$email}' is already registered.");
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $securityQuestion = $data['security_question'] ?? 'What is your primary university campus?';
        $securityAnswerHash = password_hash(strtolower(trim($data['security_answer'] ?? 'colombo')), PASSWORD_BCRYPT);

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO `user` (`username`, `email`, `phone_number`, `password_hash`, `role`, `security_question`, `security_answere_hash`)
                VALUES (:username, :email, :phone, :password_hash, :role, :sq, :sa)
            ");
            $stmt->execute([
                ':username'      => $username,
                ':email'         => $email,
                ':phone'         => $phone,
                ':password_hash' => $passwordHash,
                ':role'          => $role,
                ':sq'            => $securityQuestion,
                ':sa'            => $securityAnswerHash
            ]);

            $newUserId = (int)$this->db->lastInsertId();

            // If registering an athlete, create linked Athletic profile
            if ($role === 'Athlete') {
                $fullName     = trim($data['full_name'] ?? ucwords(str_replace('_', ' ', $username)));
                $indexNumber  = trim($data['index_number'] ?? ('S-' . str_pad($newUserId, 4, '0', STR_PAD_LEFT)));
                $faculty      = trim($data['faculty'] ?? 'Science');
                $academicYear = (int)($data['academic_year'] ?? 1);

                $stmtAth = $this->db->prepare("
                    INSERT INTO `Athletic` (`user_id`, `full_name`, `index_number`, `faculty`, `academic_year`)
                    VALUES (:uid, :name, :idx, :fac, :yr)
                ");
                $stmtAth->execute([
                    ':uid'  => $newUserId,
                    ':name' => $fullName,
                    ':idx'  => $indexNumber,
                    ':fac'  => $faculty,
                    ':yr'   => $academicYear
                ]);
            }

            $this->db->commit();
            return $newUserId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Update user details (CRUD: UPDATE)
     */
    public function updateUser($userId, $data) {
        $userId   = (int)$userId;
        $username = trim($data['username'] ?? '');
        $email    = trim($data['email'] ?? '');
        $role     = trim($data['role'] ?? '');
        $phone    = trim($data['phone_number'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($username) || empty($email) || empty($role)) {
            throw new Exception("Username, email, and role cannot be empty.");
        }

        if ($this->usernameExists($username, $userId)) {
            throw new Exception("The username '{$username}' is already in use by another user.");
        }

        if ($this->emailExists($email, $userId)) {
            throw new Exception("The email '{$email}' is already in use by another user.");
        }

        $this->db->beginTransaction();
        try {
            $params = [
                ':uid'      => $userId,
                ':username' => $username,
                ':email'    => $email,
                ':phone'    => $phone,
                ':role'     => $role
            ];

            $pwSql = "";
            if (!empty($password)) {
                $pwSql = ", `password_hash` = :password_hash";
                $params[':password_hash'] = password_hash($password, PASSWORD_BCRYPT);
            }

            $stmt = $this->db->prepare("
                UPDATE `user` 
                SET `username` = :username,
                    `email` = :email,
                    `phone_number` = :phone,
                    `role` = :role
                    {$pwSql}
                WHERE `user_id` = :uid
            ");
            $stmt->execute($params);

            // Update or create linked athlete record if role is Athlete
            if ($role === 'Athlete') {
                $fullName    = trim($data['full_name'] ?? '');
                $indexNumber = trim($data['index_number'] ?? '');
                $faculty     = trim($data['faculty'] ?? 'Science');

                $chk = $this->db->prepare("SELECT athlete_id FROM `Athletic` WHERE `user_id` = :uid LIMIT 1");
                $chk->execute([':uid' => $userId]);
                $athId = $chk->fetchColumn();

                if ($athId) {
                    $upAth = $this->db->prepare("
                        UPDATE `Athletic` 
                        SET `full_name` = :name, `index_number` = :idx, `faculty` = :fac
                        WHERE `athlete_id` = :aid
                    ");
                    $upAth->execute([
                        ':name' => $fullName ?: ucwords(str_replace('_', ' ', $username)),
                        ':idx'  => $indexNumber ?: ('S-' . str_pad($userId, 4, '0', STR_PAD_LEFT)),
                        ':fac'  => $faculty,
                        ':aid'  => $athId
                    ]);
                } else {
                    $inAth = $this->db->prepare("
                        INSERT INTO `Athletic` (`user_id`, `full_name`, `index_number`, `faculty`, `academic_year`)
                        VALUES (:uid, :name, :idx, :fac, 1)
                    ");
                    $inAth->execute([
                        ':uid'  => $userId,
                        ':name' => $fullName ?: ucwords(str_replace('_', ' ', $username)),
                        ':idx'  => $indexNumber ?: ('S-' . str_pad($userId, 4, '0', STR_PAD_LEFT)),
                        ':fac'  => $faculty
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Delete user (CRUD: DELETE)
     */
    public function deleteUser($userId, $currentAdminId) {
        $userId = (int)$userId;
        $currentAdminId = (int)$currentAdminId;

        if ($userId === $currentAdminId) {
            throw new Exception("You cannot delete your own logged-in administrator account.");
        }

        // Safety check for dependent records in critical institutional tables
        $annCheck = $this->db->prepare("SELECT COUNT(*) FROM `announcements` WHERE `published_by_user_id` = :uid");
        $annCheck->execute([':uid' => $userId]);
        if ((int)$annCheck->fetchColumn() > 0) {
            throw new Exception("Cannot delete user: This user has official university announcements linked to their account.");
        }

        $athCheck = $this->db->prepare("
            SELECT a.athlete_id,
                   (SELECT COUNT(*) FROM `match_squads` ms WHERE ms.athlete_id = a.athlete_id) AS squad_count,
                   (SELECT COUNT(*) FROM `training_attendance` ta WHERE ta.athlete_id = a.athlete_id) AS att_count
            FROM `Athletic` a WHERE a.user_id = :uid LIMIT 1
        ");
        $athCheck->execute([':uid' => $userId]);
        $athRow = $athCheck->fetch();

        if ($athRow && ((int)$athRow['squad_count'] > 0 || (int)$athRow['att_count'] > 0)) {
            throw new Exception("Cannot delete user: Athlete has active training attendance or tournament squad selections.");
        }

        $this->db->beginTransaction();
        try {
            // Delete notifications
            $delNotifs = $this->db->prepare("DELETE FROM `notfications` WHERE `user_id` = :uid");
            $delNotifs->execute([':uid' => $userId]);

            // Delete Athletic profile if exists
            $delAth = $this->db->prepare("DELETE FROM `Athletic` WHERE `user_id` = :uid");
            $delAth->execute([':uid' => $userId]);

            // Delete user record
            $delUser = $this->db->prepare("DELETE FROM `user` WHERE `user_id` = :uid");
            $delUser->execute([':uid' => $userId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Process verification queue application
     */
    public function updateApplicationStatus($applicationId, $status) {
        $allowed = ['Approved', 'Rejected'];
        if (!in_array($status, $allowed)) {
            throw new Exception("Invalid status specified.");
        }

        $stmt = $this->db->prepare("
            UPDATE `sport_application` 
            SET `status` = :status 
            WHERE `application_id` = :aid
        ");
        return $stmt->execute([
            ':status' => $status,
            ':aid'    => (int)$applicationId
        ]);
    }
}
