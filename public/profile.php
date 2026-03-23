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

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $birth_date = $_POST['birth_date'] ?? null;
  $newPassword = $_POST['new_password'] ?? '';
  $confirmPassword = $_POST['confirm_password'] ?? '';

  if ($name === '' || $email === '') {
    $error = 'Nome e email são obrigatórios.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Email inválido.';
  } else {
    try {
      // Verifica se email já existe
      $stmt = db()->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
      $stmt->execute([$email, $user['id']]);
      $existingUser = $stmt->fetch();

      if ($existingUser) {
        $error = 'Esse email já está a ser usado por outra conta.';
      } else {

        // Se quiser mudar password
        if ($newPassword !== '' || $confirmPassword !== '') {

          if (strlen($newPassword) < 6) {
            $error = 'A nova password deve ter pelo menos 6 caracteres.';
          } elseif ($newPassword !== $confirmPassword) {
            $error = 'A confirmação da password não coincide.';
          } else {
            $stmt = db()->prepare("
              UPDATE users 
              SET name = ?, email = ?, birth_date = ?, password_hash = ? 
              WHERE id = ?
            ");
            $stmt->execute([
              $name, 
              $email, 
              $birth_date, 
              password_hash($newPassword, PASSWORD_DEFAULT), 
              $user['id']
            ]);

            $success = 'Perfil e password atualizados com sucesso.';
          }

        } else {
          // Atualizar só dados
          $stmt = db()->prepare("
            UPDATE users 
            SET name = ?, email = ?, birth_date = ? 
            WHERE id = ?
          ");
          $stmt->execute([
            $name, 
            $email, 
            $birth_date, 
            $user['id']
          ]);

          $success = 'Perfil atualizado com sucesso.';
        }

        // Atualizar sessão
        $user = current_user();
      }

    } catch (Exception $e) {
      $error = 'Erro ao atualizar o perfil.';
    }
  }
}

page_start('Perfil - BookWave', $user);
?>

<div class="max-w-xl mx-auto bg-white border rounded-xl p-6">
  <h1 class="text-2xl font-bold mb-6">Meu Perfil</h1>

  <?php if ($success): ?>
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded text-green-700">
      <?= htmlspecialchars($success) ?>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <form method="post">
    <label class="block mb-1 font-medium">Nome</label>
    <input 
      type="text" 
      name="name" 
      value="<?= htmlspecialchars($user['name']) ?>" 
      class="w-full border rounded px-3 py-2 mb-4" 
      required
    >

    <label class="block mb-1 font-medium">Email</label>
    <input 
      type="email" 
      name="email" 
      value="<?= htmlspecialchars($user['email']) ?>" 
      class="w-full border rounded px-3 py-2 mb-4" 
      required
    >
    
    <label class="block mb-1 font-medium">Data de Nascimento</label>
    <input 
      type="date" 
      name="birth_date" 
      value="<?= htmlspecialchars($user['birth_date'] ?? '') ?>"
      class="w-full border rounded-lg px-3 py-2 mb-4" 
      required
    >

    <hr class="my-6">

    <h2 class="text-lg font-semibold mb-4">Mudar password</h2>

    <label class="block mb-1 font-medium">Nova password</label>
    <input 
      type="password" 
      name="new_password" 
      class="w-full border rounded px-3 py-2 mb-4"
    >

    <label class="block mb-1 font-medium">Confirmar nova password</label>
    <input 
      type="password" 
      name="confirm_password" 
      class="w-full border rounded px-3 py-2 mb-6"
    >

    <button 
      type="submit" 
      class="w-full bg-gray-900 text-white rounded px-4 py-2 hover:bg-gray-800"
    >
      Guardar alterações
    </button>
  </form>
</div>

<?php page_end(); ?>