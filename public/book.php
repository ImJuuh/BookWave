<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/ui.php';

$user = current_user();
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die('Livro inválido.');

$stmt = db()->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch();
if (!$book) die('Livro não encontrado.');

$erro = $_GET['erro'] ?? '';
page_start($book['title'] . ' - BookWave', $user);
?>

<a href="/bookwave/public/" class="text-sm text-gray-700 hover:underline">← Voltar</a>

<div class="bg-white border rounded-xl p-6 mt-4">
  <div class="grid md:grid-cols-2 gap-8">
    <div>
      <?php if (!empty($book['cover_url'])): ?>
        <img src="<?= htmlspecialchars($book['cover_url']) ?>" alt="<?= htmlspecialchars($book['title']) ?>" class="w-full h-[500px] object-cover rounded-xl">
      <?php endif; ?>
    </div>

    <div>
      <h1 class="text-3xl font-bold"><?= htmlspecialchars($book['title']) ?></h1>
      <div class="text-gray-600 mt-2"><?= htmlspecialchars($book['author']) ?></div>
      <div class="mt-3 inline-block bg-gray-100 px-2 py-1 rounded text-sm"><?= htmlspecialchars($book['category']) ?></div>
      <div class="mt-3 text-sm text-yellow-700">Classificação: <?= htmlspecialchars($book['rating']) ?></div>
      <div class="mt-3 text-sm">Disponível: <?= (int)$book['stock'] ?>/<?= (int)$book['total_stock'] ?></div>

      <?php if ($erro === 'sem_stock'): ?>
        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded text-red-700">
          Este livro não tem stock disponível.
        </div>
      <?php endif; ?>

      <div class="mt-6">
        <h2 class="font-semibold mb-2">Descrição</h2>
        <p class="text-gray-700"><?= nl2br(htmlspecialchars($book['description'] ?: 'Sem descrição disponível.')) ?></p>
      </div>

      <div class="mt-6">
        <?php if ($user): ?>
          <form method="post" action="/bookwave/public/rent_book.php">
            <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
            <button type="submit" class="px-4 py-2 rounded bg-gray-900 text-white <?= ((int)$book['stock'] <= 0 ? 'opacity-50 cursor-not-allowed' : '') ?>" <?= ((int)$book['stock'] <= 0 ? 'disabled' : '') ?>>
              Alugar
            </button>
          </form>
        <?php else: ?>
          <a href="/bookwave/public/login.php" class="px-4 py-2 rounded bg-gray-900 text-white inline-block">
            Entrar para alugar
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php page_end(); ?>
