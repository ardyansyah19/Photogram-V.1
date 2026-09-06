<?php
session_start();
require_once __DIR__ . '/../config/db.php';

/** Cek apakah user sudah login */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/** Redirect ke halaman login jika belum login */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/** Ambil data user saat ini */
function currentUser($pdo) {
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/** Bersihkan input string */
function clean($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

/** Hitung waktu relatif (mis. "5 menit lalu") */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return $diff . " detik lalu";
    if ($diff < 3600) return floor($diff / 60) . " menit lalu";
    if ($diff < 86400) return floor($diff / 3600) . " jam lalu";
    if ($diff < 2592000) return floor($diff / 86400) . " hari lalu";
    return date('d M Y', $time);
}

/** Hitung jumlah like sebuah foto */
function countLikes($pdo, $photoId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE photo_id = ?");
    $stmt->execute([$photoId]);
    return (int) $stmt->fetchColumn();
}

/** Cek apakah user tertentu sudah like foto */
function hasLiked($pdo, $photoId, $userId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE photo_id = ? AND user_id = ?");
    $stmt->execute([$photoId, $userId]);
    return (int) $stmt->fetchColumn() > 0;
}

/** Hitung jumlah komentar sebuah foto */
function countComments($pdo, $photoId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE photo_id = ?");
    $stmt->execute([$photoId]);
    return (int) $stmt->fetchColumn();
}

/** Hitung jumlah view sebuah foto */
function countViews($pdo, $photoId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM photo_views WHERE photo_id = ?");
    $stmt->execute([$photoId]);
    return (int) $stmt->fetchColumn();
}

/** Catat view foto (dipanggil via AJAX saat foto tampil di viewport) */
function recordView($pdo, $photoId, $userId = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $stmt = $pdo->prepare("INSERT INTO photo_views (photo_id, user_id, ip_address) VALUES (?, ?, ?)");
    $stmt->execute([$photoId, $userId, $ip]);
}

/** Terapkan filter warna ke gambar menggunakan GD */
function applyImageFilter($sourcePath, $destPath, $filter) {
    $info = getimagesize($sourcePath);
    $mime = $info['mime'];

    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $image = imagecreatefrompng($sourcePath);
            break;
        default:
            return false;
    }

    switch ($filter) {
        case 'grayscale':
            imagefilter($image, IMG_FILTER_GRAYSCALE);
            break;
        case 'sepia':
            imagefilter($image, IMG_FILTER_GRAYSCALE);
            imagefilter($image, IMG_FILTER_COLORIZE, 90, 60, 20);
            break;
        case 'invert':
            imagefilter($image, IMG_FILTER_NEGATE);
            break;
        case 'bright':
            imagefilter($image, IMG_FILTER_BRIGHTNESS, 30);
            break;
        case 'contrast':
            imagefilter($image, IMG_FILTER_CONTRAST, -20);
            break;
        case 'blur':
            imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
            break;
        case 'normal':
        default:
            // tidak ada perubahan
            break;
    }

    if ($mime === 'image/jpeg') {
        imagejpeg($image, $destPath, 90);
    } else {
        imagepng($image, $destPath);
    }

    imagedestroy($image);
    return true;
}

/** Crop & rotate gambar menggunakan GD berdasarkan koordinat dari Cropper.js */
function cropAndRotateImage($sourcePath, $destPath, $x, $y, $width, $height, $rotate = 0) {
    $info = getimagesize($sourcePath);
    $mime = $info['mime'];

    $image = ($mime === 'image/png')
        ? imagecreatefrompng($sourcePath)
        : imagecreatefromjpeg($sourcePath);

    if ($rotate != 0) {
        $image = imagerotate($image, -$rotate, 0);
    }

    $cropped = imagecrop($image, [
        'x' => (int) $x,
        'y' => (int) $y,
        'width' => (int) $width,
        'height' => (int) $height,
    ]);

    if ($cropped === false) return false;

    if ($mime === 'image/png') {
        imagepng($cropped, $destPath);
    } else {
        imagejpeg($cropped, $destPath, 90);
    }

    imagedestroy($image);
    imagedestroy($cropped);
    return true;
}
