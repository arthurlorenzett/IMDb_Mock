<?php
/* ==========================================================================
   menu.php — menu de navegação. Nas páginas públicas é a gaveta do
   cabeçalho; no painel é a barra lateral (gaveta no mobile).
   Usa $raiz, $area e atual() definidos em cabecalho.php.
   ========================================================================== */
?>
<?php if ($area === 'admin'): ?>
    <!-- Menu do painel: barra lateral no desktop, gaveta no mobile -->
    <nav class="menu-admin gaveta" id="menu-admin" aria-label="Menu do painel">
      <div class="gaveta-topo">
        <button class="botao botao-contorno botao-icone" id="fechar-menu" type="button">
          <i class="bi bi-x-lg" aria-hidden="true"></i>
          <span class="visualmente-oculto">Fechar menu</span>
        </button>
      </div>
      <ul class="gaveta-lista">
        <li><a href="<?= $raiz ?>/admin/painel.php#titulos"<?= atual('painel') ?>><i class="bi bi-film" aria-hidden="true"></i> Títulos</a></li>
        <li><a href="<?= $raiz ?>/admin/painel.php#avaliacoes"><i class="bi bi-chat-square-text" aria-hidden="true"></i> Avaliações<?= isset($total_pendentes) ? ' (' . (int) $total_pendentes . ')' : '' ?></a></li>
        <li><a href="<?= $raiz ?>/admin/titulo-form.php"<?= empty($_GET['id']) ? atual('titulo-form') : '' ?>><i class="bi bi-plus-lg" aria-hidden="true"></i> Novo título</a></li>
        <li><a href="<?= $raiz ?>/index.php"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Ver site</a></li>
        <li><a href="<?= $raiz ?>/admin/sair.php"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Sair</a></li>
      </ul>
    </nav>
<?php else: ?>
    <!-- Gaveta do menu (estado "menu aberto" do wireframe) -->
    <nav class="gaveta" id="menu-principal" aria-label="Menu principal">
      <div class="gaveta-topo">
        <button class="botao botao-contorno botao-icone" id="fechar-menu" type="button">
          <i class="bi bi-x-lg" aria-hidden="true"></i>
          <span class="visualmente-oculto">Fechar menu</span>
        </button>
      </div>
      <ul class="gaveta-lista">
        <li><a href="<?= $raiz ?>/index.php"<?= atual('inicio') ?>><i class="bi bi-house" aria-hidden="true"></i> Início</a></li>
        <li><a href="<?= $raiz ?>/busca.php"<?= atual('busca') ?>><i class="bi bi-search" aria-hidden="true"></i> Busca</a></li>
        <li><a href="<?= $raiz ?>/top250.php"<?= atual('top250') ?>><i class="bi bi-trophy" aria-hidden="true"></i> Top 250</a></li>
        <li><a href="<?= $raiz ?>/watchlist.php"<?= atual('watchlist') ?>><i class="bi bi-bookmark-plus" aria-hidden="true"></i> <span>Watchlist (<span data-contador-watchlist>0</span>)</span></a></li>
<?php if (admin_logado()): ?>
        <li><a href="<?= $raiz ?>/admin/painel.php"><i class="bi bi-speedometer2" aria-hidden="true"></i> Painel</a></li>
<?php else: ?>
        <li><a href="<?= $raiz ?>/admin/login.php"<?= atual('login') ?>><i class="bi bi-person-circle" aria-hidden="true"></i> Entrar</a></li>
<?php endif; ?>
      </ul>
    </nav>
<?php endif; ?>
