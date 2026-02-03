<?php
// cv-download.php - Redirects to preview for browser PDF print
require_once 'config.php';
requireLogin();

$cvId = (int)($_GET['id'] ?? 0);
$type = $_GET['type'] ?? 'pdf';

// Verify ownership
$stmt = db()->prepare("SELECT * FROM cvs WHERE id = ? AND user_id = ?");
$stmt->execute([$cvId, $_SESSION['user_id']]);
$cv = $stmt->fetch();

if (!$cv) {
    flash('danger', 'CV bulunamadı.');
    redirect('dashboard.php');
}

// Redirect to preview page with print prompt
// The user can use browser's "Print to PDF" feature
header("Location: cv-preview?id=$cvId&print=1");
exit;
