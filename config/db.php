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

            $dbNames = array_unique([self::$db_name, strtolower(self::$db_name), strtoupper(self::$db_name)]);
            $lastError = '';

            // 1. Primary Attempt: Standard TCP/IP
            foreach ($dbNames as $name) {
                try {
                    $dsn = 'mysql:host=' . self::$host . ';port=' . self::$port . ';dbname=' . $name . ';charset=utf8mb4';
                    self::$conn = new PDO($dsn, self::$username, self::$password, $options);
                    return self::$conn;
                } catch (PDOException $tcpException) {
                    $lastError = $tcpException->getMessage();
                    // If error is NOT unknown database, breaking early might skip socket fallback
                }
            }

            // 2. Fallback Attempt: OS-specific Unix sockets (macOS & Linux)
            $socketPaths = [
                '/opt/lampp/var/mysql/mysql.sock',                     // Linux XAMPP
                '/var/run/mysqld/mysqld.sock',                         // Ubuntu / Debian native
                '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock', // macOS XAMPP
                '/Applications/MAMP/tmp/mysql/mysql.sock',             // macOS MAMP
                '/tmp/mysql.sock',                                     // macOS / General Unix default
                '/var/lib/mysql/mysql.sock'                            // CentOS / RHEL native
            ];

            foreach ($socketPaths as $socket) {
                if (file_exists($socket)) {
                    foreach ($dbNames as $name) {
                        try {
                            $dsn = 'mysql:unix_socket=' . $socket . ';dbname=' . $name . ';charset=utf8mb4';
                            self::$conn = new PDO($dsn, self::$username, self::$password, $options);
                            return self::$conn;
                        } catch (PDOException $socketException) {
                            $lastError = $socketException->getMessage();
                        }
                    }
                }
            }

            // 3. If all connection attempts fail, report the exact error
            die("Database Connection Error: " . $lastError);
        }

        return self::$conn;
    }
}