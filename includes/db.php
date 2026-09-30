<?php
// includes/db.php - Database connection & table prefix definitions

// If config doesn't exist, redirect to installer
if (!file_exists(__DIR__ . '/../config.php')) {
    $redirect = file_exists('install/index.php') ? 'install/index.php' : '../install/index.php';
    header("Location: $redirect");
    exit;
}

require_once __DIR__ . '/../config.php';

// Ensure PDO connection exists
if (!isset($pdo)) {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e) {
        die("Database Connection Error: " . htmlspecialchars($e->getMessage()));
    }
}

// Define table names with your custom prefix
define('TABLE_RELEASES', DB_PREFIX . 'releases');
define('TABLE_USERS', DB_PREFIX . 'admin_users');
