<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/ui.php';

start_session();
require_admin();
$user = current_user();

// Paginação
$perPage = 10; // 10 utilizadores por página
$page = (int)($_GET['page'] ?? 1);
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

// Total de utilizadores
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

<h1 class="text-2xl font-bold mb-6">Clientes Registados</h1>

<?php if ($success): ?><div class="mb-4 p-3 bg-green-50 border border-green-200 rounded text-green-700"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="flex items-center justify-between mb-4">
    <form method="get" class="flex gap-3">
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Pesquisar por nome ou email..." class="flex-1 border rounded px-4 py-2">
        <button type="submit" class="px-5 py-2 rounded bg-slate-900 text-white">Pesquisar</button>
    </form>
    <a href="/bookwave/public/create_user.php" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Adicionar Cliente</a>
</div>

<div class="bg-white border rounded-2xl shadow-sm overflow-x-auto">
  <table class="w-full border-collapse">
    <thead>
      <tr class="border-b bg-gray-50 text-left">
        <th class="py-4 px-4">ID</th>
        <th class="py-4 px-4">Nome</th>
        <th class="py-4 px-4">Email</th>
        <th class="py-4 px-4">Tipo</th>
        <th class="py-4 px-4">Registado em</th>
        <th class="py-4 px-4">Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr class="border-b hover:bg-gray-50 align-top">
          <td class="py-4 px-4"><?= (int)$u['id'] ?></td>
          <td class="py-4 px-4"><?= htmlspecialchars($u['name']) ?></td>
          <td class="py-4 px-4"><?= htmlspecialchars($u['email']) ?></td>
          <td class="py-4 px-4"><?= ((int)$u['is_admin'] === 1) ? 'Admin' : 'Cliente' ?></td>
          <td class="py-4 px-4"><?= htmlspecialchars($u['created_at']) ?></td>
          <td class="py-4 px-4 flex gap-2">
            <a href="/bookwave/public/admin_user_details.php?id=<?= (int)$u['id'] ?>" class="px-3 py-2 rounded bg-slate-900 text-white text-sm">Ver</a>
            <form method="post" action="/bookwave/public/delete_user.php" onsubmit="return confirm('Tens a certeza que queres apagar este cliente?')">
              <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
              <button type="submit" class="px-3 py-2 rounded bg-red-600 text-white text-sm">Apagar</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>

      <?php if (!$users): ?>
        <tr>
          <td colspan="6" class="py-6 px-4 text-center text-gray-500">Nenhum cliente encontrado.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Paginação -->
<div class="mt-4 flex justify-center gap-2">
  <?php for ($p = 1; $p <= $totalPages; $p++): ?>
    <a href="?page=<?= $p ?>" class="px-3 py-1 rounded <?= ($p == $page) ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' ?>"><?= $p ?></a>
  <?php endfor; ?>
</div>

<?php page_end(); ?>