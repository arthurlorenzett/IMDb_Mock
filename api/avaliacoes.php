<?php
/* ==========================================================================
   api/avaliacoes.php — GET: só as avaliações APROVADAS em JSON (RN02), sem o
   e-mail de quem avaliou. Pendentes e rejeitadas só aparecem no painel.
   ========================================================================== */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/config.php';

try {
    responder_json(listar_avaliacoes_aprovadas());
} catch (PDOException $erro) {
    error_log($erro->getMessage());
    responder_json(['erro' => 'Não foi possível consultar as avaliações.'], 500);
}
