<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/ui.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
        $error = 'E-mail ou palavra-passe incorretos.';
    }
}

page_start('Login - BookWave');
?>

<div class="max-w-5xl mx-auto bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden min-h-[550px] grid md:grid-cols-2">
  
  <div class="bg-white flex flex-col items-center justify-center p-8 border-b md:border-b-0 md:border-r border-slate-100 selection:bg-blue-100">
    <div class="text-center w-full max-w-xs md:max-w-sm px-4">
      <img src="/bookwave/public/assets/logo.jpg" alt="BookWave Logo" class="w-500 h-auto mx-auto mix-blend-multiply pointer-events-none">
      <p class="text-slate-400 text-sm mt-6 font-medium tracking-wide leading-relaxed">
        Surfa na onda do conhecimento. Aluga, lê e evolui sem limites.
      </p>
    </div>
  </div>

  <div class="flex flex-col justify-center p-8 md:p-12">
    <div class="mb-6">
      <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Iniciar Sessão</h1>
      <p class="text-sm text-slate-400 mt-1">Bem-vindo de volta! Introduz as tuas credenciais para aceder à biblioteca.</p>
    </div>

    <?php if ($error): ?>
      <div class="mb-5 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-700 text-sm font-medium flex items-center gap-2 shadow-sm">
        <span>⚠️</span> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
      <div>
        <label class="block mb-1.5 text-sm font-semibold text-slate-700">Endereço de E-mail</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 text-xs">📧</span>
          <input type="email" name="email" class="w-full border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50/50" placeholder="exemplo@email.com" required>
        </div>
      </div>

      <div>
        <div class="flex items-center justify-between mb-1.5">
          <label class="text-sm font-semibold text-slate-700">Palavra-passe</label>
          <a href="#" class="text-xs font-semibold text-blue-600 hover:underline">Esqueceste-te?</a>
        </div>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 text-xs">🔒</span>
          <input type="password" name="password" class="w-full border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50/50" placeholder="••••••••" required>
        </div>
      </div>

      <div class="pt-2">
        <button type="submit" class="w-full py-3 rounded-xl bg-slate-950 text-white font-semibold text-sm hover:bg-blue-600 transition shadow-sm">
          Entrar na Conta
        </button>
      </div>
    </form>

    <div class="mt-8 pt-6 border-t border-slate-100 text-center">
      <p class="text-sm text-slate-500">
        Ainda não fazes parte da comunidade? 
        <a href="/bookwave/public/register.php" class="text-blue-600 font-semibold hover:underline">Criar conta</a>
      </p>
    </div>
  </div>

</div>

<?php page_end(); ?>