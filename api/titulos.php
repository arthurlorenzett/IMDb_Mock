<?php
/* ==========================================================================
   api/titulos.php — GET: todos os títulos em JSON, no mesmo formato de
   data/titulos.json. É o que js/nucleo/dados.js lê com dados.fonte = "api".
   ========================================================================== */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/config.php';

try {
    responder_json(listar_titulos());
} catch (PDOException $erro) {
    error_log($erro->getMessage());
    responder_json(['erro' => 'Não foi possível consultar os títulos.'], 500);
}
