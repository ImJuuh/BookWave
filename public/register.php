<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/ui.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';

  if ($name === '' || $email === '' || $password === '' || $confirm === '') {
    $error = 'Preenche todos os campos.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Email inválido.';
  } elseif (strlen($password) < 6) {
    $error = 'A password deve ter pelo menos 6 caracteres.';
  } elseif ($password !== $confirm) {
    $error = 'As passwords não coincidem.';
  } else {
    try {
      $stmt = db()->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
      $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
      header('Location: /bookwave/public/login.php');
      exit;
    } catch (PDOException $e) {
      $error = 'Esse email já está em uso.';
    }
  }
}

page_start('Registar - BookWave');
?>
<div class="max-w-md mx-auto bg-white border rounded-xl p-6">
  <h1 class="text-2xl font-bold mb-4">Criar conta</h1>

  <?php if ($error): ?>
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post">
    <label class="block mb-1">Nome</label>
    <input type="text" name="name" class="w-full border rounded px-3 py-2 mb-4" required>

    <label class="block mb-1">Email</label>
    <input type="email" name="email" class="w-full border rounded px-3 py-2 mb-4" required>

    <label class="block mb-1">Password</label>
    <input type="password" name="password" class="w-full border rounded px-3 py-2 mb-4" required>

    <label class="block mb-1">Confirmar password</label>
    <input type="password" name="confirm_password" class="w-full border rounded px-3 py-2 mb-4" required>

    <button type="submit" class="w-full bg-gray-900 text-white rounded px-4 py-2">Registar</button>
  </form>

  <p class="mt-4 text-sm text-center">Já tens conta? <a href="/bookwave/public/login.php" class="underline">Entrar</a></p>
</div>
<?php page_end(); ?>
