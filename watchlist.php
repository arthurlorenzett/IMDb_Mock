<?php
/* ==========================================================================
   watchlist.php — P5 Watchlist: lista salva no navegador (localStorage), sem banco.
   ========================================================================== */

require __DIR__ . '/includes/config.php';

$pagina = 'watchlist';
$titulo_pagina = 'Watchlist';
$descricao = 'Sua lista de títulos para assistir depois, salva no navegador.';
$raiz = '.';
$area = 'publica';

require __DIR__ . '/includes/cabecalho.php';
?>

  <main id="conteudo">
    <header class="cabecalho-pagina">
      <h1>Sua Watchlist</h1>
      <p class="subtitulo"><span id="contador-watchlist-pagina"></span> · lista salva neste navegador</p>
    </header>

    <!-- ===== Controles (o JS ordena, filtra e limpa a lista do localStorage) ===== -->
    <div class="barra-controles">
      <div class="campo-inline">
        <label class="rotulo" for="ordenar-watchlist">Ordenar por</label>
        <select class="controle" id="ordenar-watchlist">
          <option value="inclusao">Data de inclusão</option>
          <option value="nota">Nota</option>
          <option value="ano">Ano</option>
          <option value="titulo">Título (A–Z)</option>
        </select>
      </div>
      <div class="campo-inline">
        <label class="visualmente-oculto" for="filtro-tipo-watchlist">Tipo</label>
        <select class="controle" id="filtro-tipo-watchlist">
          <option value="">Tipo: todos</option>
          <option value="filme">Filmes</option>
          <option value="serie">Séries</option>
        </select>
      </div>
      <button class="botao botao-contorno barra-direita" id="botao-limpar-watchlist" type="button" data-abrir-modal="modal-limpar">
        <i class="bi bi-trash" aria-hidden="true"></i> Limpar lista
      </button>
    </div>

    <!-- ===== Lista (o JS gera os itens a partir do localStorage) ===== -->
    <ol class="lista-itens" id="lista-watchlist">
    </ol>

    <!-- ===== Estado vazio: o JS remove o hidden quando a lista estiver vazia ===== -->
    <section class="estado-vazio" id="watchlist-vazia" aria-labelledby="titulo-vazia" hidden>
      <i class="bi bi-bookmark-plus" aria-hidden="true"></i>
      <h2 id="titulo-vazia">Sua Watchlist está vazia</h2>
      <p>Use “+ Watchlist” nos cards para salvar títulos aqui.</p>
      <a class="botao botao-primario" href="top250.php">Explorar o Top 250</a>
    </section>

    <!-- ===== Modal: confirmar limpeza ===== -->
    <dialog class="modal modal-pequeno" id="modal-limpar" aria-labelledby="titulo-modal-limpar">
      <h2 id="titulo-modal-limpar">Limpar a Watchlist?</h2>
      <p class="modal-texto">Os títulos serão removidos deste navegador.</p>
      <div class="modal-acoes">
        <button class="botao botao-contorno" type="button" data-fechar-modal>Cancelar</button>
        <button class="botao botao-perigo" type="button" id="confirmar-limpar">Limpar</button>
      </div>
    </dialog>
  </main>

<?php require __DIR__ . '/includes/rodape.php'; ?>
