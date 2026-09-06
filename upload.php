<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caption = clean($_POST['caption'] ?? '');
    $filter = clean($_POST['filter'] ?? 'normal');

    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Silakan pilih foto untuk diunggah.";
    } else {
        $file = $_FILES['photo'];
        $allowed = ['image/jpeg', 'image/png'];
        $mime = mime_content_type($file['tmp_name']);

        if (!in_array($mime, $allowed)) {
            $errors[] = "Format file harus JPG atau PNG.";
        } elseif ($file['size'] > 8 * 1024 * 1024) {
            $errors[] = "Ukuran file maksimal 8MB.";
        } else {
            $ext = $mime === 'image/png' ? 'png' : 'jpg';
            $filename = uniqid('photo_', true) . '.' . $ext;
            $destPath = UPLOAD_DIR . $filename;

            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

            if ($filter !== 'normal') {
                move_uploaded_file($file['tmp_name'], $destPath . '.tmp');
                applyImageFilter($destPath . '.tmp', $destPath, $filter);
                unlink($destPath . '.tmp');
            } else {
                move_uploaded_file($file['tmp_name'], $destPath);
            }

            $stmt = $pdo->prepare("INSERT INTO photos (user_id, filename, caption, filter_applied) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $filename, $caption, $filter]);

            header('Location: index.php');
            exit;
        }
    }
}

$pageTitle = "Unggah Foto";
include __DIR__ . '/includes/header.php';
?>

<div class="max-w-md mx-auto bg-white border border-gray-200 rounded-xl p-6">
  <h1 class="text-xl font-bold mb-4">Buat Postingan Baru</h1>

  <?php foreach ($errors as $err): ?>
    <div class="bg-red-50 text-red-600 text-sm rounded-lg px-3 py-2 mb-2"><?= clean($err) ?></div>
  <?php endforeach; ?>

  <form method="POST" enctype="multipart/form-data" class="space-y-4">

    <div>
      <label class="block text-sm font-medium mb-1">Pilih Foto</label>
      <input type="file" name="photo" id="photoInput" accept="image/jpeg,image/png" required
        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
    </div>

    <div class="bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center h-64">
      <img id="previewImg" class="max-h-64 hidden mx-auto" style="transition: filter .2s;">
      <span id="previewPlaceholder" class="text-gray-400 text-sm">Preview akan tampil di sini</span>
    </div>

    <div>
      <label class="block text-sm font-medium mb-2">Pilih Filter</label>
      <div class="grid grid-cols-3 gap-2 text-xs text-center" id="filterOptions">
        <label class="cursor-pointer">
          <input type="radio" name="filter" value="normal" checked class="hidden filter-radio" data-filter="none">
          <div class="border-2 border-brand-500 rounded-lg py-2">Normal</div>
        </label>
        <label class="cursor-pointer">
          <input type="radio" name="filter" value="grayscale" class="hidden filter-radio" data-filter="grayscale(1)">
          <div class="border-2 border-transparent rounded-lg py-2 hover:border-gray-300">Hitam Putih</div>
        </label>
        <label class="cursor-pointer">
          <input type="radio" name="filter" value="sepia" class="hidden filter-radio" data-filter="sepia(0.8)">
          <div class="border-2 border-transparent rounded-lg py-2 hover:border-gray-300">Sepia</div>
        </label>
        <label class="cursor-pointer">
          <input type="radio" name="filter" value="invert" class="hidden filter-radio" data-filter="invert(1)">
          <div class="border-2 border-transparent rounded-lg py-2 hover:border-gray-300">Invert</div>
        </label>
        <label class="cursor-pointer">
          <input type="radio" name="filter" value="bright" class="hidden filter-radio" data-filter="brightness(1.4)">
          <div class="border-2 border-transparent rounded-lg py-2 hover:border-gray-300">Cerah</div>
        </label>
        <label class="cursor-pointer">
          <input type="radio" name="filter" value="contrast" class="hidden filter-radio" data-filter="contrast(1.5)">
          <div class="border-2 border-transparent rounded-lg py-2 hover:border-gray-300">Kontras</div>
        </label>
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Caption</label>
      <textarea name="caption" rows="3" placeholder="Tulis caption..."
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400"></textarea>
    </div>

    <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-semibold py-2 rounded-lg text-sm">
      Bagikan
    </button>
  </form>
</div>

<script>
const photoInput = document.getElementById('photoInput');
const previewImg = document.getElementById('previewImg');
const previewPlaceholder = document.getElementById('previewPlaceholder');
const filterRadios = document.querySelectorAll('.filter-radio');

photoInput.addEventListener('change', function () {
  const file = this.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    previewImg.src = e.target.result;
    previewImg.classList.remove('hidden');
    previewPlaceholder.classList.add('hidden');
  };
  reader.readAsDataURL(file);
});

filterRadios.forEach(radio => {
  radio.addEventListener('change', function () {
    previewImg.style.filter = this.dataset.filter === 'none' ? '' : this.dataset.filter;
    document.querySelectorAll('#filterOptions div').forEach(d => d.classList.replace('border-brand-500','border-transparent'));
    this.nextElementSibling.classList.replace('border-transparent','border-brand-500');
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
