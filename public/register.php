<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/ui.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    $error = 'Endereço de e-mail inválido.';
  } elseif ($birth_date > date('Y-m-d')) {
    $error = 'A data de nascimento não pode ser no futuro.';
  } elseif ($idade < 13) {
    $error = 'É necessário ter pelo menos 13 anos para criar conta.';
  } elseif (strlen($password) < 6) {
    $error = 'A palavra-passe deve ter pelo menos 6 caracteres.';
  } elseif ($password !== $confirm) {
    $error = 'As palavras-passe introduzidas não coincidem.';
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

      header('Location: /bookwave/public/login.php?success=registered');
      exit;

    } catch (PDOException $e) {
      $error = 'Este endereço de e-mail já se encontra registado.';
    }
  }
}

page_start('Registar - BookWave');
?>

<div class="max-w-5xl mx-auto bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden min-h-[600px] grid md:grid-cols-2">
  
  <div class="bg-white flex flex-col items-center justify-center p-8 border-b md:border-b-0 md:border-r border-slate-100 selection:bg-blue-100">
    <div class="text-center w-full max-w-md px-6">
      <img src="/bookwave/public/assets/logo.jpg" alt="BookWave Logo" class="w-full h-auto mx-auto mix-blend-multiply pointer-events-none transform scale-105 transition duration-300">
      <p class="text-slate-400 text-base mt-8 font-medium tracking-wide leading-relaxed">
        Surfa na onda do conhecimento. Aluga, lê e evolui sem limites.
      </p>
    </div>
  </div>

  <div class="flex flex-col justify-center p-8 md:p-12">
    <div class="mb-6">
      <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Criar Conta</h1>
      <p class="text-sm text-slate-400 mt-1">
        Junta-te ao BookWave. Os campos com <span class="text-rose-500 font-bold">*</span> são obrigatórios.
      </p>
    </div>

    <?php if ($error): ?>
      <div class="mb-5 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-700 text-sm font-medium flex items-center gap-2 shadow-sm">
        <span>⚠️</span> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
          <label class="block mb-1.5 text-sm font-semibold text-slate-700">Nome Completo <span class="text-rose-500">*</span></label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 text-xs">👤</span>
            <input type="text" name="name" value="<?= htmlspecialchars($name ?? '') ?>" class="w-full border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50/50" placeholder="Ex: Gabriel Silva" required>
          </div>
        </div>

        <div class="sm:col-span-2">
          <label class="block mb-1.5 text-sm font-semibold text-slate-700">Endereço de E-mail <span class="text-rose-500">*</span></label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 text-xs">📧</span>
            <input type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" class="w-full border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50/50" placeholder="exemplo@email.com" required>
          </div>
        </div>

        <div class="sm:col-span-2">
          <label class="block mb-1.5 text-sm font-semibold text-slate-700">Data de Nascimento <span class="text-rose-500">*</span></label>
          <input type="date" name="birth_date" value="<?= htmlspecialchars($birth_date ?? '') ?>" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50/50" required>
        </div>

        <div>
          <label class="block mb-1.5 text-sm font-semibold text-slate-700">Palavra-passe <span class="text-rose-500">*</span></label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 text-xs">🔒</span>
            <input type="password" name="password" class="w-full border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50/50" placeholder="Mín. 6 caracteres" required>
          </div>
        </div>

        <div>
          <label class="block mb-1.5 text-sm font-semibold text-slate-700">Confirmar <span class="text-rose-500">*</span></label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 text-xs">🔄</span>
            <input type="password" name="confirm_password" class="w-full border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50/50" placeholder="Repita a password" required>
          </div>
        </div>
      </div>

      <div class="pt-4">
        <button type="submit" class="w-full py-3 rounded-xl bg-slate-950 text-white font-semibold text-sm hover:bg-blue-600 transition shadow-sm">
          Registar
        </button>
      </div>
    </form>

    <div class="mt-6 pt-4 border-t border-slate-100 text-center">
      <p class="text-sm text-slate-500">
        Já tens uma conta ativa? 
        <a href="/bookwave/public/login.php" class="text-blue-600 font-semibold hover:underline">Iniciar sessão</a>
      </p>
    </div>
  </div>

</div>

<?php page_end(); ?>