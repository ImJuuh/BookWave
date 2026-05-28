<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/ui.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_admin();
$user = current_user();

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// --- CONFIGURAÇÃO DA PAGINAÇÃO ---
$limit = 5; // Quantidade de livros por página na tabela
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Contar o total de livros
$countStmt = db()->query("SELECT COUNT(*) FROM books");
$totalBooks = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalBooks / $limit);

// Buscar os livros da página atual
$stmt = db()->prepare("SELECT * FROM books ORDER BY id DESC LIMIT ? OFFSET ?");
// Usamos o PDO::PARAM_INT para garantir que o LIMIT e OFFSET funcionem corretamente
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$books = $stmt->fetchAll();

page_start('Admin - BookWave', $user);
?>

<div class="flex items-center justify-between mb-6">
  <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Painel de Administração</h1>
</div>

<?php if (!empty($success) || !empty($error)): ?>
  <div class="mb-8 space-y-3">
    <?php if ($success === 'book_created'): ?>
      <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-700 text-sm font-medium flex items-center gap-2 shadow-sm">
        <span>✅</span> Livro adicionado com sucesso à biblioteca.
      </div>
    <?php endif; ?>

    <?php if ($success === 'book_deleted'): ?>
      <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-700 text-sm font-medium flex items-center gap-2 shadow-sm">
        <span>🗑️</span> Livro removido com sucesso.
      </div>
    <?php endif; ?>

    <?php if ($success === 'book_updated'): ?>
      <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-700 text-sm font-medium flex items-center gap-2 shadow-sm">
        <span>🔄</span> Informações do livro atualizadas com sucesso.
      </div>
    <?php endif; ?>

    <?php if ($error === 'invalid_data'): ?>
      <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-700 text-sm font-medium flex items-center gap-2 shadow-sm">
        <span>⚠️</span> Dados inválidos. Certifica-te de preencher todos os campos obrigatórios corretamente.
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<div class="bg-white border border-slate-100 rounded-3xl p-6 md:p-8 shadow-sm mb-10">
  <div class="border-b border-slate-100 pb-4 mb-6">
    <h2 class="text-xl font-bold text-slate-900">Adicionar Novo Livro</h2>
    <p class="text-xs text-slate-400 mt-1">
      Os campos assinalados com <span class="text-rose-500 font-bold">*</span> são de preenchimento obrigatório.
    </p>
  </div>

  <form method="post" action="/bookwave/public/create_book.php" class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <div>
      <label class="block mb-1.5 text-sm font-semibold text-slate-700">
        Título do Livro <span class="text-rose-500">*</span>
      </label>
      <input type="text" name="title" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: O Senhor dos Anéis" required>
    </div>

    <div>
      <label class="block mb-1.5 text-sm font-semibold text-slate-700">
        Autor <span class="text-rose-500">*</span>
      </label>
      <input type="text" name="author" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: J.R.R. Tolkien" required>
    </div>

    <div>
      <label class="block mb-1.5 text-sm font-semibold text-slate-700">
        Categoria <span class="text-rose-500">*</span>
      </label>
      <select name="category" class="w-full border border-slate-200 rounded-xl px-4 py-2 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        <option value="">Selecionar categoria</option>
        <option value="Fantasia">Fantasia</option>
        <option value="Ficção">Ficção</option>
        <option value="Romance">Romance</option>
        <option value="Mistério">Mistério</option>
        <option value="Terror">Terror</option>
        <option value="História">História</option>
        <option value="Biografia">Biografia</option>
        <option value="Tecnologia">Tecnologia</option>
        <option value="Educação">Educação</option>
        <option value="Clássicos">Clássicos</option>
      </select>
    </div>

    <div>
      <label class="block mb-1.5 text-sm font-semibold text-slate-700">
        Stock Atual <span class="text-rose-500">*</span>
      </label>
      <input type="number" name="stock" min="0" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: 5" required>
    </div>

    <div>
      <label class="block mb-1.5 text-sm font-semibold text-slate-700">
        Total de Exemplares <span class="text-rose-500">*</span>
      </label>
      <input type="number" name="total_stock" min="0" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: 5" required>
    </div>

    <div>
      <label class="block mb-1.5 text-sm font-semibold text-slate-700">Rating Inicial</label>
      <input type="number" name="rating" min="0" max="5" step="0.1" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="0.0">
    </div>

    <div>
      <label class="block mb-1.5 text-sm font-semibold text-slate-700">Restrição Etária</label>
      <select name="age_restriction" class="w-full border border-slate-200 rounded-xl px-4 py-2 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="0">Sem restrição (Livre)</option>
        <option value="12">+12 anos</option>
        <option value="16">+16 anos</option>
        <option value="18">+18 anos</option>
      </select>
    </div>

    <div class="md:col-span-2">
      <label class="block mb-1.5 text-sm font-semibold text-slate-700">URL da Imagem da Capa</label>
      <input type="url" name="cover_url" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="https://images.unsplash.com/...">
    </div>

    <div class="md:col-span-3">
      <label class="block mb-1.5 text-sm font-semibold text-slate-700">Sinopse / Descrição</label>
      <textarea name="description" rows="4" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Escreve um breve resumo do livro..."></textarea>
    </div>

    <div class="md:col-span-3 pt-2">
      <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-slate-950 text-white font-semibold text-sm hover:bg-blue-600 transition shadow-sm">
        Adicionar Livro
      </button>
    </div>
  </form>
</div>

<div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm">
  <div class="border-b border-slate-100 pb-4 mb-4">
    <h2 class="text-xl font-bold text-slate-900">Acervo Cadastrado</h2>
  </div>

  <?php if (empty($books)): ?>
    <div class="text-center py-8">
      <p class="text-slate-500 text-sm">Ainda não existem livros registados no sistema.</p>
    </div>
  <?php else: ?>
    <div class="overflow-x-auto-0">
      <div class="inline-block min-w-full align-middle">
        <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
          <thead>
            <tr class="text-slate-400 font-semibold text-xs uppercase tracking-wider">
              <th class="py-3 px-4">ID</th>
              <th class="py-3 px-4">Livro</th>
              <th class="py-3 px-4">Categoria</th>
              <th class="py-3 px-4">Stock</th>
              <th class="py-3 px-4">Rating</th>
              <th class="py-3 px-4">Classificação</th>
              <th class="py-3 px-4 text-right">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
            <?php foreach ($books as $book): ?>
              <tr class="hover:bg-slate-50/70 transition">
                <td class="py-3.5 px-4 text-slate-400 font-mono text-xs">#<?= (int)$book['id'] ?></td>
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900"><?= htmlspecialchars($book['title']) ?></div>
                  <div class="text-xs text-slate-400 font-normal"><?= htmlspecialchars($book['author']) ?></div>
                </td>
                <td class="py-3.5 px-4">
                  <span class="inline-block px-2 py-0.5 bg-slate-100 rounded-md text-slate-600 text-xs">
                    <?= htmlspecialchars($book['category']) ?>
                  </span>
                </td>
                <td class="py-3.5 px-4">
                  <span class="text-slate-900 font-semibold"><?= (int)$book['stock'] ?></span><span class="text-slate-400 font-normal text-xs">/<?= (int)$book['total_stock'] ?></span>
                </td>
                <td class="py-3.5 px-4 text-amber-600">⭐ <?= htmlspecialchars($book['rating']) ?></td>
                <td class="py-3.5 px-4">
                  <?php if ((int)($book['age_restriction'] ?? 0) > 0): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-rose-50 text-rose-700 border border-rose-100">
                      +<?= (int)$book['age_restriction'] ?>
                    </span>
                  <?php else: ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                      Livre
                    </span>
                  <?php endif; ?>
                </td>
                <td class="py-3.5 px-4 text-right">
                  <div class="inline-flex items-center gap-2">
                    <a href="/bookwave/public/edit_book.php?id=<?= (int)$book['id'] ?>" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 bg-white hover:bg-slate-50 hover:text-blue-600 transition shadow-sm">
                      Editar
                    </a>
                    <form method="post" action="/bookwave/public/delete_book.php" onsubmit="return confirm('Tens a certeza absoluta que queres remover permanentemente este livro do acervo?')" class="inline">
                      <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
                      <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 border border-rose-100 text-xs font-bold text-rose-600 hover:bg-rose-600 hover:text-white transition shadow-sm">
                        Apagar
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php if ($totalPages > 1): ?>
      <div class="flex justify-center items-center gap-1.5 mt-6 pt-4 border-t border-slate-100">
        <?php if ($page > 1): ?>
          <a href="?page=<?= $page - 1 ?>" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition text-xs font-semibold text-slate-600">‹ Anterior</a>
        <?php else: ?>
          <span class="px-3 py-1.5 rounded-xl border border-slate-100 bg-slate-50 text-slate-400 text-xs font-semibold cursor-not-allowed">‹ Anterior</span>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <?php if ($i == $page): ?>
            <span class="px-3 py-1.5 rounded-xl bg-slate-950 text-white text-xs font-bold shadow-sm"><?= $i ?></span>
          <?php else: ?>
            <a href="?page=<?= $i ?>" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition text-xs font-semibold text-slate-600"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
          <a href="?page=<?= $page + 1 ?>" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition text-xs font-semibold text-slate-600">Próxima ›</a>
        <?php else: ?>
          <span class="px-3 py-1.5 rounded-xl border border-slate-100 bg-slate-50 text-slate-400 text-xs font-semibold cursor-not-allowed">Próxima ›</span>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  <?php endif; ?>
</div>

<?php page_end(); ?>