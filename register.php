<?php
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($username) < 3) $errors[] = "Username minimal 3 karakter.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email tidak valid.";
    if (strlen($password) < 6) $errors[] = "Password minimal 6 karakter.";
    if ($password !== $confirm) $errors[] = "Konfirmasi password tidak cocok.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = "Username atau email sudah terdaftar.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            header('Location: index.php');
            exit;
        }
    }
}

$pageTitle = "Daftar";
include __DIR__ . '/includes/header.php';
?>

<div class="max-w-sm mx-auto mt-6 bg-white border border-gray-200 rounded-xl p-8">
  <h1 class="text-3xl font-bold text-center mb-1 bg-gradient-to-r from-brand-500 to-purple-600 bg-clip-text text-transparent">PhotoGram</h1>
  <p class="text-center text-gray-500 mb-6">Daftar untuk mulai berbagi momen</p>

  <?php foreach ($errors as $err): ?>
    <div class="bg-red-50 text-red-600 text-sm rounded-lg px-3 py-2 mb-2"><?= clean($err) ?></div>
  <?php endforeach; ?>

  <form method="POST" class="space-y-3">
    <input type="text" name="username" placeholder="Username" required
      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
    <input type="email" name="email" placeholder="Email" required
      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
    <input type="password" name="password" placeholder="Password" required
      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
    <input type="password" name="confirm_password" placeholder="Konfirmasi Password" required
      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
    <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-semibold py-2 rounded-lg text-sm">Daftar</button>
  </form>
</div>

<p class="text-center text-sm mt-4 bg-white border border-gray-200 rounded-xl py-4 max-w-sm mx-auto">
  Sudah punya akun? <a href="login.php" class="text-brand-600 font-semibold">Masuk</a>
</p>

<?php include __DIR__ . '/includes/footer.php'; ?>
