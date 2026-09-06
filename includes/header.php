<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? clean($pageTitle) . ' - PhotoGram' : 'PhotoGram' ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          brand: {
            50:'#fdf2f8',100:'#fce7f3',200:'#fbcfe8',300:'#f9a8d4',
            400:'#f472b6',500:'#ec4899',600:'#db2777',700:'#be185d',
            800:'#9d174d',900:'#831843'
          }
        },
        fontFamily: {
          sans: ['Segoe UI','Helvetica Neue','Arial','sans-serif']
        }
      }
    }
  }
</script>
</head>
<body class="bg-gray-50 text-gray-900 font-sans min-h-screen">

<!-- ============ TOP NAVBAR ============ -->
<nav class="fixed top-0 left-0 right-0 bg-white border-b border-gray-200 z-50">
  <div class="max-w-5xl mx-auto flex items-center justify-between px-4 py-3">
    <a href="<?= BASE_URL ?>index.php" class="text-2xl font-bold bg-gradient-to-r from-brand-500 to-purple-600 bg-clip-text text-transparent" style="font-family: 'Brush Script MT', cursive;">
      PhotoGram
    </a>

    <?php if (isLoggedIn()): ?>
    <div class="flex items-center gap-5">
      <a href="<?= BASE_URL ?>index.php" title="Beranda" class="text-xl text-gray-700 hover:text-brand-600"><i class="fa-solid fa-house"></i></a>
      <a href="<?= BASE_URL ?>upload.php" title="Unggah" class="text-xl text-gray-700 hover:text-brand-600"><i class="fa-regular fa-square-plus"></i></a>
      <a href="<?= BASE_URL ?>dashboard.php" title="Dashboard Insight" class="text-xl text-gray-700 hover:text-brand-600"><i class="fa-solid fa-chart-simple"></i></a>
      <a href="<?= BASE_URL ?>profile.php" title="Profil" class="text-xl text-gray-700 hover:text-brand-600"><i class="fa-regular fa-circle-user"></i></a>
      <a href="<?= BASE_URL ?>logout.php" title="Keluar" class="text-xl text-gray-400 hover:text-red-500"><i class="fa-solid fa-right-from-bracket"></i></a>
    </div>
    <?php else: ?>
    <div class="flex items-center gap-3">
      <a href="<?= BASE_URL ?>login.php" class="px-4 py-1.5 text-sm font-semibold text-brand-600 hover:text-brand-700">Masuk</a>
      <a href="<?= BASE_URL ?>register.php" class="px-4 py-1.5 text-sm font-semibold bg-brand-500 text-white rounded-lg hover:bg-brand-600">Daftar</a>
    </div>
    <?php endif; ?>
  </div>
</nav>

<main class="pt-20 pb-10 px-4">
