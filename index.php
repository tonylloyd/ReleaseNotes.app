<?php
// index.php - Public Release Notes Feed with Tiered Layout

require_once __DIR__ . '/includes/db.php';

// Check if loaded inside the widget iframe
$isEmbed = isset($_GET['embed']) and $_GET['embed'] == '1';

// Fetch all releases for the public feed
$stmt = $pdo->query("SELECT * FROM " . TABLE_RELEASES . " ORDER BY created_at DESC");
$releases = $stmt->fetchAll();

// Get the latest release ID to check against visitor's local storage
$latestRelease = !empty($releases) ? $releases[0] : null;
$latestId = $latestRelease ? $latestRelease['id'] : 0;
$latestType = $latestRelease ? $latestRelease['type'] : 'minor';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= defined('APP_NAME') ? htmlspecialchars(APP_NAME) : 'Release Notes' ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <?php if ($isEmbed): ?>
    <style>
        body { padding: 1rem; background: white; }
        .container { max-width: 100%; }
    </style>
    <?php endif; ?>
</head>
<body>

<div class="container">
    <?php if (!$isEmbed): ?>
        <div class="header-banner">
            <h1><?= defined('APP_NAME') ? htmlspecialchars(APP_NAME) : 'Product Updates' ?></h1>
            <p>Stay up to date with our latest improvements and changes.</p>
        </div>
    <?php endif; ?>

    <?php if (empty($releases)): ?>
        <div class="card" style="text-align: center;">
            <p>No release notes published yet. Check back soon!</p>
        </div>
    <?php else: ?>
        <?php 
        // Load display limits from config with robust fallbacks
        $limitFull  = defined('LIMIT_FULL') ? LIMIT_FULL : 1;
        $limitIntro = defined('LIMIT_INTRO') ? LIMIT_INTRO : 5;
        $limitList  = defined('LIMIT_LIST') ? LIMIT_LIST : 10;

        // 1. Display full articles (header image displays on every full article)
        $fullReleases = array_slice($releases, 0, $limitFull);
        foreach ($fullReleases as $release):
        ?>
            <div class="card">
                <?php if (!empty($release['image'])): ?>
                    <img src="uploads/<?= htmlspecialchars($release['image']) ?>" alt="Release Header Image">
                <?php endif; ?>
                
                <div class="meta">
                    <?= date('F j, Y', strtotime($release['created_at'])) ?>
                    <span class="badge badge-<?= $release['type'] ?>"><?= $release['type'] ?></span>
                </div>

                <div class="title-row">
                    <h2><?= htmlspecialchars($release['title']) ?></h2>
                </div>

                <div class="summary-section">
                    <?= $release['summary'] ?>
                </div>

                <hr style="border: 0; border-top: 1px solid #eee; margin: 1.5rem 0;">

                <div class="content-section">
                    <?= $release['content'] ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php 
        // 2. Display intro/summary cards with header image and "Read More" link based on limit
        $introReleases = array_slice($releases, $limitFull, $limitIntro);
        if (!empty($introReleases)):
            foreach ($introReleases as $release):
        ?>
            <div class="card">
                <?php if (!empty($release['image'])): ?>
                    <img src="uploads/<?= htmlspecialchars($release['image']) ?>" alt="Release Header Image">
                <?php endif; ?>

                <div class="meta">
                    <?= date('F j, Y', strtotime($release['created_at'])) ?>
                    <span class="badge badge-<?= $release['type'] ?>"><?= $release['type'] ?></span>
                </div>

                <div class="title-row">
                    <h2><?= htmlspecialchars($release['title']) ?></h2>
                </div>

                <div class="summary-section">
                    <?= $release['summary'] ?>
                </div>

                <a href="view.php?id=<?= $release['id'] ?><?= $isEmbed ? '&embed=1' : '' ?>" class="read-more">Read full release &rarr;</a>
            </div>
        <?php 
            endforeach;
        endif;
        
        // 3. Display older releases as a compact bulleted list based on limit
        $listReleases = array_slice($releases, $limitFull + $limitIntro, $limitList);
        if (!empty($listReleases)):
        ?>
            <div class="archive-section">
                <h3>Older Releases</h3>
                <ul class="archive-list">
                    <?php foreach ($listReleases as $release): ?>
                        <li class="archive-item">
                            <a href="view.php?id=<?= $release['id'] ?><?= $isEmbed ? '&embed=1' : '' ?>" class="archive-title"><?= htmlspecialchars($release['title']) ?></a>
                            <span class="archive-date"><?= date('M j, Y', strtotime($release['created_at'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php if (!$isEmbed): ?>
<!-- Major Update Modal Popup -->
<div id="update-modal">
    <div class="modal-content">
        <h2>Major Update Available!</h2>
        <?php if ($latestRelease): ?>
            <h3><?= htmlspecialchars($latestRelease['title']) ?></h3>
            <div style="text-align: left; margin: 1rem 0;"><?= $latestRelease['summary'] ?></div>
            <a href="view.php?id=<?= $latestRelease['id'] ?><?= $isEmbed ? '&embed=1' : '' ?>" class="read-more">Read full release &rarr;</a>
        <?php endif; ?>
        <button class="modal-btn" onclick="acknowledgeUpdate()">Got it, thanks!</button>
    </div>
</div>

<script>
    const latestId = <?= $latestId ?>;
    const latestType = "<?= $latestType ?>";
    const seenId = localStorage.getItem('seen_release_id');

    if (latestId > 0 && seenId != latestId) {
        if (latestType === 'major') {
            document.getElementById('update-modal').style.display = 'flex';
        }
    }

    function acknowledgeUpdate() {
        localStorage.setItem('seen_release_id', latestId);
        document.getElementById('update-modal').style.display = 'none';
    }
</script>
<?php endif; ?>

</body>
</html>
