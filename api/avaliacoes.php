<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/config.php';

try {
    $sql = "
        SELECT
            id,
            titulo_id,
            nome,
            nota,
            titulo,
            comentario,
            status,
            criada_em
        FROM avaliacoes
        WHERE status = 'aprovada'
        ORDER BY id
    ";

    $stmt = $pdo->query($sql);

    $avaliacoes = $stmt->fetchAll();

    foreach ($avaliacoes as &$avaliacao) {
        $avaliacao['id'] = (int) $avaliacao['id'];
        $avaliacao['titulo_id'] = (int) $avaliacao['titulo_id'];
        $avaliacao['nota'] = (int) $avaliacao['nota'];
    }

    unset($avaliacao);

    echo json_encode(
        $avaliacoes,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

} catch (PDOException $erro) {
    http_response_code(500);

    echo json_encode([
        'erro' => 'Não foi possível consultar as avaliações.'
    ], JSON_UNESCAPED_UNICODE);
}

