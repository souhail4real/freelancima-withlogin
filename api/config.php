<?php
// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_NAME', 'freelancima');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

// Current timestamp and user
define('CURRENT_TIMESTAMP', '2025-05-26 14:57:29');
define('CURRENT_USER', 'souhail4real');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Creates a database connection
 * @return PDO|null Database connection or null on failure
 */
function createConnection() {
    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        return null;
    }
}
?>