<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$user = currentUser($pdo);

$stmt = $pdo->query("
  SELECT p.*, u.username, u.avatar
  FROM photos p
  JOIN users u ON u.id = p.user_id
  ORDER BY p.created_at DESC
");
$photos = $stmt->fetchAll();

$pageTitle = "Beranda";
include __DIR__ . '/includes/header.php';
?>

<div class="max-w-lg mx-auto space-y-6">

  <?php if (empty($photos)): ?>
    <div class="bg-white border border-gray-200 rounded-xl p-10 text-center text-gray-500">
      <i class="fa-regular fa-images text-4xl mb-3"></i>
      <p>Belum ada foto. <a href="upload.php" class="text-brand-600 font-semibold">Unggah foto pertamamu!</a></p>
    </div>
  <?php endif; ?>

  <?php foreach ($photos as $photo):
      $likeCount = countLikes($pdo, $photo['id']);
      $liked = hasLiked($pdo, $photo['id'], $user['id']);
      $commentCount = countComments($pdo, $photo['id']);
  ?>
  <article class="bg-white border border-gray-200 rounded-xl overflow-hidden" data-photo-id="<?= $photo['id'] ?>">
    <!-- Header postingan -->
    <div class="flex items-center gap-3 px-4 py-3">
      <img src="<?= UPLOAD_URL . 'avatars/' . clean($photo['avatar'] ?? 'default-avatar.png') ?>"
           onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($photo['username']) ?>&background=ec4899&color=fff'"
           class="w-9 h-9 rounded-full object-cover border">
      <div class="flex-1">
        <p class="text-sm font-semibold"><?= clean($photo['username']) ?></p>
      </div>
      <?php if ($photo['user_id'] == $user['id']): ?>
      <div class="flex gap-3 text-gray-500">
        <a href="edit.php?id=<?= $photo['id'] ?>" title="Edit"><i class="fa-solid fa-pen"></i></a>
        <a href="delete.php?id=<?= $photo['id'] ?>" title="Hapus" onclick="return confirm('Hapus foto ini?')"><i class="fa-solid fa-trash"></i></a>
      </div>
      <?php endif; ?>
    </div>

    <!-- Gambar -->
    <div class="bg-black">
      <img src="<?= UPLOAD_URL . clean($photo['filename']) ?>" class="w-full max-h-[600px] object-contain mx-auto js-photo-view" loading="lazy">
    </div>

    <!-- Aksi -->
    <div class="px-4 pt-3 flex items-center gap-4 text-2xl">
      <button class="js-like-btn <?= $liked ? 'text-red-500' : 'text-gray-700 hover:text-gray-400' ?>" data-photo-id="<?= $photo['id'] ?>">
        <i class="<?= $liked ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
      </button>
      <button class="text-gray-700 hover:text-gray-400" onclick="document.getElementById('comment-input-<?= $photo['id'] ?>').focus()">
        <i class="fa-regular fa-comment"></i>
      </button>
    </div>

    <!-- Jumlah like -->
    <p class="px-4 pt-2 text-sm font-semibold js-like-count" data-photo-id="<?= $photo['id'] ?>"><?= $likeCount ?> suka</p>

    <!-- Caption -->
    <?php if ($photo['caption']): ?>
    <p class="px-4 pt-1 text-sm"><span class="font-semibold"><?= clean($photo['username']) ?></span> <?= clean($photo['caption']) ?></p>
    <?php endif; ?>

    <!-- Komentar -->
    <div class="px-4 pt-1 pb-1 js-comment-list" id="comment-list-<?= $photo['id'] ?>">
      <?php
      $cstmt = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON u.id = c.user_id WHERE photo_id = ? ORDER BY c.created_at ASC LIMIT 3");
      $cstmt->execute([$photo['id']]);
      foreach ($cstmt->fetchAll() as $c): ?>
        <p class="text-sm"><span class="font-semibold"><?= clean($c['username']) ?></span> <?= clean($c['comment']) ?></p>
      <?php endforeach; ?>
      <?php if ($commentCount > 3): ?>
        <p class="text-xs text-gray-400">Lihat semua <?= $commentCount ?> komentar</p>
      <?php endif; ?>
    </div>

    <p class="px-4 text-[11px] text-gray-400 uppercase pt-1"><?= timeAgo($photo['created_at']) ?></p>

    <!-- Form komentar -->
    <form class="js-comment-form flex items-center border-t border-gray-100 mt-2 px-4 py-2" data-photo-id="<?= $photo['id'] ?>">
      <input id="comment-input-<?= $photo['id'] ?>" type="text" name="comment" placeholder="Tambahkan komentar..."
        class="flex-1 text-sm focus:outline-none" required>
      <button type="submit" class="text-brand-600 font-semibold text-sm">Kirim</button>
    </form>
  </article>
  <?php endforeach; ?>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
