<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/ui.php';

start_session();
$user = current_user();

if (!$user) {
  header('Location: /bookwave/public/login.php');
  exit;
}

$erro = $_GET['erro'] ?? '';

$stmt = db()->prepare("
  SELECT rentals.*, books.title, books.author, books.category
  FROM rentals
  INNER JOIN books ON rentals.book_id = books.id
  WHERE rentals.user_id = ?
  ORDER BY rentals.rented_at DESC
");
$stmt->execute([$user['id']]);
$rentals = $stmt->fetchAll();

page_start('Meus Alugueres - BookWave', $user);
?>

<h1 class="text-2xl font-bold mb-6">Meus Alugueres</h1>

<?php if ($erro === 'limite_renovacoes'): ?>
  <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700">
    Este aluguer já atingiu o limite de 2 renovações.
  </div>
<?php endif; ?>

<?php if (!$rentals): ?>
  <div class="bg-white border rounded-xl p-6 text-gray-600">
    Ainda não alugaste nenhum livro.
  </div>
<?php else: ?>

  <div class="space-y-4">
    <?php foreach ($rentals as $r): ?>

      <div class="bg-white border rounded-xl p-5">

        <div class="flex items-start justify-between gap-4">
          <div>
            <h2 class="text-lg font-semibold">
              <?= htmlspecialchars($r['title']) ?>
            </h2>

            <p class="text-sm text-gray-600">
              <?= htmlspecialchars($r['author']) ?>
            </p>

            <p class="text-sm text-gray-500 mt-1">
              <?= htmlspecialchars($r['category']) ?>
            </p>
          </div>

          <div class="text-right">
            <span class="inline-block px-3 py-1 rounded text-sm <?= $r['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700' ?>">
              
              <?= $r['status'] === 'active' ? 'Ativo' : 'Devolvido' ?>

            </span>
          </div>
        </div>

        <div class="mt-4 text-sm text-gray-700">
          <p>
            <strong>Data de aluguer:</strong>
            <?= htmlspecialchars($r['rented_at']) ?>
          </p>

          <p>
            <strong>Prazo de entrega:</strong>
            <?= htmlspecialchars($r['due_at']) ?>
          </p>

          <p>
            <strong>Renovações:</strong>
            <?= (int)$r['renewed_count'] ?>/2
          </p>

          <?php if ($r['returned_at']): ?>
            <p>
              <strong>Devolvido em:</strong>
              <?= htmlspecialchars($r['returned_at']) ?>
            </p>
          <?php endif; ?>
        </div>

        <?php if ($r['status'] === 'active'): ?>

          <div class="mt-4 flex gap-2">

            <?php if ((int)$r['renewed_count'] < 2): ?>

              <form method="post" action="/bookwave/public/renew_book.php">
                <input
                  type="hidden"
                  name="rental_id"
                  value="<?= (int)$r['id'] ?>"
                >

                <button
                  type="submit"
                  class="px-4 py-2 rounded bg-blue-600 text-white"
                >
                  Renovar
                </button>
              </form>

            <?php endif; ?>

          </div>

        <?php endif; ?>

      </div>

    <?php endforeach; ?>
  </div>

<?php endif; ?>

<?php page_end(); ?>