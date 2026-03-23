<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/ui.php';

start_session();
require_admin();

$user = current_user();

$q = trim($_GET['q'] ?? '');
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

$sql = "
  SELECT 
    users.id,
    users.name,
    users.email,
    users.is_admin,
    users.created_at,
    COUNT(rentals.id) AS total_rentals
  FROM users
  LEFT JOIN rentals ON rentals.user_id = users.id
  WHERE 1=1
";

$params = [];

if ($q !== '') {
  $sql .= " AND (users.name LIKE ? OR users.email LIKE ?)";
  $like = '%' . $q . '%';
  $params[] = $like;
  $params[] = $like;
}

$sql .= "
  GROUP BY users.id, users.name, users.email, users.is_admin, users.created_at
  ORDER BY users.id DESC
";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

page_start('Clientes - Admin - BookWave', $user);
?>

<div class="flex items-center justify-between mb-6">
  <h1 class="text-3xl font-bold">Clientes Registados</h1>
  <button type="button" onclick="openAddUserModal()" class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800">
    Adicionar Cliente
  </button>
</div>

<?php if ($success === 'user_deleted'): ?>
  <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded text-green-700">Cliente apagado com sucesso.</div>
<?php endif; ?>

<?php if ($success === 'promoted'): ?>
  <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded text-green-700">Cliente promovido para admin com sucesso.</div>
<?php endif; ?>

<?php if ($success === 'demoted'): ?>
  <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded text-green-700">Admin alterado para cliente com sucesso.</div>
<?php endif; ?>

<?php if ($success === 'user_created'): ?>
  <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded text-green-700">Utilizador criado com sucesso.</div>
<?php endif; ?>

<?php if ($error === 'cannot_delete_self'): ?>
  <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700">Não podes apagar a tua própria conta.</div>
<?php endif; ?>

<?php if ($error === 'cannot_change_self'): ?>
  <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700">Não podes alterar as permissões da tua própria conta.</div>
<?php endif; ?>

<?php if ($error === 'user_has_rentals'): ?>
  <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700">Não é possível apagar este cliente porque ele tem alugueres associados.</div>
<?php endif; ?>

<?php if ($error === 'invalid_user_data'): ?>
  <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700">Dados inválidos. Verifica nome, email e password.</div>
<?php endif; ?>

<?php if ($error === 'password_mismatch'): ?>
  <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700">As passwords não coincidem.</div>
<?php endif; ?>

<?php if ($error === 'email_exists'): ?>
  <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700">Esse email já está registado.</div>
<?php endif; ?>

<div class="bg-white border rounded-2xl shadow-sm p-4 mb-6">
  <form method="get" class="flex gap-3">
    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Pesquisar por nome ou email..." class="flex-1 border rounded px-4 py-2">
    <button type="submit" class="px-5 py-2 rounded bg-slate-900 text-white">Pesquisar</button>
  </form>
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
        <th class="py-4 px-4">Total de alugueres</th>
        <th class="py-4 px-4">Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr class="border-b hover:bg-gray-50 align-top">
          <td class="py-4 px-4"><?= (int)$u['id'] ?></td>
          <td class="py-4 px-4"><?= htmlspecialchars($u['name']) ?></td>
          <td class="py-4 px-4"><?= htmlspecialchars($u['email']) ?></td>
          <td class="py-4 px-4">
            <?php if ((int)$u['is_admin'] === 1): ?>
              <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm">Admin</span>
            <?php else: ?>
              <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-sm">Cliente</span>
            <?php endif; ?>
          </td>
          <td class="py-4 px-4"><?= htmlspecialchars($u['created_at']) ?></td>
          <td class="py-4 px-4"><?= (int)$u['total_rentals'] ?></td>
          <td class="py-4 px-4">
            <div class="flex flex-wrap gap-2">
              <a href="/bookwave/public/admin_user_details.php?id=<?= (int)$u['id'] ?>" class="px-3 py-2 rounded bg-slate-900 text-white text-sm">Ver</a>

              <?php if ((int)$u['is_admin'] === 0): ?>
                <form method="post" action="/bookwave/public/promote_user.php">
                  <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                  <button type="submit" class="px-3 py-2 rounded bg-blue-600 text-white text-sm">Tornar admin</button>
                </form>
              <?php else: ?>
                <form method="post" action="/bookwave/public/demote_user.php">
                  <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                  <button type="submit" class="px-3 py-2 rounded bg-yellow-500 text-white text-sm">Tirar admin</button>
                </form>
              <?php endif; ?>

              <form method="post" action="/bookwave/public/delete_user.php" onsubmit="return confirm('Tens a certeza que queres apagar este cliente?')">
                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                <button type="submit" class="px-3 py-2 rounded bg-red-600 text-white text-sm">Apagar</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>

      <?php if (!$users): ?>
        <tr>
          <td colspan="7" class="py-6 px-4 text-center text-gray-500">Nenhum cliente encontrado.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div id="addUserModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 px-4">
  <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b">
      <h2 class="text-xl font-semibold">Adicionar Cliente</h2>
      <button type="button" onclick="closeAddUserModal()" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
    </div>

    <form method="post" action="/bookwave/public/create_user.php" class="p-6 grid md:grid-cols-2 gap-4">
      <div>
        <label class="block mb-1 font-medium">Nome</label>
        <input type="text" name="name" class="w-full border rounded-lg px-3 py-2" required>
      </div>

      <div>
        <label class="block mb-1 font-medium">Email</label>
        <input type="email" name="email" class="w-full border rounded-lg px-3 py-2" required>
      </div>

      <div>
        <label class="block mb-1 font-medium">Data de Nascimento</label>
        <input type="date" name="birth_date" class="w-full border rounded-lg px-3 py-2" required>
      </div>

      <div>
        <label class="block mb-1 font-medium">Password</label>
        <input type="password" name="password" class="w-full border rounded-lg px-3 py-2" required>
      </div>

      <div>
        <label class="block mb-1 font-medium">Confirmar Password</label>
        <input type="password" name="confirm_password" class="w-full border rounded-lg px-3 py-2" required>
      </div>

      <div class="md:col-span-2 flex justify-end gap-3 pt-2">
        <button type="button" onclick="closeAddUserModal()" class="px-4 py-2 rounded-lg border">Cancelar</button>
        <button type="submit" class="px-5 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800">Criar Cliente</button>
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
document.addEventListener('click', function (e) {
  const modal = document.getElementById('addUserModal');
  if (e.target === modal) closeAddUserModal();
});
</script>

<?php page_end(); ?>
