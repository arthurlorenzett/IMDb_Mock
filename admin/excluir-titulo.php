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

if (!$id || $id <= 0) {
    http_response_code(400);
    exit(json_encode(['erro' => 'ID do título inválido.']));
}

$sql = "DELETE FROM titulos WHERE id = :id";

$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);

if ($stmt->rowCount() === 0) {
    http_response_code(404);
    exit(json_encode(['erro' => 'Título não encontrado.']));
}

echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Título excluído com sucesso.'
]);
