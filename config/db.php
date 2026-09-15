<?php
/**
 * Database Connection Configuration (PDO)
 * USMS - University Sports Management System
 */

class Database {
    private static $host = '127.0.0.1'; // Use IP to force TCP/IP connection in XAMPP
    private static $port = '3306';
    private static $db_name = 'usms';
    private static $username = 'root';
    private static $password = '';
    private static $conn = null;

    /**
     * Get singleton PDO Database connection
     */
    public static function connect() {
        if (self::$conn === null) {
            try {
                $dsn = 'mysql:host=' . self::$host . ';port=' . self::$port . ';dbname=' . self::$db_name . ';charset=utf8mb4';
                self::$conn = new PDO($dsn, self::$username, self::$password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                // Fallback attempt with XAMPP unix socket
                try {
                    $dsn = 'mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=' . self::$db_name . ';charset=utf8mb4';
                    self::$conn = new PDO($dsn, self::$username, self::$password, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);
                } catch (PDOException $e2) {
                    die("Database Connection Error: " . $e2->getMessage());
                }
            }
        }
        return self::$conn;
    }
}
