<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/ui.php';

start_session();
require_admin();

$admin = current_user();
$userId = (int)($_GET['id'] ?? 0);

if ($userId <= 0) die('Utilizador inválido.');

$stmt = db()->prepare("SELECT id, name, email, is_admin, created_at FROM users WHERE id = ?");
$stmt->execute([$userId]);
$client = $stmt->fetch();

if (!$client) die('Utilizador não encontrado.');

$stmt = db()->prepare("
  SELECT rentals.*, books.title, books.author, books.category
  FROM rentals
  INNER JOIN books ON rentals.book_id = books.id
  WHERE rentals.user_id = ?
  ORDER BY rentals.rented_at DESC
");
$stmt->execute([$userId]);
$rentals = $stmt->fetchAll();

$totalRentals = count($rentals);
$activeRentals = 0;
$returnedRentals = 0;
$overdueRentals = 0;
$now = date('Y-m-d H:i:s');

foreach ($rentals as $r) {
  if ($r['status'] === 'active') {
    $activeRentals++;
    if ($r['due_at'] < $now) $overdueRentals++;
  } else {
    $returnedRentals++;
  }
}

page_start('Detalhes do Cliente - BookWave', $admin);
?>

<div class="flex items-center justify-between mb-6">
  <h1 class="text-3xl font-bold">Detalhes do Cliente</h1>
  <a href="/bookwave/public/admin_users.php" class="px-4 py-2 rounded border">← Voltar</a>
</div>

<div class="grid md:grid-cols-2 gap-6 mb-8">
  <div class="bg-white border rounded-2xl p-6 shadow-sm">
    <h2 class="text-xl font-semibold mb-4">Informações</h2>

    <p class="mb-2"><strong>ID:</strong> <?= (int)$client['id'] ?></p>
    <p class="mb-2"><strong>Nome:</strong> <?= htmlspecialchars($client['name']) ?></p>
    <p class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($client['email']) ?></p>
    <p class="mb-2">
      <strong>Tipo:</strong>
      <?php if ((int)$client['is_admin'] === 1): ?>
        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm">Admin</span>
      <?php else: ?>
        <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-sm">Cliente</span>
      <?php endif; ?>
    </p>
    <p><strong>Data de registo:</strong> <?= htmlspecialchars($client['created_at']) ?></p>

    <div class="mt-6 flex flex-wrap gap-3">
      <?php if ((int)$client['id'] !== (int)$admin['id']): ?>
        <?php if ((int)$client['is_admin'] === 0): ?>
          <form method="post" action="/bookwave/public/promote_user.php">
            <input type="hidden" name="user_id" value="<?= (int)$client['id'] ?>">
            <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white text-sm">Tornar admin</button>
          </form>
        <?php else: ?>
          <form method="post" action="/bookwave/public/demote_user.php">
            <input type="hidden" name="user_id" value="<?= (int)$client['id'] ?>">
            <button type="submit" class="px-4 py-2 rounded bg-yellow-500 text-white text-sm">Tirar admin</button>
          </form>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="bg-white border rounded-2xl p-6 shadow-sm">
    <h2 class="text-xl font-semibold mb-4">Resumo</h2>
    <p class="mb-2"><strong>Total de alugueres:</strong> <?= $totalRentals ?></p>
    <p class="mb-2"><strong>Alugueres ativos:</strong> <?= $activeRentals ?></p>
    <p class="mb-2"><strong>Alugueres devolvidos:</strong> <?= $returnedRentals ?></p>
    <p class="mb-2"><strong>Livros ativos neste momento:</strong> <?= $activeRentals ?></p>
    <p><strong>Livros em atraso:</strong>
      <?php if ($overdueRentals > 0): ?>
        <span class="text-red-600 font-semibold"><?= $overdueRentals ?></span>
      <?php else: ?>
        <span class="text-green-600 font-semibold">0</span>
      <?php endif; ?>
    </p>
  </div>
</div>

<div class="bg-white border rounded-2xl p-6 shadow-sm">
  <h2 class="text-xl font-semibold mb-4">Livros alugados</h2>

  <?php if (!$rentals): ?>
    <p class="text-gray-600">Este utilizador ainda não alugou livros.</p>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full border-collapse">
        <thead>
          <tr class="border-b bg-gray-50 text-left">
            <th class="py-4 px-4">Livro</th>
            <th class="py-4 px-4">Autor</th>
            <th class="py-4 px-4">Categoria</th>
            <th class="py-4 px-4">Estado</th>
            <th class="py-4 px-4">Data de aluguer</th>
            <th class="py-4 px-4">Prazo</th>
            <th class="py-4 px-4">Devolvido em</th>
            <th class="py-4 px-4">Renovações</th>
            <th class="py-4 px-4">Situação</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rentals as $r): ?>
            <tr class="border-b hover:bg-gray-50">
              <td class="py-4 px-4"><?= htmlspecialchars($r['title']) ?></td>
              <td class="py-4 px-4"><?= htmlspecialchars($r['author']) ?></td>
              <td class="py-4 px-4"><?= htmlspecialchars($r['category']) ?></td>
              <td class="py-4 px-4">
                <?php if ($r['status'] === 'active'): ?>
                  <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm">Ativo</span>
                <?php else: ?>
                  <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-sm">Devolvido</span>
                <?php endif; ?>
              </td>
              <td class="py-4 px-4"><?= htmlspecialchars($r['rented_at']) ?></td>
              <td class="py-4 px-4"><?= htmlspecialchars($r['due_at']) ?></td>
              <td class="py-4 px-4"><?= htmlspecialchars($r['returned_at'] ?? '-') ?></td>
              <td class="py-4 px-4"><?= (int)$r['renewed_count'] ?></td>
              <td class="py-4 px-4">
                <?php if ($r['status'] === 'returned'): ?>
                  <span class="text-gray-600">Concluído</span>
                <?php elseif ($r['due_at'] < $now): ?>
                  <span class="text-red-600 font-semibold">Em atraso</span>
                <?php else: ?>
                  <span class="text-green-600 font-semibold">Dentro do prazo</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php page_end(); ?>
