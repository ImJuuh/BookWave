<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/ui.php';

start_session();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  $stmt = db()->prepare("SELECT id, name, email, password_hash, is_admin FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['id'];
    header('Location: /bookwave/public/');
    exit;
  } else {
    $error = 'Email ou password incorretos.';
  }
}

page_start('Login - BookWave');
?>
<div class="max-w-md mx-auto bg-white border rounded-xl p-6">
  <h1 class="text-2xl font-bold mb-4">Entrar</h1>

  <?php if ($error): ?>
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post">
    <label class="block mb-1">Email</label>
    <input type="email" name="email" class="w-full border rounded px-3 py-2 mb-4" required>

    <label class="block mb-1">Password</label>
    <input type="password" name="password" class="w-full border rounded px-3 py-2 mb-4" required>

    <button type="submit" class="w-full bg-gray-900 text-white rounded px-4 py-2">Entrar</button>
  </form>

  <p class="mt-4 text-sm text-center">Não tens conta? <a href="/bookwave/public/register.php" class="underline">Registar</a></p>
</div>
<?php page_end(); ?>
