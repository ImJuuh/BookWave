<?php
require_once __DIR__ . '/auth.php';

function page_start(string $title, ?array $user = null): void {
  if ($user === null) {
    $user = current_user();
  }
  ?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <title><?= htmlspecialchars($title) ?></title>
</head>
<body class="bg-gray-50 min-h-screen">
<header class="bg-white border-b shadow-sm">
  <div class="max-w-7xl mx-auto px-6 py-5 flex items-center justify-between">
    <a href="/bookwave/public/" class="flex items-center gap-3">
      <?php if (file_exists(__DIR__ . '/../public/images/logo.png')): ?>
        <img src="/bookwave/public/images/logo.png" alt="BookWave" class="h-20 w-auto">
      <?php else: ?>
        <span class="text-3xl font-bold text-slate-900">BookWave</span>
      <?php endif; ?>
    </a>

    <div class="flex items-center gap-3 flex-wrap justify-end">
      <?php if ($user): ?>
        <span class="text-sm text-gray-700">
          Olá, <?= htmlspecialchars($user['name']) ?>
        </span>

        <a href="/bookwave/public/profile.php" class="px-3 py-2 rounded border text-sm">
          Perfil
        </a>

        <?php if ((int)$user['is_admin'] === 0): ?>
          <a href="/bookwave/public/my_rentals.php" class="px-3 py-2 rounded border text-sm">
            Meus Alugueres
          </a>
        <?php endif; ?>

        <?php if ((int)$user['is_admin'] === 1): ?>
          <a href="/bookwave/public/admin.php" class="px-3 py-2 rounded border text-sm">
            Admin
          </a>

          <a href="/bookwave/public/admin_users.php" class="px-3 py-2 rounded border text-sm">
            Clientes
          </a>
        <?php endif; ?>

        <a href="/bookwave/public/logout.php" class="px-3 py-2 rounded bg-slate-900 text-white text-sm">
          Sair
        </a>
      <?php else: ?>
        <a href="/bookwave/public/login.php" class="px-3 py-2 rounded bg-slate-900 text-white text-sm">
          Entrar
        </a>

        <a href="/bookwave/public/register.php" class="px-3 py-2 rounded border text-sm">
          Registar
        </a>
      <?php endif; ?>
    </div>
  </div>
</header>

<main class="max-w-7xl mx-auto px-6 py-8">
  <?php
}

function page_end(): void {
  ?>
</main>

<footer class="bg-slate-900 text-gray-300 mt-16">
  <div class="max-w-7xl mx-auto px-6 py-12 grid md:grid-cols-3 gap-10">
    <div>
      <div class="mb-3">
        <?php if (file_exists(__DIR__ . '/../public/images/logo.png')): ?>
          <img src="/bookwave/public/images/logo.png" alt="BookWave" class="h-16 w-auto">
        <?php else: ?>
          <span class="text-white text-2xl font-bold">BookWave</span>
        <?php endif; ?>
      </div>

      <p class="text-sm text-gray-400">
        Sua biblioteca online com acesso gratuito aos melhores livros.
        Alugue, leia e devolva quando quiser.
      </p>
    </div>

    <div>
      <h3 class="text-white font-semibold mb-4">Contactos</h3>
      <p class="text-sm mb-2">✉ contato@bookwave.com</p>
      <p class="text-sm">📞 +351 123 456 789</p>
    </div>

    <div>
      <h3 class="text-white font-semibold mb-4">Localização</h3>
      <p class="text-sm text-gray-400">
        📍 Rua dos Livros, 123<br>
        1000-001 Lisboa<br>
        Portugal
      </p>
    </div>
  </div>

  <div class="border-t border-slate-700"></div>

  <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col md:flex-row justify-between items-center text-sm text-gray-400">
    <p>© <?= date('Y') ?> BookWave. Todos os direitos reservados.</p>

    <div class="flex gap-6 mt-3 md:mt-0">
      <a href="#" class="hover:text-white">Termos de Uso</a>
      <a href="#" class="hover:text-white">Política de Privacidade</a>
    </div>
  </div>
</footer>
</body>
</html>
  <?php
}