<?php

require_once __DIR__ . '/../includes/verificar-sessao.php';
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json; charset=utf-8');

$sql = "
    SELECT
        a.id,
        a.titulo_id,
        t.titulo AS nome_titulo,
        a.email,
        a.nota,
        a.comentario,
        a.criada_em
    FROM avaliacoes a
    INNER JOIN titulos t ON t.id = a.titulo_id
    WHERE a.status = 'pendente'
    ORDER BY a.criada_em ASC
";

$stmt = $pdo->query($sql);

echo json_encode(
    $stmt->fetchAll(),
    JSON_UNESCAPED_UNICODE
);
