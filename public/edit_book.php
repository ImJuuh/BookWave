<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/ui.php';

start_session();
require_admin();

$user = current_user();

$bookId = (int)($_GET['id'] ?? 0);

if ($bookId <= 0) {
  die('Livro inválido.');
}

$stmt = db()->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$bookId]);
$book = $stmt->fetch();

if (!$book) {
  die('Livro não encontrado.');
}

page_start('Editar Livro - BookWave', $user);
?>

<div class="flex items-center justify-between mb-6">
  <h1 class="text-3xl font-bold">Editar Livro</h1>

  <a href="/bookwave/public/admin.php" class="px-4 py-2 rounded border">
    ← Voltar
  </a>
</div>

<div class="bg-white border rounded-xl p-6">

  <p class="text-sm text-gray-500 mb-6">
    Os campos assinalados com
    <span class="text-red-600 font-semibold">*</span>
    são obrigatórios.
  </p>

  <form method="post" action="/bookwave/public/update_book.php" class="grid md:grid-cols-2 gap-4">

    <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">

    <div>
      <label class="block mb-1 font-medium">
        Título <span class="text-red-600">*</span>
      </label>

      <input
        type="text"
        name="title"
        value="<?= htmlspecialchars($book['title']) ?>"
        class="w-full border rounded px-3 py-2"
        required
      >
    </div>

    <div>
      <label class="block mb-1 font-medium">
        Autor <span class="text-red-600">*</span>
      </label>

      <input
        type="text"
        name="author"
        value="<?= htmlspecialchars($book['author']) ?>"
        class="w-full border rounded px-3 py-2"
        required
      >
    </div>

    <div>
      <label class="block mb-1 font-medium">
        Categoria <span class="text-red-600">*</span>
      </label>

      <select
        name="category"
        class="w-full border rounded px-3 py-2"
        required
      >
        <?php
          $categories = [
            'Fantasia',
            'Ficção',
            'Romance',
            'Mistério',
            'Terror',
            'História',
            'Biografia',
            'Tecnologia',
            'Educação',
            'Clássicos'
          ];
        ?>

        <?php foreach ($categories as $category): ?>
          <option
            value="<?= htmlspecialchars($category) ?>"
            <?= $book['category'] === $category ? 'selected' : '' ?>
          >
            <?= htmlspecialchars($category) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label class="block mb-1 font-medium">
        Stock disponível <span class="text-red-600">*</span>
      </label>

      <input
        type="number"
        name="stock"
        min="0"
        value="<?= (int)$book['stock'] ?>"
        class="w-full border rounded px-3 py-2"
        required
      >
    </div>

    <div>
      <label class="block mb-1 font-medium">
        Total de exemplares <span class="text-red-600">*</span>
      </label>

      <input
        type="number"
        name="total_stock"
        min="0"
        value="<?= (int)$book['total_stock'] ?>"
        class="w-full border rounded px-3 py-2"
        required
      >
    </div>

    <div>
      <label class="block mb-1 font-medium">
        Rating
      </label>

      <input
        type="number"
        name="rating"
        min="0"
        max="5"
        step="0.1"
        value="<?= htmlspecialchars($book['rating']) ?>"
        class="w-full border rounded px-3 py-2"
      >
    </div>

    <div>
      <label class="block mb-1 font-medium">
        Restrição Etária
      </label>

      <select
        name="age_restriction"
        class="w-full border rounded px-3 py-2"
      >
        <option
          value="0"
          <?= (int)($book['age_restriction'] ?? 0) === 0 ? 'selected' : '' ?>
        >
          Sem restrição
        </option>

        <option
          value="12"
          <?= (int)($book['age_restriction'] ?? 0) === 12 ? 'selected' : '' ?>
        >
          +12
        </option>

        <option
          value="16"
          <?= (int)($book['age_restriction'] ?? 0) === 16 ? 'selected' : '' ?>
        >
          +16
        </option>

        <option
          value="18"
          <?= (int)($book['age_restriction'] ?? 0) === 18 ? 'selected' : '' ?>
        >
          +18
        </option>
      </select>
    </div>

    <div class="md:col-span-2">
      <label class="block mb-1 font-medium">
        URL da capa
      </label>

      <input
        type="text"
        name="cover_url"
        value="<?= htmlspecialchars($book['cover_url'] ?? '') ?>"
        class="w-full border rounded px-3 py-2"
      >
    </div>

    <div class="md:col-span-2">
      <label class="block mb-1 font-medium">
        Descrição
      </label>

      <textarea
        name="description"
        rows="5"
        class="w-full border rounded px-3 py-2"
      ><?= htmlspecialchars($book['description'] ?? '') ?></textarea>
    </div>

    <div class="md:col-span-2 flex justify-end gap-3">
      <a
        href="/bookwave/public/admin.php"
        class="px-4 py-2 rounded border"
      >
        Cancelar
      </a>

      <button
        type="submit"
        class="px-5 py-2 rounded bg-slate-900 text-white hover:bg-slate-800"
      >
        Guardar Alterações
      </button>
    </div>

  </form>
</div>

<?php page_end(); ?>