<?php
/* ==========================================================================
   index.php — P1 Início: carrossel de destaques, Top da semana e Em alta (montados pelo JS a partir da API).
   ========================================================================== */

require __DIR__ . '/includes/config.php';

$pagina = 'inicio';
$titulo_pagina = 'Início';
$descricao = 'Clone educacional do IMDb: destaques, Top 10 da semana e títulos em alta.';
$raiz = '.';
$area = 'publica';

require __DIR__ . '/includes/cabecalho.php';
?>

  <main id="conteudo">
    <h1 class="visualmente-oculto">IMDb Clone: filmes e séries</h1>

    <!-- ===== Destaques: carrossel + Top 10 da semana ===== -->
    <div class="inicio-destaques">
      <section class="carrossel" id="carrossel" aria-roledescription="carrossel" aria-label="Destaques">
        <button class="carrossel-seta carrossel-anterior" id="carrossel-anterior" type="button">
          <i class="bi bi-chevron-left" aria-hidden="true"></i>
          <span class="visualmente-oculto">Slide anterior</span>
        </button>
        <!-- O JS desloca este trilho com transform: translateX(-100% * índice) -->
        <div class="carrossel-trilho" id="carrossel-trilho" aria-live="polite">
        </div>
        <button class="carrossel-seta carrossel-proximo" id="carrossel-proximo" type="button">
          <i class="bi bi-chevron-right" aria-hidden="true"></i>
          <span class="visualmente-oculto">Próximo slide</span>
        </button>
        <div class="carrossel-indicadores" id="carrossel-indicadores">
        </div>
      </section>

      <section class="top10" aria-labelledby="titulo-top10">
        <h2 class="titulo-secao" id="titulo-top10">Top 10 da semana</h2>
        <ol class="top10-lista" id="lista-top10">
        </ol>
        <a class="ver-mais" href="top250.php">Ver Top 250 <i class="bi bi-chevron-right" aria-hidden="true"></i></a>
      </section>
    </div>

    <!-- ===== Em alta: cards com botão de Watchlist ===== -->
    <section class="em-alta" aria-labelledby="titulo-em-alta">
      <div class="secao-topo">
        <h2 class="titulo-secao" id="titulo-em-alta">Em alta</h2>
        <a class="ver-mais" href="busca.php">Ver todos <i class="bi bi-chevron-right" aria-hidden="true"></i></a>
      </div>
      <!-- Grade no desktop; rolagem horizontal no mobile. O JS pode gerar estes cards a partir do JSON -->
      <ul class="grade-cartoes" id="lista-em-alta">
      </ul>
    </section>
  </main>

<?php require __DIR__ . '/includes/rodape.php'; ?>
