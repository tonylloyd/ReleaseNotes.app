<?php
// admin/create.php - Create New Release

session_start();
require_once __DIR__ . '/../includes/db.php';

// Ensure user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $type    = $_POST['type'] ?? 'minor';
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $date    = trim($_POST['created_at'] ?? '');
    $imageName = null;

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
            // Use custom date if provided, otherwise default to NOW()
            if (!empty($date)) {
                $stmt = $pdo->prepare("INSERT INTO " . TABLE_RELEASES . " (title, type, summary, content, image, created_at) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $type, $summary, $content, $imageName, $date]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO " . TABLE_RELEASES . " (title, type, summary, content, image, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$title, $type, $summary, $content, $imageName]);
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
    <title>New Release - Release Notes CMS</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <!-- Trumbowyg CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/ui/trumbowyg.min.css">
</head>
<body>
<div class="container" style="max-width: 800px;">
    <header>
        <h1>Create New Release</h1>
        <a href="index.php" class="btn btn-secondary">&larr; Back to Dashboard</a>
    </header>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Release Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>Update Type</label>
            <select name="type">
                <option value="minor">Minor Update (Notification badge dot)</option>
                <option value="major">Major Update (Popup modal alert)</option>
            </select>
        </div>

        <div class="form-group">
            <label>Publish Date & Time (Optional - defaults to current time)</label>
            <input type="datetime-local" name="created_at" value="<?= htmlspecialchars($_POST['created_at'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Header Banner Image (Optional)</label>
            <input type="file" name="image" accept="image/png, image/jpeg, image/webp">
        </div>

        <div class="form-group">
            <label>Summary (Short excerpt shown in feeds and modals)</label>
            <textarea name="summary" rows="3" required><?= htmlspecialchars($_POST['summary'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label>Full Content</label>
            <textarea name="content" id="editor" rows="10" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn">Publish Release</button>
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
