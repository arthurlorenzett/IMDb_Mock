<?php
/* ==========================================================================
   busca.php — P2 Busca: formulário GET; o JS filtra e pagina os títulos vindos da API.
   ========================================================================== */

require __DIR__ . '/includes/config.php';

$pagina = 'busca';
$titulo_pagina = 'Busca';
$descricao = 'Busque filmes e séries com filtros por gênero, ano, nota e tipo.';
$raiz = '.';
$area = 'publica';

require __DIR__ . '/includes/cabecalho.php';
?>

  <main id="conteudo">
    <header class="cabecalho-pagina">
      <h1>Resultados da busca: <span id="termo-busca"></span></h1>
      <p class="subtitulo" id="contador-resultados" aria-live="polite"></p>
    </header>

    <div class="busca-layout">
      <!-- ===== Filtros (GET): barra lateral no desktop, painel que abre no mobile ===== -->
      <aside class="filtros" id="painel-filtros" aria-labelledby="titulo-filtros">
        <form class="filtros-form" id="form-filtros" action="busca.php" method="get">
          <div class="filtros-topo">
            <h2 id="titulo-filtros">Filtros</h2>
            <!-- Só aparece no mobile; o JS fecha o painel -->
            <button class="botao botao-contorno botao-icone botao-pequeno filtros-fechar" id="fechar-filtros" type="button">
              <i class="bi bi-x-lg" aria-hidden="true"></i>
              <span class="visualmente-oculto">Fechar filtros</span>
            </button>
          </div>

          <input type="hidden" name="q" value="">

          <fieldset class="campo">
            <legend>Gênero</legend>
            <div class="opcoes opcoes-coluna">
            </div>
          </fieldset>

          <fieldset class="campo">
            <legend>Ano de lançamento</legend>
            <div class="campos-linha">
              <div class="campo">
                <label class="dica" for="filtro-ano-de">De</label>
                <input class="controle" type="number" id="filtro-ano-de" name="ano_de" min="1888" max="2031" placeholder="1990" aria-describedby="erro-filtro-ano">
              </div>
              <div class="campo">
                <label class="dica" for="filtro-ano-ate">Até</label>
                <input class="controle" type="number" id="filtro-ano-ate" name="ano_ate" min="1888" max="2031" placeholder="2026" aria-describedby="erro-filtro-ano">
              </div>
            </div>
            <p class="mensagem-erro" id="erro-filtro-ano" aria-live="polite"></p>
          </fieldset>

          <div class="campo">
            <label class="rotulo" for="filtro-nota">Nota mínima</label>
            <select class="controle" id="filtro-nota" name="nota_min">
              <option value="">Qualquer nota</option>
              <option value="9">9 ou mais</option>
              <option value="8">8 ou mais</option>
              <option value="7">7 ou mais</option>
              <option value="6">6 ou mais</option>
            </select>
          </div>

          <fieldset class="campo">
            <legend>Tipo</legend>
            <div class="opcoes">
              <label class="opcao"><input type="radio" name="tipo" value="" checked> Todos</label>
              <label class="opcao"><input type="radio" name="tipo" value="filme"> Filme</label>
              <label class="opcao"><input type="radio" name="tipo" value="serie"> Série</label>
            </div>
          </fieldset>

          <button class="botao botao-primario botao-bloco" type="submit">Aplicar filtros</button>
          <a class="filtros-limpar" href="busca.php">Limpar filtros</a>
        </form>
      </aside>

      <!-- ===== Resultados ===== -->
      <section class="resultados" aria-label="Resultados">
        <div class="barra-resultados">
          <button class="botao botao-contorno filtros-abrir" id="abrir-filtros" type="button" aria-controls="painel-filtros" aria-expanded="false">
            <i class="bi bi-funnel" aria-hidden="true"></i> Filtros
          </button>
          <div class="campo-inline">
            <label class="rotulo" for="ordenar-busca">Ordenar por</label>
            <select class="controle" id="ordenar-busca" name="ordem" form="form-filtros">
              <option value="popularidade">Popularidade</option>
              <option value="nota">Nota</option>
              <option value="ano">Ano (mais recentes)</option>
              <option value="titulo">Título (A–Z)</option>
            </select>
          </div>
          <!-- Alternância grade/lista: o JS troca data-visualizacao em #lista-resultados -->
          <div class="alternar-visualizacao" role="group" aria-label="Visualização">
            <button class="botao botao-contorno botao-icone" type="button" data-visualizacao="grade" aria-pressed="false">
              <i class="bi bi-grid-3x3-gap" aria-hidden="true"></i><span class="visualmente-oculto">Grade</span>
            </button>
            <button class="botao botao-contorno botao-icone" type="button" data-visualizacao="lista" aria-pressed="true">
              <i class="bi bi-list-ul" aria-hidden="true"></i><span class="visualmente-oculto">Lista</span>
            </button>
          </div>
        </div>

        <ol class="lista-itens" id="lista-resultados" data-visualizacao="lista">
        </ol>

        <!-- Paginação no desktop; "Carregar mais" no mobile -->
        <nav class="paginacao" aria-label="Páginas de resultados" hidden>
          <ol></ol>
        </nav>
        <button class="botao botao-contorno botao-bloco carregar-mais" id="carregar-mais" type="button" hidden>Carregar mais resultados</button>
      </section>
    </div>
  </main>

<?php require __DIR__ . '/includes/rodape.php'; ?>
