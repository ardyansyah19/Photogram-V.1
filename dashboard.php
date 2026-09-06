<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];

// ===== Ringkasan umum =====
$stmt = $pdo->prepare("SELECT COUNT(*) FROM photos WHERE user_id = ?");
$stmt->execute([$userId]);
$totalPhotos = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("
  SELECT COUNT(*) FROM likes l
  JOIN photos p ON p.id = l.photo_id
  WHERE p.user_id = ?
");
$stmt->execute([$userId]);
$totalLikes = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("
  SELECT COUNT(*) FROM comments c
  JOIN photos p ON p.id = c.photo_id
  WHERE p.user_id = ?
");
$stmt->execute([$userId]);
$totalComments = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("
  SELECT COUNT(*) FROM photo_views v
  JOIN photos p ON p.id = v.photo_id
  WHERE p.user_id = ?
");
$stmt->execute([$userId]);
$totalViews = (int) $stmt->fetchColumn();

$engagementRate = $totalViews > 0 ? round((($totalLikes + $totalComments) / $totalViews) * 100, 1) : 0;

// ===== Views 14 hari terakhir (untuk grafik garis) =====
$stmt = $pdo->prepare("
  SELECT DATE(v.viewed_at) as tgl, COUNT(*) as jumlah
  FROM photo_views v
  JOIN photos p ON p.id = v.photo_id
  WHERE p.user_id = ? AND v.viewed_at >= (CURDATE() - INTERVAL 13 DAY)
  GROUP BY DATE(v.viewed_at)
  ORDER BY tgl ASC
");
$stmt->execute([$userId]);
$viewRows = $stmt->fetchAll();

$viewsByDate = [];
for ($i = 13; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $viewsByDate[$d] = 0;
}
foreach ($viewRows as $r) {
    $viewsByDate[$r['tgl']] = (int) $r['jumlah'];
}

// ===== Foto dengan engagement tertinggi =====
$stmt = $pdo->prepare("
  SELECT p.id, p.filename, p.caption,
    (SELECT COUNT(*) FROM likes WHERE photo_id = p.id) as likes,
    (SELECT COUNT(*) FROM comments WHERE photo_id = p.id) as comments,
    (SELECT COUNT(*) FROM photo_views WHERE photo_id = p.id) as views
  FROM photos p
  WHERE p.user_id = ?
  ORDER BY (likes + comments) DESC, views DESC
  LIMIT 5
");
$stmt->execute([$userId]);
$topPhotos = $stmt->fetchAll();

// ===== Semua foto untuk tabel detail =====
$stmt = $pdo->prepare("
  SELECT p.id, p.filename, p.caption, p.created_at,
    (SELECT COUNT(*) FROM likes WHERE photo_id = p.id) as likes,
    (SELECT COUNT(*) FROM comments WHERE photo_id = p.id) as comments,
    (SELECT COUNT(*) FROM photo_views WHERE photo_id = p.id) as views
  FROM photos p
  WHERE p.user_id = ?
  ORDER BY p.created_at DESC
");
$stmt->execute([$userId]);
$allPhotos = $stmt->fetchAll();

$pageTitle = "Dashboard Insight";
include __DIR__ . '/includes/header.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>

<div class="max-w-5xl mx-auto space-y-6">

  <h1 class="text-2xl font-bold">📊 Dashboard Insight</h1>
  <p class="text-gray-500 -mt-4">Statistik performa foto-foto kamu</p>

  <!-- Kartu ringkasan -->
  <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
    <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
      <p class="text-2xl font-bold text-brand-600"><?= $totalPhotos ?></p>
      <p class="text-xs text-gray-500 mt-1">Total Foto</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
      <p class="text-2xl font-bold text-red-500"><?= $totalLikes ?></p>
      <p class="text-xs text-gray-500 mt-1">Total Suka</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
      <p class="text-2xl font-bold text-blue-500"><?= $totalComments ?></p>
      <p class="text-xs text-gray-500 mt-1">Total Komentar</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
      <p class="text-2xl font-bold text-purple-500"><?= $totalViews ?></p>
      <p class="text-xs text-gray-500 mt-1">Total Dilihat</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
      <p class="text-2xl font-bold text-green-500"><?= $engagementRate ?>%</p>
      <p class="text-xs text-gray-500 mt-1">Engagement Rate</p>
    </div>
  </div>

  <!-- Grafik views 14 hari -->
  <div class="bg-white border border-gray-200 rounded-xl p-5">
    <h2 class="font-semibold mb-3">Dilihat 14 Hari Terakhir</h2>
    <canvas id="viewsChart" height="90"></canvas>
  </div>

  <div class="grid md:grid-cols-2 gap-6">
    <!-- Top 5 foto -->
    <div class="bg-white border border-gray-200 rounded-xl p-5">
      <h2 class="font-semibold mb-3">🏆 Foto dengan Engagement Tertinggi</h2>
      <?php if (empty($topPhotos)): ?>
        <p class="text-sm text-gray-400">Belum ada data.</p>
      <?php endif; ?>
      <div class="space-y-3">
        <?php foreach ($topPhotos as $tp): ?>
        <div class="flex items-center gap-3">
          <img src="<?= UPLOAD_URL . clean($tp['filename']) ?>" class="w-12 h-12 rounded-lg object-cover">
          <div class="flex-1 min-w-0">
            <p class="text-sm truncate"><?= clean($tp['caption'] ?: '(tanpa caption)') ?></p>
            <p class="text-xs text-gray-400">
              <i class="fa-solid fa-heart text-red-400"></i> <?= $tp['likes'] ?>
              &nbsp; <i class="fa-solid fa-comment text-blue-400"></i> <?= $tp['comments'] ?>
              &nbsp; <i class="fa-solid fa-eye text-purple-400"></i> <?= $tp['views'] ?>
            </p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Distribusi engagement per foto (bar chart) -->
    <div class="bg-white border border-gray-200 rounded-xl p-5">
      <h2 class="font-semibold mb-3">Perbandingan Like vs Komentar (Top 5)</h2>
      <canvas id="engagementChart" height="140"></canvas>
    </div>
  </div>

  <!-- Tabel detail semua foto -->
  <div class="bg-white border border-gray-200 rounded-xl p-5 overflow-x-auto">
    <h2 class="font-semibold mb-3">Detail Semua Foto</h2>
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-gray-500 border-b">
          <th class="py-2 pr-3">Foto</th>
          <th class="py-2 pr-3">Caption</th>
          <th class="py-2 pr-3">Tanggal</th>
          <th class="py-2 pr-3 text-center">Suka</th>
          <th class="py-2 pr-3 text-center">Komentar</th>
          <th class="py-2 pr-3 text-center">Dilihat</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($allPhotos as $ap): ?>
        <tr class="border-b last:border-0">
          <td class="py-2 pr-3"><img src="<?= UPLOAD_URL . clean($ap['filename']) ?>" class="w-10 h-10 rounded object-cover"></td>
          <td class="py-2 pr-3 max-w-[200px] truncate"><?= clean($ap['caption'] ?: '-') ?></td>
          <td class="py-2 pr-3 text-gray-500"><?= date('d M Y', strtotime($ap['created_at'])) ?></td>
          <td class="py-2 pr-3 text-center"><?= $ap['likes'] ?></td>
          <td class="py-2 pr-3 text-center"><?= $ap['comments'] ?></td>
          <td class="py-2 pr-3 text-center"><?= $ap['views'] ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($allPhotos)): ?>
        <tr><td colspan="6" class="py-6 text-center text-gray-400">Belum ada foto.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

<script>
// Grafik garis: views 14 hari terakhir
new Chart(document.getElementById('viewsChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode(array_map(fn($d) => date('d M', strtotime($d)), array_keys($viewsByDate))) ?>,
    datasets: [{
      label: 'Dilihat',
      data: <?= json_encode(array_values($viewsByDate)) ?>,
      borderColor: '#ec4899',
      backgroundColor: 'rgba(236,72,153,0.1)',
      fill: true,
      tension: 0.3,
    }]
  },
  options: {
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
  }
});

// Grafik batang: like vs komentar top 5 foto
new Chart(document.getElementById('engagementChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_map(fn($p) => mb_strimwidth($p['caption'] ?: 'Tanpa caption', 0, 15, '...'), $topPhotos)) ?>,
    datasets: [
      {
        label: 'Suka',
        data: <?= json_encode(array_map(fn($p) => (int)$p['likes'], $topPhotos)) ?>,
        backgroundColor: '#ef4444'
      },
      {
        label: 'Komentar',
        data: <?= json_encode(array_map(fn($p) => (int)$p['comments'], $topPhotos)) ?>,
        backgroundColor: '#3b82f6'
      }
    ]
  },
  options: {
    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
  }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
