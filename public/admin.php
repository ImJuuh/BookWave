<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/ui.php';

start_session();
require_admin();

$user = current_user();

$stmt = db()->query("SELECT * FROM books ORDER BY id DESC");
$books = $stmt->fetchAll();

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

page_start('Admin - BookWave', $user);
?>

<h1 class="text-2xl font-bold mb-6">Painel de Administração</h1>

<?php if ($success === 'book_created'): ?>
  <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded text-green-700">
    Livro adicionado com sucesso.
  </div>
<?php endif; ?>

<?php if ($success === 'book_deleted'): ?>
  <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded text-green-700">
    Livro apagado com sucesso.
  </div>
<?php endif; ?>

<?php if ($success === 'book_updated'): ?>
  <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded text-green-700">
    Livro atualizado com sucesso.
  </div>
<?php endif; ?>

<?php if ($error === 'invalid_data'): ?>
  <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700">
    Dados inválidos. Preenche todos os campos corretamente.
  </div>
<?php endif; ?>

<div class="bg-white border rounded-xl p-6 mb-8">
  <h2 class="text-xl font-semibold mb-4">Adicionar Livro</h2>

  <p class="text-sm text-gray-500 mb-6">
    Os campos assinalados com
    <span class="text-red-600 font-semibold">*</span>
    são obrigatórios.
  </p>

  <form method="post" action="/bookwave/public/create_book.php" class="grid md:grid-cols-2 gap-4">
    <div>
      <label class="block mb-1 font-medium">
        Título <span class="text-red-600">*</span>
      </label>
      <input type="text" name="title" class="w-full border rounded px-3 py-2" required>
    </div>

    <div>
      <label class="block mb-1 font-medium">
        Autor <span class="text-red-600">*</span>
      </label>
      <input type="text" name="author" class="w-full border rounded px-3 py-2" required>
    </div>

    <div>
      <label class="block mb-1 font-medium">
        Categoria <span class="text-red-600">*</span>
      </label>
      <select name="category" class="w-full border rounded px-3 py-2" required>
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
      <label class="block mb-1 font-medium">
        Stock <span class="text-red-600">*</span>
      </label>
      <input type="number" name="stock" min="0" class="w-full border rounded px-3 py-2" required>
    </div>

    <div>
      <label class="block mb-1 font-medium">
        Total de exemplares <span class="text-red-600">*</span>
      </label>
      <input type="number" name="total_stock" min="0" class="w-full border rounded px-3 py-2" required>
    </div>

    <div>
      <label class="block mb-1 font-medium">Rating</label>
      <input type="number" name="rating" min="0" max="5" step="0.1" class="w-full border rounded px-3 py-2" value="0">
    </div>

    <div>
      <label class="block mb-1 font-medium">Restrição Etária</label>
      <select name="age_restriction" class="w-full border rounded px-3 py-2">
        <option value="0">Sem restrição</option>
        <option value="12">+12</option>
        <option value="16">+16</option>
        <option value="18">+18</option>
      </select>
    </div>

    <div>
      <label class="block mb-1 font-medium">URL da capa</label>
      <input type="text" name="cover_url" class="w-full border rounded px-3 py-2">
    </div>

    <div class="md:col-span-2">
      <label class="block mb-1 font-medium">Descrição</label>
      <textarea name="description" rows="4" class="w-full border rounded px-3 py-2"></textarea>
    </div>

    <div class="md:col-span-2">
      <button
        type="submit"
        class="px-4 py-2 rounded bg-gray-900 text-white hover:bg-gray-800"
      >
        Adicionar Livro
      </button>
    </div>
  </form>
</div>

<div class="bg-white border rounded-xl p-6">
  <h2 class="text-xl font-semibold mb-4">Livros</h2>

  <?php if (!$books): ?>
    <p class="text-gray-600">Ainda não existem livros.</p>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full border-collapse">
        <thead>
          <tr class="border-b text-left">
            <th class="py-3 pr-4">ID</th>
            <th class="py-3 pr-4">Título</th>
            <th class="py-3 pr-4">Autor</th>
            <th class="py-3 pr-4">Categoria</th>
            <th class="py-3 pr-4">Stock</th>
            <th class="py-3 pr-4">Rating</th>
            <th class="py-3 pr-4">Idade</th>
            <th class="py-3 pr-4">Ações</th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($books as $book): ?>
            <tr class="border-b">
              <td class="py-3 pr-4"><?= (int)$book['id'] ?></td>
              <td class="py-3 pr-4"><?= htmlspecialchars($book['title']) ?></td>
              <td class="py-3 pr-4"><?= htmlspecialchars($book['author']) ?></td>
              <td class="py-3 pr-4"><?= htmlspecialchars($book['category']) ?></td>
              <td class="py-3 pr-4"><?= (int)$book['stock'] ?></td>
              <td class="py-3 pr-4"><?= htmlspecialchars($book['rating']) ?></td>

              <td class="py-3 pr-4">
                <?php if ((int)($book['age_restriction'] ?? 0) > 0): ?>
                  <span class="px-2 py-1 rounded bg-red-100 text-red-700 text-xs">
                    +<?= (int)$book['age_restriction'] ?>
                  </span>
                <?php else: ?>
                  <span class="text-gray-500 text-sm">Livre</span>
                <?php endif; ?>
              </td>

              <td class="py-3 pr-4">
                <div class="flex gap-2">
                  <a
                    href="/bookwave/public/edit_book.php?id=<?= (int)$book['id'] ?>"
                    class="px-3 py-2 rounded bg-blue-600 text-white text-sm hover:bg-blue-700"
                  >
                    Editar
                  </a>

                  <form
                    method="post"
                    action="/bookwave/public/delete_book.php"
                    onsubmit="return confirm('Tens a certeza que queres apagar este livro?')"
                  >
                    <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">

                    <button
                      type="submit"
                      class="px-3 py-2 rounded bg-red-600 text-white text-sm hover:bg-red-700"
                    >
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
  <?php endif; ?>
</div>

<?php page_end(); ?>