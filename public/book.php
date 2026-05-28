<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/ui.php';

$user = current_user();
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die('Livro inválido.');

$stmt = db()->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch();
if (!$book) die('Livro não encontrado.');

$erro = $_GET['erro'] ?? '';
page_start($book['title'] . ' - BookWave', $user);
?>

<div class="mb-6">
  <a href="/bookwave/public/" class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-blue-600 transition">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
      <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
    </svg>
    Voltar para a biblioteca
  </a>
</div>

<div class="bg-white border border-slate-100 rounded-3xl p-6 md:p-8 shadow-sm">
  <div class="grid md:grid-cols-12 gap-8 lg:gap-12">
    
    <div class="md:col-span-5 lg:col-span-4 flex justify-center">
      <?php if (!empty($book['cover_url'])): ?>
        <img src="<?= htmlspecialchars($book['cover_url']) ?>" alt="<?= htmlspecialchars($book['title']) ?>" class="w-full max-w-[320px] md:max-w-full h-[460px] object-cover rounded-2xl shadow-md border border-slate-100">
      <?php else: ?>
        <div class="w-full max-w-[320px] md:max-w-full h-[460px] bg-slate-100 border border-slate-200 rounded-2xl flex flex-col items-center justify-center text-slate-400 gap-2">
          <span class="text-4xl">📖</span>
          <span class="text-sm font-medium">Sem imagem de capa</span>
        </div>
      <?php endif; ?>
    </div>

    <div class="md:col-span-7 lg:col-span-8 flex flex-col justify-between">
      <div>
        <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight mb-2"><?= htmlspecialchars($book['title']) ?></h1>
        <p class="text-lg text-blue-700 font-medium mb-5"><?= htmlspecialchars($book['author']) ?></p>
        
        <div class="flex flex-wrap gap-2 mb-6">
          <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
            📁 <?= htmlspecialchars($book['category']) ?>
          </span>

          <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
            ⭐ <?= htmlspecialchars($book['rating']) ?>
          </span>

          <?php if (isset($book['age_restriction'])): ?>
            <?php if ((int)$book['age_restriction'] >= 18): ?>
              <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                🔞 18+
              </span>
            <?php else: ?>
              <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                ✨ Livre
              </span>
            <?php endif; ?>
          <?php endif; ?>

          <?php if ((int)$book['stock'] > 0): ?>
            <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
              📚 Disponível: <?= (int)$book['stock'] ?> de <?= (int)$book['total_stock'] ?>
            </span>
          <?php else: ?>
            <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-rose-50 text-rose-600 border border-rose-100">
              🚫 Esgotado
            </span>
          <?php endif; ?>
        </div>

        <?php if (!empty($erro)): ?>
          <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-700 text-sm font-medium flex items-center gap-2 shadow-sm">
            <span>⚠️</span>
            <div>
              <?php if ($erro === 'sem_stock'): ?>
                Este livro não tem exemplares disponíveis em stock de momento.
              <?php elseif ($erro === 'idade_insuficiente'): ?>
                Não tens idade suficiente (mínimo 18 anos) para poder alugar este livro.
              <?php elseif ($erro === 'ja_alugado'): ?>
                Já tens um exemplar ativo deste livro alugado. Só podes alugar outro quando devolveres o atual.
              <?php elseif ($erro === 'erro_sistema'): ?>
                Ocorreu um problema ao processar o teu aluguer. Por favor, tenta novamente.
              <?php else: ?>
                Não foi possível processar o pedido de aluguer.
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="border-t border-slate-100 pt-5">
          <h2 class="text-sm font-bold text-slate-400 tracking-wider uppercase mb-3">Sinopse / Descrição</h2>
          <p class="text-slate-700 leading-relaxed text-base">
            <?= nl2br(htmlspecialchars($book['description'] ?: 'Nenhuma descrição ou sinopse foi fornecida para este livro.')) ?>
          </p>
        </div>

      </div>

      <div class="mt-8 pt-5 border-t border-slate-100">
        <?php if ($user): ?>
          <?php if ((int)$book['stock'] > 0): ?>
            <form method="post" action="/bookwave/public/rent_book.php">
              <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
              <button type="submit" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-slate-950 text-white font-bold hover:bg-blue-600 active:scale-98 transition duration-200 shadow-md shadow-slate-950/10">
                Alugar Livro Agora
              </button>
            </form>
          <?php else: ?>
            <button disabled class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-slate-100 text-slate-400 font-bold cursor-not-allowed border border-slate-200">
              Indisponível para Aluguer
            </button>
          <?php endif; ?>
        <?php else: ?>
          <a href="/bookwave/public/login.php" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-slate-950 text-white font-bold hover:bg-blue-600 transition duration-200 shadow-md shadow-slate-950/10 inline-block text-center">
            Entrar para Alugar
          </a>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<?php page_end(); ?>

```