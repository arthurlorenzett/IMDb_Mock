<?php
/* ==========================================================================
   funcoes.php — funções gerais (saída segura, sessão, respostas JSON) e as
   regras do servidor: validação/sanitização dos formulários e RN01–RN03.
   As mensagens e os limites vêm do mesmo data/site.json usado pelo JS.
   ========================================================================== */

/* ------------------------------------------------------------------ geral */

// Configuração mestre do site (lida uma vez por requisição)
function site(): array
{
    static $site = null;
    if ($site === null) {
        $site = json_decode(file_get_contents(RAIZ_PROJETO . '/data/site.json'), true);
    }
    return $site;
}

// Escapa qualquer texto antes de colocar no HTML (evita XSS)
function e($texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

// Mensagem de validação do site.json, trocando {min}/{max} pelos valores
function mensagem(string $chave, array $valores = []): string
{
    $texto = site()['validacao']['mensagens'][$chave] ?? $chave;
    foreach ($valores as $nome => $valor) {
        $texto = str_replace('{' . $nome . '}', (string) $valor, $texto);
    }
    return $texto;
}

function redirecionar(string $url): void
{
    header('Location: ' . $url);
    exit;
}

// Mensagem que sobrevive a um redirecionamento (ex.: "Você saiu do painel")
function definir_aviso(string $tipo, string $texto): void
{
    $_SESSION['aviso'] = ['tipo' => $tipo, 'texto' => $texto];
}

function pegar_aviso(): ?array
{
    $aviso = $_SESSION['aviso'] ?? null;
    unset($_SESSION['aviso']);
    return $aviso;
}

/* --------------------------------------------------------- respostas JSON */

function responder_json($dados, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/* Lê o corpo JSON de um POST feito pelo JS (fetch).
   Exigir Content-Type: application/json impede que um formulário de outro
   site dispare a ação (o navegador não envia esse tipo sem permissão). */
function ler_json(): array
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responder_json(['erro' => 'Método não permitido.'], 405);
    }
    if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') === false) {
        responder_json(['erro' => 'Envie os dados em JSON.'], 415);
    }
    $dados = json_decode(file_get_contents('php://input'), true);
    if (!is_array($dados)) {
        responder_json(['erro' => 'Dados inválidos.'], 400);
    }
    return $dados;
}

/* -------------------------------------------------- formulário de login */

// Token contra envio do formulário de login a partir de outro site (CSRF)
function token_csrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function campo_csrf(): string
{
    return '<input type="hidden" name="csrf" value="' . e(token_csrf()) . '">';
}

function csrf_valido(): bool
{
    return isset($_POST['csrf']) && hash_equals(token_csrf(), (string) $_POST['csrf']);
}

// Campo de texto do POST, sem espaços nas pontas (texto fora do UTF-8 é descartado)
function post_texto(string $nome): string
{
    return texto($_POST[$nome] ?? '');
}

/* ---------------------------------------------------------- sessão admin */

function admin_logado(): ?array
{
    return $_SESSION['admin'] ?? null;
}

// Páginas do painel chamam isto no topo: sem login, volta para o login (3.3)
function exigir_login(): array
{
    $admin = admin_logado();
    if (!$admin) {
        definir_aviso('erro', 'Entre com seu e-mail e senha para acessar o painel.');
        redirecionar('login.php');
    }
    return $admin;
}

/* ------------------------------------------------------------- formatação */

function formatar_nota(?float $media): string
{
    return $media === null ? '—' : number_format($media, 1, ',', '');
}

function formatar_duracao(array $t): string
{
    if ($t['tipo'] === 'serie') {
        return $t['episodios'] ? $t['episodios'] . ' episódios' : 'Série';
    }
    if (!$t['duracao_min']) {
        return '';
    }
    $h = intdiv((int) $t['duracao_min'], 60);
    $m = (int) $t['duracao_min'] % 60;
    return $h ? ($m ? "{$h}h {$m}min" : "{$h}h") : "{$m}min";
}

// "Um Sonho de Liberdade" → "um-sonho-de-liberdade" (sem depender do iconv)
function gerar_slug(string $texto): string
{
    $texto = mb_strtolower(trim($texto), 'UTF-8');
    $texto = strtr($texto, [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ç' => 'c', 'ñ' => 'n'
    ]);
    return trim(preg_replace('/[^a-z0-9]+/', '-', $texto), '-');
}

/* --------------------------------------------- validação no servidor (3.2) */

// Texto limpo: só strings UTF-8 válidas, sem espaços nas pontas
function texto($valor): string
{
    if (!is_string($valor) && !is_int($valor) && !is_float($valor)) {
        return '';
    }
    $valor = (string) $valor;
    return mb_check_encoding($valor, 'UTF-8') ? trim($valor) : '';
}

// Confere tamanho mínimo/máximo; devolve a mensagem de erro ou ''
function erro_tamanho(string $valor, int $min, int $max, bool $obrigatorio = true): string
{
    $tamanho = mb_strlen($valor, 'UTF-8');
    if ($tamanho === 0) {
        return $obrigatorio ? mensagem('obrigatorio') : '';
    }
    if ($tamanho < $min) {
        return mensagem('curto', ['min' => $min]);
    }
    if ($tamanho > $max) {
        return mensagem('longo', ['max' => $max]);
    }
    return '';
}

// Inteiro num intervalo (filter_var); devolve [valor, erro]
function validar_inteiro($valor, int $min, int $max, bool $obrigatorio = true): array
{
    $valor = texto($valor);
    if ($valor === '') {
        return [null, $obrigatorio ? mensagem('obrigatorio') : ''];
    }
    $numero = filter_var($valor, FILTER_VALIDATE_INT);
    if ($numero === false) {
        return [null, mensagem('inteiro')];
    }
    if ($numero < $min) {
        return [null, mensagem('menor', ['min' => $min])];
    }
    if ($numero > $max) {
        return [null, mensagem('maior', ['max' => $max])];
    }
    return [$numero, ''];
}

// URL opcional que precisa começar com https://
function erro_url(string $valor): string
{
    if ($valor === '') {
        return '';
    }
    $ok = filter_var($valor, FILTER_VALIDATE_URL) !== false && str_starts_with(strtolower($valor), 'https://');
    return $ok ? '' : mensagem('url');
}

/* Avaliação enviada pelo modal da página do título (JSON do titulo.js).
   Devolve [dados limpos, erros por campo do formulário]. */
function validar_avaliacao(array $entrada): array
{
    $dados = [
        'titulo_id'  => filter_var($entrada['titulo_id'] ?? null, FILTER_VALIDATE_INT) ?: 0,
        'nome'       => texto($entrada['nome'] ?? ''),
        'email'      => texto($entrada['email'] ?? ''),
        'titulo'     => texto($entrada['titulo'] ?? ''),
        'comentario' => texto($entrada['comentario'] ?? '')
    ];
    $erros = [];

    [$dados['nota'], $erro] = validar_inteiro($entrada['nota'] ?? '', 1, 10);
    if ($erro) {
        $erros['nota'] = $erro === mensagem('obrigatorio') ? mensagem('obrigatorio_opcao') : $erro;
    }

    if ($erro = erro_tamanho($dados['nome'], 2, 60)) {
        $erros['nome'] = $erro;
    }

    if ($dados['email'] === '') {
        $erros['email'] = mensagem('obrigatorio');
    } elseif (filter_var($dados['email'], FILTER_VALIDATE_EMAIL) === false || mb_strlen($dados['email']) > 120) {
        $erros['email'] = mensagem('email');
    }

    if ($erro = erro_tamanho($dados['titulo'], 0, 80, false)) {
        $erros['titulo'] = $erro;
    }
    if ($erro = erro_tamanho($dados['comentario'], 10, 600)) {
        $erros['comentario'] = $erro;
    }

    return [$dados, $erros];
}

/* Título do formulário do painel (JSON do titulo-form.js).
   As chaves de $erros são os nomes dos campos do formulário.
   Devolve [dados limpos, erros por campo]. */
function validar_titulo(array $entrada): array
{
    $catalogo = site()['catalogo'];
    $ano_maximo = (int) date('Y') + 5;

    $elenco = is_array($entrada['elenco'] ?? null) ? $entrada['elenco'] : explode(',', texto($entrada['elenco'] ?? ''));
    $generos = is_array($entrada['generos'] ?? null) ? $entrada['generos'] : [];

    $dados = [
        'titulo'        => texto($entrada['titulo'] ?? ''),
        'tipo'          => texto($entrada['tipo'] ?? ''),
        'lancamento'    => texto($entrada['lancamento'] ?? ''),
        'classificacao' => texto($entrada['classificacao'] ?? ''),
        'sinopse'       => texto($entrada['sinopse'] ?? ''),
        'direcao'       => texto($entrada['direcao'] ?? ''),
        'elenco'        => array_values(array_filter(array_map('texto', $elenco), 'strlen')),
        'poster'        => texto($entrada['poster'] ?? ''),
        'banner'        => texto($entrada['banner'] ?? ''),
        // Só aceita gêneros da lista oficial (o HTML pode ser alterado no navegador)
        'generos'       => array_values(array_intersect(array_map('texto', $generos), $catalogo['generos'])),
        'episodios'     => filter_var($entrada['episodios'] ?? null, FILTER_VALIDATE_INT) ?: null,
        'popularidade'  => is_numeric($entrada['popularidade'] ?? null) ? max(0, min(100, (float) $entrada['popularidade'])) : 50,
        'destaque'      => !empty($entrada['destaque'])
    ];
    $erros = [];

    if ($erro = erro_tamanho($dados['titulo'], 2, 120)) {
        $erros['titulo'] = $erro;
    }
    if (!in_array($dados['tipo'], array_column($catalogo['tipos'], 'valor'), true)) {
        $erros['tipo'] = mensagem('obrigatorio_opcao');
    }

    [$dados['ano'], $erro] = validar_inteiro($entrada['ano'] ?? '', 1888, $ano_maximo);
    if ($erro) {
        $erros['ano'] = $erro;
    }

    if ($dados['lancamento'] !== '') {
        $data = DateTime::createFromFormat('!Y-m-d', $dados['lancamento']);
        if (!$data || $data->format('Y-m-d') !== $dados['lancamento']) {
            $erros['lancamento'] = mensagem('formato');
        } elseif ((int) $data->format('Y') < 1888 || (int) $data->format('Y') > $ano_maximo) {
            $erros['lancamento'] = mensagem('maior', ['max' => "{$ano_maximo}-12-31"]);
        }
    }

    [$dados['duracao_min'], $erro] = validar_inteiro($entrada['duracao_min'] ?? '', 1, 999, false);
    if ($erro) {
        $erros['duracao'] = $erro;
    }

    if (!in_array($dados['classificacao'], array_column($catalogo['classificacoes'], 'valor'), true)) {
        $erros['classificacao'] = mensagem('obrigatorio_opcao');
    }
    if (!$dados['generos']) {
        $erros['generos'] = mensagem('generos');
    }
    foreach (['poster', 'banner'] as $campo) {
        if ($erro = erro_url($dados[$campo])) {
            $erros[$campo] = $erro;
        }
    }
    if ($erro = erro_tamanho($dados['direcao'], 0, 120, false)) {
        $erros['direcao'] = $erro;
    }
    if ($erro = erro_tamanho(implode(', ', $dados['elenco']), 0, 300, false)) {
        $erros['elenco'] = $erro;
    }
    if ($erro = erro_tamanho($dados['sinopse'], 20, 600)) {
        $erros['sinopse'] = $erro;
    }

    return [$dados, $erros];
}

/* ------------------------------------------------------ regras de negócio */

// RN01: o mesmo e-mail não pode avaliar o mesmo título duas vezes (qualquer status)
function rn01_pode_avaliar(int $titulo_id, string $email): bool
{
    return !email_ja_avaliou($titulo_id, $email);
}

function rn01_mensagem(): string
{
    return site()['regras']['RN01']['mensagem'];
}

// RN02: toda avaliação nasce com o status inicial ("pendente")
function rn02_status_inicial(): string
{
    return site()['regras']['RN02']['status_inicial'];
}

// RN02: a moderação só aceita os dois status finais
function rn02_status_moderacao_valido(string $status): bool
{
    return in_array($status, ['aprovada', 'rejeitada'], true);
}

// RN03: mínimo de avaliações aprovadas e limite do ranking (site.json)
function rn03_parametros(): array
{
    $rn03 = site()['regras']['RN03'];
    return [(int) $rn03['minimo_aprovadas'], (int) $rn03['limite']];
}
