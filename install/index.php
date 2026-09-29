<?php
// install/index.php - Simple setup wizard

// Check if already installed in includes/
if (file_exists('../includes/config.php')) {
    die('The application is already installed. Delete /includes/config.php to reinstall.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = trim($_POST['db_host'] ?? '');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass =$_POST['db_pass'] ?? '';
    $app_name = trim($_POST['app_name'] ?? 'Release Notes');
    $app_url = trim($_POST['app_url'] ?? '');

    // Test database connection
    try {
        $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user,$db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$db_name`");

        // Create initial tables with summary, content, and image fields
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `releases` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `title` VARCHAR(255) NOT NULL,
                `summary` TEXT NOT NULL,
                `content` TEXT NOT NULL,
                `image` VARCHAR(255) DEFAULT NULL,
                `type` ENUM('minor', 'major') NOT NULL DEFAULT 'minor',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `admin_users` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `username` VARCHAR(100) NOT NULL,
                `password_hash` VARCHAR(255) NOT NULL
            ) ENGINE=InnoDB;
        ");

        // Create default admin user
        $default_pass = password_hash('password123', PASSWORD_DEFAULT);
        $stmt =$pdo->prepare("INSERT INTO admin_users (username, password_hash) VALUES (?, ?)");
        $stmt->execute(['admin',$default_pass]);

        // Write config.php into the includes folder
        $config_content = "<?php\n" .
            "define('DB_HOST', " . var_export($db_host, true) . ");\n" .
            "define('DB_NAME', " . var_export($db_name, true) . ");\n" .
            "define('DB_USER', " . var_export($db_user, true) . ");\n" .
            "define('DB_PASS', " . var_export($db_pass, true) . ");\n" .
            "define('APP_NAME', " . var_export($app_name, true) . ");\n" .
            "define('APP_URL', " . var_export($app_url, true) . ");\n";

        file_put_contents('../includes/config.php', $config_content);

        $success = true;
    } catch (PDOException $e) {
        $error = 'Database Connection Failed: ' .$e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Install - Release Notes CMS</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { margin-top: 0; color: #333; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; font-size: 0.9rem; color: #555; }
        input { width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 0.75rem; background: #007bff; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
        button:hover { background: #0056b3; }
        .error { color: #dc3545; margin-bottom: 1rem; font-size: 0.9rem; }
        .success { color: #28a745; text-align: center; }
    </style>
</head>
<body>
<div class="card">
    <h2>Install Release Notes</h2>
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
        <div class="success">
            <p><strong>Installation Complete!</strong></p>
            <p>Default admin login: <code>admin</code> / <code>password123</code></p>
            <p><small>Please delete the <code>install/</code> folder for security once you've checked it.</small></p>
        </div>
    <?php else: ?>
        <form method="POST">
            <label>Database Host</label>
            <input type="text" name="db_host" value="localhost" required>

            <label>Database Name</label>
            <input type="text" name="db_name" required>

            <label>Database Username</label>
            <input type="text" name="db_user" required>

            <label>Database Password</label>
            <input type="password" name="db_pass">

            <label>Application Name</label>
            <input type="text" name="app_name" value="My Product Updates" required>

            <label>Application URL (Domain/Subdomain)</label>
            <input type="text" name="app_url" placeholder="https://example.com" required>

            <button type="submit">Install Application</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
