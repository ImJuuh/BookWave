<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/ui.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = current_user();

if (!$user) {
    header('Location: /bookwave/public/login.php');
    exit;
}

$success = '';
$error = '';

// Processar atualização dos dados e upload da foto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $birth_date = $_POST['birth_date'] ?? null;
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    $profilePicPath = $user['profile_pic'] ?? null; // Mantém a foto atual por defeito

    if ($name === '' || $email === '') {
        $error = 'Nome e e-mail são de preenchimento obrigatório.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'O endereço de e-mail introduzido é inválido.';
    } else {
        try {
            // 1. Verificar se o e-mail já existe noutra conta
            $stmt = db()->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user['id']]);
            if ($stmt->fetch()) {
                $error = 'Este e-mail já se encontra associado a outra conta.';
            } else {
                
                // 2. Processar o Upload da Foto (se o utilizador escolheu um ficheiro)
                if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
                    $fileName = $_FILES['profile_pic']['name'];
                    $fileSize = $_FILES['profile_pic']['size'];
                    
                    // Validar extensão
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    
                    // Validar tipo real do ficheiro (MIME type) por segurança
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->file($fileTmpPath);
                    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

                    if (!in_array($fileExtension, $allowedExtensions) || !in_array($mimeType, $allowedMimeTypes)) {
                        $error = 'Formato de imagem inválido. Usa apenas JPG, PNG ou WEBP.';
                    } elseif ($fileSize > 2 * 1024 * 1024) { // Limite de 2MB
                        $error = 'A imagem é demasiado grande. O limite máximo é de 2MB.';
                    } else {
                        // Criar pasta de uploads se não existir
                        $uploadDir = __DIR__ . '/../public/uploads/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }

                        // Gerar nome único para evitar ficheiros duplicados ou sobrescritas
                        $newFileName = 'user_' . $user['id'] . '_' . time() . '.' . $fileExtension;
                        $destPath = $uploadDir . $newFileName;

                        if (move_uploaded_file($fileTmpPath, $destPath)) {
                            // Se correu bem, o caminho relativo para guardar na BD será este:
                            $profilePicPath = '/bookwave/public/uploads/' . $newFileName;
                        } else {
                            $error = 'Erro ao salvar a imagem no servidor.';
                        }
                    }
                }

                // Se não houve erros no upload, avançamos para a BD
                if (empty($error)) {
                    if ($newPassword !== '' || $confirmPassword !== '') {
                        if (strlen($newPassword) < 6) {
                            $error = 'A nova palavra-passe deve conter pelo menos 6 caracteres.';
                        } elseif ($newPassword !== $confirmPassword) {
                            $error = 'A confirmação da nova palavra-passe não coincide.';
                        } else {
                            $stmt = db()->prepare("UPDATE users SET name = ?, email = ?, birth_date = ?, profile_pic = ?, password_hash = ? WHERE id = ?");
                            $stmt->execute([$name, $email, $birth_date, $profilePicPath, password_hash($newPassword, PASSWORD_DEFAULT), $user['id']]);
                            $success = 'Perfil e palavra-passe atualizados com sucesso.';
                        }
                    } else {
                        $stmt = db()->prepare("UPDATE users SET name = ?, email = ?, birth_date = ?, profile_pic = ? WHERE id = ?");
                        $stmt->execute([$name, $email, $birth_date, $profilePicPath, $user['id']]);
                        $success = 'Dados do perfil atualizados com sucesso.';
                    }

                    // Recarregar os dados do utilizador na sessão para atualizar o ecrã imediatamente
                    $_SESSION['user_id'] = $user['id']; 
                    $user = current_user();
                }
            }
        } catch (Exception $e) {
            $error = 'Ocorreu um erro inesperado ao atualizar o perfil.';
        }
    }
}

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

page_start('O Meu Perfil - BookWave', $user);
?>

<div class="max-w-3xl mx-auto bg-white border border-slate-100 rounded-3xl p-6 md:p-8 shadow-sm">
  
  <?php if ($success): ?>
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-700 text-sm font-medium flex items-center gap-2 shadow-sm animate-fade-in">
      <span>✅</span> <?= htmlspecialchars($success) ?>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-700 text-sm font-medium flex items-center gap-2 shadow-sm animate-fade-in">
      <span>⚠️</span> <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <div class="flex flex-col sm:flex-row items-center sm:justify-between gap-6 pb-6 border-b border-slate-100 mb-6">
    <div class="flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left w-full">
      
      <div class="relative shrink-0">
        <img src="<?= htmlspecialchars($user['profile_pic'] ?? 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png') ?>" 
             alt="Avatar de <?= htmlspecialchars($user['name']) ?>" 
             class="w-24 h-24 object-cover rounded-2xl border border-slate-200 bg-slate-50 p-1 shadow-sm">
      </div>

      <div class="flex-1">
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 justify-center sm:justify-start">
          <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight"><?= htmlspecialchars($user['name']) ?></h1>
          <div>
            <?php if ((int)$user['is_admin'] === 1): ?>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">🛡️ Administrador</span>
            <?php else: ?>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-600">👤 Leitor</span>
            <?php endif; ?>
          </div>
        </div>
        <p class="text-sm text-slate-400 font-medium mt-1">Membro desde: <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
      </div>
    </div>

    <div class="shrink-0 w-full sm:w-auto">
      <button type="button" onclick="toggleEditForm()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl hover:bg-slate-100 transition shadow-sm">
        ⚙️ Editar Perfil
      </button>
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <div class="bg-slate-50/60 border border-slate-100 p-4 rounded-2xl text-center">
      <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total de Alugueres</span>
      <span class="text-2xl font-black text-slate-800 mt-1 block"><?= $totalRentals ?></span>
    </div>
    <div class="bg-slate-50/60 border border-slate-100 p-4 rounded-2xl text-center">
      <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Alugueres Ativos</span>
      <span class="text-2xl font-black text-blue-600 mt-1 block"><?= $activeRentals ?></span>
    </div>
    <div class="bg-slate-50/60 border border-slate-100 p-4 rounded-2xl text-center">
      <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Livros em Atraso</span>
      <span class="text-2xl font-black <?= $overdueRentals > 0 ? 'text-rose-600' : 'text-slate-300' ?> mt-1 block">
        <?= $overdueRentals ?>
      </span>
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-sm mb-6 bg-slate-50/30 border border-slate-100 rounded-2xl p-5">
    <div>
      <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">E-mail Principal</span>
      <span class="text-slate-800 font-medium"><?= htmlspecialchars($user['email']) ?></span>
    </div>
    <div>
      <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Data de Nascimento</span>
      <span class="text-slate-800 font-medium">
        🎂 <?= !empty($user['birth_date']) ? date('d/m/Y', strtotime($user['birth_date'])) : '<span class="text-slate-300">Não configurada</span>' ?>
      </span>
    </div>
  </div>

  <div id="editForm" class="hidden border border-slate-100 rounded-2xl p-6 bg-slate-50/40 shadow-inner mt-6 animate-slide-up">
    <div class="mb-4">
      <h3 class="text-base font-bold text-slate-900">Atualizar Dados Pessoais</h3>
      <p class="text-xs text-slate-400 mt-0.5">Modifica os campos necessários. O upload aceita JPG, PNG ou WEBP até 2MB.</p>
    </div>

    <form method="post" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block mb-1.5 text-xs font-bold text-slate-700">Nome Completo</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
      </div>

      <div>
        <label class="block mb-1.5 text-xs font-bold text-slate-700">Endereço de E-mail</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
      </div>

      <div>
        <label class="block mb-1.5 text-xs font-bold text-slate-700">Data de Nascimento</label>
        <input type="date" name="birth_date" value="<?= htmlspecialchars($user['birth_date'] ?? '') ?>" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
      </div>

      <div>
        <label class="block mb-1.5 text-xs font-bold text-slate-700">Alterar Foto de Perfil</label>
        <input type="file" name="profile_pic" accept="image/jpeg, image/png, image/webp" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 transition cursor-pointer">
      </div>

      <div>
        <label class="block mb-1.5 text-xs font-bold text-slate-700">Nova Palavra-passe</label>
        <input type="password" name="new_password" placeholder="Preenche apenas para alterar" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
      </div>

      <div>
        <label class="block mb-1.5 text-xs font-bold text-slate-700">Confirmar Nova Palavra-passe</label>
        <input type="password" name="confirm_password" placeholder="Repete a nova password" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
      </div>

      <div class="sm:col-span-2 flex justify-end gap-3 pt-4 border-t border-slate-200/60 mt-2">
        <button type="button" onclick="toggleEditForm()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-white transition">
          Cancelar
        </button>
        <button type="submit" class="px-5 py-2 rounded-xl bg-slate-950 text-white text-xs font-bold hover:bg-blue-600 transition shadow-sm">
          Salvar Alterações
        </button>
      </div>
    </form>
  </div>

</div>

<script>
function toggleEditForm() {
    const form = document.getElementById('editForm');
    form.classList.toggle('hidden');
    if (!form.classList.contains('hidden')) {
        form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}
</script>

<?php page_end(); ?>