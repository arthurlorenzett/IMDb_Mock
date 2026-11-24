<?php
/* ==========================================================================
   painel.php — P7 Painel administrativo (área restrita).
   Sem login, o PHP redireciona para login.php antes de mostrar qualquer coisa.
   As tabelas são preenchidas pelo js/paginas/painel.js com os dados de
   api/titulos.php e admin/avaliacoes-pendentes.php (registros do formulário
   público); excluir e moderar vão por POST para os endpoints do painel.
   ========================================================================== */

require __DIR__ . '/../includes/config.php';

$admin = exigir_login();

$pagina = 'painel';
$titulo_pagina = 'Painel';
$descricao = 'Painel administrativo do IMDb Clone: títulos e avaliações.';
$raiz = '..';
$area = 'admin';

require __DIR__ . '/../includes/cabecalho.php';
?>

    <main id="conteudo">
      <h1 class="visualmente-oculto">Painel administrativo</h1>

      <!-- Mensagem de retorno (PHP na Parte 3); o JS pode esconder após alguns segundos -->
      <p class="alerta alerta-sucesso" id="alerta-painel" role="status" hidden>
        <i class="bi bi-check-circle" aria-hidden="true"></i>
        <span>Título salvo com sucesso.</span>
      </p>

      <!-- ===== CRUD de títulos ===== -->
      <section class="secao-painel" id="titulos" aria-labelledby="titulo-titulos">
        <div class="secao-topo">
          <h2 class="titulo-secao" id="titulo-titulos">Títulos</h2>
          <div class="secao-topo-acoes">
            <label class="visualmente-oculto" for="filtro-titulos">Filtrar por título</label>
            <input class="controle" type="search" id="filtro-titulos" placeholder="Filtrar por título...">
            <a class="botao botao-primario" href="titulo-form.php"><i class="bi bi-plus-lg" aria-hidden="true"></i> Novo título</a>
          </div>
        </div>
        <div class="tabela-rolagem">
          <table class="tabela" id="tabela-titulos">
            <caption class="visualmente-oculto">Títulos cadastrados</caption>
            <thead>
              <tr>
                <th scope="col" class="numero">ID</th>
                <th scope="col">Pôster</th>
                <th scope="col">Título</th>
                <th scope="col" class="numero">Ano</th>
                <th scope="col" class="numero">Nota</th>
                <th scope="col" class="numero">Aval.</th>
                <th scope="col">Ações</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </section>

      <!-- ===== Moderação de avaliações (RN02) ===== -->
      <section class="secao-painel" id="avaliacoes" aria-labelledby="titulo-pendentes">
        <h2 class="titulo-secao" id="titulo-pendentes">Avaliações pendentes <span class="nota-votos"></span></h2>
        <div class="tabela-rolagem">
          <table class="tabela" id="tabela-pendentes">
            <caption class="visualmente-oculto">Avaliações aguardando moderação</caption>
            <thead>
              <tr>
                <th scope="col">Título</th>
                <th scope="col">Autor (e-mail)</th>
                <th scope="col" class="numero">Nota</th>
                <th scope="col">Comentário</th>
                <th scope="col">Moderação</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </section>

      <!-- ===== Modal: confirmar exclusão ===== -->
      <dialog class="modal modal-pequeno" id="modal-excluir" aria-labelledby="titulo-modal-excluir">
        <h2 id="titulo-modal-excluir">Excluir título?</h2>
        <p class="modal-texto">“<span id="nome-excluir">Título selecionado</span>” e as avaliações dele serão removidos.</p>
        <div class="modal-acoes">
          <button class="botao botao-contorno" type="button" data-fechar-modal>Cancelar</button>
          <button class="botao botao-perigo" type="button" id="confirmar-excluir">Excluir</button>
        </div>
      </dialog>
    </main>

<?php require __DIR__ . '/../includes/rodape.php'; ?>
