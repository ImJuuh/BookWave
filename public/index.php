<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/ui.php';

$user = current_user();

$q = trim($_GET['q'] ?? '');
$cat = trim($_GET['cat'] ?? 'Todos');

// ---PAGINAÇÃO ---
$limit = 6; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 1. Montar a base das consultas (uma para os dados e outra para a contagem total)
$sqlBase = "FROM books WHERE is_active = 1";
$params = [];

if ($q !== '') {
  $sqlBase .= " AND (LOWER(title) LIKE ? OR LOWER(author) LIKE ?)";
  $like = '%' . mb_strtolower($q) . '%';
  $params[] = $like;
  $params[] = $like;
}

if ($cat !== '' && $cat !== 'Todos') {
  $sqlBase .= " AND category = ?";
  $params[] = $cat;
}

// 2. Contar o total de livros com os filtros aplicados
$countStmt = db()->prepare("SELECT COUNT(*) " . $sqlBase);
$countStmt->execute($params);
$totalBooks = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalBooks / $limit);

// 3. Buscar os livros da página atual usando LIMIT e OFFSET
$sql = "SELECT * " . $sqlBase . " ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

$totalRentals = 0;
if ($user) {
  $stmt = db()->prepare("SELECT COUNT(*) FROM rentals WHERE user_id = ? AND status = 'active'");
  $stmt->execute([$user['id']]);
  $totalRentals = (int)$stmt->fetchColumn();
}

// Função auxiliar para manter os filtros de busca nos links das páginas
function build_page_url($page_num, $q, $cat) {
  return '?' . http_build_query(['q' => $q, 'cat' => $cat, 'page' => $page_num]);
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

    <button id="prevSlide" class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/20 text-white px-4 py-2 rounded-xl text-2xl hover:bg-white/30 transition">‹</button>
    <button id="nextSlide" class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/20 text-white px-4 py-2 rounded-xl text-2xl hover:bg-white/30 transition">›</button>
  </div>
</section>

<div class="flex items-center justify-between mb-6">
  <h1 class="text-3xl font-bold text-slate-900">Livros Disponíveis</h1>
</div>

<form class="flex gap-2 mb-8" method="get">
  <input type="hidden" name="page" value="1">
  <input class="border border-slate-200 rounded-xl px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500" name="q" placeholder="Procurar por título ou autor..." value="<?= htmlspecialchars($q) ?>">
  <select name="cat" class="border border-slate-200 rounded-xl px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
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
  <button class="px-5 py-2 rounded-xl bg-slate-900 text-white font-medium hover:bg-slate-800 transition shadow-sm">Pesquisar</button>
</form>

<?php if (empty($books)): ?>
  <div class="text-center py-12">
    <p class="text-gray-500 text-lg">Nenhum livro encontrado para esta pesquisa.</p>
  </div>
<?php else: ?>
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
    <?php foreach ($books as $b): ?>
      <div class="group bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
        
        <div class="relative overflow-hidden aspect-[4/3] bg-slate-100">
          <a href="book.php?id=<?= (int)$b['id'] ?>" class="block w-full h-full">
            <?php if (!empty($b['cover_url'])): ?>
              <img src="<?= htmlspecialchars($b['cover_url']) ?>" alt="<?= htmlspecialchars($b['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
            <?php else: ?>
              <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 gap-1">
                <span class="text-2xl">📖</span>
                <span class="text-xs font-medium">Sem imagem</span>
              </div>
            <?php endif; ?>
          </a>
          
          <?php if (isset($b['age_restriction'])): ?>
            <div class="absolute top-3 left-3 z-10">
              <?php if ((int)$b['age_restriction'] >= 18): ?>
                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-red-50 text-red-700 border border-red-200 shadow-sm">
                  🔞 18+
                </span>
              <?php else: ?>
                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm">
                  ✨ Livre
                </span>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <div class="absolute top-3 right-3 z-10 inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-bold bg-white/90 backdrop-blur-sm text-slate-800 shadow-sm border border-slate-100">
            <span class="text-amber-500">⭐</span>
            <span><?= htmlspecialchars($b['rating']) ?></span>
          </div>
        </div>

        <div class="p-4 flex-1 flex flex-col justify-between">
          <div class="mb-3">
            <a href="book.php?id=<?= (int)$b['id'] ?>" class="block group-hover:text-blue-600 transition">
              <h2 class="text-lg font-bold text-slate-900 mb-0.5 line-clamp-2 leading-tight"><?= htmlspecialchars($b['title']) ?></h2>
            </a>
            <p class="text-xs text-slate-500 font-medium"><?= htmlspecialchars($b['author']) ?></p>
          </div>

          <div class="flex items-center justify-between pt-2.5 border-t border-slate-100 mt-auto">
            <div class="text-xs">
              <?php if ((int)$b['stock'] > 0): ?>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-medium">
                  📚 <?= (int)$b['stock'] ?>/<?= (int)$b['total_stock'] ?>
                </span>
              <?php else: ?>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-rose-50 text-rose-600 font-semibold border border-rose-100">
                  Esgotado
                </span>
              <?php endif; ?>
            </div>

            <div>
              <?php if ($user): ?>
                <?php if ((int)$b['stock'] > 0): ?>
                  <form method="post" action="/bookwave/public/rent_book.php">
                    <input type="hidden" name="book_id" value="<?= (int)$b['id'] ?>">
                    <button type="submit" class="bg-slate-950 text-white px-3.5 py-1.5 rounded-xl text-xs font-semibold hover:bg-blue-600 transition duration-200 shadow-sm">
                      Alugar
                    </button>
                  </form>
                <?php else: ?>
                  <button disabled class="bg-slate-100 text-slate-400 px-3.5 py-1.5 rounded-xl text-xs font-semibold cursor-not-allowed">
                    Indisponível
                  </button>
                <?php endif; ?>
              <?php else: ?>
                <a href="/bookwave/public/login.php" class="bg-slate-950 text-white px-3.5 py-1.5 rounded-xl text-xs font-semibold hover:bg-blue-600 transition duration-200 shadow-sm block">
                  Alugar
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($totalPages > 1): ?>
    <div class="flex justify-center items-center gap-2 mt-8 mb-12">
      <?php if ($page > 1): ?>
        <a href="<?= build_page_url($page - 1, $q, $cat) ?>" class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 transition text-sm font-medium">‹ Anterior</a>
      <?php else: ?>
        <span class="px-4 py-2 rounded-xl border bg-gray-100 text-gray-400 text-sm font-medium cursor-not-allowed">‹ Anterior</span>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i == $page): ?>
          <span class="px-4 py-2 rounded-xl bg-slate-950 text-white text-sm font-semibold"><?= $i ?></span>
        <?php else: ?>
          <a href="<?= build_page_url($i, $q, $cat) ?>" class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 transition text-sm font-medium"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>

      <?php if ($page < $totalPages): ?>
        <a href="<?= build_page_url($page + 1, $q, $cat) ?>" class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 transition text-sm font-medium">Próxima ›</a>
      <?php else: ?>
        <span class="px-4 py-2 rounded-xl border bg-gray-100 text-gray-400 text-sm font-medium cursor-not-allowed">Próxima ›</span>
      <?php endif; ?>
    </div>
  <?php endif; ?>
  
<?php endif; ?>

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
};
setInterval(() => {
  index = (index + 1) % slides.length;
  showSlide(index);
}, 5000);
</script>

<?php page_end(); ?>

```