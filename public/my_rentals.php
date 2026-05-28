<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/ui.php';

// Verifica se a sessão já foi iniciada para evitar conflitos
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

<div class="flex items-center justify-between mb-6">
  <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Meus Alugueres</h1>
</div>

<?php if ($erro === 'limite_renovacoes'): ?>
  <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-700 text-sm font-medium flex items-center gap-2 shadow-sm">
    <span>⚠️</span>
    Este aluguer já atingiu o limite máximo permitido de 2 renovações.
  </div>
<?php endif; ?>

<?php if (!$rentals): ?>
  <div class="bg-white border border-slate-100 rounded-3xl p-8 text-center shadow-sm max-w-xl mx-auto mt-8">
    <span class="text-4xl block mb-3">📚</span>
    <h2 class="text-lg font-bold text-slate-800 mb-1">Nenhum livro alugado</h2>
    <p class="text-slate-500 text-sm mb-4">Ainda não realizaste nenhuma requisição na nossa biblioteca.</p>
    <a href="/bookwave/public/" class="inline-flex px-5 py-2.5 bg-slate-950 text-white rounded-xl text-sm font-semibold hover:bg-blue-600 transition shadow-sm">
      Explorar Biblioteca
    </a>
  </div>
<?php else: ?>

  <div class="space-y-4 max-w-4xl mx-auto">
    <?php foreach ($rentals as $r): ?>
      <div class="bg-white border border-slate-100 rounded-2xl p-5 md:p-6 shadow-sm hover:shadow-md transition flex flex-col justify-between gap-4">
        
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 pb-4 border-b border-slate-100">
          <div>
            <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-xs font-semibold mb-1">
              <?= htmlspecialchars($r['category']) ?>
            </span>
            <h2 class="text-xl font-bold text-slate-900 leading-tight">
              <?= htmlspecialchars($r['title']) ?>
            </h2>
            <p class="text-sm text-slate-500 font-medium mt-0.5">
              por <?= htmlspecialchars($r['author']) ?>
            </p>
          </div>

          <div class="sm:text-right shrink-0">
            <?php if ($r['status'] === 'active'): ?>
              <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                🟢 Ativo
              </span>
            <?php else: ?>
              <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                ⚪ Devolvido
              </span>
            <?php endif; ?>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm text-slate-700 my-1">
          <div>
            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Data de Aluguer</span>
            <span class="font-medium text-slate-800">
              📅 <?= date('d/m/Y', strtotime($r['rented_at'])) ?>
            </span>
          </div>

          <div>
            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Prazo de Entrega</span>
            <span class="font-medium <?= ($r['status'] === 'active' && strtotime($r['due_at']) < time()) ? 'text-rose-600 font-bold' : 'text-slate-800' ?>">
              ⏰ <?= date('d/m/Y', strtotime($r['due_at'])) ?>
            </span>
          </div>

          <div>
            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Renovações Efetuadas</span>
            <span class="inline-flex items-center gap-1 font-medium px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-xs">
              🔄 <?= (int)$r['renewed_count'] ?> de 2
            </span>
          </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-50 mt-1">
          <div>
            <?php if ($r['returned_at']): ?>
              <p class="text-xs text-slate-500 font-medium">
                🎒 Entregue em: <span class="text-slate-700 font-semibold"><?= date('d/m/Y H:i', strtotime($r['returned_at'])) ?></span>
              </p>
            <?php else: ?>
              <p class="text-xs text-slate-400">
                Faltam poucos dias para o fim do prazo.
              </p>
            <?php endif; ?>
          </div>

          <div>
            <?php if ($r['status'] === 'active'): ?>
              <?php if ((int)$r['renewed_count'] < 2): ?>
                <form method="post" action="/bookwave/public/renew_book.php">
                  <input type="hidden" name="rental_id" value="<?= (int)$r['id'] ?>">
                  <button type="submit" class="bg-blue-50 text-blue-700 border border-blue-100 px-4 py-2 rounded-xl text-xs font-bold hover:bg-blue-600 hover:text-white transition shadow-sm">
                    Renovar Prazo
                  </button>
                </form>
              <?php else: ?>
                <span class="text-xs text-rose-600 bg-rose-50 border border-rose-100 px-3 py-1 rounded-xl font-medium">
                  Limite de renovações atingido
                </span>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>

      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php page_end(); ?>

```