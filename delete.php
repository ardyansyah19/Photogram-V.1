<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM photos WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$photo = $stmt->fetch();

if ($photo) {
    $path = UPLOAD_DIR . $photo['filename'];
    if (file_exists($path)) unlink($path);

    $origPath = UPLOAD_DIR . 'orig_' . $photo['filename'];
    if (file_exists($origPath)) unlink($origPath);

    $pdo->prepare("DELETE FROM photos WHERE id = ?")->execute([$id]);
}

header('Location: index.php');
exit;
