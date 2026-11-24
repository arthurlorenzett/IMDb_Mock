<?php

$pdo = new PDO(
    'mysql:host=database;dbname=imdb_clone;charset=utf8mb4',
    'imdb_user',
    'imdb_password',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

$titulos = json_decode(
    file_get_contents(__DIR__ . '/../data/titulos.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);

$avaliacoes = json_decode(
    file_get_contents(__DIR__ . '/../data/avaliacoes.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);

$pdo->beginTransaction();

try {

    // Preparar inserção de títulos
    $sqlTitulo = "
        INSERT INTO titulos (
            id, slug, titulo, tipo, ano, lancamento,
            duracao_min, episodios, classificacao,
            sinopse, direcao, elenco, poster, banner,
            trailer, popularidade, destaque, criado_em
        ) VALUES (
            :id, :slug, :titulo, :tipo, :ano, :lancamento,
            :duracao_min, :episodios, :classificacao,
            :sinopse, :direcao, :elenco, :poster, :banner,
            :trailer, :popularidade, :destaque, :criado_em
        )
    ";

    $stmtTitulo = $pdo->prepare($sqlTitulo);

    // Preparar inserção de gêneros
    $stmtGenero = $pdo->prepare(
        "INSERT INTO generos (nome)
         VALUES (?)
         ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)"
    );

    $stmtRelacionamento = $pdo->prepare(
        "INSERT INTO titulo_genero (titulo_id, genero_id)
         VALUES (?, ?)"
    );

    foreach ($titulos as $titulo) {

        $stmtTitulo->execute([
            'id' => $titulo['id'],
            'slug' => $titulo['slug'],
            'titulo' => $titulo['titulo'],
            'tipo' => $titulo['tipo'],
            'ano' => $titulo['ano'],
            'lancamento' => $titulo['lancamento'],
            'duracao_min' => $titulo['duracao_min'],
            'episodios' => $titulo['episodios'],
            'classificacao' => $titulo['classificacao'],
            'sinopse' => $titulo['sinopse'],
            'direcao' => $titulo['direcao'],
            'elenco' => implode(', ', $titulo['elenco']),
            'poster' => $titulo['poster'],
            'banner' => $titulo['banner'],
            'trailer' => $titulo['trailer'],
            'popularidade' => $titulo['popularidade'],
            'destaque' => (int) $titulo['destaque'],
            'criado_em' => str_replace('T', ' ', $titulo['criado_em'])
        ]);

        foreach ($titulo['generos'] as $genero) {

            $stmtGenero->execute([$genero]);

            $generoId = $pdo->lastInsertId();

            $stmtRelacionamento->execute([
                $titulo['id'],
                $generoId
            ]);
        }
    }

    // Preparar inserção de avaliações
    $sqlAvaliacao = "
        INSERT INTO avaliacoes (
            id, titulo_id, nome, email, nota,
            titulo, comentario, status, criada_em
        ) VALUES (
            :id, :titulo_id, :nome, :email, :nota,
            :titulo, :comentario, :status, :criada_em
        )
    ";

    $stmtAvaliacao = $pdo->prepare($sqlAvaliacao);

    foreach ($avaliacoes as $avaliacao) {

        $stmtAvaliacao->execute([
            'id' => $avaliacao['id'],
            'titulo_id' => $avaliacao['titulo_id'],
            'nome' => $avaliacao['nome'],
            'email' => $avaliacao['email'],
            'nota' => $avaliacao['nota'],
            'titulo' => $avaliacao['titulo'],
            'comentario' => $avaliacao['comentario'],
            'status' => $avaliacao['status'],
            'criada_em' => str_replace('T', ' ', $avaliacao['criada_em'])
        ]);
    }

    $pdo->commit();

    echo "Importação concluída!\n";
    echo "Títulos: " . count($titulos) . "\n";
    echo "Avaliações: " . count($avaliacoes) . "\n";

} catch (Throwable $erro) {

    $pdo->rollBack();

    echo "Erro na importação: " . $erro->getMessage() . "\n";

    exit(1);
}

