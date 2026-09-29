<?php
// admin/index.php - Admin Login & Dashboard

session_start();
require_once __DIR__ . '/../includes/db.php';

$error = '';

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Handle Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $user['username'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}

// If not logged in, show Login Screen
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Release Notes</title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>
<body class="login-body">
<div class="card">
    <h2>Admin Login</h2>
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="hidden" name="login" value="1">
        <label>Username</label>
        <input type="text" name="username" required autofocus>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Log In</button>
    </form>
</div>
</body>
</html>
<?php
exit;
endif;

// Handle Delete Action
if (isset($_GET['delete'])) {
    $del_id = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);
    if ($del_id) {
        // Delete uploaded image file if exists
        $stmt = $pdo->prepare("SELECT image FROM releases WHERE id = ?");
        $stmt->execute([$del_id]);
        $rel = $stmt->fetch();
        if ($rel && !empty($rel['image'])) {
            $imgPath = __DIR__ . '/../uploads/' . $rel['image'];
            if (file_exists($imgPath)) {
                @unlink($imgPath);
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM releases WHERE id = ?");
        $stmt->execute([$del_id]);
    }
    header('Location: index.php');
    exit;
}

// Handle Duplicate Action
if (isset($_GET['duplicate'])) {
    $dup_id = filter_input(INPUT_GET, 'duplicate', FILTER_VALIDATE_INT);
    if ($dup_id) {
        $stmt = $pdo->prepare("SELECT title, summary, content, image, type FROM releases WHERE id = ?");
        $stmt->execute([$dup_id]);
        $rel = $stmt->fetch();
        if ($rel) {
            $newTitle = 'Copy of ' . $rel['title'];
            $stmt = $pdo->prepare("INSERT INTO releases (title, summary, content, image, type, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$newTitle, $rel['summary'], $rel['content'], $rel['image'], $rel['type']]);
        }
    }
    header('Location: index.php');
    exit;
}

// Fetch all existing releases for the dashboard list
$stmt = $pdo->query("SELECT * FROM releases ORDER BY created_at DESC");
$releases = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Release Notes CMS</title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>
<body>
<div class="container">
    <header>
        <h1>Release Notes Dashboard</h1>
        <div>
            <a href="create.php" class="btn">+ New Release</a>
            <a href="index.php?logout=1" class="btn btn-danger" style="margin-left: 10px;">Log Out</a>
        </div>
    </header>

    <h2>All Releases</h2>
    <?php if (empty($releases)): ?>
        <p>No releases published yet. Click "+ New Release" to create your first one.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($releases as $release): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($release['title']) ?></strong></td>
                    <td><span class="badge badge-<?= $release['type'] ?>"><?= $release['type'] ?></span></td>
                    <td><?= htmlspecialchars($release['created_at']) ?></td>
                    <td style="text-align: right; white-space: nowrap;">
                        <a href="edit.php?id=<?= $release['id'] ?>" class="btn btn-sm">Edit</a>
                        <a href="index.php?duplicate=<?= $release['id'] ?>" class="btn btn-sm btn-secondary">Duplicate</a>
                        <a href="index.php?delete=<?= $release['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this release?');">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
