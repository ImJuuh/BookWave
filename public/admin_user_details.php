<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/ui.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_admin();

$admin = current_user();
$userId = (int)($_GET['id'] ?? 0);

if ($userId <= 0) {
    die('Utilizador inválido.');
}

$stmt = db()->prepare("SELECT id, name, email, birth_date, is_admin, created_at FROM users WHERE id = ?");
$stmt->execute([$userId]);
$client = $stmt->fetch();

if (!$client) {
    die('Utilizador não encontrado.');
}

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
        if ($r['due_at'] < $now) {
            $overdueRentals++;
        }
    } else {
        $returnedRentals++;
    }
}

page_start('Detalhes do Cliente - BookWave', $admin);
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
  <div>
    <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Gestão de Contas</span>
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-0.5">Ficha do Cliente</h1>
  </div>
  <a href="/bookwave/public/admin_users.php" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
      <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
    </svg>
    Voltar à lista
  </a>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
  <div class="bg-white border border-slate-100 p-4 rounded-2xl shadow-sm">
    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Alugueres</span>
    <span class="text-2xl font-black text-slate-900 mt-1 block"><?= $totalRentals ?></span>
  </div>
  <div class="bg-white border border-slate-100 p-4 rounded-2xl shadow-sm">
    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Ativos Agora</span>
    <span class="text-2xl font-black text-emerald-600 mt-1 block"><?= $activeRentals ?></span>
  </div>
  <div class="bg-white border border-slate-100 p-4 rounded-2xl shadow-sm">
    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Devolvidos</span>
    <span class="text-2xl font-black text-slate-700 mt-1 block"><?= $returnedRentals ?></span>
  </div>
  <div class="bg-white border border-slate-100 p-4 rounded-2xl shadow-sm">
    <span class="text-xs font-bold text-slate-400 tracking-wider block uppercase">Em Atraso</span>
    <span class="text-2xl font-black <?= $overdueRentals > 0 ? 'text-rose-600 animate-pulse' : 'text-slate-300' ?> mt-1 block">
      <?= $overdueRentals ?>
    </span>
  </div>
</div>

<div class="bg-white border border-slate-100 rounded-3xl p-6 md:p-8 shadow-sm mb-8">
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-slate-100">
    <div class="flex items-center gap-4">
      <div class="w-14 h-14 bg-slate-100 text-slate-700 font-bold text-xl flex items-center justify-center rounded-2xl border border-slate-200">
        <?= strtoupper(substr($client['name'], 0, 2)) ?>
      </div>
      <div>
        <div class="flex items-center gap-2">
          <h2 class="text-2xl font-bold text-slate-900"><?= htmlspecialchars($client['name']) ?></h2>
          <?php if ((int)$client['is_admin'] === 1): ?>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">🛡️ Admin</span>
          <?php else: ?>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-600">👤 Cliente</span>
          <?php endif; ?>
        </div>
        <p class="text-sm text-slate-400 font-medium mt-0.5">Conta criada em: <?= date('d/m/Y H:i', strtotime($client['created_at'])) ?></p>
      </div>
    </div>

    <div class="shrink-0">
      <?php if ((int)$client['id'] !== (int)$admin['id']): ?>
        <?php if ((int)$client['is_admin'] === 0): ?>
          <form method="post" action="/bookwave/public/promote_user.php" onsubmit="return confirm('Promover este utilizador a Administrador?')">
            <input type="hidden" name="user_id" value="<?= (int)$client['id'] ?>">
            <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-blue-50 border border-blue-100 text-blue-700 text-xs font-bold hover:bg-blue-600 hover:text-white transition shadow-sm">
              Promover a Administrador
            </button>
          </form>
        <?php else: ?>
          <form method="post" action="/bookwave/public/demote_user.php" onsubmit="return confirm('Revogar permissões administrativas deste utilizador?')">
            <input type="hidden" name="user_id" value="<?= (int)$client['id'] ?>">
            <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 text-xs font-bold hover:bg-rose-600 hover:text-white transition shadow-sm">
              Revogar Cargo Admin
            </button>
          </form>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 text-sm">
    <div>
      <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Identificador Único</span>
      <span class="font-mono text-slate-900 font-semibold bg-slate-50 px-2 py-1 rounded border border-slate-100">#<?= (int)$client['id'] ?></span>
    </div>
    <div>
      <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Endereço de E-mail</span>
      <span class="text-slate-800 font-medium"><?= htmlspecialchars($client['email']) ?></span>
    </div>
    <div>
      <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Data de Nascimento</span>
      <span class="text-slate-800 font-medium">
        🎂 <?= !empty($client['birth_date']) ? date('d/m/Y', strtotime($client['birth_date'])) : 'Não registada' ?>
      </span>
    </div>
  </div>
</div>

<div class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden">
  <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
    <h3 class="text-lg font-bold text-slate-900">Histórico de Alugueres</h3>
  </div>

  <?php if (empty($rentals)): ?>
    <div class="text-center py-12 text-slate-400 font-normal">
      Este utilizador ainda não realizou qualquer requisição na biblioteca.
    </div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm whitespace-nowrap">
        <thead>
          <tr class="border-b border-slate-100 bg-slate-50/30 text-slate-400 font-semibold text-xs uppercase tracking-wider">
            <th class="py-3 px-4">Obra / Título</th>
            <th class="py-3 px-4">Categoria</th>
            <th class="py-3 px-4">Retirado Em</th>
            <th class="py-3 px-4">Prazo Limite</th>
            <th class="py-3 px-4">Devolvido Em</th>
            <th class="py-3 px-4 text-center">Renovações</th>
            <th class="py-3 px-4">Situação</th>
            <th class="py-3 px-4 text-right">Ação</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
          <?php foreach ($rentals as $r): ?>
            <tr class="hover:bg-slate-50/60 transition">
              <td class="py-4 px-4">
                <div class="font-bold text-slate-900"><?= htmlspecialchars($r['title']) ?></div>
                <div class="text-xs text-slate-400 font-normal"><?= htmlspecialchars($r['author']) ?></div>
              </td>
              <td class="py-4 px-4">
                <span class="inline-block px-2 py-0.5 bg-slate-100 rounded-md text-slate-600 text-xs">
                  <?= htmlspecialchars($r['category']) ?>
                </span>
              </td>
              <td class="py-4 px-4 text-slate-600 text-xs">
                📅 <?= date('d/m/Y', strtotime($r['rented_at'])) ?>
              </td>
              <td class="py-4 px-4 text-slate-600 text-xs">
                ⏰ <?= date('d/m/Y', strtotime($r['due_at'])) ?>
              </td>
              <td class="py-4 px-4 text-xs">
                <?= $r['returned_at'] ? '🎒 ' . date('d/m/Y H:i', strtotime($r['returned_at'])) : '<span class="text-slate-300">—</span>' ?>
              </td>
              <td class="py-4 px-4 text-center text-xs">
                <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-mono font-bold"><?= (int)$r['renewed_count'] ?>/2</span>
              </td>
              <td class="py-4 px-4">
                <?php if ($r['status'] === 'returned'): ?>
                  <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-slate-100 text-slate-600">Concluído</span>
                <?php elseif ($r['due_at'] < $now): ?>
                  <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-rose-50 text-rose-700 border border-rose-100 animate-pulse">⚠️ Em atraso</span>
                <?php else: ?>
                  <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">🟢 No prazo</span>
                <?php endif; ?>
              </td>
              <td class="py-4 px-4 text-right">
                <?php if ($r['status'] === 'active'): ?>
                  <form method="post" action="/bookwave/public/return_book.php" onsubmit="return confirm('Forçar a baixa e confirmar devolução física do livro?')" class="inline">
                    <input type="hidden" name="rental_id" value="<?= (int)$r['id'] ?>">
                    <input type="hidden" name="redirect_user_id" value="<?= (int)$client['id'] ?>">
                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-100 text-xs font-bold text-emerald-700 hover:bg-emerald-600 hover:text-white transition shadow-sm">
                      Dar Baixa
                    </button>
                  </form>
                <?php else: ?>
                  <span class="text-slate-300 text-xs font-normal">Sem ações</span>
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

```