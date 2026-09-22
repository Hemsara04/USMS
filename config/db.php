<?php
/**
 * Database Connection Configuration (PDO)
 * USMS - University Sports Management System
 * Cross-platform compatible (macOS, Windows, Linux)
 */

class Database {
    private static $host = '127.0.0.1';
    private static $port = '3306';
    private static $db_name = 'USMS';
    private static $username = 'root';
    private static $password = '';
    private static $conn = null;

    /**
     * Get singleton PDO Database connection
     */
    public static function connect() {
        if (self::$conn === null) {
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            // 1. Primary Attempt: Standard TCP/IP (Works across Windows, macOS, and Linux)
            try {
                $dsn = 'mysql:host=' . self::$host . ';port=' . self::$port . ';dbname=' . self::$db_name . ';charset=utf8mb4';
                self::$conn = new PDO($dsn, self::$username, self::$password, $options);
                return self::$conn;
            } catch (PDOException $tcpException) {
                // Keep track of primary failure
                $lastError = $tcpException->getMessage();
            }

            // 2. Fallback Attempt: OS-specific Unix sockets (macOS & Linux)
            $socketPaths = [
                '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock', // macOS XAMPP
                '/Applications/MAMP/tmp/mysql/mysql.sock',             // macOS MAMP
                '/tmp/mysql.sock',                                     // macOS / General Unix default
                '/opt/lampp/var/mysql/mysql.sock',                     // Linux XAMPP
                '/var/run/mysqld/mysqld.sock',                         // Ubuntu / Debian native
                '/var/lib/mysql/mysql.sock'                            // CentOS / RHEL native
            ];

            foreach ($socketPaths as $socket) {
                if (file_exists($socket)) {
                    try {
                        $dsn = 'mysql:unix_socket=' . $socket . ';dbname=' . self::$db_name . ';charset=utf8mb4';
                        self::$conn = new PDO($dsn, self::$username, self::$password, $options);
                        return self::$conn;
                    } catch (PDOException $socketException) {
                        $lastError = $socketException->getMessage();
                    }
                }
            }

            // 3. If all connection attempts fail, report the exact error
            die("Database Connection Error: " . $lastError);
        }

        return self::$conn;
    }
}