<?php
/**
 * User Model
 * Handles user authentication & registration queries
 */

require_once __DIR__ . '/../config/db.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Find user by either email or username
     */
    public function findByEmailOrUsername($identifier) {
        $stmt = $this->db->prepare("SELECT * FROM `user` WHERE `email` = :email OR `username` = :username LIMIT 1");
        $stmt->execute([
            ':email'    => $identifier,
            ':username' => $identifier
        ]);
        return $stmt->fetch();
    }

    /**
     * Check if email already exists
     */
    public function emailExists($email) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `user` WHERE `email` = :email");
        $stmt->execute([':email' => $email]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Check if username already exists
     */
    public function usernameExists($username) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `user` WHERE `username` = :username");
        $stmt->execute([':username' => $username]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Find user by email
     */
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM `user` WHERE `email` = :email LIMIT 1");
        $stmt->execute([':email' => trim($email)]);
        return $stmt->fetch();
    }

    /**
     * Check if index number already exists in Athletic table
     */
    public function indexExists($index_number) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `Athletic` WHERE `index_number` = :idx");
        $stmt->execute([':idx' => trim($index_number)]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Create a new user with secure password and security answer hashing
     */
    public function create($username, $email, $password, $role, $security_question = 'What is your favorite sport?', $security_answer = 'football', $phone = '+94 11 258 1835') {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $security_answer_hash = password_hash(strtolower(trim($security_answer)), PASSWORD_BCRYPT);

        $stmt = $this->db->prepare("
            INSERT INTO `user` (`username`, `email`, `phone_number`, `password_hash`, `role`, `security_question`, `security_answere_hash`)
            VALUES (:username, :email, :phone, :password_hash, :role, :security_question, :security_answere_hash)
        ");

        return $stmt->execute([
            ':username'              => $username,
            ':email'                 => $email,
            ':phone'                 => $phone,
            ':password_hash'         => $password_hash,
            ':role'                  => $role,
            ':security_question'     => $security_question,
            ':security_answere_hash' => $security_answer_hash
        ]);
    }

    /**
     * Create a new student athlete user and corresponding Athletic record atomically
     */
    public function createAthlete($username, $email, $phone, $password, $fullname, $index_number, $faculty, $academic_year, $security_question, $security_answer) {
        $this->db->beginTransaction();
        try {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $security_answer_hash = password_hash(strtolower(trim($security_answer)), PASSWORD_BCRYPT);

            $stmtUser = $this->db->prepare("
                INSERT INTO `user` (`username`, `email`, `phone_number`, `password_hash`, `role`, `security_question`, `security_answere_hash`)
                VALUES (:username, :email, :phone, :password_hash, 'Athlete', :security_question, :security_answere_hash)
            ");
            $stmtUser->execute([
                ':username'              => $username,
                ':email'                 => $email,
                ':phone'                 => $phone,
                ':password_hash'         => $password_hash,
                ':security_question'     => $security_question,
                ':security_answere_hash' => $security_answer_hash
            ]);

            $userId = (int) $this->db->lastInsertId();

            $stmtAth = $this->db->prepare("
                INSERT INTO `Athletic` (`user_id`, `index_number`, `full_name`, `academic_year`, `faculty`, `height`, `weight`, `emergency_Contact`)
                VALUES (:user_id, :index_number, :full_name, :academic_year, :faculty, 0.0, 0.0, :contact)
            ");
            $stmtAth->execute([
                ':user_id'       => $userId,
                ':index_number'  => $index_number,
                ':full_name'     => $fullname,
                ':academic_year' => $academic_year,
                ':faculty'       => $faculty,
                ':contact'       => $phone
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Update user password
     */
    public function updatePassword($user_id, $new_password) {
        $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE `user` SET `password_hash` = :hash WHERE `user_id` = :id");
        return $stmt->execute([
            ':hash' => $password_hash,
            ':id'   => $user_id
        ]);
    }

    /**
     * Verify user security answer against bcrypt hash
     */
    public function verifySecurityAnswer($user, $answer) {
        if (empty($user) || empty($user['security_answere_hash'])) {
            return false;
        }
        return password_verify(strtolower(trim($answer)), $user['security_answere_hash']);
    }

    /**
     * Find user by ID
     */
    public function findById($user_id) {
        $stmt = $this->db->prepare("SELECT `user_id`, `username`, `email`, `role`, `security_question` FROM `user` WHERE `user_id` = :id LIMIT 1");
        $stmt->execute([':id' => $user_id]);
        return $stmt->fetch();
    }
}
?>
