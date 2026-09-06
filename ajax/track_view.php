<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

$photoId = (int) ($_POST['photo_id'] ?? 0);
if (!$photoId) {
    http_response_code(400);
    echo json_encode(['error' => 'photo_id tidak valid.']);
    exit;
}

$userId = isLoggedIn() ? $_SESSION['user_id'] : null;
recordView($pdo, $photoId, $userId);

echo json_encode(['success' => true]);
