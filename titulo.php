<?php
/* ==========================================================================
   titulo.php — P3 Detalhe do título.
   ========================================================================== */

require __DIR__ . '/includes/config.php';

$pagina = 'titulo';
$titulo_pagina = 'Título';
$descricao = 'Ficha do título: sinopse, elenco, nota média e avaliações.';
$raiz = '.';
$area = 'publica';

require __DIR__ . '/includes/cabecalho.php';
?>

  <main id="conteudo">
    <!-- ===== Ficha do título (o JS/PHP preenche a partir do id na URL) ===== -->
    <article class="ficha" id="ficha-titulo" hidden>
      <header class="ficha-topo">
        <div>
          <h1 id="titulo-nome">Carregando título…</h1>
          <ul class="metadados">
          </ul>
        </div>
        <div class="ficha-notas">
          <div>
            <p class="rotulo-nota">Nota média</p>
            <p class="nota"><i class="bi bi-star-fill" aria-hidden="true"></i> <span class="nota-valor" id="nota-media">—</span><span class="nota-max">/10</span> <span class="nota-votos"></span></p>
          </div>
          <div>
            <p class="rotulo-nota">Sua avaliação</p>
            <button class="botao botao-contorno botao-pequeno" type="button" data-abrir-modal="modal-avaliacao">
              <i class="bi bi-star" aria-hidden="true"></i> Avaliar
            </button>
          </div>
        </div>
      </header>

      <!-- Mídia: pôster, trailer e ações principais -->
      <div class="ficha-midia">
        <img class="poster ficha-poster" src="img/sem-poster.svg" alt="" width="300" height="450">

        <!-- Trailer: imagem de capa com botão; o JS pode abrir um modal com o vídeo -->
        <figure class="trailer">
          <img src="img/sem-poster.svg" alt="" width="1280" height="720">
          <button class="trailer-play" id="botao-trailer" type="button">
            <i class="bi bi-play-circle-fill" aria-hidden="true"></i>
            <span>Trailer <span class="visualmente-oculto"></span></span>
          </button>
          <figcaption class="visualmente-oculto">Trailer do título</figcaption>
        </figure>

        <div class="ficha-acoes">
          <button class="botao botao-primario botao-grande botao-bloco botao-watchlist" type="button" aria-pressed="false">
            <i class="bi bi-plus-lg" aria-hidden="true"></i> Adicionar à Watchlist
          </button>
          <button class="botao botao-contorno botao-bloco ficha-acao-secundaria" type="button" data-abrir-trailer>
            <i class="bi bi-play-fill" aria-hidden="true"></i> Assistir trailer
          </button>
          <a class="botao botao-contorno botao-bloco ficha-acao-secundaria" href="#">
            <i class="bi bi-images" aria-hidden="true"></i> Ver foto
          </a>
        </div>
      </div>

      <!-- Gêneros, sinopse e equipe -->
      <div class="ficha-info">
        <ul class="lista-chips" aria-label="Gêneros">
        </ul>
        <p class="sinopse" id="titulo-sinopse"></p>
        <dl class="ficha-equipe">
          <div>
            <dt>Direção</dt>
            <dd></dd>
          </div>
          <div>
            <dt>Elenco</dt>
            <dd></dd>
          </div>
        </dl>
      </div>
    </article>

    <!-- ===== Avaliações aprovadas (RN02: só aparecem depois da moderação) ===== -->
    <section class="avaliacoes" id="avaliacoes" aria-labelledby="titulo-avaliacoes" hidden>
      <div class="secao-topo">
        <h2 class="titulo-secao" id="titulo-avaliacoes">Avaliações de usuários <span class="nota-votos"></span></h2>
        <button class="botao botao-contorno" type="button" id="avaliar" data-abrir-modal="modal-avaliacao">
          <i class="bi bi-plus-lg" aria-hidden="true"></i> Escrever avaliação
        </button>
      </div>
      <div class="grade-avaliacoes" id="lista-avaliacoes">
      </div>
    </section>

    <!-- ===== Modal: escrever avaliação (POST na Parte 3) ===== -->
    <dialog class="modal" id="modal-avaliacao" aria-labelledby="titulo-modal-avaliacao">
      <form id="form-avaliacao" action="#" method="post">
        <div class="modal-topo">
          <h2 id="titulo-modal-avaliacao">Avaliar</h2>
          <button class="botao botao-contorno botao-icone" type="button" data-fechar-modal>
            <i class="bi bi-x-lg" aria-hidden="true"></i>
            <span class="visualmente-oculto">Fechar</span>
          </button>
        </div>

        <input type="hidden" name="titulo_id" value="">

        <div class="modal-corpo">
          <!-- Nota de 1 a 10 com estrelas (radios em ordem invertida, ver componentes.css) -->
          <fieldset class="campo">
            <legend>Nota <span class="obrigatorio" aria-hidden="true">*</span></legend>
            <div class="estrelas">
                <input type="radio" id="nota-10" name="nota" value="10" required>
                <label for="nota-10"><i class="bi bi-star-fill" aria-hidden="true"></i><span class="visualmente-oculto">10 de 10</span></label>
                <input type="radio" id="nota-9" name="nota" value="9" required>
                <label for="nota-9"><i class="bi bi-star-fill" aria-hidden="true"></i><span class="visualmente-oculto">9 de 10</span></label>
                <input type="radio" id="nota-8" name="nota" value="8" required>
                <label for="nota-8"><i class="bi bi-star-fill" aria-hidden="true"></i><span class="visualmente-oculto">8 de 10</span></label>
                <input type="radio" id="nota-7" name="nota" value="7" required>
                <label for="nota-7"><i class="bi bi-star-fill" aria-hidden="true"></i><span class="visualmente-oculto">7 de 10</span></label>
                <input type="radio" id="nota-6" name="nota" value="6" required>
                <label for="nota-6"><i class="bi bi-star-fill" aria-hidden="true"></i><span class="visualmente-oculto">6 de 10</span></label>
                <input type="radio" id="nota-5" name="nota" value="5" required>
                <label for="nota-5"><i class="bi bi-star-fill" aria-hidden="true"></i><span class="visualmente-oculto">5 de 10</span></label>
                <input type="radio" id="nota-4" name="nota" value="4" required>
                <label for="nota-4"><i class="bi bi-star-fill" aria-hidden="true"></i><span class="visualmente-oculto">4 de 10</span></label>
                <input type="radio" id="nota-3" name="nota" value="3" required>
                <label for="nota-3"><i class="bi bi-star-fill" aria-hidden="true"></i><span class="visualmente-oculto">3 de 10</span></label>
                <input type="radio" id="nota-2" name="nota" value="2" required>
                <label for="nota-2"><i class="bi bi-star-fill" aria-hidden="true"></i><span class="visualmente-oculto">2 de 10</span></label>
                <input type="radio" id="nota-1" name="nota" value="1" required>
                <label for="nota-1"><i class="bi bi-star-fill" aria-hidden="true"></i><span class="visualmente-oculto">1 de 10</span></label>
            </div>
            <p class="mensagem-erro" id="erro-nota" aria-live="polite"></p>
          </fieldset>

          <div class="campos-linha">
            <div class="campo">
              <label class="rotulo" for="avaliacao-nome">Nome <span class="obrigatorio" aria-hidden="true">*</span></label>
              <input class="controle" type="text" id="avaliacao-nome" name="nome" required minlength="2" maxlength="60" autocomplete="name" aria-describedby="erro-avaliacao-nome">
              <p class="mensagem-erro" id="erro-avaliacao-nome" aria-live="polite"></p>
            </div>
            <div class="campo">
              <label class="rotulo" for="avaliacao-email">E-mail <span class="obrigatorio" aria-hidden="true">*</span></label>
              <input class="controle" type="email" id="avaliacao-email" name="email" required maxlength="120" autocomplete="email" placeholder="nome@exemplo.com" aria-describedby="erro-avaliacao-email">
              <p class="mensagem-erro" id="erro-avaliacao-email" aria-live="polite"></p>
            </div>
          </div>

          <div class="campo">
            <label class="rotulo" for="avaliacao-titulo">Título da avaliação</label>
            <input class="controle" type="text" id="avaliacao-titulo" name="titulo" maxlength="80">
          </div>

          <div class="campo">
            <label class="rotulo" for="avaliacao-comentario">Comentário <span class="obrigatorio" aria-hidden="true">*</span></label>
            <textarea class="controle" id="avaliacao-comentario" name="comentario" required minlength="10" maxlength="600" aria-describedby="dica-comentario erro-avaliacao-comentario"></textarea>
            <p class="dica" id="dica-comentario">Até 600 caracteres.</p>
            <p class="mensagem-erro" id="erro-avaliacao-comentario" aria-live="polite"></p>
          </div>

          <!-- Mensagem geral do envio: o JS mostra (remove hidden) e troca a classe/texto -->
          <p class="alerta alerta-erro" id="alerta-avaliacao" role="alert" hidden>
            <i class="bi bi-x-circle" aria-hidden="true"></i>
            <span>Este e-mail já avaliou o título.</span>
          </p>
        </div>

        <div class="modal-acoes">
          <button class="botao botao-contorno" type="button" data-fechar-modal>Cancelar</button>
          <button class="botao botao-primario" type="submit">Enviar avaliação</button>
        </div>
      </form>
    </dialog>

    <!-- ===== Modal: trailer (o JS preenche a cena e o link para o vídeo no IMDb) ===== -->
    <dialog class="modal modal-trailer" id="modal-trailer" aria-labelledby="titulo-modal-trailer">
      <div class="modal-topo">
        <h2 id="titulo-modal-trailer">Trailer</h2>
        <button class="botao botao-contorno botao-icone" type="button" data-fechar-modal>
          <i class="bi bi-x-lg" aria-hidden="true"></i>
          <span class="visualmente-oculto">Fechar</span>
        </button>
      </div>
      <div class="modal-corpo">
        <img id="trailer-imagem" src="img/sem-poster.svg" alt="" width="1280" height="720">
        <p class="modal-texto" id="trailer-texto">O vídeo do trailer abre no site do IMDb, em uma nova aba.</p>
      </div>
      <div class="modal-acoes">
        <a class="botao botao-primario" id="trailer-link" href="#" target="_blank" rel="noopener" hidden>
          <i class="bi bi-play-fill" aria-hidden="true"></i> Abrir trailer
        </a>
        <button class="botao botao-contorno" type="button" data-fechar-modal>Fechar</button>
      </div>
    </dialog>
  </main>

<?php require __DIR__ . '/includes/rodape.php'; ?>
