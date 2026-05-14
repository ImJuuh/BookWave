<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/ui.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $birth_date = $_POST['birth_date'] ?? null;
  $password = $_POST['password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';

  $idade = null;

  if ($birth_date) {
    $today = new DateTime();
    $birth = new DateTime($birth_date);
    $idade = $today->diff($birth)->y;
  }

  if ($name === '' || $email === '' || $birth_date === '' || $password === '' || $confirm === '') {
    $error = 'Preenche todos os campos obrigatórios.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Email inválido.';
  } elseif ($birth_date > date('Y-m-d')) {
    $error = 'A data de nascimento não pode ser no futuro.';
  } elseif ($idade < 13) {
    $error = 'É necessário ter pelo menos 13 anos para criar conta.';
  } elseif (strlen($password) < 6) {
    $error = 'A password deve ter pelo menos 6 caracteres.';
  } elseif ($password !== $confirm) {
    $error = 'As passwords não coincidem.';
  } else {
    try {
      $stmt = db()->prepare("
        INSERT INTO users (name, email, birth_date, password_hash)
        VALUES (?, ?, ?, ?)
      ");

      $stmt->execute([
        $name,
        $email,
        $birth_date,
        password_hash($password, PASSWORD_DEFAULT)
      ]);

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

  <p class="text-sm text-gray-500 mb-6">
    Os campos assinalados com
    <span class="text-red-600 font-semibold">*</span>
    são obrigatórios.
  </p>

  <?php if ($error): ?>
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <form method="post">
    <label class="block mb-1 font-medium">
      Nome <span class="text-red-600">*</span>
    </label>
    <input
      type="text"
      name="name"
      class="w-full border rounded px-3 py-2 mb-4"
      required
    >

    <label class="block mb-1 font-medium">
      Email <span class="text-red-600">*</span>
    </label>
    <input
      type="email"
      name="email"
      class="w-full border rounded px-3 py-2 mb-4"
      required
    >

    <label class="block mb-1 font-medium">
      Data de Nascimento <span class="text-red-600">*</span>
    </label>
    <input
      type="date"
      name="birth_date"
      class="w-full border rounded px-3 py-2 mb-4"
      required
    >

    <label class="block mb-1 font-medium">
      Password <span class="text-red-600">*</span>
    </label>
    <input
      type="password"
      name="password"
      class="w-full border rounded px-3 py-2 mb-4"
      required
    >

    <label class="block mb-1 font-medium">
      Confirmar password <span class="text-red-600">*</span>
    </label>
    <input
      type="password"
      name="confirm_password"
      class="w-full border rounded px-3 py-2 mb-4"
      required
    >

    <button
      type="submit"
      class="w-full bg-gray-900 text-white rounded px-4 py-2 hover:bg-gray-800"
    >
      Registar
    </button>
  </form>

  <p class="mt-4 text-sm text-center">
    Já tens conta?
    <a href="/bookwave/public/login.php" class="underline">Entrar</a>
  </p>
</div>

<?php page_end(); ?>