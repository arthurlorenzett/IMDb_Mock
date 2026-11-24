<?php
/* ==========================================================================
   admin/moderar-avaliacao.php — POST (JSON, só com login): aprova ou
   rejeita uma avaliação pendente (RN02). Só as aprovadas entram na média.
   ========================================================================== */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/verificar-sessao.php';

$dados = ler_json();
$id = filter_var($dados['id'] ?? null, FILTER_VALIDATE_INT);
$status = texto($dados['status'] ?? '');

if (!$id || !rn02_status_moderacao_valido($status)) {
    responder_json(['erro' => 'Dados inválidos.'], 400);
}

if (!moderar_avaliacao($id, $status)) {
    responder_json(['erro' => 'Avaliação não encontrada ou já moderada.'], 404);
}

responder_json([
    'sucesso'  => true,
    'mensagem' => 'Avaliação moderada com sucesso.'
]);
