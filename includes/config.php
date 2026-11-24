<?php
/* ==========================================================================
   config.php — incluído no topo de todas as páginas e endpoints.
   Abre a sessão, conecta ao MySQL/MariaDB com PDO e carrega as funções de
   dados (dados.php) e de regras/validação (funcoes.php).

   De onde vêm as credenciais, nesta ordem:
     1. includes/config.local.php, se existir (fica fora do Git);
     2. variáveis de ambiente DB_HOST, DB_NAME, DB_USER, DB_PASS (Docker);
     3. padrão do XAMPP: 127.0.0.1, banco imdb_clone, usuário root sem senha.
   ========================================================================== */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
date_default_timezone_set('America/Sao_Paulo');

// Pasta raiz do projeto no disco (para ler data/site.json)
define('RAIZ_PROJETO', dirname(__DIR__));

$local = __DIR__ . '/config.local.php';
$cfg = file_exists($local) ? require $local : [];

$host    = $cfg['host']    ?? (getenv('DB_HOST') ?: '127.0.0.1');
$porta   = $cfg['porta']   ?? (getenv('DB_PORT') ?: '3306');
$banco   = $cfg['banco']   ?? (getenv('DB_NAME') ?: 'imdb_clone');
$usuario = $cfg['usuario'] ?? (getenv('DB_USER') ?: 'root');
$senha   = $cfg['senha']   ?? (getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$porta;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $erro) {
    error_log($erro->getMessage());
    http_response_code(500);

    // Endpoints JSON recebem o erro em JSON; páginas recebem uma mensagem legível
    $resposta_json = false;
    foreach (headers_list() as $cabecalho) {
        if (stripos($cabecalho, 'application/json') !== false) {
            $resposta_json = true;
        }
    }
    if ($resposta_json) {
        exit(json_encode(['erro' => 'Banco de dados indisponível.'], JSON_UNESCAPED_UNICODE));
    }
    exit('<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Erro · IMDb Clone</title></head>'
        . '<body style="font-family: sans-serif; max-width: 40rem; margin: 3rem auto; padding: 0 1rem">'
        . '<h1>Não foi possível conectar ao banco de dados</h1>'
        . '<p>Confira se o MySQL está ligado e se o banco <code>imdb_clone</code> foi criado com o arquivo '
        . '<code>db/seed.sql</code> (veja o README).</p></body></html>');
}

// Conexão usada pelas funções de dados.php
function conexao(): PDO
{
    global $pdo;
    return $pdo;
}

require_once __DIR__ . '/funcoes.php';
require_once __DIR__ . '/dados.php';
