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

page_start('Perfil - BookWave', $user);

// Estatísticas de alugueres
$stmt = db()->prepare("SELECT COUNT(*) FROM rentals WHERE user_id = ?");
$stmt->execute([$user['id']]);
$totalRentals = (int)$stmt->fetchColumn();

$stmt = db()->prepare("SELECT COUNT(*) FROM rentals WHERE user_id = ? AND status='active'");
$stmt->execute([$user['id']]);
$activeRentals = (int)$stmt->fetchColumn();

$stmt = db()->prepare("SELECT COUNT(*) FROM rentals WHERE user_id = ? AND status='active' AND due_at < NOW()");
$stmt->execute([$user['id']]);
$overdueRentals = (int)$stmt->fetchColumn();
?>

<div class="max-w-3xl mx-auto bg-white border rounded-xl p-6 relative shadow-sm">
  <!-- Botão editar -->
  <button 
    onclick="document.getElementById('editForm').classList.toggle('hidden')"
    class="absolute top-4 right-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
    Editar
  </button>

  <div class="flex flex-col md:flex-row items-center gap-6 mb-6">
    <!-- Foto do utilizador -->
    <div>
      <img src="<?= htmlspecialchars($user['profile_pic'] ?? 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png') ?>" 
           alt="Foto de <?= htmlspecialchars($user['name']) ?>" 
           class="w-32 h-32 object-cover rounded-full border-2 border-gray-300">
    </div>

    <!-- Informações -->
    <div class="flex-1">
      <h1 class="text-3xl font-bold mb-2"><?= htmlspecialchars($user['name']) ?></h1>
      <p class="text-gray-700 mb-1"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
      <p class="text-gray-700 mb-1"><strong>Data de Nascimento:</strong> <?= htmlspecialchars($user['birth_date'] ?? '-') ?></p>
      <p class="text-gray-700 mb-1"><strong>Tipo:</strong> <?= ((int)$user['is_admin'] === 1 ? 'Admin' : 'Cliente') ?></p>
      <p class="text-gray-700 mb-1"><strong>Data de Registo:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
    </div>
  </div>

  <!-- Estatísticas rápidas -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-gray-100 p-4 rounded text-center">
      <p class="text-sm text-gray-500">Total de Alugueres</p>
      <p class="text-xl font-bold"><?= $totalRentals ?></p>
    </div>
    <div class="bg-gray-100 p-4 rounded text-center">
      <p class="text-sm text-gray-500">Alugueres Ativos</p>
      <p class="text-xl font-bold"><?= $activeRentals ?></p>
    </div>
    <div class="bg-gray-100 p-4 rounded text-center">
      <p class="text-sm text-gray-500">Livros em Atraso</p>
      <p class="text-xl font-bold text-red-600"><?= $overdueRentals ?></p>
    </div>
  </div>

  <!-- Formulário de edição inline -->
  <div id="editForm" class="hidden bg-gray-50 p-6 border rounded-xl">
    <form method="post" class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block mb-1 font-medium">Nome</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <label class="block mb-1 font-medium">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <label class="block mb-1 font-medium">Data de Nascimento</label>
        <input type="date" name="birth_date" value="<?= htmlspecialchars($user['birth_date'] ?? '') ?>" class="w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <label class="block mb-1 font-medium">Nova Password</label>
        <input type="password" name="new_password" class="w-full border rounded px-3 py-2">
      </div>
      <div>
        <label class="block mb-1 font-medium">Confirmar Nova Password</label>
        <input type="password" name="confirm_password" class="w-full border rounded px-3 py-2">
      </div>
      <div class="md:col-span-2 flex justify-end gap-3 pt-2">
        <button type="submit" class="px-5 py-2 rounded bg-gray-900 text-white hover:bg-gray-800">Guardar Alterações</button>
      </div>
    </form>
  </div>
</div>

<?php page_end(); ?>