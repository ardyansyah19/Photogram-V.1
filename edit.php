<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM photos WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$photo = $stmt->fetch();

if (!$photo) {
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $caption = clean($_POST['caption'] ?? '');

    // Update caption selalu disimpan
    $pdo->prepare("UPDATE photos SET caption = ? WHERE id = ?")->execute([$caption, $id]);

    if ($action === 'apply_filter') {
        $filter = clean($_POST['filter'] ?? 'normal');
        $srcPath = UPLOAD_DIR . $photo['filename'];
        $tmpPath = UPLOAD_DIR . 'orig_' . $photo['filename'];

        // Simpan salinan asli jika belum ada, supaya filter tidak menumpuk merusak kualitas
        if (!file_exists($tmpPath)) {
            copy($srcPath, $tmpPath);
        }
        applyImageFilter($tmpPath, $srcPath, $filter);
        $pdo->prepare("UPDATE photos SET filter_applied = ? WHERE id = ?")->execute([$filter, $id]);
    }

    if ($action === 'crop') {
        $x = (float) $_POST['crop_x'];
        $y = (float) $_POST['crop_y'];
        $w = (float) $_POST['crop_w'];
        $h = (float) $_POST['crop_h'];
        $rotate = (float) $_POST['rotate'];

        $srcPath = UPLOAD_DIR . $photo['filename'];
        $tmpCropSrc = UPLOAD_DIR . 'precrop_' . $photo['filename'];
        copy($srcPath, $tmpCropSrc);
        cropAndRotateImage($tmpCropSrc, $srcPath, $x, $y, $w, $h, $rotate);
        unlink($tmpCropSrc);
    }

    header('Location: edit.php?id=' . $id . '&saved=1');
    exit;
}

$pageTitle = "Edit Foto";
include __DIR__ . '/includes/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

<div class="max-w-2xl mx-auto bg-white border border-gray-200 rounded-xl p-6">
  <h1 class="text-xl font-bold mb-4">Edit Foto</h1>

  <?php if (isset($_GET['saved'])): ?>
    <div class="bg-green-50 text-green-700 text-sm rounded-lg px-3 py-2 mb-3">Perubahan berhasil disimpan.</div>
  <?php endif; ?>

  <div class="grid md:grid-cols-2 gap-6">
    <!-- Area crop -->
    <div>
      <p class="text-sm font-medium mb-2">Potong / Putar Gambar</p>
      <div class="bg-gray-100 rounded-lg overflow-hidden" style="max-height:400px;">
        <img id="cropImage" src="<?= UPLOAD_URL . clean($photo['filename']) ?>?t=<?= time() ?>" style="max-width:100%;">
      </div>
      <div class="flex gap-2 mt-3">
        <button type="button" id="rotateLeft" class="flex-1 border border-gray-300 rounded-lg py-1.5 text-sm hover:bg-gray-50"><i class="fa-solid fa-rotate-left"></i> Putar Kiri</button>
        <button type="button" id="rotateRight" class="flex-1 border border-gray-300 rounded-lg py-1.5 text-sm hover:bg-gray-50"><i class="fa-solid fa-rotate-right"></i> Putar Kanan</button>
      </div>
      <form method="POST" id="cropForm" class="mt-3">
        <input type="hidden" name="action" value="crop">
        <input type="hidden" name="crop_x" id="crop_x">
        <input type="hidden" name="crop_y" id="crop_y">
        <input type="hidden" name="crop_w" id="crop_w">
        <input type="hidden" name="crop_h" id="crop_h">
        <input type="hidden" name="rotate" id="rotate_val" value="0">
        <input type="hidden" name="caption" value="<?= clean($photo['caption']) ?>">
        <button type="submit" class="w-full bg-gray-800 hover:bg-black text-white text-sm font-semibold py-2 rounded-lg">
          Simpan Potongan / Rotasi
        </button>
      </form>
    </div>

    <!-- Filter & Caption -->
    <div>
      <p class="text-sm font-medium mb-2">Ganti Filter</p>
      <form method="POST" class="space-y-3">
        <input type="hidden" name="action" value="apply_filter">
        <select name="filter" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
          <?php
          $filters = ['normal' => 'Normal', 'grayscale' => 'Hitam Putih', 'sepia' => 'Sepia', 'invert' => 'Invert', 'bright' => 'Cerah', 'contrast' => 'Kontras', 'blur' => 'Blur'];
          foreach ($filters as $val => $label):
          ?>
          <option value="<?= $val ?>" <?= $photo['filter_applied'] === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>

        <label class="block text-sm font-medium mt-4 mb-1">Caption</label>
        <textarea name="caption" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"><?= clean($photo['caption']) ?></textarea>

        <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold py-2 rounded-lg">
          Terapkan Filter &amp; Simpan Caption
        </button>
      </form>

      <a href="index.php" class="block text-center text-sm text-gray-500 mt-4 hover:underline">Selesai, kembali ke beranda</a>
    </div>
  </div>
</div>

<script>
const image = document.getElementById('cropImage');
let rotateDeg = 0;
const cropper = new Cropper(image, {
  viewMode: 1,
  autoCropArea: 1,
  responsive: true,
});

document.getElementById('rotateLeft').addEventListener('click', () => { cropper.rotate(-90); rotateDeg -= 90; });
document.getElementById('rotateRight').addEventListener('click', () => { cropper.rotate(90); rotateDeg += 90; });

document.getElementById('cropForm').addEventListener('submit', function (e) {
  const data = cropper.getData(true);
  document.getElementById('crop_x').value = data.x;
  document.getElementById('crop_y').value = data.y;
  document.getElementById('crop_w').value = data.width;
  document.getElementById('crop_h').value = data.height;
  document.getElementById('rotate_val').value = rotateDeg % 360;
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
