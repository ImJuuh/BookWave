<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/ui.php';

$user = current_user();
page_start('Termos e Condições - BookWave', $user);
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
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-2">Termos e Condições de Uso</h1>
    <p class="text-sm text-slate-500">Última atualização: <?= date('d/m/Y') ?></p>
  </div>

  <div class="prose prose-slate max-w-none space-y-6 text-slate-700 leading-relaxed">
    
    <section>
      <h2 class="text-xl font-bold text-slate-900 mb-2">1. Aceitação dos Termos</h2>
      <p>
        Ao aceder e utilizar a plataforma <strong>BookWave</strong>, o utilizador concorda em cumprir e vincular-se aos presentes Termos e Condições de Uso. Se não concordar com algum dos termos, não deverá utilizar os nossos serviços.
      </p>
    </section>

    <section>
      <h2 class="text-xl font-bold text-slate-900 mb-2">2. Cadastro e Segurança da Conta</h2>
      <p>
        Para usufruir do sistema de aluguer de livros, o utilizador deve registar-se fornecendo dados verídicos e atualizados (incluindo a data de nascimento para validação de faixa etária). A segurança da senha de acesso é da inteira responsabilidade do utilizador.
      </p>
    </section>

    <section>
      <h2 class="text-xl font-bold text-slate-900 mb-2">3. Política de Aluguer</h2>
      <p>
        O BookWave funciona sob um modelo de empréstimo gratuito com base nas seguintes regras fundamentais:
      </p>
      <ul class="list-disc pl-5 mt-2 space-y-1 text-slate-600">
        <li>Cada livro alugado tem um prazo padrão de devolução de <strong>30 dias</strong>.</li>
        <li>O utilizador <strong>não pode alugar mais do que um exemplar ativo do mesmo livro</strong> em simultâneo. Para alugar o mesmo título novamente, deve primeiro devolver o exemplar anterior.</li>
        <li>Livros com restrição de idade (<strong>+18</strong>) exigem obrigatoriamente que o utilizador tenha a idade mínima estipulada no momento do pedido.</li>
      </ul>
    </section>

    <section>
      <h2 class="text-xl font-bold text-slate-900 mb-2">4. Uso Adequado e Disponibilidade</h2>
      <p>
        O stock de livros é limitado e atualizado em tempo real. O BookWave reserva-se o direito de suspender temporariamente contas que demonstrem comportamentos abusivos, tentativas de burla ao sistema ou retenção indevida e prolongada de exemplares.
      </p>
    </section>

    <section>
      <h2 class="text-xl font-bold text-slate-900 mb-2">5. Alterações nos Termos</h2>
      <p>
        Podemos modificar estes termos a qualquer momento. Quaisquer alterações entrarão em vigor imediatamente após a publicação nesta página. O uso continuado da plataforma após as alterações constitui a aceitação dos novos termos.
      </p>
    </section>

    <section class="pt-4 border-t border-slate-100 flex items-center justify-between flex-wrap gap-4">
      <p class="text-sm text-slate-500">
        Se tiver alguma dúvida sobre os nossos termos, entre em contacto com o suporte da biblioteca.
      </p>
      <a href="/bookwave/public/" class="px-5 py-2.5 bg-slate-950 text-white rounded-xl text-sm font-semibold hover:bg-blue-600 transition shadow-sm">
        Entendi e Aceito
      </a>
    </section>

  </div>
</div>

<?php page_end(); ?>

```