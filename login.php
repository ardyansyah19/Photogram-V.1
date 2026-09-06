<?php
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = clean($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Username/email atau password salah.";
    }
}

$pageTitle = "Masuk";
include __DIR__ . '/includes/header.php';
?>

<div class="max-w-sm mx-auto mt-6 bg-white border border-gray-200 rounded-xl p-8">
  <h1 class="text-3xl font-bold text-center mb-1 bg-gradient-to-r from-brand-500 to-purple-600 bg-clip-text text-transparent">PhotoGram</h1>
  <p class="text-center text-gray-500 mb-6">Masuk untuk melihat foto dari temanmu</p>

  <?php if ($error): ?>
    <div class="bg-red-50 text-red-600 text-sm rounded-lg px-3 py-2 mb-3"><?= clean($error) ?></div>
  <?php endif; ?>

  <form method="POST" class="space-y-3">
    <input type="text" name="login" placeholder="Username atau email" required
      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
    <input type="password" name="password" placeholder="Password" required
      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
    <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-semibold py-2 rounded-lg text-sm">Masuk</button>
  </form>

  <p class="text-xs text-gray-400 text-center mt-4">Demo: username <b>demo</b> / password <b>password123</b></p>
</div>

<p class="text-center text-sm mt-4 bg-white border border-gray-200 rounded-xl py-4 max-w-sm mx-auto">
  Belum punya akun? <a href="register.php" class="text-brand-600 font-semibold">Daftar</a>
</p>

<?php include __DIR__ . '/includes/footer.php'; ?>
