<?php
// includes/db.php - Database connection helper

// If config doesn't exist, redirect to installer
if (!file_exists(__DIR__ . '/config.php')) {
    // Determine redirect path based on where this was included from
    $redirect = file_exists('install/index.php') ? 'install/index.php' : '../install/index.php';
    header("Location: $redirect");
    exit;
}

require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Database Connection Error: " . htmlspecialchars($e->getMessage()));
}
