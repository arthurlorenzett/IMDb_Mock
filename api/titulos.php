<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/config.php';

try {
    $sql = "
        SELECT
            t.id,
            t.slug,
            t.titulo,
            t.tipo,
            t.ano,
            t.lancamento,
            t.duracao_min,
            t.episodios,
            t.classificacao,
            t.sinopse,
            t.direcao,
            t.elenco,
            t.poster,
            t.banner,
            t.trailer,
            t.popularidade,
            t.destaque,
            t.criado_em,
            GROUP_CONCAT(g.nome ORDER BY g.nome SEPARATOR '|||') AS generos
        FROM titulos t
        LEFT JOIN titulo_genero tg ON tg.titulo_id = t.id
        LEFT JOIN generos g ON g.id = tg.genero_id
        GROUP BY t.id
        ORDER BY t.id
    ";

    $stmt = $pdo->query($sql);

    $titulos = $stmt->fetchAll();

    foreach ($titulos as &$titulo) {
        $titulo['generos'] = $titulo['generos']
            ? explode('|||', $titulo['generos'])
            : [];

        $titulo['elenco'] = $titulo['elenco']
            ? explode(', ', $titulo['elenco'])
            : [];

        $titulo['ano'] = (int) $titulo['ano'];
        $titulo['duracao_min'] = $titulo['duracao_min'] !== null
            ? (int) $titulo['duracao_min']
            : null;

        $titulo['episodios'] = $titulo['episodios'] !== null
            ? (int) $titulo['episodios']
            : null;

        $titulo['popularidade'] = (float) $titulo['popularidade'];
        $titulo['destaque'] = (bool) $titulo['destaque'];
    }

    unset($titulo);

    echo json_encode(
        $titulos,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

} catch (PDOException $erro) {
    http_response_code(500);

    echo json_encode([
        'erro' => 'Não foi possível consultar os títulos.'
    ], JSON_UNESCAPED_UNICODE);
}

