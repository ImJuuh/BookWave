<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/ui.php';

$user = current_user();
page_start('Política de Privacidade - BookWave', $user);
?>

<div class="mb-6">
  <a href="/bookwave/public/" class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-blue-600 transition">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
      <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
    </svg>
    Voltar para a biblioteca
  </a>
</div>

<div class="bg-white border border-slate-100 rounded-3xl p-6 md:p-10 shadow-sm max-w-4xl mx-auto">
  <div class="border-b border-slate-100 pb-6 mb-8">
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-2">Política de Privacidade</h1>
    <p class="text-sm text-slate-500">Última atualização: <?= date('d/m/Y') ?></p>
  </div>

  <div class="prose prose-slate max-w-none space-y-6 text-slate-700 leading-relaxed">
    
    <section>
      <h2 class="text-xl font-bold text-slate-900 mb-2">1. Informações que Recolhemos</h2>
      <p>
        Para garantir o correto funcionamento dos alugueres e a segurança dos nossos utilizadores, a plataforma <strong>BookWave</strong> recolhe os seguintes dados:
      </p>
      <ul class="list-disc pl-5 mt-2 space-y-1 text-slate-600">
        <li><strong>Dados de Registo:</strong> Nome, endereço de e-mail e credenciais de acesso seguras (encriptadas).</li>
        <li><strong>Data de Nascimento:</strong> Utilizada única e exclusivamente pelo sistema para verificar o cumprimento das regras de restrição de idade (livros +18).</li>
        <li><strong>Histórico de Alugueres:</strong> Livros requisitados, datas de aluguer, prazos de entrega e o estado atual de cada livro.</li>
      </ul>
    </section>

    <section>
      <h2 class="text-xl font-bold text-slate-900 mb-2">2. Como Utilizamos os Seus Dados</h2>
      <p>
        Os dados recolhidos servem estritamente para a gestão interna da biblioteca escolar/digital. Utilizamos as tuas informações para:
      </p>
      <ul class="list-disc pl-5 mt-2 space-y-1 text-slate-600">
        <li>Processar os teus pedidos de aluguer e gerir o stock da plataforma.</li>
        <li>Validar se tens a idade regulamentar para aceder a determinados conteúdos.</li>
        <li>Manter um registo seguro das tuas requisições ativas e do teu histórico de leitura.</li>
      </ul>
    </section>

    <section>
      <h2 class="text-xl font-bold text-slate-900 mb-2">3. Partilha de Dados com Terceiros</h2>
      <p>
        O BookWave valoriza a tua privacidade. <strong>Não vendemos, trocamos ou transferimos</strong> os teus dados pessoais para empresas externas ou entidades de publicidade. Toda a informação permanece guardada de forma segura na nossa base de dados.
      </p>
    </section>

    <section>
      <h2 class="text-xl font-bold text-slate-900 mb-2">4. Segurança da Informação</h2>
      <p>
        Implementamos um conjunto de medidas tecnológicas para proteger as tuas informações. As palavras-passe dos utilizadores são protegidas utilizando algoritmos modernos de hashing (encriptação), e os processos de aluguer são controlados diretamente através de transações seguras e isoladas na base de dados (PDO).
      </p>
    </section>

    <section>
      <h2 class="text-xl font-bold text-slate-900 mb-2">5. Os Seus Direitos</h2>
      <p>
        Como utilizador da plataforma, tens o direito de aceder ao teu painel de perfil, verificar os teus dados guardados e solicitar a alteração ou eliminação definitiva da tua conta, desde que <strong>não tenhas nenhum livro pendente de devolução</strong> no sistema.
      </p>
    </section>

    <section class="pt-4 border-t border-slate-100 flex items-center justify-between flex-wrap gap-4">
      <p class="text-sm text-slate-500">
        Se tiveres dúvidas sobre como lidamos com os teus dados de privacidade, fala com a administração da plataforma.
      </p>
      <a href="/bookwave/public/" class="px-5 py-2.5 bg-slate-950 text-white rounded-xl text-sm font-semibold hover:bg-blue-600 transition shadow-sm">
        Aceitar e Continuar
      </a>
    </section>

  </div>
</div>

<?php page_end(); ?>

```