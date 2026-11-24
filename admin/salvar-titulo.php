<?php

require_once __DIR__ . '/../includes/verificar-sessao.php';
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json; charset=utf-8');

function gerarSlug(string $texto): string
{
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
    $texto = strtolower($texto);
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);

    return trim($texto, '-');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['erro' => 'Método não permitido.']));
}

$dados = json_decode(file_get_contents('php://input'), true);

if (!is_array($dados)) {
    http_response_code(400);
    exit(json_encode(['erro' => 'Dados inválidos.']));
}

$id = !empty($dados['id'])
    ? filter_var($dados['id'], FILTER_VALIDATE_INT)
    : null;

$titulo = trim($dados['titulo'] ?? '');
$slug = gerarSlug($titulo);
$tipo = $dados['tipo'] ?? '';
$ano = filter_var($dados['ano'] ?? null, FILTER_VALIDATE_INT);
$classificacao = $dados['classificacao'] ?? '';
$generos = $dados['generos'] ?? [];

if (
    ($dados['id'] ?? null) !== null &&
    $dados['id'] !== '' &&
    (!$id || $id <= 0)
) {
    http_response_code(400);
    exit(json_encode(['erro' => 'ID inválido.']));
}

if (
    $titulo === '' ||
    !in_array($tipo, ['filme', 'serie'], true) ||
    !$ano ||
    $ano < 1888 ||
    $classificacao === '' ||
    !is_array($generos) ||
    count($generos) === 0
) {
    http_response_code(400);
    exit(json_encode(['erro' => 'Preencha corretamente os campos obrigatórios.']));
}

try {
    $pdo->beginTransaction();

    $campos = [
        'slug' => $slug,
        'titulo' => $titulo,
        'tipo' => $tipo,
        'ano' => $ano,
        'lancamento' => $dados['lancamento'] ?: null,
        'duracao_min' => $dados['duracao_min'] ?: null,
        'episodios' => $dados['episodios'] ?? null,
        'classificacao' => $classificacao,
        'sinopse' => trim($dados['sinopse'] ?? ''),
        'direcao' => trim($dados['direcao'] ?? ''),
        'elenco' => json_encode($dados['elenco'] ?? [], JSON_UNESCAPED_UNICODE),
        'poster' => $dados['poster'] ?: null,
        'banner' => $dados['banner'] ?: null,
        'trailer' => $dados['trailer'] ?: null,
        'popularidade' => $dados['popularidade'] ?? 50,
        'destaque' => !empty($dados['destaque']) ? 1 : 0,
    ];

    if ($id) {
        $sql = "
            UPDATE titulos SET
                slug = :slug,
                titulo = :titulo,
                tipo = :tipo,
                ano = :ano,
                lancamento = :lancamento,
                duracao_min = :duracao_min,
                episodios = :episodios,
                classificacao = :classificacao,
                sinopse = :sinopse,
                direcao = :direcao,
                elenco = :elenco,
                poster = :poster,
                banner = :banner,
                trailer = :trailer,
                popularidade = :popularidade,
                destaque = :destaque
            WHERE id = :id
        ";

        $campos['id'] = $id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($campos);

        if ($stmt->rowCount() === 0) {
            $verificar = $pdo->prepare("SELECT id FROM titulos WHERE id = ?");
            $verificar->execute([$id]);

            if (!$verificar->fetch()) {
                throw new RuntimeException('Título não encontrado.');
            }
        }

    } else {
        $campos['criado_em'] = date('Y-m-d H:i:s');

        $sql = "
            INSERT INTO titulos (
                slug, titulo, tipo, ano, lancamento, duracao_min,
                episodios, classificacao, sinopse, direcao, elenco,
                poster, banner, trailer, popularidade, destaque, criado_em
            ) VALUES (
                :slug, :titulo, :tipo, :ano, :lancamento, :duracao_min,
                :episodios, :classificacao, :sinopse, :direcao, :elenco,
                :poster, :banner, :trailer, :popularidade, :destaque, :criado_em
            )
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($campos);

        $id = (int) $pdo->lastInsertId();
    }

    // Atualiza os gêneros vinculados ao título
    $stmt = $pdo->prepare(
        "DELETE FROM titulo_genero WHERE titulo_id = ?"
    );
    $stmt->execute([$id]);

    $stmtGenero = $pdo->prepare("
        INSERT INTO titulo_genero (titulo_id, genero_id)
        SELECT ?, id FROM generos WHERE nome = ?
    ");

    foreach ($generos as $genero) {
        $stmtGenero->execute([$id, $genero]);

        if ($stmtGenero->rowCount() === 0) {
            throw new RuntimeException("Gênero inválido: $genero");
        }
    }

    $pdo->commit();

    echo json_encode([
        'sucesso' => true,
        'id' => $id,
        'mensagem' => 'Título salvo com sucesso.'
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(400);

    echo json_encode([
        'erro' => $erro instanceof PDOException
            ? 'Não foi possível salvar o título. Verifique os dados e tente novamente.'
            : $erro->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
