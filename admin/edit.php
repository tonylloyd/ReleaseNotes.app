<?php
// admin/edit.php - Edit Existing Release

session_start();
require_once __DIR__ . '/../includes/db.php';

// Ensure user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: index.php');
    exit;
}

// Fetch existing release
$stmt = $pdo->prepare("SELECT * FROM " . TABLE_RELEASES . " WHERE id = ?");
$stmt->execute([$id]);
$release = $stmt->fetch();

if (!$release) {
    die('Release not found.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $type    = $_POST['type'] ?? 'minor';
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $date    = trim($_POST['created_at'] ?? '');
    $imageName = $release['image']; // Keep existing image by default

    // Handle Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = __DIR__ . '/../uploads/';
            
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            $dest_path = $uploadFileDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Delete old image if it exists
                if (!empty($release['image']) && file_exists($uploadFileDir . $release['image'])) {
                    @unlink($uploadFileDir . $release['image']);
                }
                $imageName = $newFileName;
            } else {
                $error = 'Error moving the uploaded file.';
            }
        } else {
            $error = 'Invalid image file type. Allowed types: JPG, PNG, WEBP.';
        }
    }

    if (empty($error)) {
        if (empty($title) || empty($summary) || empty($content)) {
            $error = 'Please fill in all required fields.';
        } else {
            if (!empty($date)) {
                $stmt = $pdo->prepare("UPDATE " . TABLE_RELEASES . " SET title = ?, type = ?, summary = ?, content = ?, image = ?, created_at = ? WHERE id = ?");
                $stmt->execute([$title, $type, $summary, $content, $imageName, $date, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE " . TABLE_RELEASES . " SET title = ?, type = ?, summary = ?, content = ?, image = ? WHERE id = ?");
                $stmt->execute([$title, $type, $summary, $content, $imageName, $id]);
            }
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Release - Release Notes CMS</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <!-- Trumbowyg CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/ui/trumbowyg.min.css">
</head>
<body>
<div class="container" style="max-width: 800px;">
    <header>
        <h1>Edit Release</h1>
        <a href="index.php" class="btn btn-secondary">&larr; Back to Dashboard</a>
    </header>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Release Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($_POST['title'] ?? $release['title']) ?>" required>
        </div>

        <div class="form-group">
            <label>Update Type</label>
            <select name="type">
                <option value="minor" <?= (($_POST['type'] ?? $release['type']) === 'minor') ? 'selected' : '' ?>>Minor Update (Notification badge dot)</option>
                <option value="major" <?= (($_POST['type'] ?? $release['type']) === 'major') ? 'selected' : '' ?>>Major Update (Popup modal alert)</option>
            </select>
        </div>

        <div class="form-group">
            <label>Publish Date & Time</label>
            <input type="datetime-local" name="created_at" value="<?= htmlspecialchars($_POST['created_at'] ?? date('Y-m-d\TH:i', strtotime($release['created_at']))) ?>">
        </div>

        <div class="form-group">
            <label>Header Banner Image (Optional)</label>
            <?php if (!empty($release['image'])): ?>
                <div style="margin-bottom: 0.5rem; font-size: 0.85rem; color: #64748b;">
                    Current: <code><?= htmlspecialchars($release['image']) ?></code>
                </div>
            <?php endif; ?>
            <input type="file" name="image" accept="image/png, image/jpeg, image/webp">
        </div>

        <div class="form-group">
            <label>Summary (Short excerpt shown in feeds and modals)</label>
            <textarea name="summary" rows="3" required><?= htmlspecialchars($_POST['summary'] ?? $release['summary']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Full Content</label>
            <textarea name="content" id="editor" rows="10" required><?= htmlspecialchars($_POST['content'] ?? $release['content']) ?></textarea>
        </div>

        <button type="submit" class="btn">Update Release</button>
    </form>
</div>

<!-- jQuery and Trumbowyg JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/trumbowyg.min.js"></script>
<script>
    $('#editor').trumbowyg();
</script>
</body>
</html>
