<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Silakan login terlebih dahulu.']);
    exit;
}

$photoId = (int) ($_POST['photo_id'] ?? 0);
$userId = $_SESSION['user_id'];

if (!$photoId) {
    http_response_code(400);
    echo json_encode(['error' => 'photo_id tidak valid.']);
    exit;
}

if (hasLiked($pdo, $photoId, $userId)) {
    $stmt = $pdo->prepare("DELETE FROM likes WHERE photo_id = ? AND user_id = ?");
    $stmt->execute([$photoId, $userId]);
    $liked = false;
} else {
    $stmt = $pdo->prepare("INSERT IGNORE INTO likes (photo_id, user_id) VALUES (?, ?)");
    $stmt->execute([$photoId, $userId]);
    $liked = true;
}

echo json_encode([
    'liked' => $liked,
    'count' => countLikes($pdo, $photoId),
]);
