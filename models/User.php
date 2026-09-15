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
     * Create a new user with secure password hashing
     */
    public function create($username, $email, $password, $role, $security_question = 'Default Question', $security_answer = 'Default Answer') {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $security_answer_hash = password_hash($security_answer, PASSWORD_BCRYPT);

        $stmt = $this->db->prepare("
            INSERT INTO `user` (`username`, `email`, `password_hash`, `role`, `security_question`, `security_answere_hash`)
            VALUES (:username, :email, :password_hash, :role, :security_question, :security_answere_hash)
        ");

        return $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => $password_hash,
            ':role' => $role,
            ':security_question' => $security_question,
            ':security_answere_hash' => $security_answer_hash
        ]);
    }

    /**
     * Find user by ID
     */
    public function findById($user_id) {
        $stmt = $this->db->prepare("SELECT `user_id`, `username`, `email`, `role` FROM `user` WHERE `user_id` = :id LIMIT 1");
        $stmt->execute([':id' => $user_id]);
        return $stmt->fetch();
    }
}
