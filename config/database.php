<?php
/**
 * Database Configuration & PDO Connection
 * Knowledge Base Portal
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'knowledge_base');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP MySQL password is empty

/**
 * Get PDO database connection
 * @return PDO
 */
function getConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    return $pdo;
}
