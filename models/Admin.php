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
                    SUM(CASE WHEN role IN ('Coach', 'TIC', 'Medical_Officer', 'President', 'System_Admin') THEN 1 ELSE 0 END) AS staff
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
                a.athlete_id,
                a.full_name AS athlete_name,
                a.index_number,
                a.faculty,
                a.academic_year,
                a.height,
                a.weight,
                a.blood_group,
                a.emergency_Contact,
                a.medical_status
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
                a.academic_year,
                a.height,
                a.weight,
                a.blood_group,
                a.emergency_Contact,
                a.medical_status
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
     * Check if index number exists in Athletic table (excluding specific user ID if editing)
     */
    public function indexExists($indexNumber, $excludeUserId = null) {
        $sql = "SELECT COUNT(*) FROM `Athletic` WHERE `index_number` = :idx";
        $params = [':idx' => trim($indexNumber)];
        if ($excludeUserId) {
            $sql .= " AND `user_id` != :uid";
            $params[':uid'] = (int)$excludeUserId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * In-memory logical pre-validation guard
     * Validates business and data rules strictly BEFORE querying the database
     */
    public function validateUserPayload($data, $isUpdate = false) {
        $allowedRoles = ['System_Admin', 'Coach', 'Athlete', 'TIC', 'Medical_Officer', 'President'];
        
        $username = trim($data['username'] ?? '');
        $email    = trim($data['email'] ?? '');
        $role     = trim($data['role'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($username)) {
            throw new Exception("Username is required.");
        }
        if (strlen($username) < 3) {
            throw new Exception("Username must be at least 3 characters long.");
        }
        if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
            throw new Exception("Username contains invalid characters. Use letters, numbers, dots, dashes, or underscores.");
        }

        if (empty($email)) {
            throw new Exception("Email address is required.");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address format. Please enter a valid email address.");
        }

        if (empty($role) || !in_array($role, $allowedRoles, true)) {
            throw new Exception("Invalid role selected. Allowed roles are: " . implode(', ', $allowedRoles));
        }

        if (!$isUpdate) {
            if (empty($password)) {
                throw new Exception("Initial password is required.");
            }
            if (strlen($password) < 8) {
                throw new Exception("Password must be at least 8 characters long.");
            }
        } elseif (!empty($password) && strlen($password) < 8) {
            throw new Exception("New password must be at least 8 characters long.");
        }

        // Logical guards for Athlete profile fields
        if ($role === 'Athlete') {
            if (isset($data['height']) && $data['height'] !== '') {
                if (!is_numeric($data['height'])) {
                    throw new Exception("Height must be a numeric value.");
                }
                if ((float)$data['height'] < 0) {
                    throw new Exception("Height cannot be a negative value.");
                }
            }

            if (isset($data['weight']) && $data['weight'] !== '') {
                if (!is_numeric($data['weight'])) {
                    throw new Exception("Weight must be a numeric value.");
                }
                if ((float)$data['weight'] < 0) {
                    throw new Exception("Weight cannot be a negative value.");
                }
            }

            if (isset($data['academic_year']) && $data['academic_year'] !== '') {
                $yr = (int)$data['academic_year'];
                if ($yr < 1 || $yr > 6) {
                    throw new Exception("Academic year must be between 1 and 6.");
                }
            }
        }
    }

    /**
     * Create a new user (CRUD: CREATE)
     */
    public function createUser($data) {
        // Step 1: In-memory pre-validation (prevents unnecessary DB calls)
        $this->validateUserPayload($data, false);

        $username = trim($data['username'] ?? '');
        $email    = trim($data['email'] ?? '');
        $role     = trim($data['role'] ?? 'Coach');
        $phone    = trim($data['phone_number'] ?? '');
        $password = $data['password'] ?? '';

        // Step 2: Uniqueness checks in DB
        if ($this->usernameExists($username)) {
            throw new Exception("The username '{$username}' is already taken.");
        }

        if ($this->emailExists($email)) {
            throw new Exception("The email '{$email}' is already registered.");
        }

        if ($role === 'Athlete') {
            $indexNumber = trim($data['index_number'] ?? '');
            if (!empty($indexNumber) && $this->indexExists($indexNumber)) {
                throw new Exception("The student index number '{$indexNumber}' is already registered.");
            }
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $securityQuestion = $data['security_question'] ?? 'What is your favorite sport?';
        $securityAnswerHash = password_hash(strtolower(trim($data['security_answer'] ?? 'football')), PASSWORD_BCRYPT);

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

            // If registering an athlete, create linked Athletic profile with complete data
            if ($role === 'Athlete') {
                $fullName         = trim($data['full_name'] ?? '') ?: ucwords(str_replace('_', ' ', $username));
                $indexNumber      = trim($data['index_number'] ?? '') ?: ('S-' . str_pad($newUserId, 4, '0', STR_PAD_LEFT));
                $faculty          = trim($data['faculty'] ?? '') ?: 'Science';
                $academicYear     = !empty($data['academic_year']) ? (int)$data['academic_year'] : 1;
                $height           = isset($data['height']) && $data['height'] !== '' ? max(0, (float)$data['height']) : 0.00;
                $weight           = isset($data['weight']) && $data['weight'] !== '' ? max(0, (float)$data['weight']) : 0.00;
                $bloodGroup       = trim($data['blood_group'] ?? 'O+');
                $emergencyContact = trim($data['emergency_Contact'] ?? $data['emergency_contact'] ?? '');

                $stmtAth = $this->db->prepare("
                    INSERT INTO `Athletic` (`user_id`, `full_name`, `index_number`, `faculty`, `academic_year`, `height`, `weight`, `blood_group`, `emergency_Contact`)
                    VALUES (:uid, :name, :idx, :fac, :yr, :ht, :wt, :bg, :ec)
                ");
                $stmtAth->execute([
                    ':uid'  => $newUserId,
                    ':name' => $fullName,
                    ':idx'  => $indexNumber,
                    ':fac'  => $faculty,
                    ':yr'   => $academicYear,
                    ':ht'   => $height,
                    ':wt'   => $weight,
                    ':bg'   => $bloodGroup,
                    ':ec'   => $emergencyContact
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
        $userId = (int)$userId;

        // Step 1: In-memory pre-validation (prevents unnecessary DB calls)
        $this->validateUserPayload($data, true);

        $username = trim($data['username'] ?? '');
        $email    = trim($data['email'] ?? '');
        $role     = trim($data['role'] ?? '');
        $phone    = trim($data['phone_number'] ?? '');
        $password = $data['password'] ?? '';

        // Step 2: Uniqueness checks in DB
        if ($this->usernameExists($username, $userId)) {
            throw new Exception("The username '{$username}' is already in use by another user.");
        }

        if ($this->emailExists($email, $userId)) {
            throw new Exception("The email '{$email}' is already in use by another user.");
        }

        if ($role === 'Athlete') {
            $indexNumber = trim($data['index_number'] ?? '');
            if (!empty($indexNumber) && $this->indexExists($indexNumber, $userId)) {
                throw new Exception("The student index number '{$indexNumber}' is already registered to another athlete.");
            }
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
                $fullName         = trim($data['full_name'] ?? '') ?: ucwords(str_replace('_', ' ', $username));
                $indexNumber      = trim($data['index_number'] ?? '') ?: ('S-' . str_pad($userId, 4, '0', STR_PAD_LEFT));
                $faculty          = trim($data['faculty'] ?? '') ?: 'Science';
                $academicYear     = !empty($data['academic_year']) ? (int)$data['academic_year'] : 1;
                $height           = isset($data['height']) && $data['height'] !== '' ? max(0, (float)$data['height']) : 0.00;
                $weight           = isset($data['weight']) && $data['weight'] !== '' ? max(0, (float)$data['weight']) : 0.00;
                $bloodGroup       = trim($data['blood_group'] ?? 'O+');
                $emergencyContact = trim($data['emergency_Contact'] ?? $data['emergency_contact'] ?? '');

                $chk = $this->db->prepare("SELECT athlete_id FROM `Athletic` WHERE `user_id` = :uid LIMIT 1");
                $chk->execute([':uid' => $userId]);
                $athId = $chk->fetchColumn();

                if ($athId) {
                    $upAth = $this->db->prepare("
                        UPDATE `Athletic` 
                        SET `full_name` = :name,
                            `index_number` = :idx,
                            `faculty` = :fac,
                            `academic_year` = :yr,
                            `height` = :ht,
                            `weight` = :wt,
                            `blood_group` = :bg,
                            `emergency_Contact` = :ec
                        WHERE `athlete_id` = :aid
                    ");
                    $upAth->execute([
                        ':name' => $fullName,
                        ':idx'  => $indexNumber,
                        ':fac'  => $faculty,
                        ':yr'   => $academicYear,
                        ':ht'   => $height,
                        ':wt'   => $weight,
                        ':bg'   => $bloodGroup,
                        ':ec'   => $emergencyContact,
                        ':aid'  => $athId
                    ]);
                } else {
                    $inAth = $this->db->prepare("
                        INSERT INTO `Athletic` (`user_id`, `full_name`, `index_number`, `faculty`, `academic_year`, `height`, `weight`, `blood_group`, `emergency_Contact`)
                        VALUES (:uid, :name, :idx, :fac, :yr, :ht, :wt, :bg, :ec)
                    ");
                    $inAth->execute([
                        ':uid'  => $userId,
                        ':name' => $fullName,
                        ':idx'  => $indexNumber,
                        ':fac'  => $faculty,
                        ':yr'   => $academicYear,
                        ':ht'   => $height,
                        ':wt'   => $weight,
                        ':bg'   => $bloodGroup,
                        ':ec'   => $emergencyContact
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

    /**
     * University Colours & Awards Management
     */
    public function getColorsStats() {
        $stats = [
            'total'     => 0,
            'nominated' => 0,
            'approved'  => 0,
            'awarded'   => 0
        ];
        try {
            $stmt = $this->db->query("
                SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN `status` = 'Nominated' THEN 1 ELSE 0 END) AS nominated,
                    SUM(CASE WHEN `status` = 'Approved' THEN 1 ELSE 0 END) AS approved,
                    SUM(CASE WHEN `status` = 'Awarded' THEN 1 ELSE 0 END) AS awarded
                FROM `university_colors`
            ");
            $row = $stmt->fetch();
            if ($row) {
                $stats['total']     = (int)$row['total'];
                $stats['nominated'] = (int)$row['nominated'];
                $stats['approved']  = (int)$row['approved'];
                $stats['awarded']   = (int)$row['awarded'];
            }
        } catch (Exception $e) {
            // Keep defaults
        }
        return $stats;
    }

    public function getAllColors($statusFilter = '', $sportFilter = '', $seasonFilter = '', $search = '') {
        $sql = "
            SELECT 
                uc.award_id,
                uc.award_name,
                uc.status,
                a.athlete_id,
                a.full_name AS athlete_name,
                a.index_number,
                a.faculty,
                s.sport_id,
                s.sport_name,
                sea.season_id,
                sea.season_year,
                sea.academic_year
            FROM `university_colors` uc
            JOIN `Athletic` a ON uc.athelet_id = a.athlete_id
            JOIN `sports` s ON uc.sport_id = s.sport_id
            JOIN `seasons` sea ON uc.season_id = sea.season_id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($statusFilter) && $statusFilter !== 'All') {
            $sql .= " AND uc.status = :st";
            $params[':st'] = $statusFilter;
        }

        if (!empty($sportFilter) && $sportFilter !== 'All') {
            $sql .= " AND uc.sport_id = :sp";
            $params[':sp'] = (int)$sportFilter;
        }

        if (!empty($seasonFilter) && $seasonFilter !== 'All') {
            $sql .= " AND uc.season_id = :sea";
            $params[':sea'] = (int)$seasonFilter;
        }

        if (!empty($search)) {
            $sql .= " AND (a.full_name LIKE :s1 OR a.index_number LIKE :s2 OR s.sport_name LIKE :s3 OR uc.award_name LIKE :s4)";
            $params[':s1'] = "%{$search}%";
            $params[':s2'] = "%{$search}%";
            $params[':s3'] = "%{$search}%";
            $params[':s4'] = "%{$search}%";
        }

        $sql .= " ORDER BY uc.award_id DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function createColorAward($data) {
        $athleteId = (int)($data['athlete_id'] ?? 0);
        $sportId   = (int)($data['sport_id'] ?? 0);
        $seasonId  = (int)($data['season_id'] ?? 0);
        $awardName = trim($data['award_name'] ?? 'University Colours');
        $status    = trim($data['status'] ?? 'Nominated');

        if ($athleteId <= 0) {
            throw new Exception("Please select a student athlete.");
        }
        if ($sportId <= 0) {
            throw new Exception("Please select a sport.");
        }
        if ($seasonId <= 0) {
            throw new Exception("Please select an athletic season.");
        }
        if (empty($awardName)) {
            throw new Exception("Award designation title cannot be empty.");
        }
        if (!in_array($status, ['Nominated', 'Approved', 'Awarded'], true)) {
            throw new Exception("Invalid award status.");
        }

        // Check duplicate entry
        $chk = $this->db->prepare("
            SELECT COUNT(*) FROM `university_colors`
            WHERE `athelet_id` = :ath AND `sport_id` = :sp AND `season_id` = :sea
        ");
        $chk->execute([
            ':ath' => $athleteId,
            ':sp'  => $sportId,
            ':sea' => $seasonId
        ]);
        if ((int)$chk->fetchColumn() > 0) {
            throw new Exception("A University Colours record already exists for this athlete in the selected sport and season.");
        }

        $stmt = $this->db->prepare("
            INSERT INTO `university_colors` (`award_name`, `athelet_id`, `season_id`, `sport_id`, `status`)
            VALUES (:name, :ath, :sea, :sp, :st)
        ");
        return $stmt->execute([
            ':name' => $awardName,
            ':ath'  => $athleteId,
            ':sea'  => $seasonId,
            ':sp'   => $sportId,
            ':st'   => $status
        ]);
    }

    public function updateColorStatus($awardId, $status) {
        $awardId = (int)$awardId;
        if (!in_array($status, ['Nominated', 'Approved', 'Awarded'], true)) {
            throw new Exception("Invalid status specified.");
        }

        $stmt = $this->db->prepare("UPDATE `university_colors` SET `status` = :st WHERE `award_id` = :id");
        return $stmt->execute([
            ':st' => $status,
            ':id' => $awardId
        ]);
    }

    public function deleteColorAward($awardId) {
        $awardId = (int)$awardId;
        $stmt = $this->db->prepare("DELETE FROM `university_colors` WHERE `award_id` = :id");
        return $stmt->execute([':id' => $awardId]);
    }

    public function getEligibleAthletes() {
        try {
            $stmt = $this->db->query("SELECT athlete_id, full_name, index_number, faculty FROM `Athletic` ORDER BY full_name ASC");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function getAllSportsList() {
        try {
            $stmt = $this->db->query("SELECT sport_id, sport_name FROM `sports` ORDER BY sport_name ASC");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function getAllSeasonsList() {
        try {
            $stmt = $this->db->query("SELECT season_id, season_year, academic_year, status FROM `seasons` ORDER BY season_year DESC, academic_year DESC");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}
