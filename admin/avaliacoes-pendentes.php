<?php
/* ==========================================================================
   admin/avaliacoes-pendentes.php — GET (JSON, só com login): avaliações
   enviadas pelo formulário público que aguardam moderação (RN02).
   ========================================================================== */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/verificar-sessao.php';

responder_json(listar_pendentes());
