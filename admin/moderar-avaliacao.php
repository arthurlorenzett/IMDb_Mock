<?php

require_once __DIR__ . '/../includes/verificar-sessao.php';
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['erro' => 'Método não permitido.']));
}

$dados = json_decode(file_get_contents('php://input'), true);

$id = filter_var($dados['id'] ?? null, FILTER_VALIDATE_INT);
$status = $dados['status'] ?? '';

if (!$id || !in_array($status, ['aprovada', 'rejeitada'], true)) {
    http_response_code(400);
    exit(json_encode(['erro' => 'Dados inválidos.']));
}

$sql = "
    UPDATE avaliacoes
    SET status = :status
    WHERE id = :id
      AND status = 'pendente'
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    'status' => $status,
    'id' => $id
]);

if ($stmt->rowCount() === 0) {
    http_response_code(404);
    exit(json_encode(['erro' => 'Avaliação não encontrada ou já moderada.']));
}

echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Avaliação moderada com sucesso.'
]);
