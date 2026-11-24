<?php
/* ==========================================================================
   cabecalho.php — <head>, abertura do <body> e cabeçalho global.
   Cada página define antes do require:
     $pagina        nome usado pelo JS (data-pagina) e para marcar o menu
     $titulo_pagina texto da aba do navegador
     $descricao     meta description
     $raiz          "." nas páginas públicas, ".." nas de admin/
     $area          "publica" (padrão) ou "admin"
   ========================================================================== */

$area = $area ?? 'publica';
$admin = admin_logado();

// Marca o link da página atual no menu (aria-current)
function atual(string $nome): string
{
  global $pagina;
  return $pagina === $nome ? ' aria-current="page"' : '';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titulo_pagina) ?> · IMDb Clone</title>
  <meta name="description" content="<?= e($descricao) ?>">
  <!-- Fonte Roboto (Google Fonts) e Bootstrap Icons (CDN), conforme a Tabela 5 da Parte 1 -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600&display=swap">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- CSS próprio, em camadas: variáveis → base → componentes → páginas -->
  <link rel="stylesheet" href="<?= $raiz ?>/css/variaveis.css">
  <link rel="stylesheet" href="<?= $raiz ?>/css/base.css">
  <link rel="stylesheet" href="<?= $raiz ?>/css/componentes.css">
  <link rel="stylesheet" href="<?= $raiz ?>/css/paginas.css">
  <!-- Aplica o tema salvo antes de desenhar a página (evita piscar) -->
  <script src="<?= $raiz ?>/js/tema-inicial.js"></script>
</head>
<body data-pagina="<?= e($pagina) ?>" data-raiz="<?= $raiz ?>">
  <a class="pular-conteudo" href="#conteudo">Pular para o conteúdo</a>
  <noscript><p class="alerta alerta-erro">Algumas partes deste site (busca, Watchlist, carrossel e tema) precisam de JavaScript ativado.</p></noscript>

<?php if ($area === 'admin'): ?>
  <!-- ===== Cabeçalho do painel (área restrita) ===== -->
  <header class="cabecalho" id="cabecalho">
    <div class="cabecalho-interno">
      <!-- No mobile o ☰ abre o menu do painel (#menu-admin); no desktop ele vira barra lateral -->
      <button class="botao-menu botao-menu-admin" id="botao-menu" type="button" aria-controls="menu-admin" aria-expanded="false">
        <i class="bi bi-list" aria-hidden="true"></i>
        <span class="visualmente-oculto">Abrir menu do painel</span>
      </button>
      <a class="logo" href="<?= $raiz ?>/index.php">
        <img src="<?= $raiz ?>/img/logo-imdb.svg" alt="IMDb Clone, ir para o site" width="64" height="32">
      </a>
      <span class="titulo-painel"><span class="titulo-painel-completo">Painel administrativo</span><span class="titulo-painel-curto">Painel</span></span>

      <div class="atalhos">
        <span class="saudacao">Olá, <?= e($admin['nome'] ?? 'Administrador') ?></span>
        <a class="botao botao-contorno botao-pequeno" href="<?= $raiz ?>/admin/sair.php" id="botao-sair">
          <i class="bi bi-box-arrow-right" aria-hidden="true"></i> Sair
        </a>
      </div>

      <button class="botao-tema" id="botao-tema" type="button" aria-pressed="false">
        <i class="bi bi-moon" aria-hidden="true"></i>
        <span class="visualmente-oculto">Ativar tema escuro</span>
      </button>
    </div>
  </header>

  <div class="painel-layout">
<?php require __DIR__ . '/menu.php'; ?>
<?php else: ?>
  <!-- ===== Cabeçalho global (igual em todas as páginas públicas) ===== -->
  <header class="cabecalho" id="cabecalho">
    <div class="cabecalho-interno">
      <!-- Botão do menu: o JS alterna .aberta em #menu-principal e aria-expanded -->
      <button class="botao-menu" id="botao-menu" type="button" aria-controls="menu-principal" aria-expanded="false">
        <i class="bi bi-list" aria-hidden="true"></i>
        <span class="botao-menu-texto">Menu</span>
        <span class="visualmente-oculto">Abrir menu</span>
      </button>

      <a class="logo" href="<?= $raiz ?>/index.php">
        <img src="<?= $raiz ?>/img/logo-imdb.svg" alt="IMDb Clone, ir para a página inicial" width="64" height="32">
      </a>

      <!-- Busca do cabeçalho: envia por GET para a página de busca -->
      <form class="busca-cabecalho" id="form-busca-cabecalho" action="<?= $raiz ?>/busca.php" method="get" role="search">
        <label class="visualmente-oculto" for="busca-tipo">Tipo de título</label>
        <select id="busca-tipo" name="tipo">
          <option value="">Todos</option>
          <option value="filme">Filmes</option>
          <option value="serie">Séries</option>
        </select>
        <label class="visualmente-oculto" for="busca-termo">Buscar títulos</label>
        <input type="search" id="busca-termo" name="q" placeholder="Buscar filmes, séries..." autocomplete="off">
        <button type="submit">
          <i class="bi bi-search" aria-hidden="true"></i>
          <span class="visualmente-oculto">Buscar</span>
        </button>
      </form>

      <div class="atalhos">
        <a class="atalho atalho-mobile" href="<?= $raiz ?>/busca.php"><i class="bi bi-search" aria-hidden="true"></i> Buscar</a>
        <a class="atalho atalho-desktop" href="<?= $raiz ?>/top250.php"<?= atual('top250') ?>>Top 250</a>
        <a class="atalho atalho-desktop" href="<?= $raiz ?>/watchlist.php"<?= atual('watchlist') ?>>
          <i class="bi bi-bookmark-plus" aria-hidden="true"></i> Watchlist
          <span class="contador" data-contador-watchlist>0</span>
        </a>
<?php if ($admin): ?>
        <a class="atalho" href="<?= $raiz ?>/admin/painel.php">Painel</a>
<?php else: ?>
        <a class="atalho" href="<?= $raiz ?>/admin/login.php"<?= atual('login') ?>>Entrar</a>
<?php endif; ?>
      </div>

      <!-- Alternar tema: o JS troca html[data-tema="escuro"] -->
      <button class="botao-tema" id="botao-tema" type="button" aria-pressed="false">
        <i class="bi bi-moon" aria-hidden="true"></i>
        <span class="visualmente-oculto">Ativar tema escuro</span>
      </button>
    </div>

<?php require __DIR__ . '/menu.php'; ?>
  </header>
<?php endif; ?>
