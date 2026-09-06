<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Silakan login terlebih dahulu.']);
    exit;
}

$photoId = (int) ($_POST['photo_id'] ?? 0);
$comment = clean($_POST['comment'] ?? '');
$userId = $_SESSION['user_id'];

if (!$photoId || $comment === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Data tidak lengkap.']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO comments (photo_id, user_id, comment) VALUES (?, ?, ?)");
$stmt->execute([$photoId, $userId, $comment]);

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$userId]);
$username = $stmt->fetchColumn();

echo json_encode([
    'success' => true,
    'username' => $username,
    'comment' => $comment,
    'count' => countComments($pdo, $photoId),
]);
