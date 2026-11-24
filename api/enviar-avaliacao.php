<?php
/* ==========================================================================
   api/enviar-avaliacao.php — POST (JSON) do modal "Avaliar" da página do
   título. Valida no servidor (mesmos limites do formulário), aplica a RN01
   (um e-mail por título) e grava como pendente (RN02).
   Erros voltam por campo, para o JS mostrar ao lado de cada um sem apagar
   o que foi digitado.
   ========================================================================== */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/config.php';

[$avaliacao, $erros] = validar_avaliacao(ler_json());

if ($avaliacao['titulo_id'] <= 0 || !titulo_existe($avaliacao['titulo_id'])) {
    responder_json(['erro' => 'Título não encontrado.'], 404);
}

if ($erros) {
    responder_json(['erro' => mensagem('corrija'), 'erros' => $erros], 422);
}

// RN01: confere antes de gravar; o UNIQUE do banco garante mesmo com dois envios ao mesmo tempo
if (!rn01_pode_avaliar($avaliacao['titulo_id'], $avaliacao['email']) || !inserir_avaliacao($avaliacao)) {
    responder_json(['erro' => rn01_mensagem(), 'erros' => ['email' => rn01_mensagem()]], 409);
}

responder_json([
    'sucesso'  => true,
    'mensagem' => site()['regras']['RN02']['mensagem']
]);
