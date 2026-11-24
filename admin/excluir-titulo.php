<?php
/* ==========================================================================
   admin/excluir-titulo.php — POST (JSON, só com login): exclui um título.
   As avaliações e os gêneros dele saem junto (ON DELETE CASCADE).
   ========================================================================== */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/verificar-sessao.php';

$dados = ler_json();
$id = filter_var($dados['id'] ?? null, FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    responder_json(['erro' => 'ID do título inválido.'], 400);
}

if (!excluir_titulo($id)) {
    responder_json(['erro' => 'Título não encontrado.'], 404);
}

responder_json([
    'sucesso'  => true,
    'mensagem' => 'Título excluído com sucesso.'
]);
