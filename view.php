<?php
// view.php - Single Release Detail View

require_once __DIR__ . '/includes/db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM releases WHERE id = ?");
$stmt->execute([$id]);
$release = $stmt->fetch();

if (!$release) {
    die('Release not found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($release['title']) ?> - <?= defined('APP_NAME') ? htmlspecialchars(APP_NAME) : 'Release Notes' ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container">
    <a href="index.php" class="back-link">&larr; Back to all updates</a>

    <div class="card">
        <?php if (!empty($release['image'])): ?>
            <img src="uploads/<?= htmlspecialchars($release['image']) ?>" alt="Release Header Image">
        <?php endif; ?>
        
        <div class="meta">
            <?= date('F j, Y', strtotime($release['created_at'])) ?>
            <span class="badge badge-<?= $release['type'] ?>"><?= $release['type'] ?></span>
        </div>

        <h1 style="margin: 0 0 0.5rem 0; font-size: 1.8rem;"><?= htmlspecialchars($release['title']) ?></h1>

        <div class="summary-section">
            <?= $release['summary'] ?>
        </div>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 1.5rem 0;">

        <div class="content-section">
            <?= $release['content'] ?>
        </div>
    </div>
</div>

</body>
</html>