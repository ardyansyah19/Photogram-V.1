<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$user = currentUser($pdo);

$stmt = $pdo->prepare("SELECT * FROM photos WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$photos = $stmt->fetchAll();

$totalPhotos = count($photos);
$totalLikes = 0;
$totalComments = 0;
foreach ($photos as $p) {
    $totalLikes += countLikes($pdo, $p['id']);
    $totalComments += countComments($pdo, $p['id']);
}

$pageTitle = "Profil";
include __DIR__ . '/includes/header.php';
?>

<div class="max-w-3xl mx-auto">
  <div class="bg-white border border-gray-200 rounded-xl p-6 flex items-center gap-6 mb-6">
    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=ec4899&color=fff&size=100"
         class="w-24 h-24 rounded-full object-cover">
    <div class="flex-1">
      <h1 class="text-xl font-bold"><?= clean($user['username']) ?></h1>
      <p class="text-gray-500 text-sm mb-3"><?= clean($user['bio'] ?? 'Belum ada bio') ?></p>
      <div class="flex gap-6 text-sm">
        <span><b><?= $totalPhotos ?></b> Postingan</span>
        <span><b><?= $totalLikes ?></b> Suka</span>
        <span><b><?= $totalComments ?></b> Komentar</span>
      </div>
    </div>
    <a href="dashboard.php" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg">
      <i class="fa-solid fa-chart-simple"></i> Lihat Insight
    </a>
  </div>

  <div class="grid grid-cols-3 gap-1">
    <?php foreach ($photos as $p): ?>
      <a href="edit.php?id=<?= $p['id'] ?>" class="relative aspect-square block group overflow-hidden bg-gray-100">
        <img src="<?= UPLOAD_URL . clean($p['filename']) ?>" class="w-full h-full object-cover group-hover:opacity-75 transition">
        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center gap-4 text-white text-sm transition">
          <span><i class="fa-solid fa-heart"></i> <?= countLikes($pdo, $p['id']) ?></span>
          <span><i class="fa-solid fa-comment"></i> <?= countComments($pdo, $p['id']) ?></span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if (empty($photos)): ?>
    <p class="text-center text-gray-400 mt-10">Belum ada foto yang diunggah.</p>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
