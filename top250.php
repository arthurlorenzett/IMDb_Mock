<?php
/* ==========================================================================
   top250.php — P4 Top 250: listagem pública gerada pelo PHP a partir do
   banco. A RN03 roda no SQL (mínimo de avaliações aprovadas, ordem por
   média e votos). Depois o JS só reordena e filtra as linhas na tela.
   ========================================================================== */

require __DIR__ . '/includes/config.php';

[$minimo, $limite] = rn03_parametros();
$ranking = ranking_top250($minimo, $limite);

$pagina = 'top250';
$titulo_pagina = 'Top 250';
$descricao = 'Ranking dos títulos mais bem avaliados.';
$raiz = '.';
$area = 'publica';

require __DIR__ . '/includes/cabecalho.php';
?>

  <main id="conteudo">
    <header class="cabecalho-pagina">
      <h1>Top 250 filmes</h1>
      <p class="subtitulo">Classificados pela nota média das avaliações aprovadas; exige no mínimo <?= $minimo ?> avaliações (RN03).</p>
    </header>

    <!-- ===== Controles de ordenação e filtro (o JS reordena as linhas) ===== -->
    <div class="barra-controles">
      <div class="campo-inline">
        <label class="rotulo" for="ordenar-top">Ordenar por</label>
        <select class="controle" id="ordenar-top">
          <option value="posicao">Posição</option>
          <option value="nota">Nota</option>
          <option value="votos">Votos</option>
          <option value="ano">Ano</option>
          <option value="titulo">Título</option>
        </select>
      </div>
      <div class="campo-inline">
        <label class="visualmente-oculto" for="filtro-genero-top">Gênero</label>
        <select class="controle" id="filtro-genero-top">
          <option value="">Gênero: todos</option>
          <option value="acao">Ação</option>
          <option value="comedia">Comédia</option>
          <option value="drama">Drama</option>
          <option value="ficcao">Ficção científica</option>
          <option value="suspense">Suspense</option>
        </select>
      </div>
      <button class="botao botao-contorno botao-icone" id="inverter-ordem" type="button">
        <i class="bi bi-arrow-down-up" aria-hidden="true"></i><span class="visualmente-oculto">Inverter ordem</span>
      </button>
      <p class="subtitulo barra-contador" id="contador-top" aria-live="polite"><?= count($ranking) ?> <?= count($ranking) === 1 ? 'título' : 'títulos' ?></p>
    </div>

    <!-- ===== Tabela do ranking (vira lista de cards abaixo de 600 px) ===== -->
    <div class="tabela-rolagem tabela-top">
      <table class="tabela" id="tabela-top250">
        <caption class="visualmente-oculto">Ranking dos títulos por nota média, com ano, nota, votos e ações</caption>
        <thead>
          <tr>
            <th scope="col" class="numero">#</th>
            <th scope="col">Título</th>
            <th scope="col" class="numero"><button class="botao-ordenar" type="button" data-ordenar="ano">Ano <i class="bi bi-caret-down-fill" aria-hidden="true"></i></button></th>
            <th scope="col" class="numero" aria-sort="descending"><button class="botao-ordenar" type="button" data-ordenar="nota">Nota <i class="bi bi-caret-down-fill" aria-hidden="true"></i></button></th>
            <th scope="col" class="numero"><button class="botao-ordenar" type="button" data-ordenar="votos">Votos <i class="bi bi-caret-down-fill" aria-hidden="true"></i></button></th>
            <th scope="col" class="centro">Sua nota</th>
            <th scope="col" class="centro">Watchlist</th>
          </tr>
        </thead>
        <tbody id="corpo-top250">
<?php foreach ($ranking as $posicao => $t): ?>
<?php $nome = e($t['titulo']); ?>
          <tr data-id="<?= (int) $t['id'] ?>">
            <td class="numero top-posicao"><?= $posicao + 1 ?></td>
            <th scope="row" class="top-titulo">
              <img class="poster" src="<?= e($t['poster'] ?: 'img/sem-poster.svg') ?>" alt="Pôster de <?= $nome ?>" width="32" height="48" loading="lazy">
              <span>
                <a href="titulo.php?id=<?= (int) $t['id'] ?>"><?= $nome ?></a>
                <span class="top-meta"><?= (int) $t['ano'] ?> · <?= e(formatar_duracao($t)) ?></span>
              </span>
            </th>
            <td class="numero top-ano"><?= (int) $t['ano'] ?></td>
            <td class="numero top-nota"><span class="nota"><i class="bi bi-star-fill" aria-hidden="true"></i> <span class="nota-valor"><?= formatar_nota((float) $t['media']) ?></span></span></td>
            <td class="numero top-votos"><?= (int) $t['votos'] ?></td>
            <td class="centro top-acao">
              <a class="botao botao-contorno botao-icone botao-pequeno" href="titulo.php?id=<?= (int) $t['id'] ?>#avaliar">
                <i class="bi bi-star" aria-hidden="true"></i><span class="visualmente-oculto">Avaliar <?= $nome ?></span>
              </a>
            </td>
            <td class="centro top-acao">
              <button class="botao botao-watchlist botao-contorno botao-icone botao-pequeno" type="button" data-id="<?= (int) $t['id'] ?>" data-nome="<?= $nome ?>" data-variante="icone" aria-pressed="false">
                <i class="bi bi-plus-lg" aria-hidden="true"></i><span class="visualmente-oculto">Adicionar <?= $nome ?> à Watchlist</span>
              </button>
            </td>
          </tr>
<?php endforeach; ?>
<?php if (!$ranking): ?>
          <tr><td colspan="7" class="centro">Nenhum título com <?= $minimo ?> ou mais avaliações aprovadas.</td></tr>
<?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="centralizar">
      <button class="botao botao-contorno" id="mostrar-mais-top" type="button" hidden>Mostrar mais</button>
    </div>
  </main>

<?php require __DIR__ . '/includes/rodape.php'; ?>
