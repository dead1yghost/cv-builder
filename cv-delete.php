<?php
// cv-delete.php
require_once 'config.php';
requireLogin();

$cvId = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM cvs WHERE id = ? AND user_id = ?");
$stmt->execute([$cvId, $_SESSION['user_id']]);
$cv = $stmt->fetch();

if ($cv) {
    // Delete photo
    if ($cv['photo_path'] && file_exists(UPLOAD_PATH . $cv['photo_path'])) {
        unlink(UPLOAD_PATH . $cv['photo_path']);
    }
    // Delete CV (cascades)
    db()->prepare("DELETE FROM cvs WHERE id = ?")->execute([$cvId]);
    flash('success', 'CV silindi.');
}

redirect('dashboard.php');
