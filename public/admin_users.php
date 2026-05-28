<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/ui.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_admin();
$user = current_user();

// Configuração da Paginação
$perPage = 7; 
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) $page = 1;
$offset = ($page - 1) * $perPage;

$q = trim($_GET['q'] ?? '');
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

$params = [];
$sqlWhere = '';
if ($q !== '') {
    $sqlWhere = "WHERE name LIKE ? OR email LIKE ?";
    $like = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
}

// Total de utilizadores (para cálculo da paginação)
$stmt = db()->prepare("SELECT COUNT(*) FROM users $sqlWhere");
$stmt->execute($params);
$totalUsers = (int)$stmt->fetchColumn();
$totalPages = ceil($totalUsers / $perPage);

// Buscar utilizadores da página atual
$sql = "SELECT * FROM users $sqlWhere ORDER BY id DESC LIMIT $perPage OFFSET $offset";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

page_start('Clientes - Admin - BookWave', $user);
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
  <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Clientes Registados</h1>
  <button type="button" onclick="openAddUserModal()" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition shadow-sm">
    ➕ Adicionar Cliente
  </button>
</div>

<?php if (!empty($success) || !empty($error)): ?>
  <div class="mb-6 space-y-3">
    <?php if ($success === 'user_created'): ?>
      <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-700 text-sm font-medium flex items-center gap-2 shadow-sm">
        <span>✅</span> Cliente adicionado com sucesso.
      </div>
    <?php elseif ($success): ?>
      <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-700 text-sm font-medium flex items-center gap-2 shadow-sm">
        <span>✅</span> <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-700 text-sm font-medium flex items-center gap-2 shadow-sm">
        <span>⚠️</span> Erro ao processar o pedido. Verifica os dados introduzidos.
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm mb-6">
  <form method="get" class="flex flex-col sm:flex-row gap-3">
    <div class="relative flex-1">
      <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">🔍</span>
      <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Pesquisar por nome ou email..." class="w-full border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50/50">
    </div>
    <button type="submit" class="px-5 py-2 rounded-xl bg-slate-950 text-white text-sm font-semibold hover:bg-blue-600 transition shadow-sm">
      Pesquisar
    </button>
    <?php if ($q !== ''): ?>
      <a href="?" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 text-center transition">
        Limpar Filtro
      </a>
    <?php endif; ?>
  </form>
</div>

<div class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-sm whitespace-nowrap">
      <thead>
        <tr class="border-b border-slate-100 bg-slate-50/70 text-slate-400 font-semibold text-xs uppercase tracking-wider">
          <th class="py-3.5 px-4">ID</th>
          <th class="py-3.5 px-4">Utilizador / Cliente</th>
          <th class="py-3.5 px-4">Email</th>
          <th class="py-3.5 px-4">Cargo / Tipo</th>
          <th class="py-3.5 px-4">Data de Registo</th>
          <th class="py-3.5 px-4 text-right">Ações</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
        <?php foreach ($users as $u): ?>
          <tr class="hover:bg-slate-50/60 transition">
            <td class="py-4 px-4 text-slate-400 font-mono text-xs">#<?= (int)$u['id'] ?></td>
            <td class="py-4 px-4">
              <div class="font-bold text-slate-900"><?= htmlspecialchars($u['name']) ?></div>
            </td>
            <td class="py-4 px-4 text-slate-600"><?= htmlspecialchars($u['email']) ?></td>
            <td class="py-4 px-4">
              <?php if ((int)$u['is_admin'] === 1): ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                  🛡️ Administrador
                </span>
              <?php else: ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-600">
                  👤 Cliente
                </span>
              <?php endif; ?>
            </td>
            <td class="py-4 px-4 text-slate-500 text-xs">
              📅 <?= date('d/m/Y H:i', strtotime($u['created_at'])) ?>
            </td>
            <td class="py-4 px-4 text-right">
              <div class="inline-flex items-center gap-2">
                <a href="/bookwave/public/admin_user_details.php?id=<?= (int)$u['id'] ?>" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 bg-white hover:bg-slate-50 hover:text-blue-600 transition shadow-sm">
                  Ver Ficha
                </a>
                <form method="post" action="/bookwave/public/delete_user.php" onsubmit="return confirm('Tens a certeza que queres apagar permanentemente este cliente e revogar todos os seus acessos?')" class="inline">
                  <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                  <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 border border-rose-100 text-xs font-bold text-rose-600 hover:bg-rose-600 hover:text-white transition shadow-sm">
                    Apagar
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>

        <?php if (empty($users)): ?>
          <tr>
            <td colspan="6" class="py-12 px-4 text-center text-slate-400 font-normal">
              Nenhum cliente encontrado correspondente aos critérios de pesquisa.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($totalPages > 1): ?>
    <div class="flex justify-center items-center gap-1.5 py-4 bg-slate-50/50 border-t border-slate-100">
      <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>&q=<?= urlencode($q) ?>" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition text-xs font-semibold text-slate-600">‹ Anterior</a>
      <?php else: ?>
        <span class="px-3 py-1.5 rounded-xl border border-slate-100 bg-slate-50 text-slate-400 text-xs font-semibold cursor-not-allowed">‹ Anterior</span>
      <?php endif; ?>

      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <?php if ($p == $page): ?>
          <span class="px-3 py-1.5 rounded-xl bg-slate-950 text-white text-xs font-bold shadow-sm"><?= $p ?></span>
        <?php else: ?>
          <a href="?page=<?= $p ?>&q=<?= urlencode($q) ?>" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition text-xs font-semibold text-slate-600"><?= $p ?></a>
        <?php endif; ?>
      <?php endfor; ?>

      <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>&q=<?= urlencode($q) ?>" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition text-xs font-semibold text-slate-600">Próxima ›</a>
      <?php else: ?>
        <span class="px-3 py-1.5 rounded-xl border border-slate-100 bg-slate-50 text-slate-400 text-xs font-semibold cursor-not-allowed">Próxima ›</span>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<div id="addUserModal" class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm hidden items-center justify-center z-50 px-4 transition-all duration-200 animate-fade-in">
  <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden border border-slate-100 animate-slide-up">
    
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
      <h2 class="text-xl font-bold text-slate-900">Adicionar Novo Cliente</h2>
      <button type="button" onclick="closeAddUserModal()" class="w-8 h-8 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-slate-600 hover:shadow-sm text-xl transition">&times;</button>
    </div>

    <form method="post" action="/bookwave/public/create_user.php" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block mb-1.5 text-sm font-semibold text-slate-700">Nome Completo *</label>
        <input type="text" name="name" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: Maria Silva" required>
      </div>

      <div>
        <label class="block mb-1.5 text-sm font-semibold text-slate-700">Endereço de Email *</label>
        <input type="email" name="email" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: maria@email.com" required>
      </div>

      <div class="md:col-span-2">
        <label class="block mb-1.5 text-sm font-semibold text-slate-700">Data de Nascimento *</label>
        <input type="date" name="birth_date" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>

      <div>
        <label class="block mb-1.5 text-sm font-semibold text-slate-700">Palavra-passe *</label>
        <input type="password" name="password" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>

      <div>
        <label class="block mb-1.5 text-sm font-semibold text-slate-700">Confirmar Palavra-passe *</label>
        <input type="password" name="confirm_password" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>

      <div class="md:col-span-2 flex items-center justify-end gap-3 pt-4 border-t border-slate-100 mt-4">
        <button type="button" onclick="closeAddUserModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition">
          Cancelar
        </button>
        <button type="submit" class="px-5 py-2 rounded-xl bg-slate-950 text-white text-sm font-semibold hover:bg-blue-600 transition shadow-sm">
          Criar Perfil de Cliente
        </button>
      </div>
    </form>

  </div>
</div>

<script>
function openAddUserModal() {
    const modal = document.getElementById('addUserModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeAddUserModal() {
    const modal = document.getElementById('addUserModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
document.addEventListener('click', function(e) {
    const modal = document.getElementById('addUserModal');
    if(e.target === modal) closeAddUserModal();
});
</script>

<?php page_end(); ?>

```