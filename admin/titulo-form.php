<?php
/* ==========================================================================
   titulo-form.php — P8 Cadastro (sem ?id) e edição (?id=) de título
   (área restrita). Sem login, o PHP redireciona para login.php.
   O js/paginas/titulo-form.js preenche o formulário na edição, valida e
   envia por POST (JSON) para admin/salvar-titulo.php, que valida de novo.
   ========================================================================== */

require __DIR__ . '/../includes/config.php';

$admin = exigir_login();
$editando = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) > 0;

$pagina = 'titulo-form';
$titulo_pagina = $editando ? 'Editar título' : 'Novo título';
$descricao = 'Cadastro e edição de títulos no painel do IMDb Clone.';
$raiz = '..';
$area = 'admin';

require __DIR__ . '/../includes/cabecalho.php';
?>

    <main id="conteudo">
      <!-- Trilha de navegação -->
      <nav class="trilha" aria-label="Trilha de navegação">
        <ol>
          <li><a href="painel.php#titulos">Títulos</a></li>
          <li aria-current="page">Novo título</li>
        </ol>
      </nav>

      <header class="cabecalho-pagina cabecalho-form">
        <!-- Na edição (?id=) o PHP troca para "Editar título" e preenche os campos -->
        <h1 id="titulo-form-cabecalho">Novo título</h1>
        <p class="subtitulo"><span class="obrigatorio" aria-hidden="true">*</span> campos obrigatórios</p>
      </header>

      <!-- Erro devolvido pelo PHP (salvar-titulo.php); o JS mostra o texto aqui -->
      <p class="alerta alerta-erro" id="alerta-form" role="alert" hidden>
        <i class="bi bi-x-circle" aria-hidden="true"></i>
        <span></span>
      </p>

      <!-- ===== Formulário de título (validação no navegador e no PHP) ===== -->
      <form class="form-titulo" id="form-titulo" action="salvar-titulo.php" method="post">
        <input type="hidden" name="id" value="">

        <div class="campo area-nome">
          <label class="rotulo" for="titulo-nome-campo">Título <span class="obrigatorio" aria-hidden="true">*</span></label>
          <input class="controle" type="text" id="titulo-nome-campo" name="titulo" required minlength="2" maxlength="120" placeholder="Ex.: Interestelar" aria-describedby="erro-titulo-nome">
          <p class="mensagem-erro" id="erro-titulo-nome" aria-live="polite"></p>
        </div>

        <fieldset class="campo area-tipo">
          <legend>Tipo <span class="obrigatorio" aria-hidden="true">*</span></legend>
          <div class="opcoes">
            <label class="opcao"><input type="radio" name="tipo" value="filme" required checked> Filme</label>
            <label class="opcao"><input type="radio" name="tipo" value="serie"> Série</label>
          </div>
        </fieldset>

        <div class="campos-linha area-numeros">
          <div class="campo">
            <label class="rotulo" for="titulo-ano">Ano <span class="obrigatorio" aria-hidden="true">*</span></label>
            <input class="controle" type="number" id="titulo-ano" name="ano" required min="1888" max="2031" step="1" placeholder="2024" aria-describedby="erro-titulo-ano">
            <p class="mensagem-erro" id="erro-titulo-ano" aria-live="polite"></p>
          </div>
          <div class="campo">
            <label class="rotulo" for="titulo-lancamento">Lançamento</label>
            <input class="controle" type="date" id="titulo-lancamento" name="lancamento" min="1888-01-01" max="2031-12-31" aria-describedby="erro-titulo-lancamento">
            <p class="mensagem-erro" id="erro-titulo-lancamento" aria-live="polite"></p>
          </div>
          <div class="campo">
            <label class="rotulo" for="titulo-duracao">Duração (min)</label>
            <input class="controle" type="number" id="titulo-duracao" name="duracao" min="1" max="999" step="1" placeholder="125" aria-describedby="erro-titulo-duracao">
            <p class="mensagem-erro" id="erro-titulo-duracao" aria-live="polite"></p>
          </div>
        </div>

        <div class="campo area-classificacao">
          <label class="rotulo" for="titulo-classificacao">Classificação <span class="obrigatorio" aria-hidden="true">*</span></label>
          <select class="controle" id="titulo-classificacao" name="classificacao" required aria-describedby="erro-titulo-classificacao">
            <option value="">Selecione</option>
            <option value="L">Livre</option>
            <option value="10">10 anos</option>
            <option value="12">12 anos</option>
            <option value="14">14 anos</option>
            <option value="16">16 anos</option>
            <option value="18">18 anos</option>
          </select>
          <p class="mensagem-erro" id="erro-titulo-classificacao" aria-live="polite"></p>
        </div>

        <fieldset class="campo area-generos">
          <legend>Gêneros <span class="obrigatorio" aria-hidden="true">*</span></legend>
          <div class="opcoes">
          </div>
          <!-- "Pelo menos um gênero" não existe em HTML puro: o JS valida e escreve aqui -->
          <p class="mensagem-erro" id="erro-titulo-generos" aria-live="polite"></p>
        </fieldset>

        <div class="campo area-poster">
          <label class="rotulo" for="titulo-poster">URL do pôster</label>
          <input class="controle" type="url" id="titulo-poster" name="poster" pattern="https://.*" placeholder="https://..." aria-describedby="dica-poster erro-titulo-poster">
          <p class="dica" id="dica-poster">Endereço começando com https://</p>
          <p class="mensagem-erro" id="erro-titulo-poster" aria-live="polite"></p>
        </div>

        <!-- Prévia do pôster: o JS troca o src quando a URL muda -->
        <figure class="previa-poster area-previa">
          <img class="poster" id="previa-poster" src="../img/sem-poster.svg" alt="Prévia do pôster informado" width="150" height="225">
          <figcaption class="dica">Prévia</figcaption>
        </figure>

        <div class="campo area-direcao">
          <label class="rotulo" for="titulo-direcao">Direção</label>
          <input class="controle" type="text" id="titulo-direcao" name="direcao" maxlength="120" placeholder="Nome da direção">
        </div>

        <div class="campo area-elenco">
          <label class="rotulo" for="titulo-elenco">Elenco (separado por vírgulas)</label>
          <input class="controle" type="text" id="titulo-elenco" name="elenco" maxlength="300" placeholder="Ator Um, Atriz Dois, Ator Três">
        </div>

        <div class="campo area-sinopse">
          <label class="rotulo" for="titulo-sinopse-campo">Sinopse <span class="obrigatorio" aria-hidden="true">*</span></label>
          <textarea class="controle" id="titulo-sinopse-campo" name="sinopse" required minlength="20" maxlength="600" placeholder="Até 600 caracteres" aria-describedby="erro-titulo-sinopse"></textarea>
          <p class="mensagem-erro" id="erro-titulo-sinopse" aria-live="polite"></p>
        </div>

        <div class="form-acoes area-acoes">
          <button class="botao botao-primario botao-grande" type="submit">Salvar</button>
          <a class="botao botao-contorno botao-grande" href="painel.php">Cancelar</a>
        </div>
      </form>
    </main>

<?php require __DIR__ . '/../includes/rodape.php'; ?>
