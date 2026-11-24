<?php

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['erro' => 'Método não permitido.']));
}

$dados = json_decode(file_get_contents('php://input'), true);

if (!is_array($dados)) {
    http_response_code(400);
    exit(json_encode(['erro' => 'Dados inválidos.']));
}

$tituloId = filter_var($dados['titulo_id'] ?? null, FILTER_VALIDATE_INT);
$nome = trim($dados['nome'] ?? '');
$email = trim($dados['email'] ?? '');
$nota = filter_var($dados['nota'] ?? null, FILTER_VALIDATE_INT);
$titulo = trim($dados['titulo'] ?? '');
$comentario = trim($dados['comentario'] ?? '');

if (
    !$tituloId || $tituloId <= 0 ||
    $nome === '' ||
    strlen($nome) > 150 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 254 ||
    $nota === false || $nota < 1 || $nota > 10 ||
    strlen($titulo) > 150 ||
    $comentario === ''
) {
    http_response_code(400);
    exit(json_encode([
        'erro' => 'Preencha corretamente os campos da avaliação.'
    ], JSON_UNESCAPED_UNICODE));
}

try {
    $sql = "
        INSERT INTO avaliacoes (
            titulo_id,
            nome,
            email,
            nota,
            titulo,
            comentario,
            status,
            criada_em
        ) VALUES (
            :titulo_id,
            :nome,
            :email,
            :nota,
            :titulo,
            :comentario,
            'pendente',
            NOW()
        )
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'titulo_id' => $tituloId,
        'nome' => $nome,
        'email' => $email,
        'nota' => $nota,
        'titulo' => $titulo,
        'comentario' => $comentario
    ]);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Avaliação enviada para moderação.'
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $erro) {
    if ($erro->getCode() === '23000') {
        http_response_code(409);

        echo json_encode([
            'erro' => 'Este e-mail já enviou uma avaliação para este título.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    http_response_code(500);

    echo json_encode([
        'erro' => 'Não foi possível enviar sua avaliação.'
    ], JSON_UNESCAPED_UNICODE);
}

