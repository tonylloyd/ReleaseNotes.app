<?php
// admin/create.php - Create New Release

session_start();
require_once __DIR__ . '/../includes/db.php';

// Check if logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$error = '';
$title = '';
$summary = '';
$content = '';
$type = 'minor';
$custom_date = date('Y-m-d\TH:i');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $type = $_POST['type'] === 'major' ? 'major' : 'minor';
    $custom_date = trim($_POST['created_at'] ?? '');
    
    $created_at = !empty($custom_date) ? date('Y-m-d H:i:s', strtotime($custom_date)) : date('Y-m-d H:i:s');
    
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
            $error = 'Invalid file type. Allowed types: JPG, PNG, WEBP.';
        }
    }

    if (empty($error)) {
        if (empty($title) || empty($summary) || empty($content)) {
            $error = 'Please fill in all required text fields.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO releases (title, summary, content, image, type, created_at) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $summary, $content, $imageName, $type, $created_at]);
            
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
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Trumbowyg CSS & JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/ui/trumbowyg.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/trumbowyg.min.js"></script>
    <script>
        $(document).ready(function(){
            $('.wysiwyg-editor').trumbowyg({
                btns: [
                    ['viewHTML'],
                    ['formatting'],
                    ['strong', 'em', 'del'],
                    ['link'],
                    ['insertImage'],
                    ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                    ['unorderedList', 'orderedList'],
                    ['horizontalRule'],
                    ['removeformat']
                ],
                autogrow: true
            });
        });
    </script>
</head>
<body>
<div class="container">
    <h1>Create New Release</h1>
    
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Release Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($title) ?>" required>

        <label>Update Type</label>
        <select name="type">
            <option value="minor" <?= $type === 'minor' ? 'selected' : '' ?>>Minor Update (Badge Notification)</option>
            <option value="major" <?= $type === 'major' ? 'selected' : '' ?>>Major Update (Forced Popup Notification)</option>
        </select>

        <label>Publish Date & Time</label>
        <input type="datetime-local" name="created_at" value="<?= htmlspecialchars($custom_date) ?>" required>

        <label>Header Banner Image (Optional)</label>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp">

        <div class="field-group">
            <label>Summary (Introduction Paragraph)</label>
            <textarea name="summary" class="wysiwyg-editor"><?= htmlspecialchars($summary) ?></textarea>
        </div>

        <div class="field-group">
            <label>Full Content Details</label>
            <textarea name="content" class="wysiwyg-editor"><?= htmlspecialchars($content) ?></textarea>
        </div>

        <div>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn">Publish Release</button>
        </div>
    </form>
</div>
</body>
</html>