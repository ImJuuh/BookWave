<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/ui.php';

$user = current_user();

$q = trim($_GET['q'] ?? '');
$cat = trim($_GET['cat'] ?? 'Todos');

$sql = "SELECT * FROM books WHERE 1=1";
$params = [];

if ($q !== '') {
  $sql .= " AND (LOWER(title) LIKE ? OR LOWER(author) LIKE ?)";
  $like = '%' . mb_strtolower($q) . '%';
  $params[] = $like;
  $params[] = $like;
}

if ($cat !== '' && $cat !== 'Todos') {
  $sql .= " AND category = ?";
  $params[] = $cat;
}

$sql .= " ORDER BY id DESC";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

$totalRentals = 0;
if ($user) {
  $stmt = db()->prepare("SELECT COUNT(*) FROM rentals WHERE user_id = ? AND status = 'active'");
  $stmt->execute([$user['id']]);
  $totalRentals = (int)$stmt->fetchColumn();
}

page_start('BookWave', $user);
?>

<section class="mb-10">
  <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-600 to-blue-800 shadow-lg">
    <div class="slide flex items-center justify-between px-10 py-12">
      <div class="text-white max-w-xl">
        <h2 class="text-5xl font-bold mb-4">Leia Gratuitamente</h2>
        <p class="text-xl text-blue-100">Alugue quantos livros quiser sem nenhum custo</p>
      </div>
      <img src="https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=900&q=80" class="w-96 h-64 object-cover rounded-2xl shadow-lg">
    </div>

    <div class="slide hidden flex items-center justify-between px-10 py-12">
      <div class="text-white max-w-xl">
        <h2 class="text-5xl font-bold mb-4">Descubra Novos Livros</h2>
        <p class="text-xl text-blue-100">Explore milhares de histórias incríveis</p>
      </div>
      <img src="https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=900&q=80" class="w-96 h-64 object-cover rounded-2xl shadow-lg">
    </div>

    <button id="prevSlide" class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/20 text-white px-4 py-2 rounded-xl text-2xl">‹</button>
    <button id="nextSlide" class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/20 text-white px-4 py-2 rounded-xl text-2xl">›</button>
  </div>
</section>

<div class="flex items-center justify-between mb-6">
  <h1 class="text-3xl font-bold">Livros</h1>
  <?php if ($user): ?>
    <span class="text-sm text-gray-700"><?= $totalRentals ?> alugados</span>
  <?php endif; ?>
</div>

<form class="flex gap-2 mb-8" method="get">
  <input class="border rounded px-3 py-2 w-full" name="q" placeholder="Procurar por título ou autor..." value="<?= htmlspecialchars($q) ?>">
  <select name="cat" class="border rounded px-3 py-2">
    <option value="Todos" <?= $cat === 'Todos' ? 'selected' : '' ?>>Todas as categorias</option>
    <option value="Fantasia" <?= $cat === 'Fantasia' ? 'selected' : '' ?>>Fantasia</option>
    <option value="Ficção" <?= $cat === 'Ficção' ? 'selected' : '' ?>>Ficção</option>
    <option value="Romance" <?= $cat === 'Romance' ? 'selected' : '' ?>>Romance</option>
    <option value="Mistério" <?= $cat === 'Mistério' ? 'selected' : '' ?>>Mistério</option>
    <option value="Terror" <?= $cat === 'Terror' ? 'selected' : '' ?>>Terror</option>
    <option value="História" <?= $cat === 'História' ? 'selected' : '' ?>>História</option>
    <option value="Biografia" <?= $cat === 'Biografia' ? 'selected' : '' ?>>Biografia</option>
    <option value="Tecnologia" <?= $cat === 'Tecnologia' ? 'selected' : '' ?>>Tecnologia</option>
    <option value="Educação" <?= $cat === 'Educação' ? 'selected' : '' ?>>Educação</option>
    <option value="Clássicos" <?= $cat === 'Clássicos' ? 'selected' : '' ?>>Clássicos</option>
  </select>
  <button class="px-4 py-2 rounded bg-slate-900 text-white">Pesquisar</button>
</form>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
  <?php foreach ($books as $b): ?>
    <div class="bg-white rounded-2xl shadow border overflow-hidden hover:shadow-lg transition">
      <a href="book.php?id=<?= (int)$b['id'] ?>" class="block">
        <?php if (!empty($b['cover_url'])): ?>
          <img src="<?= htmlspecialchars($b['cover_url']) ?>" alt="<?= htmlspecialchars($b['title']) ?>" class="w-full h-96 object-cover">
        <?php else: ?>
          <div class="w-full h-96 bg-gray-200 flex items-center justify-center text-gray-500">Sem imagem</div>
        <?php endif; ?>
      </a>

      <div class="p-4">
        <a href="book.php?id=<?= (int)$b['id'] ?>" class="block">
          <h2 class="text-2xl font-semibold text-slate-900 mb-2 leading-tight"><?= htmlspecialchars($b['title']) ?></h2>
        </a>
        <p class="text-lg text-blue-800 mb-3"><?= htmlspecialchars($b['author']) ?></p>

        <div class="flex items-center gap-2 text-sm mb-4">
          <span class="text-yellow-500">⭐</span>
          <span class="font-semibold text-slate-900"><?= htmlspecialchars($b['rating']) ?></span>
        </div>

        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-600">
            <?php if ((int)$b['stock'] > 0): ?>
              <span>📚 <?= (int)$b['stock'] ?>/<?= (int)$b['total_stock'] ?> disponíveis</span>
            <?php else: ?>
              <span class="text-red-600 font-semibold">Indisponível</span>
            <?php endif; ?>
          </div>

          <?php if ($user): ?>
            <?php if ((int)$b['stock'] > 0): ?>
              <form method="post" action="/bookwave/public/rent_book.php">
                <input type="hidden" name="book_id" value="<?= (int)$b['id'] ?>">
                <button type="submit" class="bg-slate-950 text-white px-5 py-2 rounded-xl font-semibold hover:bg-slate-800 transition">
                  Alugar
                </button>
              </form>
            <?php else: ?>
              <button disabled class="bg-gray-300 text-gray-500 px-5 py-2 rounded-xl font-semibold cursor-not-allowed">
                Esgotado
              </button>
            <?php endif; ?>
          <?php else: ?>
            <a href="/bookwave/public/login.php" class="bg-slate-950 text-white px-5 py-2 rounded-xl font-semibold hover:bg-slate-800 transition">
              Alugar
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<script>
const slides = document.querySelectorAll(".slide");
let index = 0;
function showSlide(i) {
  slides.forEach(slide => slide.classList.add("hidden"));
  slides[i].classList.remove("hidden");
}
document.getElementById("nextSlide").onclick = () => {
  index = (index + 1) % slides.length;
  showSlide(index);
};
document.getElementById("prevSlide").onclick = () => {
  index = (index - 1 + slides.length) % slides.length;
  showSlide(index);
};
setInterval(() => {
  index = (index + 1) % slides.length;
  showSlide(index);
}, 5000);
</script>

<?php page_end(); ?>
