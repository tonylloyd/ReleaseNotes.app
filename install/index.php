<?php
// install/index.php - Installation Wizard

$error = '';$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost   = trim($_POST['db_host'] ?? '');
    $dbName   = trim($_POST['db_name'] ?? '');
    $dbUser   = trim($_POST['db_user'] ?? '');
    $dbPass   =$_POST['db_pass'] ?? '';
    $dbPrefix = trim($_POST['db_prefix'] ?? 'rn_');
    $appName  = trim($_POST['app_name'] ?? 'Release Notes');
    $adminUser = trim($_POST['admin_user'] ?? '');
    $adminPass =$_POST['admin_pass'] ?? '';

    if (empty($dbHost) || empty($dbName) || empty($dbUser) || empty($adminUser) || empty($adminPass)) {$error = 'Please fill in all required fields.';
    } else {
        try {
            // Test connection
            $dsn = "mysql:host=$dbHost;charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser,$dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // Create database if it doesn't exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$dbName`");

            // Create tables with prefix
            $releasesTable =$dbPrefix . 'releases';
            $usersTable =$dbPrefix . 'admin_users';

            $pdo->exec("CREATE TABLE IF NOT EXISTS `$releasesTable` (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                type ENUM('minor', 'major') DEFAULT 'minor',
                summary TEXT NOT NULL,
                content LONGTEXT NOT NULL,
                image VARCHAR(255) DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `$usersTable` (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;");

            // Insert admin user if not exists
            $stmt =$pdo->prepare("SELECT COUNT(*) FROM `$usersTable`");
            $stmt->execute();
            if ($stmt->fetchColumn() == 0) {
                $hash = password_hash($adminPass, PASSWORD_DEFAULT);
                $stmt =$pdo->prepare("INSERT INTO `$usersTable` (username, password) VALUES (?, ?)");
                $stmt->execute([$adminUser,$hash]);
            }

            // Generate config.php
            $configContent = "<?php\n" .
                "// config.php - Auto-generated during installation\n\n" .
                "define('DB_HOST', " . var_export($dbHost, true) . ");\n" .
                "define('DB_NAME', " . var_export($dbName, true) . ");\n" .
                "define('DB_USER', " . var_export($dbUser, true) . ");\n" .
                "define('DB_PASS', " . var_export($dbPass, true) . ");\n" .
                "define('DB_PREFIX', " . var_export($dbPrefix, true) . ");\n" .
                "define('APP_NAME', " . var_export($appName, true) . ");\n\n" .
                "try {\n" .
                "    \$pdo = new PDO(\"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=utf8mb4\", DB_USER, DB_PASS, [\n" .
                "        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n" .
                "        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC\n" .
                "    ]);\n" .
                "} catch (PDOException \$e) {\n" .
                "    die('Database connection failed: ' . \$e->getMessage());\n" .
                "}\n";

            file_put_contents(__DIR__ . '/../config.php', $configContent);$success = true;

        } catch (PDOException $e) {
            $error = 'Database Error: ' .$e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Install - Release Notes CMS</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #f4f6f8; margin: 0; }
        .install-box { background: white; padding: 2.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); width: 100%; max-width: 450px; }
        .install-box h2 { margin-top: 0; margin-bottom: 1.5rem; color: #1e293b; font-size: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; font-size: 0.875rem; color: #475569; }
        .form-group input { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 1rem; box-sizing: border-box; }
        .btn-primary { width: 100%; padding: 0.75rem; background: #2563eb; color: white; border: none; border-radius: 4px; font-size: 1rem; font-weight: 500; cursor: pointer; }
        .btn-primary:hover { background: #1d4ed8; }
        .error { background: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; font-size: 0.875rem; }
        .success-box { text-align: center; }
        .success-box a { display: inline-block; margin-top: 1rem; padding: 0.75rem 1.5rem; background: #16a34a; color: white; text-decoration: none; border-radius: 4px; font-weight: 500; }
    </style>
</head>
<body>

<div class="install-box">
    <?php if ($success): ?>
        <div class="success-box">
            <h2>Installation Successful!</h2>
            <p>Your database tables have been created with your chosen prefix and configuration has been saved.</p>
            <p><strong>Security Notice:</strong> Please delete the <code>install/</code> folder from your server.</p>
            <a href="../admin/">Go to Admin Dashboard</a>
        </div>
    <?php else: ?>
        <h2>Release Notes Setup</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Database Host</label>
                <input type="text" name="db_host" value="localhost" required>
            </div>
            <div class="form-group">
                <label>Database Name</label>
                <input type="text" name="db_name" required>
            </div>
            <div class="form-group">
                <label>Database Username</label>
                <input type="text" name="db_user" required>
            </div>
            <div class="form-group">
                <label>Database Password</label>
                <input type="password" name="db_pass">
            </div>
            <div class="form-group">
                <label>Table Prefix (e.g., rn_)</label>
                <input type="text" name="db_prefix" value="rn_" required>
            </div>
            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 1.5rem 0;">
            <div class="form-group">
                <label>Application Name</label>
                <input type="text" name="app_name" value="Product Updates" required>
            </div>
            <div class="form-group">
                <label>Admin Username</label>
                <input type="text" name="admin_user" value="admin" required>
            </div>
            <div class="form-group">
                <label>Admin Password</label>
                <input type="password" name="admin_pass" required>
            </div>
            <button type="submit" class="btn-primary">Complete Installation</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
