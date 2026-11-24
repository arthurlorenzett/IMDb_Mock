<?php
/* ==========================================================================
   admin/salvar-titulo.php — POST (JSON, só com login): cria (sem id) ou
   atualiza (com id) um título. Valida no servidor com os mesmos limites do
   formulário e devolve os erros por campo, para o JS mostrar na página sem
   apagar o que foi digitado.
   ========================================================================== */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/verificar-sessao.php';

$dados = ler_json();

$id = null;
if (($dados['id'] ?? null) !== null && $dados['id'] !== '') {
    $id = filter_var($dados['id'], FILTER_VALIDATE_INT);
    if (!$id || $id <= 0) {
        responder_json(['erro' => 'ID inválido.'], 400);
    }
    if (!titulo_existe($id)) {
        responder_json(['erro' => 'Título não encontrado.'], 404);
    }
}

[$titulo, $erros] = validar_titulo($dados);

if ($erros) {
    responder_json(['erro' => mensagem('corrija'), 'erros' => $erros], 422);
}

try {
    $id = salvar_titulo($titulo, $id);
} catch (PDOException $erro) {
    error_log($erro->getMessage());
    responder_json(['erro' => 'Não foi possível salvar o título. Tente novamente.'], 500);
}

responder_json([
    'sucesso'  => true,
    'id'       => $id,
    'mensagem' => 'Título salvo com sucesso.'
]);
