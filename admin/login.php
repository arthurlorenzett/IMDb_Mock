<?php
/* ==========================================================================
   login.php — P6 Login do administrador (mostra o formulário e recebe o POST).
   POST: valida os campos, busca o administrador pelo e-mail e confere a
   senha com password_verify. Certo → abre a $_SESSION e vai para o painel.
   Errado → mensagem genérica, mantendo o e-mail digitado (3.2).
   ========================================================================== */

require_once __DIR__ . '/../includes/config.php';

// Já logado: vai direto para o painel
if (admin_logado()) {
    redirecionar('painel.php');
}

$email = '';
$erros = [];
$erro_geral = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = post_texto('email');
    $senha = (string) ($_POST['senha'] ?? '');

    if ($email === '') {
        $erros['email'] = mensagem('obrigatorio');
    } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $erros['email'] = mensagem('email');
    }
    if ($senha === '') {
        $erros['senha'] = mensagem('obrigatorio');
    }

    if (!csrf_valido()) {
        $erro_geral = 'A página ficou aberta por muito tempo. Tente entrar de novo.';
    } elseif ($erros) {
        $erro_geral = mensagem('corrija');
    } else {
        $admin = buscar_admin_por_email($email);
        if ($admin && password_verify($senha, $admin['senha_hash'])) {
            session_regenerate_id(true); // nova sessão a cada login
            $_SESSION['admin'] = [
                'id'    => (int) $admin['id'],
                'nome'  => $admin['nome'],
                'email' => $admin['email']
            ];
            redirecionar('painel.php');
        }
        // Mensagem genérica: não diz se o erro foi no e-mail ou na senha
        $erro_geral = mensagem('login_invalido');
    }
}

// Aviso de outra página (tentou abrir o painel sem login) ou do logout
$aviso = pegar_aviso();
if (!$aviso && isset($_GET['saiu'])) {
    $aviso = ['tipo' => 'sucesso', 'texto' => 'Você saiu do painel.'];
}

$pagina = 'login';
$titulo_pagina = 'Entrar';
$descricao = 'Login do administrador do IMDb Clone.';
$raiz = '..';
$area = 'publica';

require __DIR__ . '/../includes/cabecalho.php';
?>

  <main id="conteudo" class="main-centralizado">
    <!-- ===== Login do administrador (POST com $_SESSION) ===== -->
    <section class="cartao-form" aria-labelledby="titulo-login">
      <h1 id="titulo-login">Entrar</h1>
      <p class="subtitulo">Área administrativa do IMDb Clone</p>

<?php if ($aviso): ?>
      <p class="alerta alerta-<?= e($aviso['tipo']) ?>" role="status">
        <i class="bi <?= $aviso['tipo'] === 'sucesso' ? 'bi-check-circle' : 'bi-info-circle' ?>" aria-hidden="true"></i>
        <span><?= e($aviso['texto']) ?></span>
      </p>
<?php endif; ?>

      <form class="form-login" id="form-login" action="login.php" method="post">
        <?= campo_csrf() ?>
        <div class="campo">
          <label class="rotulo" for="login-email">E-mail</label>
          <input class="controle" type="email" id="login-email" name="email" value="<?= e($email) ?>" required maxlength="120" autocomplete="username" placeholder="admin@exemplo.com" aria-describedby="erro-login-email"<?= isset($erros['email']) ? ' aria-invalid="true"' : '' ?>>
          <p class="mensagem-erro" id="erro-login-email" aria-live="polite"><?= e($erros['email'] ?? '') ?></p>
        </div>

        <div class="campo">
          <label class="rotulo" for="login-senha">Senha</label>
          <div class="campo-senha">
            <!-- A senha nunca volta preenchida, nem em caso de erro -->
            <input class="controle" type="password" id="login-senha" name="senha" required autocomplete="current-password" aria-describedby="erro-login-senha"<?= isset($erros['senha']) ? ' aria-invalid="true"' : '' ?>>
            <!-- O JS alterna type="password"/"text" e o texto do botão -->
            <button class="botao botao-texto" id="mostrar-senha" type="button" aria-controls="login-senha" aria-pressed="false">Mostrar</button>
          </div>
          <p class="mensagem-erro" id="erro-login-senha" aria-live="polite"><?= e($erros['senha'] ?? '') ?></p>
        </div>

        <!-- Erro geral do login (vem do PHP) -->
        <p class="alerta alerta-erro" id="alerta-login" role="alert"<?= $erro_geral ? '' : ' hidden' ?>>
          <i class="bi bi-x-circle" aria-hidden="true"></i>
          <span><?= e($erro_geral ?: mensagem('login_invalido')) ?></span>
        </p>

        <button class="botao botao-primario botao-grande botao-bloco" type="submit">Entrar</button>
        <p class="dica">Usuário de teste: <strong>admin@exemplo.com</strong> e senha <strong>admin123</strong>.</p>
      </form>

      <a class="link-voltar" href="../index.php"><i class="bi bi-arrow-left" aria-hidden="true"></i> Voltar ao site</a>
    </section>
  </main>

<?php require __DIR__ . '/../includes/rodape.php'; ?>
